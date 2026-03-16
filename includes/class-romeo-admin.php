<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Romerema_Admin {

    private $option_key = 'romeo_redirect_manager_rules';

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_dashboard_setup', array( $this, 'register_dashboard_widget' ) );
        
        // AJAX
        add_action( 'wp_ajax_romerema_save_redirect', array( $this, 'ajax_save_redirect' ) );
        add_action( 'wp_ajax_romerema_delete_redirect', array( $this, 'ajax_delete_redirect' ) );
        add_action( 'wp_ajax_romerema_bulk_delete', array( $this, 'ajax_bulk_delete' ) );
        add_action( 'wp_ajax_romerema_search_posts', array( $this, 'ajax_search_posts' ) );
        add_action( 'wp_ajax_romerema_export_redirects', array( $this, 'ajax_export_redirects' ) );
        add_action( 'wp_ajax_romerema_import_redirects', array( $this, 'ajax_import_redirects' ) );
        add_action( 'wp_ajax_romerema_check_conflict', array( $this, 'ajax_check_conflict' ) );
        add_action( 'wp_ajax_romerema_save_404', array( $this, 'ajax_save_404' ) );
        add_action( 'wp_ajax_romerema_toggle_override', array( $this, 'ajax_toggle_override' ) );
        add_action( 'wp_ajax_romerema_toggle_404',     array( $this, 'ajax_toggle_404_enabled' ) );
    }

    public function add_admin_menu() {
        $icon_url = plugins_url( 'assets/images/icon.svg', dirname( __FILE__ ) . '/../romeo-redirect-manager.php' );
        add_menu_page(
            __( 'Romeo Redirects', 'romeo-redirect-manager' ),
            __( 'Romeo Redirects', 'romeo-redirect-manager' ),
            'manage_options',
            'romeo-redirect-manager',
            array( $this, 'render_admin_page' ),
            $icon_url,
            80
        );

        add_submenu_page(
            'romeo-redirect-manager',
            __( 'Redirects', 'romeo-redirect-manager' ),
            __( 'Redirects', 'romeo-redirect-manager' ),
            'manage_options',
            'romeo-redirect-manager',
            array( $this, 'render_admin_page' )
        );

        add_submenu_page(
            'romeo-redirect-manager',
            __( '404 Settings', 'romeo-redirect-manager' ),
            __( '404 Settings', 'romeo-redirect-manager' ),
            'manage_options',
            'romeo-redirect-manager-404',
            array( $this, 'render_404_page' )
        );
    }

    public function enqueue_assets( $hook ) {
        $main_file = dirname( __FILE__ ) . '/../romeo-redirect-manager.php';

        // Load CSS on ALL admin pages so the sidebar icon is styled everywhere.
        wp_enqueue_style( 'romeo-admin-css', plugins_url( 'assets/css/admin.css', $main_file ), array(), '1.4.0' );

        // Load JS only on plugin pages (heavy, not needed elsewhere).
        if ( strpos( $hook, 'romeo-redirect-manager' ) === false ) {
            return;
        }

        wp_enqueue_script( 'romeo-admin-js', plugins_url( 'assets/js/admin.js', $main_file ), array(), '1.4.0', true );

        wp_localize_script( 'romeo-admin-js', 'romerema_vars', array(
            'nonce' => wp_create_nonce( 'romerema_save_nonce' ),
            'delete_nonce' => wp_create_nonce( 'romerema_delete_nonce' ),
            'import_nonce' => wp_create_nonce( 'romerema_import_nonce' ),
            'export_nonce' => wp_create_nonce( 'romerema_export_nonce' ),
            'check_nonce'  => wp_create_nonce( 'romerema_check_nonce' ),
            'save_404_nonce'   => wp_create_nonce( 'romerema_save_404_nonce' ),
            'toggle_404_nonce' => wp_create_nonce( 'romerema_toggle_404_nonce' )
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
                        <img src="<?php echo esc_url( $logo_url ); ?>" alt="Romeo Redirects Manager" style="width:48px;height:48px;">
                    </div>
                    <div>
                        <h1>Redirection Romeo</h1>
                        <small>by <a href="https://harsh98trivedi.github.io/" target="_blank" style="color:#f0405f; text-decoration:none; font-weight:600;">Harsh Trivedi</a></small>
                    </div>
                </div>
                <!-- Action Buttons: Import/Export/New -->
                <div style="display: flex; gap: 8px;">
                    <input type="file" id="rr-import-file" accept=".json" style="display:none;" />
                    <a href="https://wordpress.org/support/plugin/romeo-redirect-manager/reviews/#new-post" target="_blank" rel="noopener noreferrer" class="rr-btn rr-btn-secondary header-action-btn" style="text-decoration:none; display:inline-flex; align-items:center;">
                        <span class="dashicons dashicons-star-filled" style="font-size:16px; width:16px; height:16px; color:#fbbf24;"></span> <span class="rr-btn-text"><?php esc_html_e( 'Rate', 'romeo-redirect-manager' ); ?></span>
                    </a>
                    <a href="https://buymeacoffee.com/harshtrivedi" target="_blank" rel="noopener noreferrer" class="rr-btn rr-btn-secondary header-action-btn" style="text-decoration:none; display:inline-flex; align-items:center;">
                        <span class="dashicons dashicons-heart" style="font-size:18px; width:18px; height:18px; color:#ff4d6d;"></span> <span class="rr-btn-text"><?php esc_html_e( 'Donate', 'romeo-redirect-manager' ); ?></span>
                    </a>
                    <button id="rr-btn-import" class="rr-btn rr-btn-secondary header-action-btn">
                        <span class="dashicons dashicons-upload" style="font-size:18px; width:18px; height:18px;"></span> <span class="rr-btn-text"><?php esc_html_e( 'Import', 'romeo-redirect-manager' ); ?></span>
                    </button>
                    <button id="rr-btn-export" class="rr-btn rr-btn-secondary header-action-btn">
                        <span class="dashicons dashicons-download" style="font-size:18px; width:18px; height:18px;"></span> <span class="rr-btn-text"><?php esc_html_e( 'Export', 'romeo-redirect-manager' ); ?></span>
                    </button>
                    <button id="rr-btn-new" class="rr-btn rr-btn-outline">
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

                    <!-- Conflict Warning & Override (Moved Below) -->
                    <div id="rr-conflict-warning" class="rr-warning hidden">
                        <div class="rr-warning-content">
                            <span class="dashicons dashicons-warning"></span> 
                            <span class="rr-warning-text">
                                <?php esc_html_e( 'Warning: A page with this slug already exists.', 'romeo-redirect-manager' ); ?>
                                <a href="#" id="rr-conflict-link" target="_blank"><?php esc_html_e( 'View Page', 'romeo-redirect-manager' ); ?> &rarr;</a>
                            </span>
                        </div>
                        <label class="rr-checkbox-wrapper rr-override-wrapper" style="cursor:pointer; display:inline-flex; align-items:center; gap:8px; margin-top:12px; position:relative; padding:8px 14px; background:#fff7ed; border:1px solid #fed7aa; border-radius:8px; transition:all 0.2s ease;">
                            <input type="checkbox" name="override" id="rr-override-check" value="1" class="rr-custom-checkbox" style="opacity:0; position:absolute; inset:0; width:100%; height:100%; margin:0; z-index:10; cursor:pointer;">
                            <span class="rr-checkbox-box" style="width:18px; height:18px; border-radius:5px; border:2.5px solid #fb923c; display:flex; align-items:center; justify-content:center; background:white; flex-shrink:0; transition:all 0.2s ease;"></span>
                            <span class="rr-override-label" style="font-weight:600; color:#ea580c; font-size:13px; letter-spacing:-0.01em;">Override</span>
                        </label>
                    </div>

                    <div class="rr-form-row">
                        <!-- Target: URL -->
                        <div class="rr-form-group" id="rr-group-url">
                            <label class="rr-label"><?php esc_html_e( 'Target URL', 'romeo-redirect-manager' ); ?></label>
                            <input class="rr-input" type="text" name="target_url" placeholder="https://www.google.com">
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
            <div data-view="card">
                
                <div class="rr-search-container">
                    <span class="dashicons dashicons-search rr-search-icon"></span>
                    <input type="text" id="rr-card-search" class="rr-search-input" placeholder="<?php esc_attr_e( 'Type to search redirects...', 'romeo-redirect-manager' ); ?>">
                </div>

                <div class="rr-filters" style="display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; justify-content: center;">
                    <button class="rr-filter-btn active" data-filter="all">All</button>
                    <button class="rr-filter-btn" data-filter="301">301</button>
                    <button class="rr-filter-btn" data-filter="302">302</button>
                    <button class="rr-filter-btn" data-filter="307">307</button>
                    <button class="rr-filter-btn" data-filter="308">308</button>
                </div>

                <div class="rr-view-toggles" style="display: flex; gap: 8px; justify-content: flex-end; margin-bottom: 20px; align-items: center;">
                    <div class="rr-sort-wrapper">
                        <select id="rr-sort-select" class="rr-input" style="width: 100%; height: 38px; margin: 0; font-size: 13px;">
                            <option value="date-desc"><?php esc_html_e( 'Newest First', 'romeo-redirect-manager' ); ?></option>
                            <option value="name-asc"><?php esc_html_e( 'Name (A-Z)', 'romeo-redirect-manager' ); ?></option>
                            <option value="hits-desc"><?php esc_html_e( 'Most Hits', 'romeo-redirect-manager' ); ?></option>
                            <option value="type-page"><?php esc_html_e( 'Internal Pages First', 'romeo-redirect-manager' ); ?></option>
                            <option value="type-post"><?php esc_html_e( 'Internal Posts First', 'romeo-redirect-manager' ); ?></option>
                            <option value="type-url"><?php esc_html_e( 'External Sites First', 'romeo-redirect-manager' ); ?></option>
                        </select>
                    </div>
                    <button class="rr-view-btn active" data-view="card" title="Card View"><span class="dashicons dashicons-grid-view"></span></button>
                    <button class="rr-view-btn" data-view="list" title="List View"><span class="dashicons dashicons-list-view"></span></button>
                </div>

                <div id="rr-report-summary"></div>

                <div class="rr-grid card-view" id="rr-card-grid">
                    <?php 
                    // This div is used for both initial empty state and filtering
                    $show_initial_empty = empty( $redirects );
                    ?>
                    <div id="rr-no-results" class="rr-empty-state <?php echo $show_initial_empty ? '' : 'hidden'; ?>">
                        <span class="dashicons dashicons-search"></span>
                        <h3><?php esc_html_e( 'No redirects found', 'romeo-redirect-manager' ); ?></h3>
                        <p><?php esc_html_e( 'Try a different filter or create your first one to get started.', 'romeo-redirect-manager' ); ?></p>
                    </div>

                    <?php if ( ! empty( $redirects ) ) : ?>
                        <?php foreach ( $redirects as $r ) : 
                            $data_attr = $r;
                            $target_display = $r['target'];
                            $target_label = 'URL Redirect';
                            
                            if( 'post' === $r['type'] ) {
                                $target_label = 'Page Redirect';
                                $title = get_the_title( $r['target'] );
                                $ptype_raw = get_post_type( $r['target'] );
                                $detailed_type = $ptype_raw === 'page' ? 'page' : 'post';
                                // If post is deleted, handle gracefully
                                if( $title ) {
                                    $target_display = $title;
                                    $full_target = get_permalink( $r['target'] );
                                } else {
                                    $target_display = '(Deleted Post ID: ' . $r['target'] . ')';
                                    $full_target = '#';
                                    $detailed_type = 'post'; // fallback
                                }
                                $data_attr['target_title'] = $title;
                            } else {
                                $full_target = $r['target'];
                                $detailed_type = 'url';
                            }
                            
                            $full_source = home_url( '/' . $r['slug'] );
                            $added_ts = isset($r['date']) ? strtotime($r['date']) : 0;
                        ?>
                            <div class="rr-card" id="card-<?php echo esc_attr( $r['id'] ); ?>" data-slug="<?php echo esc_attr( strtolower($r['slug'] ) ); ?>" data-target="<?php echo esc_attr( strtolower( $target_display ) ); ?>" data-code="<?php echo esc_attr( $r['code'] ); ?>" data-hits="<?php echo intval( $r['hits'] ?? 0 ); ?>" data-ptype="<?php echo esc_attr( $detailed_type ); ?>" data-added="<?php echo esc_attr( $added_ts ); ?>" style="position:relative;">
                                
                                <?php 
                                    $is_override = isset($r['override']) && ( $r['override'] === true || $r['override'] === '1' || $r['override'] === 'true' );
                                    
                                    // Conflict Check
                                    $conflict_args = array(
                                        'name'        => $r['slug'],
                                        'post_type'   => array( 'post', 'page' ),
                                        'post_status' => 'publish',
                                        'numberposts' => 1
                                    );
                                    $conflicts = get_posts($conflict_args);
                                    $has_conflict = !empty($conflicts);
                                ?>

                                <!-- Slug row: /slug text + inline copy button -->
                                <div class="rr-card-slug-wrap">
                                    <span class="rr-card-slug" id="rr-slug-<?php echo esc_attr( $r['id'] ); ?>" title="<?php echo esc_attr( $full_source ); ?>" data-copy="<?php echo esc_url( $full_source ); ?>">
                                        <span class="slash">/</span><span class="rr-slug-text"><?php echo esc_html( $r['slug'] ); ?></span>
                                    </span>
                                    <button class="rr-slug-copy rr-copy-btn" data-copy="<?php echo esc_url( $full_source ); ?>" title="<?php esc_attr_e('Copy source URL', 'romeo-redirect-manager'); ?>">
                                        <span class="dashicons dashicons-admin-page"></span>
                                    </button>
                                </div>

                                <!-- Info: target URL + copy -->
                                <div class="rr-card-info">
                                    <div class="rr-card-info-inner">
                                        <span class="rr-info-label"><?php echo esc_html( $target_label ); ?></span>
                                        <span class="rr-info-value" title="<?php echo esc_attr( $full_target ); ?>" data-copy="<?php echo esc_url( $full_target ); ?>"><?php echo esc_html( $target_display ); ?></span>
                                    </div>
                                    <button class="rr-inline-copy rr-copy-btn" data-copy="<?php echo esc_url( $full_target ); ?>" title="<?php esc_attr_e('Copy target URL', 'romeo-redirect-manager'); ?>">
                                        <span class="dashicons dashicons-admin-page"></span>
                                    </button>
                                </div>

                                <!-- Footer: status + hits -->
                                <div class="rr-card-footer">
                                    <div class="rr-status-block">
                                        <div class="rr-status-dot code-<?php echo esc_attr( $r['code'] ); ?>"></div>
                                        <span class="rr-status-label"><?php echo esc_attr( $r['code'] ); ?> Redirect</span>
                                    </div>
                                    <div class="rr-hits-badge">
                                        <span class="rr-hits-num"><?php echo isset($r['hits']) ? esc_html( $this->format_big_number( $r['hits'] ) ) : '0'; ?></span>
                                        <span class="rr-hits-lbl">HITS</span>
                                    </div>
                                    <?php if( $has_conflict && $is_override ) : ?>
                                    <div class="rr-override-indicator">
                                        <div class="rr-override-dot"></div>
                                        <span>Overridden</span>
                                    </div>
                                    <?php endif; ?>
                                    <?php if( isset( $r['date'] ) ) : ?>
                                    <div class="rr-date-badge"><?php echo esc_html( date_i18n( get_option('date_format'), strtotime($r['date']) ) ); ?></div>
                                    <?php endif; ?>
                                </div>

                                <!-- Bottom bar: [☐ checkbox LEFT] ......... [open | edit | delete RIGHT] -->
                                <div class="rr-card-bottom">
                                    <label class="rr-checkbox-wrapper rr-card-select" title="Select">
                                        <input type="checkbox" class="rr-bulk-checkbox" value="<?php echo esc_attr( $r['id'] ); ?>">
                                        <span class="rr-checkbox-style"></span>
                                    </label>
                                    <div class="rr-card-actions-group">
                                        <a href="<?php echo esc_url( $full_source ); ?>" target="_blank" class="rr-action-btn" title="Open source URL">
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
                                            data-override="<?php echo isset($r['override']) && $r['override'] ? '1' : '0'; ?>"
                                            <?php if( 'post' === $r['type'] && isset($data_attr['target_title']) ) : ?>
                                            data-target-title="<?php echo esc_attr( $data_attr['target_title'] ); ?>"
                                            <?php endif; ?>
                                        >
                                            <span class="dashicons dashicons-edit"></span>
                                        </button>
                                        <button onclick="rrDelete('<?php echo esc_attr( $r['id'] ); ?>')" class="rr-action-btn rr-delete-action-btn" title="Delete">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>



                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- Import Modal -->
            <div id="rr-import-modal" class="rr-modal-overlay hidden" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;">
                <div class="rr-creator" style="width:400px; margin:0; max-width:90%; position:relative;">
                    <h3 style="margin-bottom:8px;">Import Redirects</h3>
                    <p style="color:#64748b; margin-bottom:16px; font-size:13px;">Upload a JSON file exported from Romeo Redirects.</p>
                    
                    <!-- No conflicts section -->
                    <div id="rr-import-no-conflict-section">
                        <div style="display:flex; align-items:center; gap:10px; background:#f0fdf4; border:1px solid #bbf7d0; border-radius:10px; padding:14px 16px; margin-bottom:20px;">
                            <span class="dashicons dashicons-yes-alt" style="color:#16a34a; font-size:20px; width:20px; height:20px; flex-shrink:0;"></span>
                            <span style="font-size:13px; color:#15803d; font-weight:600;">No conflicts detected. All redirects will be added.</span>
                        </div>
                        <button id="rr-btn-merge" class="rr-btn rr-btn-primary" style="width:100%; justify-content:center;">
                            <span class="dashicons dashicons-upload" style="font-size:16px; width:16px; height:16px;"></span> Import All
                        </button>
                    </div>

                    <!-- Conflicts detected section -->
                    <div id="rr-import-conflict-section" class="hidden">
                        <div style="display:flex; align-items:center; gap:10px; background:#FFF5F5; border:1px solid #fecaca; border-radius:10px; padding:14px 16px; margin-bottom:16px;">
                            <span class="dashicons dashicons-warning" style="color:var(--rr-primary); font-size:20px; width:20px; height:20px; flex-shrink:0;"></span>
                            <div>
                                <span style="font-size:13px; color:var(--rr-primary); font-weight:700;"><span id="rr-import-conflict-count">0</span> conflict(s) found</span>
                                <p style="margin:2px 0 0; font-size:12px; color:#64748b;">Some slugs already exist. Choose how to handle them.</p>
                            </div>
                        </div>
                        <div style="margin-bottom:16px; background:#f8fafc; padding:12px; border-radius:8px; border:1px solid #e2e8f0;">
                            <label style="font-weight:500; display:flex; align-items:center; gap:8px; cursor:pointer; font-size:13px; color:#334155;">
                                <input type="checkbox" id="rr-import-update" checked style="accent-color:var(--rr-primary);"> 
                                Update existing redirects with same slug
                            </label>
                        </div>
                        <div style="display:flex; gap:10px;">
                            <button id="rr-btn-merge" class="rr-btn rr-btn-primary" style="flex:1; justify-content:center;">Merge</button>
                            <button id="rr-btn-overwrite" class="rr-btn rr-btn-secondary" style="flex:1; justify-content:center; color:#ef4444; border-color:#ef4444;">Overwrite All</button>
                        </div>
                    </div>

                    <button id="rr-btn-close-import" style="position:absolute; top:28px; right:20px; background:none; border:none; cursor:pointer; padding:4px;">
                        <span class="dashicons dashicons-no-alt" style="color:#94a3b8;"></span>
                    </button>
                </div>
            </div>

            <!-- Import Logs Modal -->
            <div id="rr-import-logs-modal" class="rr-modal-overlay hidden" style="position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999; display:flex; align-items:center; justify-content:center;">
                <div class="rr-creator" style="width:600px; margin:0; max-width:90%; position:relative; max-height:80vh; display:flex; flex-direction:column;">
                    <h3 style="margin-bottom:12px;">Import Status</h3>
                    <div id="rr-import-status-text" style="margin-bottom: 16px; font-weight: 600; color: #334155;"></div>
                    <div id="rr-import-logs-content" style="flex:1; overflow-y:auto; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-family: monospace; font-size: 12px; white-space: pre-wrap; margin-bottom: 24px;"></div>
                    <button id="rr-btn-close-logs" class="rr-btn rr-btn-primary" style="align-self: flex-end;">Close</button>
                    <button id="rr-btn-close-logs-icon" style="position:absolute; top:28px; right:20px; background:none; border:none; cursor:pointer; padding:4px;">
                        <span class="dashicons dashicons-no-alt" style="color:#94a3b8;"></span>
                    </button>
                </div>
            </div>

            <div class="rr-status-info" style="margin-top: 50px; padding: 32px; background: white; border-radius: 16px; font-size: 14px; box-shadow: 0 4px 12px rgba(0,0,0,0.05); border: 1px solid #f1f5f9;">
                <h4 style="margin-top: 0; margin-bottom: 24px; font-weight: 800; color: #1e293b; font-size: 16px; text-transform: uppercase; letter-spacing: 0.5px;">Understanding Redirect Status Codes</h4>
                <div class="rr-status-list" style="display: flex; flex-direction: column; gap: 12px;">
                    <div class="rr-status-item" data-code="301">
                        <div class="rr-status-item-header">
                            <div class="rr-code-dot code-301"></div>
                            <span class="rr-code-num">301</span>
                            <span class="rr-code-desc">Moved Permanently</span>
                        </div>
                        <div class="rr-status-item-body">
                            The requested resource has been assigned a new permanent URI. Best for SEO as search engines update their index to the new URL.
                        </div>
                    </div>
                    <div class="rr-status-item" data-code="302">
                        <div class="rr-status-item-header">
                            <div class="rr-code-dot code-302"></div>
                            <span class="rr-code-num">302</span>
                            <span class="rr-code-desc">Found (Temporary)</span>
                        </div>
                        <div class="rr-status-item-body">
                            The resource is temporarily located at a different URI. Used when you want search engines to keep the original URL indexed.
                        </div>
                    </div>
                    <div class="rr-status-item" data-code="307">
                        <div class="rr-status-item-header">
                            <div class="rr-code-dot code-307"></div>
                            <span class="rr-code-num">307</span>
                            <span class="rr-code-desc">Temporary Redirect</span>
                        </div>
                        <div class="rr-status-item-body">
                            Similar to 302, but guarantees the HTTP method (e.g., POST) and body remain unchanged during the redirect process.
                        </div>
                    </div>
                    <div class="rr-status-item" data-code="308">
                        <div class="rr-status-item-header">
                            <div class="rr-code-dot code-308"></div>
                            <span class="rr-code-num">308</span>
                            <span class="rr-code-desc">Permanent Redirect</span>
                        </div>
                        <div class="rr-status-item-body">
                            The permanent version of 307. It ensures the HTTP method doesn't change, while signaling a permanent move to search engines.
                        </div>
                    </div>
                </div>
            </div>

        </div>
            <!-- Bulk Actions Floating Bar -->
            <div id="rr-bulk-bar" class="rr-bulk-bar hidden">
                <div class="rr-bulk-count"><span id="rr-selected-count">0</span> selected</div>
                <button id="rr-bulk-select-all-btn" class="rr-btn-select-all-bulk" style="margin-right: 12px; background:none; border:none; color:#64748b; cursor:pointer; font-weight:600; font-size:13px; display:flex; align-items:center; gap:4px;">
                    <span class="dashicons dashicons-yes-alt"></span> <span class="rr-btn-text">Select All</span>
                </button>
                <button id="rr-bulk-clear-btn" class="rr-btn-clear-bulk">
                    <span class="dashicons dashicons-no-alt"></span> <span class="rr-btn-text">Clear Selection</span>
                </button>
                <button id="rr-bulk-delete-btn" class="rr-btn rr-btn-delete-bulk" style="background:#FFEBEE; color:var(--rr-primary); border-color:var(--rr-primary); border-width: 1px; border-style: solid; box-shadow:none; gap:6px;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg> <span class="rr-btn-text">Delete Selection</span>
                </button>
            </div>
        </div>
        <?php
    }

    public function render_404_page() {
        $option_404_enabled = get_option( 'romeo_redirect_404_enabled', '1' );
        $option_404_target  = get_option( 'romeo_redirect_404_target', '' );
        $option_404_type    = get_option( 'romeo_redirect_404_type', 'url' );
        $option_404_post_id = get_option( 'romeo_redirect_404_post_id', 0 );
        
        $post_title = '';
        if ( $option_404_post_id ) {
            $post_title = get_the_title( $option_404_post_id );
            if ( ! $post_title ) {
                $post_title = 'Deleted Post #' . $option_404_post_id;
            }
        }
        ?>
        <div class="rr-wrapper">
             <div class="rr-header">
                <div>
                    <h1><?php esc_html_e( '404 Settings', 'romeo-redirect-manager' ); ?></h1>
                    <small><?php esc_html_e( 'Manage how 404 errors are handled.', 'romeo-redirect-manager' ); ?></small>
                </div>
            </div>

            <div class="rr-creator rr-404-container">

                <!-- ── Enable / Disable Toggle ── -->
                <div class="rr-404-toggle-row" id="rr-404-toggle-row">
                    <div class="rr-404-toggle-info">
                        <span class="rr-404-toggle-title"><?php esc_html_e( '404 Handler', 'romeo-redirect-manager' ); ?></span>
                        <span class="rr-404-toggle-desc" id="rr-404-toggle-desc"><?php echo $option_404_enabled ? esc_html__( 'Active — 404s are being redirected', 'romeo-redirect-manager' ) : esc_html__( 'Inactive — 404s load normally', 'romeo-redirect-manager' ); ?></span>
                    </div>
                    <label class="rr-toggle-switch" title="<?php esc_attr_e( 'Enable / disable 404 redirect', 'romeo-redirect-manager' ); ?>">
                        <input type="checkbox" id="rr-404-enabled-check" <?php checked( $option_404_enabled, '1' ); ?> data-nonce="<?php echo esc_attr( wp_create_nonce( 'romerema_toggle_404_nonce' ) ); ?>">
                        <span class="rr-toggle-track"><span class="rr-toggle-thumb"></span></span>
                    </label>
                </div>

                <h3><?php esc_html_e( 'Default 404 Redirect', 'romeo-redirect-manager' ); ?></h3>
                <p class="rr-404-description">
                    <?php esc_html_e( 'You can choose to redirect all 404 (Not Found) errors to a specific specific URL, such as your homepage or a custom search page. If left empty, the default 404 behavior will be used.', 'romeo-redirect-manager' ); ?>
                </p>
                
                <form id="rr-404-form">
                    
                    <div class="rr-segmented-control">
                        <div class="rr-segment-btn <?php echo ($option_404_type === 'home') ? 'active' : ''; ?>" data-value="home">
                            <span class="dashicons dashicons-admin-home"></span> <?php esc_html_e( 'To Homepage', 'romeo-redirect-manager' ); ?>
                        </div>
                        <div class="rr-segment-btn <?php echo ($option_404_type === 'url') ? 'active' : ''; ?>" data-value="url">
                            <span class="dashicons dashicons-admin-site"></span> <?php esc_html_e( 'External URL', 'romeo-redirect-manager' ); ?>
                        </div>
                        <div class="rr-segment-btn <?php echo ($option_404_type === 'post') ? 'active' : ''; ?>" data-value="post">
                            <span class="dashicons dashicons-admin-post"></span> <?php esc_html_e( 'Existing Page', 'romeo-redirect-manager' ); ?>
                        </div>
                        <input type="hidden" name="type" id="rr-input-type" value="<?php echo esc_attr( $option_404_type ); ?>">
                    </div>

                    <!-- Input Area -->
                    <div class="rr-404-input-wrapper">
                        
                        <div id="rr-view-home" class="<?php echo ($option_404_type !== 'home') ? 'hidden' : ''; ?>">
                            <label class="rr-form-label"><?php esc_html_e( 'Homepage URL', 'romeo-redirect-manager' ); ?></label>
                            <input class="rr-input" type="text" disabled value="<?php echo esc_url( home_url('/') ); ?>" style="background: #f8fafc; color: #94a3b8;">
                            <p class="rr-form-help"><?php esc_html_e( 'Redirects all 404 traffic to your homepage.', 'romeo-redirect-manager' ); ?></p>
                        </div>

                        <!-- URL View -->
                        <div id="rr-view-url" class="<?php echo ($option_404_type !== 'url') ? 'hidden' : ''; ?>">
                            <label class="rr-form-label"><?php esc_html_e( 'Destination URL', 'romeo-redirect-manager' ); ?></label>
                            <input class="rr-input" type="text" name="url_404" placeholder="https://example.com/404-page" value="<?php echo esc_attr( $option_404_target ); ?>">
                             <p class="rr-form-help"><?php esc_html_e( 'Redirects all 404 traffic to this external URL.', 'romeo-redirect-manager' ); ?></p>
                        </div>

                        <!-- Post View -->
                        <div id="rr-view-post" class="<?php echo ($option_404_type !== 'post') ? 'hidden' : ''; ?>" style="position:relative;">
                            
                            <select name="target_post_id" class="rr-input">
                                <option value="0"><?php esc_html_e( 'Select a page...', 'romeo-redirect-manager' ); ?></option>
                                <?php 
                                $pages = get_pages(); 
                                foreach ( $pages as $page ) {
                                    $selected = ( intval($option_404_post_id) === $page->ID ) ? 'selected' : '';
                                    echo '<option value="' . esc_attr( $page->ID ) . '" ' . $selected . '>' . esc_html( $page->post_title ) . '</option>';
                                }
                                ?>
                            </select>
                             <p class="rr-form-help"><?php esc_html_e( 'Redirects all 404 traffic to this internal page.', 'romeo-redirect-manager' ); ?></p>
                        </div>

                    </div>
                    
                    <p class="rr-form-help main-help"><?php esc_html_e( 'Leave all fields empty to disable the 404 redirect behavior.', 'romeo-redirect-manager' ); ?></p>

                    <div class="rr-form-actions">
                         <button type="submit" id="rr-save-404-btn" class="rr-btn rr-btn-save"><?php esc_html_e( 'Save 404 Settings', 'romeo-redirect-manager' ); ?></button>
                    </div>
                </form>
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
        $override = isset( $_POST['override'] ) && $_POST['override'] === 'true';
        
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
                    $r['override'] = $override;
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
                    'override' => $override,
                    'hits'   => 0,
                    'date'   => current_time('mysql')
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
                'override' => $override,
                'hits'   => 0,
                'date'   => current_time('mysql')
            );
        }

        update_option( $this->option_key, $redirects );

        // Return saved data so JS can inject the card without reloading
        $saved = null;
        foreach ( $redirects as $r ) {
            if ( $r['id'] === $id ) { $saved = $r; break; }
        }
        
        $saved['ptype'] = 'url';
        if ( 'post' === $saved['type'] ) {
            $pt = get_post_type( $saved['target'] );
            $saved['ptype'] = ( $pt === 'page' ) ? 'page' : 'post';
        }

        wp_send_json_success( array(
            'redirect' => $saved,
            'is_edit'  => ! empty( $_POST['id'] ),
            'site_url' => trailingslashit( home_url() ),
            'date_fmt' => date_i18n( get_option( 'date_format' ), strtotime( $saved['date'] ?? 'now' ) ),
        ) );
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

    public function ajax_export_redirects() {
        check_ajax_referer( 'romerema_export_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permission denied' );

        $redirects = get_option( $this->option_key, array() );
        
        // Remove hits and ID from export
        foreach ( $redirects as &$redirect ) {
            if ( isset( $redirect['hits'] ) ) unset( $redirect['hits'] );
            if ( isset( $redirect['id'] ) ) unset( $redirect['id'] );
        }
        
        wp_send_json_success( $redirects );
    }

    public function ajax_import_redirects() {
        check_ajax_referer( 'romerema_import_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error( 'Permission denied' );

        $mode = isset( $_POST['mode'] ) ? sanitize_text_field( wp_unslash( $_POST['mode'] ) ) : 'merge';
        $update_existing = isset( $_POST['update_existing'] ) && $_POST['update_existing'] === 'true';

        // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Processed via json_decode immediately
        $json_data = isset( $_POST['data'] ) ? wp_unslash( $_POST['data'] ) : '';
        
        $imported_items = json_decode( $json_data, true );

        if ( ! is_array( $imported_items ) ) {
            wp_send_json_error( 'Invalid JSON data' );
        }

        // Validate structure
        $valid_items = array();
        foreach ( $imported_items as $item ) {
            if ( isset( $item['slug'] ) && isset( $item['target'] ) ) {
                // Sanitize
                $valid_items[] = array(
                    'id'     => isset( $item['id'] ) ? sanitize_text_field( $item['id'] ) : uniqid(),
                    'slug'   => sanitize_title( $item['slug'] ),
                    'type'   => isset( $item['type'] ) ? sanitize_text_field( $item['type'] ) : 'url',
                    'target' => ($item['type'] === 'url') ? esc_url_raw( $item['target'] ) : intval( $item['target'] ),
                    'code'   => isset( $item['code'] ) ? intval( $item['code'] ) : 301,
                    'hits'   => isset( $item['hits'] ) ? intval( $item['hits'] ) : 0
                );
            }
        }

        if ( empty( $valid_items ) ) {
            wp_send_json_error( 'No valid redirects found in file.' );
        }

        $current_redirects = get_option( $this->option_key, array() );
        
        $success_count = 0;
        $failed_count = 0;
        $logs = array();

        if ( $mode === 'overwrite' ) {
            $redirects = $valid_items;
            $success_count = count($valid_items);
            $logs[] = "Overwrote existing redirects with " . count($valid_items) . " items.";
        } else {
            $redirects = $current_redirects;
            foreach ( $valid_items as $new_item ) {
                $found = false;
                foreach ( $redirects as &$existing ) {
                    if ( $existing['slug'] === $new_item['slug'] ) {
                        // MERGE CONFLICT: Slug exists
                        if ( $update_existing ) {
                            $new_item['id'] = $existing['id'];
                            if ( empty($new_item['hits']) ) {
                                $new_item['hits'] = $existing['hits'];
                            }
                            if ( !isset($new_item['date']) ) {
                                $new_item['date'] = isset($existing['date']) ? $existing['date'] : current_time('mysql');
                            }
                            $existing = $new_item; 
                            $success_count++;
                            $logs[] = "Updated existing redirect for slug: " . $new_item['slug'];
                        } else {
                            $failed_count++;
                            $logs[] = "Skipped duplicate slug (no update): " . $new_item['slug'];
                        }
                        $found = true;
                        break;
                    }
                }
                if ( ! $found ) {
                    if ( !isset($new_item['date']) ) {
                        $new_item['date'] = current_time('mysql');
                    }
                    $redirects[] = $new_item;
                    $success_count++;
                    $logs[] = "Added new redirect for slug: " . $new_item['slug'];
                }
            }
        }

        update_option( $this->option_key, $redirects );
        wp_send_json_success( array(
            'success_count' => $success_count,
            'failed_count' => $failed_count,
            'logs' => $logs
        ) );
    }

    public function ajax_check_conflict() {
        check_ajax_referer( 'romerema_check_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $slug = isset( $_POST['slug'] ) ? sanitize_title( wp_unslash( $_POST['slug'] ) ) : '';
        
        if ( empty( $slug ) ) wp_send_json_success( array( 'exists' => false ) );

        // Check if slug exists as a page or post
        // Using get_page_by_path is appropriate for hierarchical pages and flattened logic
        // But for generic posts, we might also want to query.
        // However, most redirects conflict with Pages.
        // Let's use a dual check.
        
        $exists = false;
        $url = '';
        
        // 1. Check Page by path
        $page = get_page_by_path( $slug );
        if ( $page ) {
            $exists = true;
            $url = get_permalink( $page->ID );
        } 
        
        // 2. Fallback: simple WP_Query for post_name (slug) - covers posts
        if ( ! $exists ) {
            $args = array(
                'name'        => $slug,
                'post_type'   => 'any',
                'post_status' => 'publish',
                'numberposts' => 1
            );
            $posts = get_posts($args);
            if($posts) {
                $exists = true;
                $url = get_permalink( $posts[0]->ID );
            }
        }

        wp_send_json_success( array( 'exists' => $exists, 'url' => $url ) );
    }

    public function ajax_save_404() {
        check_ajax_referer( 'romerema_save_404_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();

        $type = isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'url';
        
        // Initialize defaults
        $url = '';
        $post_id = 0;

        // Only populate relevant fields based on type
        if ( 'url' === $type ) {
            $url = isset( $_POST['url_404'] ) ? esc_url_raw( wp_unslash( $_POST['url_404'] ) ) : '';
        } elseif ( 'post' === $type ) {
            $post_id = isset( $_POST['target_post_id'] ) ? intval( wp_unslash( $_POST['target_post_id'] ) ) : 0;
        } 
        // If 'home', both remain default (empty/0)

        update_option( 'romeo_redirect_404_target', $url );
        update_option( 'romeo_redirect_404_type', $type );
        update_option( 'romeo_redirect_404_post_id', $post_id );
        
        wp_send_json_success();
    }

    public function ajax_toggle_404_enabled() {
        check_ajax_referer( 'romerema_toggle_404_nonce', 'nonce' );
        if ( ! current_user_can( 'manage_options' ) ) wp_send_json_error();
        $enabled = isset( $_POST['enabled'] ) && ( $_POST['enabled'] === 'true' || $_POST['enabled'] === '1' ) ? '1' : '0';
        update_option( 'romeo_redirect_404_enabled', $enabled );
        wp_send_json_success( array( 'enabled' => $enabled === '1' ) );
    }

    public function ajax_toggle_override() {
        check_ajax_referer( 'romerema_save_nonce', 'nonce' );

        $id = isset( $_POST['id'] ) ? sanitize_text_field( $_POST['id'] ) : '';
        $state = isset( $_POST['state'] ) ? sanitize_text_field( $_POST['state'] ) : 'false';
        $override_val = ( $state === 'true' );

        if ( ! $id ) {
            wp_send_json_error( array( 'message' => 'Invalid ID' ) );
        }

        $redirects = get_option( $this->option_key, array() );
        $found = false;

        foreach ( $redirects as $key => $r ) {
            if ( $r['id'] === $id ) {
                $redirects[$key]['override'] = $override_val;
                $found = true;
                break;
            }
        }

        if ( $found ) {
            update_option( $this->option_key, $redirects );
            wp_send_json_success();
        } else {
            wp_send_json_error( array( 'message' => 'Redirect not found' ) );
        }
    }

    // ==========================================
    // DASHBOARD WIDGET
    // ==========================================

    public function register_dashboard_widget() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        wp_add_dashboard_widget(
            'romerema_dashboard_widget',
            __( 'Romeo Redirects', 'romeo-redirect-manager' ),
            array( $this, 'render_dashboard_widget' )
        );
    }

    public function render_dashboard_widget() {
        $redirects    = get_option( $this->option_key, array() );
        $total        = count( $redirects );
        $total_hits   = (int) array_sum( array_column( $redirects, 'hits' ) );
        $logo_url     = plugins_url( 'assets/images/icon.svg', dirname( __FILE__ ) . '/../romeo-redirect-manager.php' );
        $manage_url   = admin_url( 'admin.php?page=romeo-redirect-manager' );
        $new_url      = admin_url( 'admin.php?page=romeo-redirect-manager&rr_open=new' );
        $settings_url = admin_url( 'admin.php?page=romeo-redirect-manager-404' );
        $nonce        = wp_create_nonce( 'romerema_save_nonce' );

        // Count by type
        $url_count  = 0;
        $post_count = 0;
        foreach ( $redirects as $r ) {
            if ( 'post' === $r['type'] ) { $post_count++; } else { $url_count++; }
        }

        // Count by HTTP code
        $codes       = array( '301' => 0, '302' => 0, '307' => 0, '308' => 0 );
        $code_colors = array( '301' => '#3b82f6', '302' => '#f59e0b', '307' => '#8b5cf6', '308' => '#ec4899' );
        foreach ( $redirects as $r ) {
            $code = (string) $r['code'];
            if ( isset( $codes[ $code ] ) ) { $codes[ $code ]++; }
        }

        // Recent (newest first) and Top by hits
        $recent   = array_slice( array_reverse( $redirects ), 0, 4 );
        $by_hits  = $redirects;
        usort( $by_hits, function( $a, $b ) { return (int)( $b['hits'] ?? 0 ) - (int)( $a['hits'] ?? 0 ); } );
        $top_hits = array_slice( $by_hits, 0, 4 );

        // 404 settings
        $r404_type   = get_option( 'romeo_redirect_404_type', '' );
        $r404_active = ! empty( $r404_type );
        ?>
        <div class="rr-dw" id="romerema_dashboard_widget">

            <!-- ══ Hero Banner ══ -->
            <div class="rr-dw-hero">
                <div class="rr-dw-hero-brand">
                    <img src="<?php echo esc_url( $logo_url ); ?>" alt="Romeo" class="rr-dw-logo">
                    <div>
                        <div class="rr-dw-hero-title">Romeo Redirects</div>
                        <div class="rr-dw-hero-sub">redirect manager</div>
                    </div>
                </div>
                <div class="rr-dw-hero-stats">
                    <div class="rr-dw-big-stat">
                        <span class="rr-dw-big-num" id="rr-dw-total-redirects"><?php echo esc_html( $this->format_big_number( $total ) ); ?></span>
                        <span class="rr-dw-big-lbl"><?php esc_html_e( 'Redirects', 'romeo-redirect-manager' ); ?></span>
                    </div>
                    <div class="rr-dw-big-stat">
                        <span class="rr-dw-big-num rr-dw-hits"><?php echo esc_html( $this->format_big_number( $total_hits ) ); ?></span>
                        <span class="rr-dw-big-lbl"><?php esc_html_e( 'Total Hits', 'romeo-redirect-manager' ); ?></span>
                    </div>
                </div>
            </div>

            <!-- ══ Quick-Add Section ══ -->
            <div class="rr-dw-quick-add">
                <div class="rr-dw-qa-label"><?php esc_html_e( 'Quick Add Link', 'romeo-redirect-manager' ); ?></div>
                
                <div class="rr-dw-qa-main-card">
                    <!-- Step 1: Source -->
                    <div class="rr-dw-qa-item">
                        <div class="rr-dw-qa-item-header"><?php esc_html_e( '1. From Source (Slug)', 'romeo-redirect-manager' ); ?></div>
                        <div class="rr-dw-qa-input-box">
                            <span class="rr-dw-qa-pfx rr-dw-slash">/</span>
                            <input type="text" id="rr-dw-slug" placeholder="<?php esc_attr_e( 'your-slug', 'romeo-redirect-manager' ); ?>" autocomplete="off">
                        </div>
                    </div>

                    <!-- Step 2: Destination -->
                    <div class="rr-dw-qa-item">
                        <div class="rr-dw-qa-item-header"><?php esc_html_e( '2. To Destination', 'romeo-redirect-manager' ); ?></div>
                        <div class="rr-dw-qa-flex-row">
                            <select id="rr-dw-type" class="rr-select type-sel">
                                <option value="url"><?php esc_html_e( 'External URL', 'romeo-redirect-manager' ); ?></option>
                                <option value="post"><?php esc_html_e( 'Internal Page', 'romeo-redirect-manager' ); ?></option>
                            </select>
                            <div class="rr-dw-qa-input-box" id="rr-dw-target-wrap">
                                <input type="text" id="rr-dw-url" placeholder="https://example.com">
                                <div id="rr-dw-post-selector" class="rr-dw-hidden">
                                    <input type="text" id="rr-dw-post-search" placeholder="<?php esc_attr_e( 'Search content...', 'romeo-redirect-manager' ); ?>" autocomplete="off">
                                    <div id="rr-dw-post-results" class="rr-dw-post-results rr-dw-hidden"></div>
                                    <input type="hidden" id="rr-dw-target-post-id">
                                </div>
                            </div>
                        </div>
                        <div id="rr-dw-selected-post-row" class="rr-dw-hidden">
                            <div id="rr-dw-selected-post" class="rr-dw-selected-post"></div>
                        </div>
                    </div>

                    <!-- Step 3: Actions -->
                    <div class="rr-dw-qa-action-footer">
                        <div class="rr-dw-qa-status-group">
                            <span class="rr-dw-qa-footer-lbl"><?php esc_html_e( 'Code:', 'romeo-redirect-manager' ); ?></span>
                            <select id="rr-dw-code" class="rr-select" style="max-width:120px;height:38px;">
                                <option value="301" selected>301</option>
                                <option value="302">302</option>
                                <option value="307">307</option>
                                <option value="308">308</option>
                            </select>
                        </div>
                        <button id="rr-dw-qa-btn" class="rr-dw-qa-primary-btn" data-nonce="<?php echo esc_attr( $nonce ); ?>">
                            <span class="rr-dw-qa-btn-text"><?php esc_html_e( 'Add Redirect', 'romeo-redirect-manager' ); ?></span>
                            <span class="dashicons dashicons-arrow-right-alt2"></span>
                        </button>
                    </div>
                </div>
                <div id="rr-dw-qa-msg" class="rr-dw-qa-msg rr-dw-hidden"></div>
            </div>

            <?php if ( $total > 0 ) : ?>

            <!-- ══ Mini Stats Row ══ -->
            <div class="rr-dw-mini-stats">
                <div class="rr-dw-mini-stat"><span class="rr-dw-mini-num rr-c-url"><?php echo esc_html( number_format_i18n( $url_count ) ); ?></span><span class="rr-dw-mini-lbl"><?php esc_html_e( 'URL', 'romeo-redirect-manager' ); ?></span></div>
                <div class="rr-dw-mini-divider"></div>
                <div class="rr-dw-mini-stat"><span class="rr-dw-mini-num rr-c-page"><?php echo esc_html( number_format_i18n( $post_count ) ); ?></span><span class="rr-dw-mini-lbl"><?php esc_html_e( 'Page', 'romeo-redirect-manager' ); ?></span></div>
                <div class="rr-dw-mini-divider"></div>
                <?php foreach ( $codes as $code => $cnt ) : if ( $cnt === 0 ) continue; ?>
                <div class="rr-dw-mini-stat">
                    <span class="rr-dw-mini-dot" style="background:<?php echo esc_attr( $code_colors[ $code ] ); ?>;"></span>
                    <span class="rr-dw-mini-num" style="color:<?php echo esc_attr( $code_colors[ $code ] ); ?>;"><?php echo esc_html( number_format_i18n( $cnt ) ); ?></span>
                    <span class="rr-dw-mini-lbl"><?php echo esc_html( $code ); ?></span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- ══ Code Breakdown Bars ══ -->
            <div class="rr-dw-toggle-hd" data-target="rr-dw-breakdown">
                <span><?php esc_html_e( 'Breakdown', 'romeo-redirect-manager' ); ?></span>
                <span class="dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="rr-dw-bars rr-dw-collapsible" id="rr-dw-breakdown">
                <?php foreach ( $codes as $code => $cnt ) :
                    if ( $cnt === 0 ) continue;
                    $pct = $total > 0 ? round( $cnt / $total * 100 ) : 0;
                ?>
                <div class="rr-dw-bar-row">
                    <span class="rr-dw-bar-code" style="color:<?php echo esc_attr( $code_colors[ $code ] ); ?>"><?php echo esc_html( $code ); ?></span>
                    <div class="rr-dw-bar-track">
                        <div class="rr-dw-bar-fill" style="width:<?php echo esc_attr( $pct ); ?>%;background:<?php echo esc_attr( $code_colors[ $code ] ); ?>"></div>
                    </div>
                    <span class="rr-dw-bar-cnt"><?php echo esc_html( $this->format_big_number( $cnt ) ); ?></span>
                    <span class="rr-dw-bar-pct"><?php echo esc_html( $pct ); ?>%</span>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="rr-dw-sep-line"></div>

            <!-- ══ Top by Hits ══ -->
            <?php if ( $total_hits > 0 ) : ?>
            <div class="rr-dw-toggle-hd rr-dw-section-mt" data-target="rr-dw-hits">
                <span><?php esc_html_e( 'Top Hits', 'romeo-redirect-manager' ); ?></span>
                <span class="dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <ul class="rr-dw-list rr-dw-collapsible" id="rr-dw-hits">
                <?php foreach ( $top_hits as $r ) :
                    $h = (int)( $r['hits'] ?? 0 ); if ( $h === 0 ) continue;
                    $code_s = (string) $r['code'];
                    $bw = round( $h / max(1,(int)$top_hits[0]['hits']) * 100 );
                ?>
                <li class="rr-dw-item">
                    <div class="rr-status-dot code-<?php echo esc_attr( $code_s ); ?>" style="margin-right: 2px;"></div>
                    <div class="rr-dw-item-slug-wrap">
                        <a href="<?php echo esc_url( $manage_url . '&rr_s=' . urlencode($r['slug']) ); ?>" class="rr-dw-slug" data-code="<?php echo esc_attr($code_s); ?>">
                            <span class="rr-dw-slug-text"><span class="rr-dw-slash">/</span><?php echo esc_html( $r['slug'] ); ?></span>
                            <span class="dashicons dashicons-external"></span>
                        </a>
                        <div class="rr-dw-hit-track"><div class="rr-dw-hit-fill" style="width:<?php echo esc_attr($bw); ?>%; background: var(--dot-color-<?php echo esc_attr($code_s); ?>);"></div></div>
                    </div>
                    <span class="rr-dw-hcount"><?php echo esc_html( $this->format_big_number( $h ) ); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <div class="rr-dw-sep-line"></div>
            <?php endif; ?>

            <!-- ══ Recently Added ══ -->
            <div class="rr-dw-toggle-hd rr-dw-section-mt" data-target="rr-dw-recent">
                <span><?php esc_html_e( 'Recently Added', 'romeo-redirect-manager' ); ?></span>
                <span class="dashicons dashicons-arrow-down-alt2"></span>
            </div>
            <div class="rr-dw-collapsible" id="rr-dw-recent">
                <ul class="rr-dw-list" id="rr-dw-recent-list">
                    <?php foreach ( $recent as $r ) :
                        $code_s = (string) $r['code'];
                        $tgt    = $r['target'];
                        if ( 'post' === $r['type'] ) {
                            $t   = get_the_title( $r['target'] );
                            $tgt = $t ? $t : __( 'Deleted', 'romeo-redirect-manager' );
                        }
                        $ds = isset( $r['date'] ) ? date_i18n( 'M j', strtotime( $r['date'] ) ) : '';
                    ?>
                    <li class="rr-dw-item">
                        <div class="rr-status-dot code-<?php echo esc_attr( $code_s ); ?>" style="margin-right: 2px;"></div>
                        <a href="<?php echo esc_url( $manage_url . '&rr_s=' . urlencode($r['slug']) ); ?>" class="rr-dw-slug" title="/<?php echo esc_attr( $r['slug'] ); ?>" data-code="<?php echo esc_attr($code_s); ?>">
                            <span class="rr-dw-slug-text"><span class="rr-dw-slash">/</span><?php echo esc_html( $r['slug'] ); ?></span>
                            <span class="dashicons dashicons-external"></span>
                        </a>
                        <span class="rr-dw-target" title="<?php echo esc_attr( $tgt ); ?>"><?php echo esc_html( $tgt ); ?></span>
                        <?php if ( $ds ) : ?><span class="rr-dw-date"><?php echo esc_html( $ds ); ?></span><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- ══ 404 Status ══ -->
            <!-- 404 Status + Toggle -->
            <?php
            $r404_enabled = get_option( 'romeo_redirect_404_enabled', '1' );
            $r404_on      = $r404_enabled === '1';
            $toggle_nonce = wp_create_nonce( 'romerema_toggle_404_nonce' );
            ?>
            <div class="rr-dw-404 <?php echo $r404_on ? 'rr-dw-404-on' : 'rr-dw-404-off'; ?>" id="rr-dw-404-row">
                <span class="rr-dw-404-dot" id="rr-dw-404-dot"></span>
                <span id="rr-dw-404-label"><?php
                    if ( $r404_on && $r404_active ) {
                        esc_html_e( '404 handler active', 'romeo-redirect-manager' );
                        echo ' <span class="rr-dw-404-type">('. esc_html( $r404_type ) . ')</span>';
                    } elseif ( $r404_on && ! $r404_active ) {
                        esc_html_e( '404 on - no destination set', 'romeo-redirect-manager' );
                    } else {
                        esc_html_e( '404 handler off', 'romeo-redirect-manager' );
                    }
                ?></span>
                <div style="display:flex;align-items:center;gap:6px;margin-left:auto;">
                    <label class="rr-dw-toggle-wrap" title="Toggle 404 handler">
                        <input type="checkbox" id="rr-dw-404-toggle"
                            <?php checked( $r404_on ); ?>
                            data-nonce="<?php echo esc_attr( $toggle_nonce ); ?>"
                            style="position:absolute;opacity:0;width:0;height:0;">
                        <span class="rr-dw-toggle-track"><span class="rr-dw-toggle-thumb"></span></span>
                    </label>
                    <a href="<?php echo esc_url( $settings_url ); ?>" class="rr-dw-404-edit"><?php esc_html_e( 'Config', 'romeo-redirect-manager' ); ?></a>
                </div>

            </div>

            <?php endif; // $total > 0 ?>


            <!-- ══ Footer ══ -->
            <div class="rr-dw-footer">
                <a href="<?php echo esc_url( $manage_url ); ?>" class="rr-dw-foot-link">
                    <span class="dashicons dashicons-randomize"></span> <?php esc_html_e( 'All Redirects', 'romeo-redirect-manager' ); ?>
                </a>
                <a href="<?php echo esc_url( $new_url ); ?>" class="rr-dw-foot-link rr-dw-foot-new">
                    <span class="dashicons dashicons-plus-alt2"></span> <?php esc_html_e( 'Create Redirect', 'romeo-redirect-manager' ); ?>
                </a>
            </div>

        </div><!-- .rr-dw -->

        <script>
        (function(){
            // Custom Select for Widget
            function initWidgetSelects() {
                var widget = document.querySelector('.rr-dw');
                if (!widget) return;
                var selects = widget.querySelectorAll('select.rr-select:not(.rr-select-native)');
                
                selects.forEach(function(select) {
                    var wrapper = document.createElement('div');
                    wrapper.className = 'rr-select-custom ' + select.className;
                    if (select.id) wrapper.id = 'rr-select-custom-' + select.id;
                    select.parentNode.insertBefore(wrapper, select);
                    wrapper.appendChild(select);
                    select.classList.add('rr-select-native');

                    var trigger = document.createElement('div');
                    trigger.className = 'rr-select-trigger';
                    var initialText = select.options[select.selectedIndex] ? select.options[select.selectedIndex].text : 'Select...';
                    trigger.innerHTML = '<span class="rr-selected-text">' + initialText + '</span><span class="dashicons dashicons-arrow-down-alt2"></span>';
                    
                    if (['301','302','307','308'].includes(select.value)) {
                        trigger.setAttribute('data-code', select.value);
                    }
                    wrapper.appendChild(trigger);

                    var menu = document.createElement('div');
                    menu.className = 'rr-select-dropdown';
                    Array.from(select.options).forEach(function(option, idx) {
                        var optDiv = document.createElement('div');
                        optDiv.className = 'rr-select-option' + (select.selectedIndex === idx ? ' selected' : '');
                        optDiv.textContent = option.text;
                        optDiv.dataset.value = option.value;
                        optDiv.addEventListener('click', function(e) {
                            e.stopPropagation();
                            select.value = option.value;
                            trigger.querySelector('.rr-selected-text').textContent = option.text;
                            
                            if (['301','302','307','308'].includes(option.value)) {
                                trigger.setAttribute('data-code', option.value);
                            } else {
                                trigger.removeAttribute('data-code');
                            }

                            menu.classList.remove('show');
                            trigger.classList.remove('active');
                            menu.querySelectorAll('.rr-select-option').forEach(function(o) { o.classList.remove('selected'); });
                            optDiv.classList.add('selected');
                            select.dispatchEvent(new Event('change', { bubbles: true }));
                        });
                        menu.appendChild(optDiv);
                    });
                    wrapper.appendChild(menu);

                    trigger.addEventListener('click', function(e) {
                        e.stopPropagation();
                        document.querySelectorAll('.rr-select-dropdown.show').forEach(function(m) {
                            if (m !== menu) {
                                m.classList.remove('show');
                                m.previousSibling.classList.remove('active');
                            }
                        });
                        menu.classList.toggle('show');
                        trigger.classList.toggle('active');
                    });
                });
            }

            document.addEventListener('click', function() {
                document.querySelectorAll('.rr-select-dropdown.show').forEach(function(menu) {
                    menu.classList.remove('show');
                    menu.previousSibling.classList.remove('active');
                });
            });

            initWidgetSelects();

            var typeSelect = document.getElementById('rr-dw-type');
            var targetUrl = document.getElementById('rr-dw-url');
            var postSelector = document.getElementById('rr-dw-post-selector');
            var selectedPostRow = document.getElementById('rr-dw-selected-post-row');
            
            typeSelect.addEventListener('change', function() {
                if (this.value === 'url') {
                    targetUrl.classList.remove('rr-dw-hidden');
                    postSelector.classList.add('rr-dw-hidden');
                } else {
                    targetUrl.classList.add('rr-dw-hidden');
                    postSelector.classList.remove('rr-dw-hidden');
                }
            });

            // Post Search
            var postSearch = document.getElementById('rr-dw-post-search');
            var postResults = document.getElementById('rr-dw-post-results');
            var targetPostId = document.getElementById('rr-dw-target-post-id');
            var selectedPost = document.getElementById('rr-dw-selected-post');
            var searchTimer;

            postSearch.addEventListener('input', function() {
                clearTimeout(searchTimer);
                var q = this.value.trim();
                if (q.length < 2) {
                    postResults.classList.add('rr-dw-hidden');
                    return;
                }

                searchTimer = setTimeout(function() {
                    fetch(ajaxurl + '?action=romerema_search_posts&term=' + encodeURIComponent(q) + '&nonce=' + btn.dataset.nonce)
                    .then(function(r){ return r.json(); })
                    .then(function(r){
                        if (r.success && r.data.length) {
                            var html = '';
                            r.data.forEach(function(item) {
                                var capType = item.type.charAt(0).toUpperCase() + item.type.slice(1);
                                html += '<div class="rr-dw-post-item" data-id="'+item.id+'" data-title="'+item.title+'"><span class="ptype">'+capType+'</span> '+item.title+'</div>';
                            });
                            postResults.innerHTML = html;
                            postResults.classList.remove('rr-dw-hidden');
                        } else {
                            postResults.innerHTML = '<div class="rr-dw-post-item" style="cursor:default;opacity:0.6;">No results</div>';
                            postResults.classList.remove('rr-dw-hidden');
                        }
                    });
                }, 300);
            });

            postResults.addEventListener('click', function(e) {
                var item = e.target.closest('.rr-dw-post-item');
                if (!item || !item.dataset.id) return;
                
                targetPostId.value = item.dataset.id;
                selectedPost.innerHTML = '<span>' + item.dataset.title + '</span><span class="dashicons dashicons-no-alt remove-post"></span>';
                selectedPostRow.classList.remove('rr-dw-hidden');
                postSelector.classList.add('rr-dw-hidden');
                postResults.classList.add('rr-dw-hidden');
                postSearch.value = '';
            });

            selectedPostRow.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-post')) {
                    targetPostId.value = '';
                    selectedPostRow.classList.add('rr-dw-hidden');
                    postSelector.classList.remove('rr-dw-hidden');
                }
            });

            var btn = document.getElementById('rr-dw-qa-btn');
            if (!btn) return;
            btn.addEventListener('click', function() {
                var slug = document.getElementById('rr-dw-slug').value.trim().replace(/^\/+/, '');
                var type = typeSelect.value;
                var code = document.getElementById('rr-dw-code').value;
                var url  = targetUrl.value.trim();
                var pid  = targetPostId.value;
                var msg  = document.getElementById('rr-dw-qa-msg');
                var nonce = btn.dataset.nonce;

                if (!slug) { showMsg('Please enter a slug.', false); return; }
                if (type === 'url' && !url) { showMsg('Please enter a target URL.', false); return; }
                if (type === 'post' && !pid) { showMsg('Please select a page.', false); return; }

                // Auto-lowercase
                slug = slug.toLowerCase();
                if (type === 'url') {
                    url = url.toLowerCase();
                    if (!/^https?:\/\//.test(url)) url = 'https://' + url;
                }

                btn.disabled = true;
                btn.querySelector('.rr-dw-qa-btn-text').textContent = '...';

                var fd = new FormData();
                fd.append('action', 'romerema_save_redirect');
                fd.append('nonce', nonce);
                fd.append('slug', slug);
                fd.append('type', type);
                fd.append('target_url', url);
                fd.append('target_post_id', pid);
                fd.append('code', code);
                fd.append('override', 'false');

                fetch(ajaxurl, { method: 'POST', body: fd })
                .then(function(r){ return r.json(); })
                .then(function(r){
                    if (r.success) {
                        showMsg('✓ Redirect added!', true);
                        
                        // Dynamically update Recently Added list
                        var list = document.getElementById('rr-dw-recent-list');
                        if (list) {
                            var li = document.createElement('li');
                            li.className = 'rr-dw-item';
                            li.style.opacity = '0';
                            li.style.transform = 'translateY(-10px)';
                            li.style.transition = 'all 0.4s ease';
                            
                            var manageUrl = '<?php echo esc_js( $manage_url ); ?>';
                            var displayTarget = (type === 'post') ? selectedPost.querySelector('span').textContent : url;
                            var badgeClass = 'rr-dw-b' + code;
                            
                            var itemHtml = '<span class="rr-dw-badge ' + badgeClass + '">' + code + '</span>' +
                                           '<a href="' + manageUrl + '&rr_s=' + encodeURIComponent(slug) + '" class="rr-dw-slug" title="/' + slug + '">' +
                                           '<span class="rr-dw-slug-text">/' + slug + '</span><span class="dashicons dashicons-external"></span></a>' +
                                           '<span class="rr-dw-target" title="' + displayTarget + '">' + displayTarget + '</span>' +
                                           '<span class="rr-dw-date">Just now</span>';
                            li.innerHTML = itemHtml;
                            list.insertBefore(li, list.firstChild);
                            
                            if (list.children.length > 4) list.removeChild(list.lastChild);
                            
                            requestAnimationFrame(function(){
                                li.style.opacity = '1';
                                li.style.transform = 'translateY(0)';
                            });
                        }
                        
                        // Update total count
                        var totalEl = document.getElementById('rr-dw-total-redirects');
                        if (totalEl) {
                            var current = parseInt(totalEl.textContent.replace(/,/g, ''));
                            totalEl.textContent = (current + 1).toLocaleString();
                        }

                        // Reset fields
                        document.getElementById('rr-dw-slug').value = '';
                        targetUrl.value = '';
                        targetPostId.value = '';
                        selectedPostRow.classList.add('rr-dw-hidden');
                        if (type === 'post') postSelector.classList.remove('rr-dw-hidden');
                    } else {
                        showMsg(r.data || 'Error saving.', false);
                    }
                    btn.disabled = false;
                    btn.querySelector('.rr-dw-qa-btn-text').textContent = '<?php echo esc_js( __( 'Add', 'romeo-redirect-manager' ) ); ?>';
                })
                .catch(function(){
                    showMsg('Network error.', false);
                    btn.disabled = false;
                    btn.querySelector('.rr-dw-qa-btn-text').textContent = '<?php echo esc_js( __( 'Add', 'romeo-redirect-manager' ) ); ?>';
                });

                function showMsg(text, ok) {
                    msg.textContent = text;
                    msg.className = 'rr-dw-qa-msg ' + (ok ? 'rr-dw-qa-ok' : 'rr-dw-qa-err');
                    setTimeout(function(){ msg.className = 'rr-dw-qa-msg rr-dw-hidden'; }, 3500);
                }
            });

            // Add on Enter Support
            [document.getElementById('rr-dw-slug'), document.getElementById('rr-dw-url'), document.getElementById('rr-dw-post-search'), document.getElementById('rr-dw-type'), document.getElementById('rr-dw-code')].forEach(function(el){
                if (!el) return;
                el.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        var btn = document.getElementById('rr-dw-qa-btn');
                        if (btn) btn.click();
                    }
                });
            });

            // Cookie Helpers
            function setRRCookie(name, value, days) {
                var expires = "";
                if (days) {
                    var date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/";
            }
            function getRRCookie(name) {
                var nameEQ = name + "=";
                var ca = document.cookie.split(';');
                for (var i = 0; i < ca.length; i++) {
                    var c = ca[i];
                    while (c.charAt(0) == ' ') c = c.substring(1, c.length);
                    if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
                }
                return null;
            }

            // Collapsible Logic
            document.querySelectorAll('.rr-dw-toggle-hd').forEach(function(hd) {
                var targetId = hd.dataset.target;
                var target = document.getElementById(targetId);
                if (!target) return;

                // Load state
                if (getRRCookie('rr_dw_collapsed_' + targetId) === '1') {
                    target.classList.add('collapsed');
                    hd.classList.add('collapsed');
                }

                hd.addEventListener('click', function() {
                    var isCollapsed = target.classList.toggle('collapsed');
                    hd.classList.toggle('collapsed');
                    setRRCookie('rr_dw_collapsed_' + targetId, isCollapsed ? '1' : '0', 30);
                });
            });
        })();

            // 404 Toggle
            (function() {
                var toggle = document.getElementById('rr-dw-404-toggle');
                if (!toggle) return;
                toggle.addEventListener('change', function() {
                    var enabled = toggle.checked;
                    var row = document.getElementById('rr-dw-404-row');
                    var label = document.getElementById('rr-dw-404-label');
                    var dot = document.getElementById('rr-dw-404-dot');
                    var fd = new FormData();
                    fd.append('action', 'romerema_toggle_404');
                    fd.append('nonce', toggle.dataset.nonce);
                    fd.append('enabled', enabled ? 'true' : 'false');
                    fetch(ajaxurl, { method: 'POST', body: fd })
                    .then(function(r) { return r.json(); })
                    .then(function(r) {
                        if (r.success) {
                            if (row) {
                                row.className = 'rr-dw-404 ' + (enabled ? 'rr-dw-404-on' : 'rr-dw-404-off');
                            }
                            if (label) {
                                label.textContent = enabled ? '404 handler active' : '404 handler off';
                            }
                        } else {
                            toggle.checked = !enabled; // revert
                        }
                    })
                    .catch(function() { toggle.checked = !enabled; });
                });
            })();
        </script>
        <?php
    }

    private function format_big_number( $n ) {
        $n = (int) $n;
        if ( $n >= 1000000000 ) {
            return round( $n / 1000000000, 1 ) . 'B+';
        }
        if ( $n >= 1000000 ) {
            return round( $n / 1000000, 1 ) . 'M+';
        }
        if ( $n >= 1000 ) {
            return round( $n / 1000, 1 ) . 'k';
        }
        return number_format_i18n( $n );
    }
}
