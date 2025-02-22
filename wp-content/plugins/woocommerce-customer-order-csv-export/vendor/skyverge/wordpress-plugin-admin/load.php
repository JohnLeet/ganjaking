<?php
/**
 * WordPress Admin
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * This file is based on the loader for Action Scheduler
 * @link https://github.com/woocommerce/action-scheduler/blob/3.1.5/action-scheduler.php
 *
 * @author    SkyVerge
 * @copyright Copyright (c) 2020, SkyVerge, Inc. (info@skyverge.com)
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

// PHP 7.0+ required
if ( PHP_VERSION_ID < 70000 ) {
	return;
}

// only proceed if some other plugin hasn't already loaded this version
if ( ! function_exists( 'sv_wordpress_plugin_admin_initialize_1_0_1' ) ) {

	// load the versions handler unless already loaded
	if ( ! class_exists( '\SkyVerge\WordPress\Plugin_Admin\Versions' ) ) {

		require_once( 'src/Versions.php' );

		add_action( 'plugins_loaded', [ \SkyVerge\WordPress\Plugin_Admin\Versions::class, 'initialize_latest_version' ], 99, 0 );
	}

	// register v1.0.2
	\SkyVerge\WordPress\Plugin_Admin\Versions::register( '1.0.2', 'sv_wordpress_plugin_admin_initialize_1_0_2' );

	/**
	 * Initializes the WordPress Admin package v1.0.2.
	 *
	 * This function should not be called directly.
	 *
	 * @since 1.0.2
	 */
	function sv_wordpress_plugin_admin_initialize_1_0_2() {

		require_once( 'src/Package.php' );

		\SkyVerge\WordPress\Plugin_Admin\Package::instance();
	}
}
