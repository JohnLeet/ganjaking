<?php
/**
 * Default skin functions
 *
 * @package YITH\FrontendManager\Templates
 */

/**
 * SKIN CUSTOM CODE GOES HERE
 */

/**
 * Remove the unused scripts and styles from the active theme
 */
function yfmfw_remove_scripts() {
	$wp_theme   = wp_get_theme();
	$theme_name = strtolower( $wp_theme->Name ); // phpcs:ignore WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

	// Specific for YITH Nielsen theme.
	if ( 'nielsen' === $theme_name || 'rémy' === $theme_name || 'mindig' === $theme_name || 'desire-sexy-shop' === $theme_name ) {

		/**
		 * Redefine the smooth scroll js function to avoid the functionality
		 */
		$remove_scroll = 'jQuery.srSmoothscroll = function() {
		  return false;
		}';
		wp_add_inline_script( 'yit-common', $remove_scroll );
	}

}

add_action( 'wp_enqueue_scripts', 'yfmfw_remove_scripts', 100 );
