<?php
/**
 * WooCommerce Ajax Layered Navigation.
 *
 * @since 2.0.0
 */

namespace Themesquad\WC_Ajax_Layered_Nav;

defined( 'ABSPATH' ) || exit;

use Themesquad\WC_Ajax_Layered_Nav\Admin\Admin;
use Themesquad\WC_Ajax_Layered_Nav\Internal\Traits\Singleton;

/**
 * Plugin class.
 */
class Plugin {

	use Singleton;

	/**
	 * Constructor.
	 *
	 * @since 2.0.0
	 */
	protected function __construct() {
		$this->define_constants();
		$this->init();
	}

	/**
	 * Define constants.
	 *
	 * @since 2.0.0
	 */
	private function define_constants() {
		$this->define( 'WC_AJAX_LAYERED_NAV_VERSION', '2.0.0' );
		$this->define( 'WC_AJAX_LAYERED_NAV_PATH', plugin_dir_path( WC_AJAX_LAYERED_NAV_FILE ) );
		$this->define( 'WC_AJAX_LAYERED_NAV_URL', plugin_dir_url( WC_AJAX_LAYERED_NAV_FILE ) );
		$this->define( 'WC_AJAX_LAYERED_NAV_BASENAME', plugin_basename( WC_AJAX_LAYERED_NAV_FILE ) );
	}

	/**
	 * Define constant if not already set.
	 *
	 * @since 2.0.0
	 *
	 * @param string      $name  The constant name.
	 * @param string|bool $value The constant value.
	 */
	private function define( $name, $value ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Init plugin.
	 *
	 * @since 2.0.0
	 */
	private function init() {
		add_action( 'before_woocommerce_init', array( $this, 'declare_compatibility' ) );
		add_action( 'init', array( $this, 'load_plugin_textdomain' ) );

		if ( is_admin() ) {
			Admin::init();
		}
	}

	/**
	 * Declares compatibility with the WC features.
	 *
	 * @since 2.0.0
	 */
	public function declare_compatibility() {
		// Compatible with the 'High-Performance Order Storage' feature.
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', WC_AJAX_LAYERED_NAV_FILE, true );
		}
	}

	/**
	 * Load plugin text domain.
	 *
	 * @since 2.0.0
	 */
	public function load_plugin_textdomain() {
		load_plugin_textdomain( 'woocommerce-ajax-layered-nav', false, dirname( WC_AJAX_LAYERED_NAV_BASENAME ) . '/languages' );
	}
}
