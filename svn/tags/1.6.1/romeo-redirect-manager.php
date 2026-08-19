<?php
/**
 * Plugin Name: Romeo Redirect Manager
 * Description: A modern, lightweight redirect manager with XML sitemap scanning, full 301/302/307/308 support, 404 capture, and SEO analytics.
 * Version:     1.6.1
 * Author:      Harsh Trivedi
 * Author URI:  https://harsh98trivedi.github.io/
 * Text Domain: romeo-redirect-manager
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! defined( 'ROMEREMA_VERSION' ) ) {
	define( 'ROMEREMA_VERSION', '1.6.1' );
}

// Autoloader - simplified manual require for plugin classes
require_once plugin_dir_path( __FILE__ ) . 'includes/class-romeo-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-romeo-redirect.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-romeo-sitemap-scanner.php';

if ( ! class_exists( 'Romerema_Plugin' ) ) {

	/**
	 * Main Plugin Class
	 */
	class Romerema_Plugin {

		private static $instance = null;

		public static function get_instance() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		public function __construct() {
			// Initialize Admin
			if ( is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
				new Romerema_Admin();
				new Romerema_Sitemap_Scanner();
			}

			// Initialize Frontend Redirects
			new Romerema_Redirect();
		}
	}

	// Initialize the plugin.
	Romerema_Plugin::get_instance();
}
