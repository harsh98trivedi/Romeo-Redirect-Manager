<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Class Romerema_Sitemap_Scanner
 * Handles XML Sitemap discovery, recursive parsing, URL batch health checks,
 * redirect chain detection, external redirects, duplicate destination tracking,
 * and admin alert notices.
 */
class Romerema_Sitemap_Scanner {

    private $option_results_key = 'romeo_sitemap_scan_results';
    private $option_dismissed_key = 'romeo_sitemap_notice_dismissed';

    public function __construct() {
        // AJAX Endpoints
        add_action( 'wp_ajax_romerema_sitemap_get_default_url', array( $this, 'ajax_get_default_url' ) );
        add_action( 'wp_ajax_romerema_sitemap_fetch_urls', array( $this, 'ajax_fetch_urls' ) );
        add_action( 'wp_ajax_romerema_sitemap_scan_batch', array( $this, 'ajax_scan_batch' ) );
        add_action( 'wp_ajax_romerema_sitemap_save_results', array( $this, 'ajax_save_results' ) );
        add_action( 'wp_ajax_romerema_sitemap_get_results', array( $this, 'ajax_get_results' ) );
        add_action( 'wp_ajax_romerema_sitemap_clear_results', array( $this, 'ajax_clear_results' ) );
        add_action( 'wp_ajax_romerema_sitemap_dismiss_notice', array( $this, 'ajax_dismiss_notice' ) );
        add_action( 'wp_ajax_romerema_sitemap_create_redirect', array( $this, 'ajax_create_redirect' ) );

        // Admin Notices
        add_action( 'admin_notices', array( $this, 'render_admin_notice' ) );
    }

    /**
     * Determine the default sitemap URL for this WordPress installation.
     */
    public static function get_default_sitemap_url() {
        // 1. Check if WordPress core sitemaps are enabled (WP 5.5+)
        if ( function_exists( 'wp_sitemaps_get_server' ) ) {
            return home_url( '/wp-sitemap.xml' );
        }

        // 2. Common SEO plugin sitemaps fallback
        return home_url( '/sitemap_index.xml' );
    }

    /**
     * Get stored scan results.
     */
    public function get_stored_results() {
        $results = get_option( $this->option_results_key, array() );
        if ( ! is_array( $results ) ) {
            $results = array();
        }
        return $results;
    }

    /**
     * Save scan results to WordPress options.
     */
    public function save_results( $data ) {
        // Reset dismissed flag whenever a new scan is saved with issues
        if ( ! empty( $data['issues_count'] ) && $data['issues_count'] > 0 ) {
            delete_option( $this->option_dismissed_key );
        }
        return update_option( $this->option_results_key, $data );
    }

    /**
     * AJAX: Get default sitemap URL
     */
    public function ajax_get_default_url() {
        check_ajax_referer( 'romerema_sitemap_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized access.', 'romeo-redirect-manager' ) );
        }

        $url = self::get_default_sitemap_url();
        wp_send_json_success( array( 'url' => $url ) );
    }

    /**
     * AJAX: Fetch and parse XML sitemap (supports recursive sitemap indexes)
     */
    public function ajax_fetch_urls() {
        check_ajax_referer( 'romerema_sitemap_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized access.', 'romeo-redirect-manager' ) );
        }

        $sitemap_url = isset( $_POST['sitemap_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['sitemap_url'] ) ) ) : '';

        if ( empty( $sitemap_url ) ) {
            $sitemap_url = self::get_default_sitemap_url();
        }

        if ( ! filter_var( $sitemap_url, FILTER_VALIDATE_URL ) ) {
            wp_send_json_error( __( 'Invalid sitemap URL provided.', 'romeo-redirect-manager' ) );
        }

        $visited_sitemaps = array();
        $urls = $this->parse_sitemap_recursive( $sitemap_url, $visited_sitemaps );

        if ( is_wp_error( $urls ) ) {
            wp_send_json_error( $urls->get_error_message() );
        }

        if ( empty( $urls ) ) {
            wp_send_json_error( __( 'No URLs found in the specified sitemap. Please verify the sitemap URL and ensure it contains valid XML.', 'romeo-redirect-manager' ) );
        }

        $urls = array_values( array_unique( $urls ) );

        wp_send_json_success( array(
            'urls'        => $urls,
            'total'       => count( $urls ),
            'sitemap_url' => $sitemap_url,
        ) );
    }

    /**
     * Recursively fetch and parse XML sitemaps and sitemap index files.
     */
    private function parse_sitemap_recursive( $url, &$visited, $depth = 0 ) {
        if ( $depth > 5 || in_array( $url, $visited, true ) ) {
            return array();
        }
        $visited[] = $url;

        $response = wp_safe_remote_get( $url, array(
            'timeout'     => 15,
            'sslverify'   => false,
            'user-agent'  => 'RomeoRedirectManager/' . ( defined( 'ROMEREMA_VERSION' ) ? ROMEREMA_VERSION : '1.6.0' ) . '; ' . home_url(),
            'headers'     => array(
                'Accept' => 'application/xml, text/xml, */*',
            ),
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        if ( $code < 200 || $code >= 300 ) {
            return new WP_Error( 'http_error', sprintf( __( 'Failed to fetch sitemap: HTTP status %d returned from %s', 'romeo-redirect-manager' ), $code, esc_url( $url ) ) );
        }

        $body = wp_remote_retrieve_body( $response );
        if ( empty( $body ) ) {
            return new WP_Error( 'empty_sitemap', sprintf( __( 'Empty response received from sitemap at %s', 'romeo-redirect-manager' ), esc_url( $url ) ) );
        }

        // Disable XML entity loader for security
        $prev_entity_loader = null;
        if ( function_exists( 'libxml_disable_entity_loader' ) && PHP_VERSION_ID < 80000 ) {
            $prev_entity_loader = @libxml_disable_entity_loader( true );
        }

        $prev_use_errors = libxml_use_internal_errors( true );
        $xml = simplexml_load_string( $body, 'SimpleXMLElement', LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING );
        libxml_clear_errors();
        libxml_use_internal_errors( $prev_use_errors );

        if ( $prev_entity_loader !== null && function_exists( 'libxml_disable_entity_loader' ) && PHP_VERSION_ID < 80000 ) {
            @libxml_disable_entity_loader( $prev_entity_loader );
        }

        if ( ! $xml ) {
            return new WP_Error( 'invalid_xml', sprintf( __( 'Could not parse XML from sitemap at %s', 'romeo-redirect-manager' ), esc_url( $url ) ) );
        }

        $extracted_urls = array();
        $root_name = strtolower( $xml->getName() );

        // 1. If this is a Sitemap Index (<sitemapindex>)
        if ( 'sitemapindex' === $root_name || isset( $xml->sitemap ) ) {
            foreach ( $xml->sitemap as $sitemap_node ) {
                $child_loc = isset( $sitemap_node->loc ) ? trim( (string) $sitemap_node->loc ) : '';
                if ( ! empty( $child_loc ) && filter_var( $child_loc, FILTER_VALIDATE_URL ) ) {
                    $child_urls = $this->parse_sitemap_recursive( $child_loc, $visited, $depth + 1 );
                    if ( is_array( $child_urls ) ) {
                        $extracted_urls = array_merge( $extracted_urls, $child_urls );
                    }
                }
            }
        }

        // 2. If this is a URL Set (<urlset>)
        if ( 'urlset' === $root_name || isset( $xml->url ) ) {
            foreach ( $xml->url as $url_node ) {
                $loc = isset( $url_node->loc ) ? trim( (string) $url_node->loc ) : '';
                if ( ! empty( $loc ) && filter_var( $loc, FILTER_VALIDATE_URL ) ) {
                    $extracted_urls[] = $loc;
                }
            }
        }

        return $extracted_urls;
    }

    /**
     * AJAX: Scan a batch of URLs and trace their redirect chains / health
     */
    public function ajax_scan_batch() {
        check_ajax_referer( 'romerema_sitemap_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized access.', 'romeo-redirect-manager' ) );
        }

        $urls = isset( $_POST['urls'] ) && is_array( $_POST['urls'] ) ? array_map( 'esc_url_raw', $_POST['urls'] ) : array();

        if ( empty( $urls ) ) {
            wp_send_json_error( __( 'No URLs provided for batch scanning.', 'romeo-redirect-manager' ) );
        }

        $site_host = wp_parse_url( home_url(), PHP_URL_HOST );
        $batch_results = array();

        foreach ( $urls as $url ) {
            $batch_results[] = $this->inspect_single_url( $url, $site_host );
        }

        wp_send_json_success( array(
            'results' => $batch_results,
        ) );
    }

    /**
     * Inspect a single URL: follow redirects step-by-step to detect:
     * - Status codes (404, 301, 302, 307, 308, 200, etc.)
     * - Full redirect chains (A -> B -> C...)
     * - Loop detection
     * - External redirect detection
     */
    public function inspect_single_url( $initial_url, $site_host = null ) {
        if ( empty( $site_host ) ) {
            $site_host = wp_parse_url( home_url(), PHP_URL_HOST );
        }

        $current_url   = $initial_url;
        $chain         = array();
        $visited_chain = array();
        $max_hops      = 10;
        $hop_count     = 0;
        $is_loop       = false;
        $initial_code  = 200;
        $final_code    = 200;
        $error_message = '';

        while ( $hop_count < $max_hops ) {
            if ( in_array( $current_url, $visited_chain, true ) ) {
                $is_loop = true;
                break;
            }
            $visited_chain[] = $current_url;

            // Make request without auto-following redirects so we can inspect every hop
            $response = wp_safe_remote_head( $current_url, array(
                'timeout'     => 10,
                'redirection' => 0,
                'sslverify'   => false,
                'user-agent'  => 'RomeoRedirectManager/' . ( defined( 'ROMEREMA_VERSION' ) ? ROMEREMA_VERSION : '1.6.0' ) . '; ' . home_url(),
                'headers'     => array(
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                ),
            ) );

            // If HEAD fails or method not allowed (405), fallback to GET with range
            if ( is_wp_error( $response ) || 405 === wp_remote_retrieve_response_code( $response ) ) {
                $response = wp_safe_remote_get( $current_url, array(
                    'timeout'     => 10,
                    'redirection' => 0,
                    'sslverify'   => false,
                    'user-agent'  => 'RomeoRedirectManager/' . ( defined( 'ROMEREMA_VERSION' ) ? ROMEREMA_VERSION : '1.6.0' ) . '; ' . home_url(),
                    'headers'     => array(
                        'Range'  => 'bytes=0-1024',
                        'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    ),
                ) );
            }

            if ( is_wp_error( $response ) ) {
                $error_message = $response->get_error_message();
                $final_code    = 0;
                if ( 0 === $hop_count ) {
                    $initial_code = 0;
                }
                $chain[] = array(
                    'url'    => $current_url,
                    'code'   => 0,
                    'error'  => $error_message,
                );
                break;
            }

            $code = (int) wp_remote_retrieve_response_code( $response );
            if ( 0 === $hop_count ) {
                $initial_code = $code;
            }
            $final_code = $code;

            // Check if response is a redirect (301, 302, 303, 307, 308)
            if ( in_array( $code, array( 301, 302, 303, 307, 308 ), true ) ) {
                $location = wp_remote_retrieve_header( $response, 'location' );

                if ( ! empty( $location ) ) {
                    // Resolve relative URLs if needed
                    $next_url = $this->resolve_relative_url( $location, $current_url );

                    $chain[] = array(
                        'url'        => $current_url,
                        'code'       => $code,
                        'target_url' => $next_url,
                    );

                    $current_url = $next_url;
                    $hop_count++;
                    continue;
                }
            }

            // Normal or error terminal response (200, 404, 500, etc.)
            $chain[] = array(
                'url'  => $current_url,
                'code' => $code,
            );
            break;
        }

        $final_url = $current_url;
        $hops = count( $chain ) > 1 ? count( $chain ) - 1 : 0;

        // Determine issues
        $issues = array();
        $is_healthy = true;

        // 1. 404 Not Found detection
        if ( 404 === $initial_code || 404 === $final_code ) {
            $issues[]   = '404';
            $is_healthy = false;
        }

        // 2. 301 Moved Permanently detection
        if ( 301 === $initial_code ) {
            $issues[]   = '301';
            $is_healthy = false;
        }

        // 3. 302 Found / Temporary Redirect detection (including 307)
        if ( 302 === $initial_code || 307 === $initial_code ) {
            $issues[]   = '302';
            $is_healthy = false;
        }

        // 308 detection
        if ( 308 === $initial_code ) {
            $issues[]   = '308';
            $is_healthy = false;
        }

        // 4. Redirect Chain detection (2 or more hops or redirect loop)
        if ( $hops >= 2 || $is_loop ) {
            $issues[]   = 'chain';
            $is_healthy = false;
        }

        // 5. External Redirection detection
        $final_host = wp_parse_url( $final_url, PHP_URL_HOST );
        $is_external = false;
        if ( $hops > 0 && ! empty( $final_host ) && ! empty( $site_host ) ) {
            $site_host_clean  = preg_replace( '/^www\./i', '', strtolower( $site_host ) );
            $final_host_clean = preg_replace( '/^www\./i', '', strtolower( $final_host ) );

            if ( $site_host_clean !== $final_host_clean ) {
                $issues[]    = 'external';
                $is_external = true;
                $is_healthy  = false;
            }
        }

        // Server error (5xx) or network error
        if ( $initial_code >= 500 || $final_code >= 500 || 0 === $final_code ) {
            $issues[]   = 'error';
            $is_healthy = false;
        }

        return array(
            'url'           => $initial_url,
            'initial_code'  => $initial_code,
            'final_code'    => $final_code,
            'final_url'     => $final_url,
            'hops'          => $hops,
            'chain'         => $chain,
            'is_loop'       => $is_loop,
            'is_external'   => $is_external,
            'issues'        => $issues,
            'is_healthy'    => $is_healthy,
            'error_message' => $error_message,
        );
    }

    /**
     * Resolve relative URL against base URL
     */
    private function resolve_relative_url( $rel, $base ) {
        if ( parse_url( $rel, PHP_URL_SCHEME ) != '' ) {
            return $rel;
        }
        if ( $rel[0] === '#' || $rel[0] === '?' ) {
            return $base . $rel;
        }
        extract( parse_url( $base ) );
        $path = preg_replace( '#/[^/]*$#', '', isset( $path ) ? $path : '/' );
        if ( $rel[0] === '/' ) {
            $path = '';
        }
        $abs = ( isset( $host ) ? ( ( isset( $scheme ) ? $scheme : 'http' ) . '://' . $host ) : '' ) . $path . '/' . $rel;
        $re = array( '#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#' );
        for ( $n = 1; $n > 0; $abs = preg_replace( $re, '/', $abs, -1, $n ) ) {}
        return $abs;
    }

    /**
     * AJAX: Save complete scan results with duplicate destination detection
     */
    public function ajax_save_results() {
        check_ajax_referer( 'romerema_sitemap_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized access.', 'romeo-redirect-manager' ) );
        }

        $raw_results = isset( $_POST['results'] ) ? json_decode( wp_unslash( $_POST['results'] ), true ) : null;
        $sitemap_url = isset( $_POST['sitemap_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['sitemap_url'] ) ) ) : '';
        $total_urls  = isset( $_POST['total_urls'] ) ? intval( $_POST['total_urls'] ) : 0;

        if ( ! is_array( $raw_results ) ) {
            wp_send_json_error( __( 'Invalid scan results payload.', 'romeo-redirect-manager' ) );
        }

        // Post-process for duplicate destinations:
        // Group all redirecting URLs by their final destination URL
        $destinations_map = array();
        foreach ( $raw_results as $index => $item ) {
            if ( ! empty( $item['hops'] ) && $item['hops'] > 0 && ! empty( $item['final_url'] ) ) {
                $norm_dest = strtolower( rtrim( $item['final_url'], '/' ) );
                if ( ! isset( $destinations_map[ $norm_dest ] ) ) {
                    $destinations_map[ $norm_dest ] = array();
                }
                $destinations_map[ $norm_dest ][] = $index;
            }
        }

        $duplicate_dest_count = 0;
        foreach ( $destinations_map as $dest => $indices ) {
            if ( count( $indices ) > 1 ) {
                $duplicate_dest_count += count( $indices );
                foreach ( $indices as $idx ) {
                    if ( ! in_array( 'duplicate', $raw_results[ $idx ]['issues'], true ) ) {
                        $raw_results[ $idx ]['issues'][] = 'duplicate';
                        $raw_results[ $idx ]['is_healthy'] = false;
                        $raw_results[ $idx ]['duplicate_group_count'] = count( $indices );
                    }
                }
            }
        }

        // Compute summary counts
        $counts = array(
            'total'        => count( $raw_results ),
            'healthy'      => 0,
            'issues_total' => 0,
            'c404'         => 0,
            'c301'         => 0,
            'c302'         => 0,
            'chain'        => 0,
            'external'     => 0,
            'duplicate'    => 0,
            'errors'       => 0,
        );

        foreach ( $raw_results as $item ) {
            if ( ! empty( $item['is_healthy'] ) ) {
                $counts['healthy']++;
            } else {
                $counts['issues_total']++;
            }

            if ( in_array( '404', $item['issues'], true ) ) {
                $counts['c404']++;
            }
            if ( in_array( '301', $item['issues'], true ) ) {
                $counts['c301']++;
            }
            if ( in_array( '302', $item['issues'], true ) || in_array( '307', $item['issues'], true ) || in_array( '308', $item['issues'], true ) ) {
                $counts['c302']++;
            }
            if ( in_array( 'chain', $item['issues'], true ) ) {
                $counts['chain']++;
            }
            if ( in_array( 'external', $item['issues'], true ) ) {
                $counts['external']++;
            }
            if ( in_array( 'duplicate', $item['issues'], true ) ) {
                $counts['duplicate']++;
            }
            if ( in_array( 'error', $item['issues'], true ) ) {
                $counts['errors']++;
            }
        }

        $data_to_store = array(
            'last_scan'    => current_time( 'mysql' ),
            'timestamp'    => time(),
            'sitemap_url'  => $sitemap_url,
            'total_urls'   => $total_urls ?: count( $raw_results ),
            'issues_count' => $counts['issues_total'],
            'counts'       => $counts,
            'items'        => $raw_results,
        );

        $this->save_results( $data_to_store );

        wp_send_json_success( array(
            'message' => __( 'Scan results saved successfully.', 'romeo-redirect-manager' ),
            'data'    => $data_to_store,
        ) );
    }

    /**
     * AJAX: Get stored results
     */
    public function ajax_get_results() {
        check_ajax_referer( 'romerema_sitemap_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized access.', 'romeo-redirect-manager' ) );
        }

        $results = $this->get_stored_results();
        wp_send_json_success( $results );
    }

    /**
     * AJAX: Clear scan results
     */
    public function ajax_clear_results() {
        check_ajax_referer( 'romerema_sitemap_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized access.', 'romeo-redirect-manager' ) );
        }

        delete_option( $this->option_results_key );
        delete_option( $this->option_dismissed_key );

        wp_send_json_success( array( 'message' => __( 'Scan results cleared.', 'romeo-redirect-manager' ) ) );
    }

    /**
     * AJAX: Dismiss the admin warning notice
     */
    public function ajax_dismiss_notice() {
        check_ajax_referer( 'romerema_sitemap_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized access.', 'romeo-redirect-manager' ) );
        }

        update_option( $this->option_dismissed_key, time() );
        wp_send_json_success();
    }

    /**
     * AJAX: One-click Create/Fix Redirect from a Sitemap Issue item
     */
    public function ajax_create_redirect() {
        check_ajax_referer( 'romerema_sitemap_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( __( 'Unauthorized access.', 'romeo-redirect-manager' ) );
        }

        $source_url = isset( $_POST['source_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['source_url'] ) ) ) : '';
        $target_url = isset( $_POST['target_url'] ) ? esc_url_raw( trim( wp_unslash( $_POST['target_url'] ) ) ) : '';
        $code       = isset( $_POST['code'] ) ? intval( $_POST['code'] ) : 301;

        if ( empty( $source_url ) ) {
            wp_send_json_error( __( 'Source URL is required.', 'romeo-redirect-manager' ) );
        }

        // Extract path slug from source URL
        $path = wp_parse_url( $source_url, PHP_URL_PATH );
        $slug = trim( (string) $path, '/' );

        if ( empty( $slug ) ) {
            $slug = 'home';
        }

        // Add to Romeo Redirects rules
        $rules = get_option( 'romeo_redirect_manager_rules', array() );
        if ( ! is_array( $rules ) ) {
            $rules = array();
        }

        // Normalize and clean up existing rules
        $clean_rules = array();
        foreach ( $rules as $r ) {
            if ( is_array( $r ) && ! empty( $r['slug'] ) ) {
                if ( ! isset( $r['target'] ) && isset( $r['target_url'] ) ) {
                    $r['target'] = $r['target_url'];
                }
                if ( ! isset( $r['date'] ) && isset( $r['created_at'] ) ) {
                    $r['date'] = $r['created_at'];
                }
                if ( ! isset( $r['type'] ) ) {
                    $r['type'] = 'url';
                }
                $clean_rules[] = $r;
            }
        }

        $id = uniqid();
        $target = ! empty( $target_url ) ? esc_url_raw( $target_url ) : home_url( '/' );
        $final_code = in_array( intval( $code ), array( 301, 302, 307, 308 ), true ) ? intval( $code ) : 301;
        $updated = false;

        foreach ( $clean_rules as &$existing ) {
            if ( strtolower( $existing['slug'] ) === strtolower( $slug ) ) {
                $existing['target']   = $target;
                $existing['type']     = 'url';
                $existing['code']     = $final_code;
                $existing['override'] = true;
                $existing['date']     = current_time( 'mysql' );
                $updated = true;
                $id = $existing['id'];
                break;
            }
        }

        if ( ! $updated ) {
            $clean_rules[] = array(
                'id'       => $id,
                'slug'     => sanitize_title( $slug ),
                'type'     => 'url',
                'target'   => $target,
                'code'     => $final_code,
                'override' => true,
                'hits'     => 0,
                'date'     => current_time( 'mysql' ),
            );
        }

        update_option( 'romeo_redirect_manager_rules', array_values( $clean_rules ) );

        // Auto-resolve this issue in stored sitemap scan results
        $stored = $this->get_stored_results();
        if ( ! empty( $stored ) && ! empty( $stored['items'] ) ) {
            $updated_stored = false;
            $normalized_source = untrailingslashit( strtolower( $source_url ) );

            foreach ( $stored['items'] as &$sm_item ) {
                $item_norm = untrailingslashit( strtolower( $sm_item['url'] ) );
                if ( $item_norm === $normalized_source || strtolower( trim( (string) wp_parse_url( $sm_item['url'], PHP_URL_PATH ), '/' ) ) === strtolower( $slug ) ) {
                    $sm_item['is_healthy'] = true;
                    $sm_item['issues']     = array();
                    $updated_stored = true;
                }
            }

            if ( $updated_stored ) {
                // Recount remaining issues
                $remaining_issues = 0;
                $counts = array(
                    'total'        => count( $stored['items'] ),
                    'healthy'      => 0,
                    'issues_total' => 0,
                    'c404'         => 0,
                    'c301'         => 0,
                    'c302'         => 0,
                    'chain'        => 0,
                    'external'     => 0,
                    'duplicate'    => 0,
                    'errors'       => 0,
                );

                foreach ( $stored['items'] as $item ) {
                    if ( ! empty( $item['is_healthy'] ) ) {
                        $counts['healthy']++;
                    } else {
                        $remaining_issues++;
                        if ( ! empty( $item['issues'] ) ) {
                            if ( in_array( '404', $item['issues'], true ) ) $counts['c404']++;
                            if ( in_array( '301', $item['issues'], true ) ) $counts['c301']++;
                            if ( in_array( '302', $item['issues'], true ) || in_array( '307', $item['issues'], true ) || in_array( '308', $item['issues'], true ) ) $counts['c302']++;
                            if ( in_array( 'chain', $item['issues'], true ) ) $counts['chain']++;
                            if ( in_array( 'external', $item['issues'], true ) ) $counts['external']++;
                            if ( in_array( 'duplicate', $item['issues'], true ) ) $counts['duplicate']++;
                        }
                    }
                }
                $counts['issues_total'] = $remaining_issues;
                $stored['counts'] = $counts;
                $stored['issues_count'] = $remaining_issues;

                update_option( $this->option_results_key, $stored );

                if ( $remaining_issues <= 0 ) {
                    update_option( $this->option_dismissed_key, time() );
                }
            }
        }

        wp_send_json_success( array(
            'message'     => sprintf( __( 'Redirect rule for /%s successfully created!', 'romeo-redirect-manager' ), esc_html( $slug ) ),
            'redirect_id' => $id,
            'slug'        => $slug,
            'issues_left' => isset( $stored['issues_count'] ) ? $stored['issues_count'] : 0,
        ) );
    }

    /**
     * Render the Top Admin Warning Notice if sitemap issues are present
     */
    public function render_admin_notice() {
        // Only show to admins who can manage options
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        // Check if notice is dismissed
        $dismissed = get_option( $this->option_dismissed_key, false );
        if ( ! empty( $dismissed ) ) {
            return;
        }

        $results = $this->get_stored_results();
        if ( empty( $results ) || empty( $results['issues_count'] ) || $results['issues_count'] <= 0 || empty( $results['items'] ) ) {
            return;
        }

        // Check if issues have already been fixed via active redirect rules
        $rules = get_option( 'romeo_redirect_manager_rules', array() );
        $active_slugs = array();
        if ( is_array( $rules ) ) {
            foreach ( $rules as $r ) {
                if ( is_array( $r ) && ! empty( $r['slug'] ) ) {
                    $active_slugs[] = strtolower( trim( $r['slug'], '/' ) );
                }
            }
        }

        $unresolved_issues = 0;
        foreach ( $results['items'] as $item ) {
            if ( empty( $item['is_healthy'] ) ) {
                $item_slug = strtolower( trim( (string) wp_parse_url( $item['url'], PHP_URL_PATH ), '/' ) );
                if ( ! in_array( $item_slug, $active_slugs, true ) ) {
                    $unresolved_issues++;
                }
            }
        }

        // If all detected issues are resolved by redirects, auto-close the notice
        if ( $unresolved_issues <= 0 ) {
            $results['issues_count'] = 0;
            if ( isset( $results['counts'] ) ) {
                $results['counts']['issues_total'] = 0;
            }
            update_option( $this->option_results_key, $results );
            return;
        }

        $counts = isset( $results['counts'] ) ? $results['counts'] : array();
        $issues_total = $unresolved_issues;
        $sitemap_page_url = admin_url( 'admin.php?page=romeo-redirect-manager-sitemap' );

        // Count breakdown message
        $redirects_count = ( isset( $counts['c301'] ) ? $counts['c301'] : 0 ) + ( isset( $counts['c302'] ) ? $counts['c302'] : 0 );
        $c404_count = isset( $counts['c404'] ) ? $counts['c404'] : 0;
        $chain_count = isset( $counts['chain'] ) ? $counts['chain'] : 0;

        if ( $redirects_count > 0 && 0 === $c404_count && 0 === $chain_count ) {
            $message_text = sprintf(
                /* translators: %d: number of redirecting sitemap URLs */
                _n(
                    '⚠️ <strong>%d sitemap URL</strong> redirects to another URL.',
                    '⚠️ <strong>%d sitemap URLs</strong> redirect to another URL.',
                    $redirects_count,
                    'romeo-redirect-manager'
                ),
                $redirects_count
            );
        } else {
            $message_text = sprintf(
                /* translators: %d: total issues count */
                _n(
                    '⚠️ <strong>%d issue</strong> detected in your XML sitemap (redirects, 404s, or chains).',
                    '⚠️ <strong>%d issues</strong> detected in your XML sitemap (redirects, 404s, or chains).',
                    $issues_total,
                    'romeo-redirect-manager'
                ),
                $issues_total
            );
        }

        $nonce = wp_create_nonce( 'romerema_sitemap_nonce' );
        ?>
        <div class="notice notice-warning is-dismissible rr-sitemap-notice-banner" id="rr-sitemap-admin-notice" data-nonce="<?php echo esc_attr( $nonce ); ?>">
            <div class="rr-sitemap-notice-inner" style="display:flex; align-items:center; justify-content:space-between; padding:10px 0; gap:16px; flex-wrap:wrap;">
                <div class="rr-sitemap-notice-text" style="display:flex; align-items:center; gap:10px; font-size:13.5px; color:#1e293b;">
                    <span class="dashicons dashicons-warning" style="color:#d97706; font-size:20px; width:20px; height:20px;"></span>
                    <span><?php echo wp_kses_post( $message_text ); ?></span>
                </div>
                <div class="rr-sitemap-notice-actions" style="display:flex; align-items:center; gap:10px;">
                    <a href="<?php echo esc_url( $sitemap_page_url ); ?>" class="button button-primary rr-btn-view-issues" style="background:#FA3C34; border:none; color:#fff; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:6px; border-radius:8px; padding:4px 14px; box-shadow:0 2px 6px rgba(250,60,52,0.25);">
                        <span class="dashicons dashicons-visibility" style="font-size:16px; width:16px; height:16px;"></span>
                        <span><?php esc_html_e( 'View Issues', 'romeo-redirect-manager' ); ?></span>
                    </a>
                </div>
            </div>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var notice = document.getElementById('rr-sitemap-admin-notice');
            if (!notice) return;
            notice.addEventListener('click', function(e) {
                if (e.target.classList.contains('notice-dismiss') || e.target.closest('.notice-dismiss')) {
                    var nonce = notice.dataset.nonce;
                    var fd = new FormData();
                    fd.append('action', 'romerema_sitemap_dismiss_notice');
                    fd.append('nonce', nonce);
                    fetch(ajaxurl, { method: 'POST', body: fd });
                }
            });
        });
        </script>
        <?php
    }
}
