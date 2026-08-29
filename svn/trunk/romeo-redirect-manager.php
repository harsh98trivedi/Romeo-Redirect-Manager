<?php
/**
 * Plugin Name:       Romeo Redirect Manager
 * Plugin URI:        https://wordpress.org/plugins/romeo-redirect-manager/
 * Description:       A modern, lightweight redirect manager with XML sitemap scanning, full 301/302/307/308 support, 404 capture, and SEO analytics.
 * Version:           1.6.3
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * Author:            Harsh Trivedi
 * Author URI:        https://harsh98trivedi.github.io/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       romeo-redirect-manager
 * Domain Path:       /languages
 *
 * @package           Romeo_Redirect_Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define Plugin Constants.
if ( ! defined( 'ROMEREMA_VERSION' ) ) {
	define( 'ROMEREMA_VERSION', '1.6.3' );
}
if ( ! defined( 'ROMEREMA_FILE' ) ) {
	define( 'ROMEREMA_FILE', __FILE__ );
}
if ( ! defined( 'ROMEREMA_PATH' ) ) {
	define( 'ROMEREMA_PATH', plugin_dir_path( __FILE__ ) );
}
if ( ! defined( 'ROMEREMA_URL' ) ) {
	define( 'ROMEREMA_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'ROMEREMA_BASENAME' ) ) {
	define( 'ROMEREMA_BASENAME', plugin_basename( __FILE__ ) );
}

/**
 * Load plugin textdomain for internationalization.
 */
function romerema_load_textdomain() {
	load_plugin_textdomain(
		'romeo-redirect-manager',
		false,
		dirname( plugin_basename( __FILE__ ) ) . '/languages'
	);
}
add_action( 'plugins_loaded', 'romerema_load_textdomain' );

/**
 * Plugin activation hook callback.
 */
function romerema_activate() {
	// Initialize default options if not already present.
	if ( false === get_option( 'romeo_redirect_manager_rules' ) ) {
		add_option( 'romeo_redirect_manager_rules', array(), '', 'no' );
	}
	if ( false === get_option( 'romeo_redirect_404_enabled' ) ) {
		add_option( 'romeo_redirect_404_enabled', '1', '', 'no' );
	}
}
register_activation_hook( __FILE__, 'romerema_activate' );

/**
 * Plugin deactivation hook callback.
 */
function romerema_deactivate() {
	// Flush rewrite rules or clean temporary runtime caches if needed.
}
register_deactivation_hook( __FILE__, 'romerema_deactivate' );

/**
 * Add quick action links to the plugins page.
 *
 * @param array $links Existing action links.
 * @return array Modified action links.
 */
function romerema_plugin_action_links( $links ) {
	$plugin_links = array(
		'<a href="' . esc_url( admin_url( 'admin.php?page=romeo-redirect-manager' ) ) . '">' . esc_html__( 'Settings', 'romeo-redirect-manager' ) . '</a>',
		'<a href="' . esc_url( admin_url( 'admin.php?page=romeo-redirect-sitemap' ) ) . '">' . esc_html__( 'Sitemap Scanner', 'romeo-redirect-manager' ) . '</a>',
	);
	return array_merge( $plugin_links, $links );
}
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'romerema_plugin_action_links' );

/**
 * Add row meta links under plugin details on plugins page.
 *
 * @param array  $links Existing row meta links.
 * @param string $file  Plugin file name.
 * @return array Modified row meta links.
 */
function romerema_plugin_row_meta( $links, $file ) {
	if ( plugin_basename( __FILE__ ) === $file ) {
		$row_meta = array(
			'docs'    => '<a href="' . esc_url( 'https://github.com/harsh98trivedi/Romeo-Redirect-Manager#readme' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'romeo-redirect-manager' ) . '</a>',
			'support' => '<a href="' . esc_url( 'https://wordpress.org/support/plugin/romeo-redirect-manager/' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Support', 'romeo-redirect-manager' ) . '</a>',
			'donate'  => '<a href="' . esc_url( 'https://buymeacoffee.com/harshtrivedi' ) . '" target="_blank" rel="noopener noreferrer" style="color: #0073aa; font-weight: 500;">' . esc_html__( 'Donate', 'romeo-redirect-manager' ) . '</a>',
		);
		return array_merge( $links, $row_meta );
	}
	return $links;
}
add_filter( 'plugin_row_meta', 'romerema_plugin_row_meta', 10, 2 );

// Autoloader - require plugin core classes.
require_once ROMEREMA_PATH . 'includes/class-romeo-admin.php';
require_once ROMEREMA_PATH . 'includes/class-romeo-redirect.php';
require_once ROMEREMA_PATH . 'includes/class-romeo-sitemap-scanner.php';

if ( ! class_exists( 'Romerema_Plugin' ) ) {

	/**
	 * Main Plugin Class
	 */
	class Romerema_Plugin {

		/**
		 * Singleton instance.
		 *
		 * @var Romerema_Plugin|null
		 */
		private static $instance = null;

		/**
		 * Get singleton instance.
		 *
		 * @return Romerema_Plugin
		 */
		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor.
		 */
		public function __construct() {
			// Initialize Admin.
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				new Romerema_Admin();
				new Romerema_Sitemap_Scanner();
			}

			// Initialize Frontend Redirects.
			new Romerema_Redirect();
		}
	}

	// Initialize the plugin.
	Romerema_Plugin::get_instance();
}
