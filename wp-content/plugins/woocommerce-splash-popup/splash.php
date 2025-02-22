<?php
/**
 * Backwards compat.
 *
 * @since 1.2.5
 * @package woocommerce-splash-popup
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$active_plugins = get_option( 'active_plugins', array() );
foreach ( $active_plugins as $key => $active_plugin ) {
	if ( strstr( $active_plugin, '/splash.php' ) ) {
		$active_plugins[ $key ] = str_replace( '/splash.php', '/woocommerce-splash-popup.php', $active_plugin );
	}
}
update_option( 'active_plugins', $active_plugins );
