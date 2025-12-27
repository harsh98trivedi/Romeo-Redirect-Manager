<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Romerema_Admin {

    private $option_key = 'romeo_redirect_manager_rules';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        
        // AJAX
        add_action( 'wp_ajax_romerema_save_redirect', array( $this, 'ajax_save_redirect' ) );
        add_action( 'wp_ajax_romerema_delete_redirect', array( $this, 'ajax_delete_redirect' ) );
        add_action( 'wp_ajax_romerema_bulk_delete', array( $this, 'ajax_bulk_delete' ) );
        add_action( 'wp_ajax_romerema_search_posts', array( $this, 'ajax_search_posts' ) );
    }

    public function add_admin_menu() {
        add_menu_page(
            __( 'Romeo Redirect Manager', 'romeo-redirect-manager' ),
            __( 'Romeo Redirects', 'romeo-redirect-manager' ),
            'manage_options',
            'romeo-redirect-manager',
            array( $this, 'render_admin_page' ),
            'dashicons-randomize',
            80
        );
    }

    public function enqueue_assets( $hook ) {
        if ( 'toplevel_page_romeo-redirect-manager' !== $hook ) {
            return;
        }

        $main_file = dirname( __FILE__ ) . '/../romeo-redirect-manager.php';
        wp_enqueue_style( 'romeo-admin-css', plugins_url( 'assets/css/admin.css', $main_file ), array(), '1.1' );
        wp_enqueue_script( 'romeo-admin-js', plugins_url( 'assets/js/admin.js', $main_file ), array(), '1.1', true );

        wp_localize_script( 'romeo-admin-js', 'romerema_vars', array(
            'nonce' => wp_create_nonce( 'romerema_save_nonce' ),
            'delete_nonce' => wp_create_nonce( 'romerema_delete_nonce' )
        ));
    }

    public function render_admin_page() {
        $redirects = get_option( $this->option_key, array() );
        $redirects = array_reverse( $redirects ); // Newest first
        $logo_url = plugins_url( 'assets/images/icon.svg', dirname( __FILE__ ) . '/../romeo-redirect-manager.php' );
        ?>
        <div class="rr-wrapper">
            
            <!-- Header -->
            <div class="rr-header">
                <div class="rr-brand">
                    <div class="rr-logo-icon">
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="Redirection Romeo">
                    </div>
                    <div>
                        <h1>Romeo Redirect Manager</h1>
                        <small>by <a href="https://harsh98trivedi.github.io/" target="_blank" style="color:#f0405f; text-decoration:none; font-weight:600;">Harsh Trivedi</a></small>
                    </div>
                </div>
                <div>
                    <button id="rr-btn-new" class="rr-btn rr-btn-primary">
                        <span class="dashicons dashicons-plus-alt2" style="font-size:18px; width:18px; height:18px;"></span> <span class="rr-btn-text"><?php esc_html_e( 'Create New Redirect', 'romeo-redirect-manager' ); ?></span>
                    </button>
                </div>
            </div>

            <!-- Creator / Edit Panel -->
            <div id="rr-creator-panel" class="rr-creator hidden">
                <h3 id="rr-modal-title"><?php esc_html_e( 'Create Redirect', 'romeo-redirect-manager' ); ?></h3>
                
                <form id="rr-form">
                    <div class="rr-form-row">
                        <!-- Source -->
                        <div class="rr-form-group">
                            <label class="rr-label"><?php esc_html_e( 'Source Slug', 'romeo-redirect-manager' ); ?></label>
                            <div class="rr-input-group">
                                <div class="rr-prefix-box">/</div>
                                <input class="rr-input" type="text" name="slug" placeholder="my-redirection-slug" required>
                            </div>
                        </div>

                        <!-- Type -->
                        <div class="rr-form-group">
                            <label class="rr-label"><?php esc_html_e( 'Target Type', 'romeo-redirect-manager' ); ?></label>
                            <select id="rr-target-type" name="type" class="rr-select">
                                <option value="url"><?php esc_html_e( 'External URL', 'romeo-redirect-manager' ); ?></option>
                                <option value="post"><?php esc_html_e( 'Internal Post / Page', 'romeo-redirect-manager' ); ?></option>
                            </select>
                        </div>
                    </div>

                    <div class="rr-form-row">
                        <!-- Target: URL -->
                        <div class="rr-form-group" id="rr-group-url">
                            <label class="rr-label"><?php esc_html_e( 'Target URL', 'romeo-redirect-manager' ); ?></label>
                            <input class="rr-input" type="url" name="target_url" placeholder="https://www.google.com">
                        </div>

                        <!-- Target: Post -->
                        <div class="rr-form-group hidden" id="rr-group-post" style="position:relative;">
                            <label class="rr-label"><?php esc_html_e( 'Search Content', 'romeo-redirect-manager' ); ?></label>
                            
                            <input class="rr-input" type="text" id="rr-post-search-input" placeholder="<?php esc_attr_e( 'Type to search pages...', 'romeo-redirect-manager' ); ?>">
                            <input type="hidden" name="target_post_id" id="rr-target-post-id">
                            
                            <div id="rr-search-results" class="rr-autocomplete-results hidden"></div>
                            
                            <div id="rr-selected-post" class="rr-post-selected hidden">
                                <span class="dashicons dashicons-admin-links"></span> 
                                <span class="text"></span>
                                <button type="button" class="rr-remove-selection" style="margin-left:auto; cursor:pointer; background:none; border:none;">&times;</button>
                            </div>
                        </div>

                        <!-- HTTP Code -->
                        <div class="rr-form-group">
                            <label class="rr-label"><?php esc_html_e( 'Redirect Type', 'romeo-redirect-manager' ); ?></label>
                            <select name="code" class="rr-select">
                                <option value="301">301 - Permanent Redirect</option>
                                <option value="302">302 - Temporary Redirect</option>
                                <option value="307">307 - Temporary Redirect (No Cache)</option>
                                <option value="308">308 - Permanent Redirect (Preserve Method)</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="rr-form-actions">
                        <button type="button" id="rr-cancel" class="rr-btn rr-btn-cancel"><?php esc_html_e( 'Cancel', 'romeo-redirect-manager' ); ?></button>
                        <button type="submit" id="rr-save-btn" class="rr-btn rr-btn-save"><?php esc_html_e( 'Save Redirect', 'romeo-redirect-manager' ); ?></button>
                    </div>
                </form>
            </div>

            <!-- List View -->
            <div data-view="list">
                
                <div class="rr-search-container">
                    <span class="dashicons dashicons-search rr-search-icon"></span>
                    <input type="text" id="rr-card-search" class="rr-search-input" placeholder="<?php esc_attr_e( 'Type to search redirects...', 'romeo-redirect-manager' ); ?>">
                </div>

                <div class="rr-grid" id="rr-card-grid">
                    <?php if ( empty( $redirects ) ) : ?>
                        <div class="rr-empty">
                            <span class="dashicons dashicons-randomize" style="font-size:48px; width:48px; height:48px; margin-bottom:16px; opacity:0.5;"></span>
                            <h3><?php esc_html_e( 'No redirects found', 'romeo-redirect-manager' ); ?></h3>
                            <p><?php esc_html_e( 'Create your first redirect to get started.', 'romeo-redirect-manager' ); ?></p>
                        </div>
                    <?php else : ?>
                        <?php foreach ( $redirects as $r ) : 
                            $data_attr = $r;
                            $target_display = $r['target'];
                            $target_label = 'URL Redirect';
                            
                            if( 'post' === $r['type'] ) {
                                $target_label = 'Page Redirect';
                                $title = get_the_title( $r['target'] );
                                // If post is deleted, handle gracefully
                                if( $title ) {
                                    $target_display = $title;
                                    $full_target = get_permalink( $r['target'] );
                                } else {
                                    $target_display = '(Deleted Post ID: ' . $r['target'] . ')';
                                    $full_target = '#';
                                }
                                $data_attr['target_title'] = $title;
                            } else {
                                $full_target = $r['target'];
                            }
                            
                            $full_source = home_url( '/' . $r['slug'] );
                        ?>
                            <div class="rr-card" id="card-<?php echo esc_attr( $r['id'] ); ?>" data-slug="<?php echo esc_attr( strtolower($r['slug'] ) ); ?>" data-target="<?php echo esc_attr( strtolower( $target_display ) ); ?>">
                                
                                <div class="rr-card-actions">
                                    <label class="rr-checkbox-wrapper">
                                        <input type="checkbox" class="rr-bulk-checkbox" value="<?php echo esc_attr( $r['id'] ); ?>">
                                        <span class="rr-checkbox-style"></span>
                                    </label>
                                    
                                    <a href="<?php echo esc_url( $full_source ); ?>" target="_blank" class="rr-action-btn" title="Open Link">
                                        <span class="dashicons dashicons-external"></span>
                                    </a>
                                    <button 
                                        class="rr-action-btn rr-edit-btn" 
                                        title="Edit"
                                        data-id="<?php echo esc_attr( $r['id'] ); ?>"
                                        data-slug="<?php echo esc_attr( $r['slug'] ); ?>"
                                        data-type="<?php echo esc_attr( $r['type'] ); ?>"
                                        data-target="<?php echo esc_attr( $r['target'] ); ?>"
                                        data-code="<?php echo esc_attr( $r['code'] ); ?>"
                                        <?php if( 'post' === $r['type'] && isset($data_attr['target_title']) ) : ?>
                                        data-target-title="<?php echo esc_attr( $data_attr['target_title'] ); ?>"
                                        <?php endif; ?>
                                    >
                                        <span class="dashicons dashicons-edit"></span>
                                    </button>
                                    <button onclick="rrDelete('<?php echo esc_attr( $r['id'] ); ?>')" class="rr-action-btn" title="Delete" style="color:#ef4444;">
                                        <span class="dashicons dashicons-trash"></span>
                                    </button>
                                </div>

                                <div class="rr-card-slug" title="<?php echo esc_attr( $r['slug'] ); ?>">
                                    <span class="slash">/</span><?php echo esc_html( $r['slug'] ); ?>
                                </div>

                                <div class="rr-card-info">
                                    <span class="rr-info-label"><?php echo esc_html( $target_label ); ?></span>
                                    <span class="rr-info-value" title="<?php echo esc_attr( $full_target ); ?>">
                                        <?php echo esc_html( $target_display ); ?>
                                    </span>
                                </div>

                                <div class="rr-card-footer">
                                    <div class="rr-status">
                                        <div class="rr-status-dot code-<?php echo esc_attr( $r['code'] ); ?>"></div>
                                        <?php echo esc_attr( $r['code'] ); ?> Redirect
                                    </div>
                                    <div class="rr-hits">
                                        <?php echo isset($r['hits']) ? esc_html( number_format_i18n( $r['hits'] ) ) . ' Hits' : '0 Hits'; ?>
                                    </div>
                                </div>

                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Bulk Actions Floating Bar -->
            <div id="rr-bulk-bar" class="rr-bulk-bar hidden">
                <div class="rr-bulk-count"><span id="rr-selected-count">0</span> selected</div>
                <button id="rr-bulk-clear-btn" class="rr-btn-clear-bulk">
                    <span class="dashicons dashicons-no-alt"></span> <span class="rr-btn-text">Clear Selection</span>
                </button>
                <button id="rr-bulk-delete-btn" class="rr-btn rr-btn-delete-bulk">
                    <span class="dashicons dashicons-trash"></span> <span class="rr-btn-text">Delete Selection</span>
                </button>
            </div>
        </div>
        <?php
    }

    public function ajax_save_redirect() {
        check_ajax_referer( 'romerema_save_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $id   = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        $slug = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
        $type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'url';
        $code = isset( $_POST['code'] ) ? intval( wp_unslash( $_POST['code'] ) ) : 301;
        
        if ( empty( $slug ) ) {
            wp_send_json_error( 'Slug is required.' );
        }

        $target = '';
        if ( 'post' === $type ) {
            $target = isset( $_POST['target_post_id'] ) ? intval( wp_unslash( $_POST['target_post_id'] ) ) : 0;
            if ( ! $target ) {
                wp_send_json_error( 'Please select a post.' );
            }
        } else {
            $target = isset( $_POST['target_url'] ) ? esc_url_raw( wp_unslash( $_POST['target_url'] ) ) : '';
            if ( empty( $target ) ) {
                wp_send_json_error( 'Target URL is required.' );
            }
        }

        $redirects = get_option( $this->option_key, array() );
        
        // Check for duplicates (slug collision), excluding self
        foreach($redirects as $r) {
            if($r['slug'] === $slug && $r['id'] !== $id) {
                wp_send_json_error('Slug is already in use.');
            }
        }

        if ( $id ) {
            // Update
            $updated = false;
            foreach ( $redirects as &$r ) {
                if ( $r['id'] === $id ) {
                    $r['slug']   = $slug;
                    $r['type']   = $type;
                    $r['target'] = $target;
                    $r['code']   = $code;
                    $updated = true;
                    break;
                }
            }
            if ( ! $updated ) {
                // ID provided but not found? Treat as new or error? 
                // Let's safe fallback to new
                $id = uniqid(); 
                $redirects[] = array(
                    'id'     => $id,
                    'slug'   => $slug,
                    'type'   => $type,
                    'target' => $target,
                    'code'   => $code,
                    'hits'   => 0
                );
            }
        } else {
            // Create New
            $id = uniqid();
            $redirects[] = array(
                'id'     => $id,
                'slug'   => $slug,
                'type'   => $type,
                'target' => $target,
                'code'   => $code,
                'hits'   => 0
            );
        }

        update_option( $this->option_key, $redirects );
        wp_send_json_success();
    }

    public function ajax_delete_redirect() {
        check_ajax_referer( 'romerema_delete_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $id = isset( $_POST['id'] ) ? sanitize_text_field( wp_unslash( $_POST['id'] ) ) : '';
        $redirects = get_option( $this->option_key, array() );

        foreach ( $redirects as $key => $r ) {
            if ( $r['id'] === $id ) {
                unset( $redirects[ $key ] );
                update_option( $this->option_key, array_values( $redirects ) );
                wp_send_json_success();
            }
        }
        wp_send_json_error( 'Not found' );
    }

    public function ajax_bulk_delete() {
        check_ajax_referer( 'romerema_delete_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $ids = isset( $_POST['ids'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['ids'] ) ) : array();
        
        if ( empty( $ids ) ) {
            wp_send_json_error( 'No items selected' );
        }

        $redirects = get_option( $this->option_key, array() );
        $original_count = count( $redirects );
        
        // Filter out deleted IDs
        $redirects = array_filter( $redirects, function( $r ) use ( $ids ) {
            return ! in_array( $r['id'], $ids, true );
        } );
        
        if ( count( $redirects ) < $original_count ) {
            update_option( $this->option_key, array_values( $redirects ) );
            wp_send_json_success();
        }
        
        wp_send_json_error( 'Nothing deleted' );
    }

    public function ajax_search_posts() {
        // ... (existing search posts logic) ...
        // Verify Nonce
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['nonce'] ) ), 'romerema_save_nonce' ) ) {
            wp_send_json_error( 'Invalid nonce' );
        }

        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $term = isset( $_GET['term'] ) ? sanitize_text_field( wp_unslash( $_GET['term'] ) ) : '';
        
        $query = new WP_Query( array(
            's' => $term,
            'post_type' => array( 'post', 'page' ),
            'posts_per_page' => 10,
            'post_status' => 'publish'
        ) );

        $results = array();
        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $results[] = array(
                    'id' => get_the_ID(),
                    'title' => get_the_title(),
                    'type' => get_post_type()
                );
            }
        }
        wp_reset_postdata();
        wp_send_json_success( $results );
    }
}
