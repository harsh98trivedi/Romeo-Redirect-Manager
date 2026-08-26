<?php
/**
 * Romeo Redirect Manager Uninstall
 *
 * Triggered when the plugin is deleted via the WordPress Admin Plugins screen.
 * Removes all database options and transients created by the plugin.
 *
 * @package Romeo_Redirect_Manager
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

/**
 * Delete all options and data associated with Romeo Redirect Manager.
 */
function romerema_uninstall_cleanup() {
	$options_to_delete = array(
		'romeo_redirect_manager_rules',
		'romeo_redirect_404_enabled',
		'romeo_redirect_404_target',
		'romeo_redirect_404_type',
		'romeo_redirect_404_post_id',
		'romeo_sitemap_scan_results',
		'romeo_sitemap_notice_dismissed',
	);

	foreach ( $options_to_delete as $option_name ) {
		delete_option( $option_name );
		// Clean site-wide network option if set on multisite
		delete_site_option( $option_name );
	}
}

if ( is_multisite() ) {
	$romerema_sites = get_sites( array( 'fields' => 'ids' ) );
	if ( ! empty( $romerema_sites ) ) {
		foreach ( $romerema_sites as $romerema_blog_id ) {
			switch_to_blog( $romerema_blog_id );
			romerema_uninstall_cleanup();
			restore_current_blog();
		}
	}
} else {
	romerema_uninstall_cleanup();
}
