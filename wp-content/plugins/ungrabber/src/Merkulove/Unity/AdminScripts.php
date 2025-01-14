<?php
/**
 * UnGrabber
 * A most effective way to protect your online content from being copied or grabbed
 * Exclusively on https://1.envato.market/ungrabber
 *
 * @encoding        UTF-8
 * @version         3.0.4
 * @copyright       (C) 2018 - 2023 Merkulove ( https://merkulov.design/ ). All rights reserved.
 * @license         Commercial Software
 * @contributors    Dmitry Merkulov (dmitry@merkulov.design)
 * @support         help@merkulov.design
 **/

namespace Merkulove\Ungrabber\Unity;

/** Exit if accessed directly. */
if ( ! defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit;
}

/**
 * Class adds admin js scripts.
 *
 * @since 1.0.0
 *
 **/
final class AdminScripts {

	/**
	 * The one true AdminScripts.
	 *
	 * @var AdminScripts
	 **/
	private static $instance;

	/**
	 * Sets up a new AdminScripts instance.
	 *
	 * @access public
	 **/
	private function __construct() {

		add_action( 'admin_enqueue_scripts', [ $this, 'admin_scripts' ] );

	}

	/**
	 * Add JavaScrips for admin area.
	 *
	 * @return void
	 **/
	public function admin_scripts() {

	    /** Add scripts on plugin setting page. */
		$this->settings_scripts();

	}

	/**
	 * Scripts for plugin setting page.
	 *
	 * @return void
	 **/
	private function settings_scripts() {

		/** Add styles only on setting page */
		$screen = get_current_screen();
		if ( null === $screen ) { return; }

		/** Add styles only on plugin settings page */
		if ( ! in_array( $screen->base, Plugin::get_menu_bases(), true ) ) { return; }

        wp_enqueue_script( 'mdp-ungrabber-ui', Plugin::get_url() . 'src/Merkulove/Unity/assets/js/merkulov-ui' . Plugin::get_suffix() . '.js', [], Plugin::get_version(), true );

        /** Add tab related scripts */
        if ( isset( $_GET[ 'tab' ] ) && $_GET[ 'tab' ] === 'migration' ) {
            wp_enqueue_script( 'mdp-ungrabber-unity-migration-tab', Plugin::get_url() . 'src/Merkulove/Unity/assets/js/migration' . Plugin::get_suffix() . '.js', [], Plugin::get_version(), true );
        }

        /** Prepare values to pass to JS. */
        $to_js = [
            'ajaxURL'   => admin_url('admin-ajax.php'),
            'restBase'  => get_rest_url(),
            'nonce'     => wp_create_nonce( 'ungrabber-unity' ), // Nonce for security.
            'translation' => [
                'pidErrorElements' => esc_html__( 'Wrong format! It looks like you are using the Envato Elements license code instead of CodeCanyon purchase ID.', 'ungrabber' ),
                'pidErrorInvoice'  => esc_html__( 'Wrong format! It looks like you are using the Invoice Number instead of CodeCanyon purchase ID.', 'ungrabber' ),
                'pidErrorOrder'    => esc_html__( 'Wrong format! It looks like you are using the Order ID instead of CodeCanyon purchase ID.', 'ungrabber' ),
                'pidError'   => esc_html__( 'Wrong format! The purchase code must be 36 characters long', 'ungrabber' ),
            ]
        ];

        wp_enqueue_script( 'mdp-ungrabber-unity-admin', Plugin::get_url() . 'src/Merkulove/Unity/assets/js/admin' . Plugin::get_suffix() . '.js', [ 'jquery' ], Plugin::get_version(), true );
        wp_localize_script( 'mdp-ungrabber-unity-admin', 'mdpUngrabberUnity', $to_js );
        wp_enqueue_script( 'mdp-ungrabber-admin', Plugin::get_url() . 'js/admin' . Plugin::get_suffix() . '.js', [ 'jquery' ], Plugin::get_version(), true );

	}

	/**
	 * Main AdminScripts Instance.
	 * Insures that only one instance of AdminScripts exists in memory at any one time.
	 *
	 * @static
	 * @return AdminScripts
	 **/
	public static function get_instance() {

        /** @noinspection SelfClassReferencingInspection */
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof AdminScripts ) ) {

			self::$instance = new AdminScripts;

		}

		return self::$instance;

	}

}
