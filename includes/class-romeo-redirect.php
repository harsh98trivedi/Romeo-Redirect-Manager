<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Romerema_Redirect {

    private $option_key = 'romeo_redirect_manager_rules';

    public function __construct() {
        // Hooking early into parse_request to bypass main query and avoid 404 conflicts with SEO plugins
        add_action( 'parse_request', array( $this, 'handle_redirections' ), 0 );
        // Hook for 404 handling
        add_action( 'template_redirect', array( $this, 'handle_404' ) );
    }

    public function handle_redirections() {
        if ( is_admin() ) {
            return;
        }

        // 1. Get current path
        // Use wp_unslash before processing
        $request_uri = isset($_SERVER['REQUEST_URI']) ? wp_unslash($_SERVER['REQUEST_URI']) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        
        // Parse URL safely
        $parsed_url = wp_parse_url( $request_uri );
        if ( ! $parsed_url || ! isset( $parsed_url['path'] ) ) {
            return;
        }

        $path = untrailingslashit( $parsed_url['path'] );
        $path = trim( $path, '/' );

        // 2. Load redirects
        $redirects = get_option( $this->option_key, array() );

        if ( empty( $redirects ) ) {
            return;
        }

        // 3. Match
        foreach ( $redirects as $key => &$r ) {
            // Case-insensitive match for slug
            if ( strtolower( $r['slug'] ) === strtolower( $path ) ) {
                
                // Check Override Logic
                // If override is NOT set (or false), and content exists, we skip this redirect
                // to let the actual page load.
                $override = isset( $r['override'] ) && $r['override'];
                
                if ( ! $override && $this->content_exists( $path ) ) {
                    continue; // Skip, let WP load the page
                }

                // Track Hit
                if ( ! isset( $r['hits'] ) ) {
                    $r['hits'] = 0;
                }
                $r['hits']++;
                $redirects[$key] = $r; // Ensure array is updated
                update_option( $this->option_key, $redirects ); // Save hits

                // Resolve Target
                $target_url = '';
                if ( 'post' === $r['type'] ) {
                    $target_url = get_permalink( intval( $r['target'] ) );
                } else {
                    $target_url = $r['target'];
                }

                if ( $target_url ) {
                    // Redirect
                    // Use wp_redirect as we support external domains.
                    // phpcs:ignore WordPress.Security.SafeRedirect.wp_redirect_wp_redirect
                    wp_redirect( $target_url, intval( $r['code'] ) );
                    exit;
                }
            }
        }
    }

    public function handle_404() {
        if ( is_404() ) {
            $type = get_option( 'romeo_redirect_404_type', 'url' );
            $final_url = '';

            if ( 'post' === $type ) {
                $post_id = get_option( 'romeo_redirect_404_post_id', 0 );
                if ( $post_id ) {
                    $final_url = get_permalink( $post_id );
                }
            } elseif ( 'home' === $type ) {
                $final_url = home_url('/');
            } else {
                $final_url = get_option( 'romeo_redirect_404_target', '' );
            }

            if ( ! empty( $final_url ) ) {
                wp_redirect( $final_url );
                exit;
            }
        }
    }

    private function content_exists( $slug ) {
        // 1. Check Page/Post by path
        if ( get_page_by_path( $slug ) ) {
            return true;
        } 
        
        // 2. Start checking for other post types or flat slugs
        $args = array(
            'name'        => $slug,
            'post_type'   => 'any',
            'post_status' => 'publish',
            'numberposts' => 1
        );
        $posts = get_posts( $args );
        if ( ! empty( $posts ) ) {
            return true;
        }

        return false;
    }
}
