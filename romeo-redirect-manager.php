<?php
/**
 * Plugin Name: Romeo Redirect Manager
 * Description: A modern, lightweight redirect manager. Redirect slugs to external URLs or internal posts with style.
 * Version:     1.1.1
 * Author:      Harsh Trivedi
 * Author URI:  https://harsh98trivedi.github.io/
 * Text Domain: romeo-redirect-manager
 * License:     GPL-2.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Autoloader - simplified manual require for now strictly for this task scope
require_once plugin_dir_path( __FILE__ ) . 'includes/class-romeo-admin.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/class-romeo-redirect.php';

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
			}

			// Initialize Frontend Redirects
			new Romerema_Redirect();
		}
	}

	// Initialize the plugin.
	Romerema_Plugin::get_instance();
}
