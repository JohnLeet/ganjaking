<?php
/**
 * Plugin Name: WooCommerce Servired/RedSys Spain Gateway
 * Plugin URI: https://woocommerce.com/products/redsys-gateway/
 * Description: Extends WooCommerce with RedSys gateway.
 * Version: 24.0.0
 * Author: José Conti
 * Author URI: https://www.joseconti.com/
 * Tested up to: 6.4
 * WC requires at least: 7.4
 * WC tested up to: 8.3
 * Woo: 187871:50392593e834002d8bee386333d1ed3c
 * Text Domain: woocommerce-redsys
 * Domain Path: /languages/
 * Copyright: (C) 2013 - 2023 José Conti
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 *
 * @package WooCommerce Redsys Gateway WooCommerce.com
 * @author  José Conti
 * @since   1.0
 */

use Automattic\WooCommerce\Utilities\OrderUtil;

if ( ! defined( 'REDSYS_VERSION' ) ) {
	define( 'REDSYS_VERSION', '24.0.0' );
}
if ( ! defined( 'REDSYS_LICENSE_SITE_ID' ) ) {
	define( 'REDSYS_LICENSE_SITE_ID', 1 );
}
if ( ! defined( 'REDSYS_FLUSH_VERSION' ) ) {
	define( 'REDSYS_FLUSH_VERSION', 200 );
}

if ( ! defined( 'REDSYS_PLUGIN_URL_P' ) ) {
	define( 'REDSYS_PLUGIN_URL_P', plugin_dir_url( __FILE__ ) );
}

if ( ! defined( 'REDSYS_PLUGIN_PATH_P' ) ) {
	define( 'REDSYS_PLUGIN_PATH_P', plugin_dir_path( __FILE__ ) );
}

if ( ! defined( 'REDSYS_PLUGIN_FILE' ) ) {
	define( 'REDSYS_PLUGIN_FILE', __FILE__ );
}

if ( ! defined( 'REDSYS_ABSPATH' ) ) {
	define( 'REDSYS_ABSPATH', dirname( REDSYS_PLUGIN_FILE ) . '/' );
}

if ( ! defined( 'REDSYS_PLUGIN_BASENAME' ) ) {
	define( 'REDSYS_PLUGIN_BASENAME', plugin_basename( REDSYS_PLUGIN_FILE ) );
}

if ( ! defined( 'REDSYS_POST_UPDATE_URL_P' ) ) {
	define( 'REDSYS_POST_UPDATE_URL_P', 'https://redsys.joseconti.com/2023/10/27/woocommerce-redsys-gateway-24-0-x/' );
}

if ( ! defined( 'REDSYS_ITEM_NANE' ) ) {
	define( 'REDSYS_ITEM_NANE', 'woocommerce-gateway-redsys' );
}
$spaces = wp_spaces_regexp();
$prefix = preg_replace( "/$spaces/", '_', strtolower( REDSYS_ITEM_NANE ) );
if ( ! defined( 'REDSYS_PREFIX' ) ) {
	define( 'REDSYS_PREFIX', $prefix );
}

add_action(
	'before_woocommerce_init',
	function() {
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

require_once REDSYS_PLUGIN_PATH_P . 'includes/defines.php';
require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-redsys-push-notifications.php'; // Version 18.0 Add Push Notifications.

/**
 * Package: WooCommerce Redsys Gateway
 * Plugin URI: https://woocommerce.com/es-es/products/redsys-gateway/
 * Copyright: (C) 2013 - 2023 José Conti
 */
function WCPSD2() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-redsys-psd2.php'; // PSD2 class for Redsys.
	return new WC_Gateway_Redsys_PSD2();
}

/**
 * Global functions WCRed
 */
function WCRed() { // phpcs:ignore WordPress.NamingConventions.ValidFunctionName.FunctionNameInvalid
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-redsys-global.php'; // Global class for global functions.
	return new WC_Gateway_Redsys_Global();
}

/**
 * Package: WooCommerce Redsys Gateway
 * Plugin URI: https://woocommerce.com/es-es/products/redsys-gateway/
 * Copyright: (C) 2013 - 2023 José Conti
 */
function redsys_deactivate_plugins() {
	include_once REDSYS_PLUGIN_DATA_PATH_P . 'deactivate-plugins.php';
	$plugins = array();
	$plugins = plugins_to_deactivate();
	deactivate_plugins( $plugins, true );
}
add_action( 'admin_init', 'redsys_deactivate_plugins' );

// Site Health.
require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-redsys-site-health.php';
require_once REDSYS_PLUGIN_NOTICE_PATH_P . 'notices.php';
require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-redsys-card-images.php';
require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-redsys-qr-codes.php';
require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-redsys-advanced-setings.php';
require_once REDSYS_PLUGIN_PATH_P . 'bloques-redsys/bloques-redsys.php';


if ( ! class_exists( 'WooRedsysAPI' ) ) {
	require_once REDSYS_PLUGIN_API_REDSYS_PATH . 'apiRedsys7.php';
	define( 'REDSYS_API_LOADED', 'yes' );
}

if ( ! class_exists( 'WooRedsysAPIWS' ) ) {
	require_once REDSYS_PLUGIN_API_REDSYS_PATH . 'apiRedsysWs7.php';
	define( 'REDSYS_API_LOADED_WS', 'yes' );
}

require_once REDSYS_PLUGIN_API_REDSYS_PATH . 'initRedsysApi.php';

if ( defined( 'REDSYS_WOOCOMMERCE_VERSION' ) ) {
	return;
}

add_action( 'plugins_loaded', 'woocommerce_gateway_redsys_premium_init', 12 );

/**
 * Add Query Vars
 *
 * @param array $vars Query vars.
 */
function redsys_add_query_vars( $vars ) {
	$vars[] = 'add-redsys-method';
	return $vars;
}
add_filter( 'query_vars', 'redsys_add_query_vars' );

/**
 * Add Endpoint
 */
function redsys_add_endpoint() {
	global $wp_rewrite;

	add_rewrite_endpoint( 'add-redsys-method', EP_ALL );

	if ( WCRed()->has_to_flush() ) {
		$wp_rewrite->flush_rules();
	}
}
add_action( 'init', 'redsys_add_endpoint', 0 );
add_action( 'parse_request', array( 'WC_Gateway_Redsys', 'redsys_handle_requests' ) );

/**
 * Query Vars Pay
 *
 * @param array $vars Query vars.
 */
function redsys_add_query_vars_pay( $vars ) {
	$vars[] = 'redsys-add-card';
	return $vars;
}
add_filter( 'query_vars', 'redsys_add_query_vars_pay' );

/**
 * Add Endpoint Pay
 */
function redsys_add_endpoint_pay() {
	global $wp_rewrite;

	add_rewrite_endpoint( 'redsys-add-card', EP_ALL );

	if ( WCRed()->has_to_flush() ) {
		$wp_rewrite->flush_rules();
	}
}
add_action( 'init', 'redsys_add_endpoint_pay', 0 );

/**
 * Custom Template Pay
 *
 * @param string $template Template.
 */
function redsys_custom_template_pay( $template ) {
	global $wp_query;

	if ( isset( $wp_query->query_vars['redsys-add-card'] ) ) {
		$template = REDSYS_PLUGIN_PATH_P . 'includes/redsys-add-card.php';
	}
	return $template;
}
add_filter( 'template_include', 'redsys_custom_template_pay' );

/**
 * WooCommerce Redsys Gateway Init
 */
function woocommerce_gateway_redsys_premium_init() {

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-redsys-scheduled-actions.php';

	load_plugin_textdomain( 'woocommerce-redsys', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );

	/**
	 * Add Select2 to users Test Field
	 */
	function redsys_add_select2_to_users_test() {

		$screen = get_current_screen();

		if ( 'woocommerce_page_wc-settings' === $screen->id ) {
			wp_register_script( 'redsys-select2', REDSYS_PLUGIN_URL_P . 'assets/js/test-users-min.js', array( 'jquery', 'select2' ), REDSYS_VERSION, true );
			wp_enqueue_script( 'redsys-select2' );
		}
		if ( 'woocommerce_page_paygold-page' === $screen->id ) {
			wp_register_script( 'redsys-select2b', REDSYS_PLUGIN_URL_P . 'assets/js/pay-gold-search-user.js', array( 'jquery', 'select2' ), REDSYS_VERSION, true );
			wp_enqueue_script( 'redsys-select2b' );
		}
	}
	/**
	 * Get users for Test Field
	 */
	function redsys_get_users_settings_ajax_callback() {

		if ( ! isset( $_GET['q'] ) ) {
			wp_die();
		}
		$search = sanitize_text_field( wp_unslash( $_GET['q'] ) );
		$args   = array(
			'search'         => "*{$search}*",
			'fields'         => 'all',
			'search_columns' => array( 'user_login', 'user_email', 'user_nicename' ),
		);
		// The User Query.
		$user_query = new WP_User_Query( $args );
		$users      = $user_query->get_results();

		if ( ! empty( $users ) ) {
			$return = array();
			foreach ( $users as $user ) {
				$user_info = get_userdata( $user->ID );
				$return[]  = array( $user_info->ID, $user_info->user_email );
			}
			echo wp_json_encode( $return );
			wp_die();
		} else {
			wp_die();
		}
	}

	add_action( 'admin_enqueue_scripts', 'redsys_add_select2_to_users_test' );
	add_action( 'wp_ajax_redsys_get_users_settings_search_users', 'redsys_get_users_settings_ajax_callback' );
	add_action( 'wp_ajax_nopriv_redsys_get_users_settings_search_users_show_gateway', 'redsys_get_users_settings_ajax_callback' );
	add_action( 'wp_ajax_redsys_get_users_settings_search_users_show_gateway', 'redsys_get_users_settings_ajax_callback' );
	add_action( 'wp_ajax_nopriv_verificar_estado_pago', array( 'WC_Gateway_Bizum_Checkout_Redsys', 'verificar_estado_pago_ajax' ) );
	add_action( 'wp_ajax_verificar_estado_pago', array( 'WC_Gateway_Bizum_Checkout_Redsys', 'verificar_estado_pago_ajax' ) );

	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-redsys.php';

	/**
	 * Add the Gateway to WooCommerce
	 *
	 * @param array $methods WooCommerce payment methods.
	 */
	function woocommerce_add_gateway_redsys_gateway( $methods ) {
		$methods[] = 'WC_Gateway_redsys';
		return $methods;
	}
	add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_gateway_redsys_gateway' );

	// inlude metaboxes.
	require_once REDSYS_PLUGIN_METABOXES_PATH . 'metaboxes.php';

	/**
	 * Redirect users to Checkout after add to cart
	 *
	 * @param string $checkout_url URl checkout.
	 */
	function redsys_add_to_cart_redirect( $checkout_url ) {

		if ( ! is_checkout() && ! is_wc_endpoint_url() ) {

			$redirect = WCRed()->get_redsys_option( 'checkoutredirect', 'redsys' );

			if ( 'yes' === $redirect ) {
				$checkout_url = wc_get_checkout_url();
			}
		}
		return $checkout_url;
	}
	add_filter( 'woocommerce_add_to_cart_redirect', 'redsys_add_to_cart_redirect' );

	require_once REDSYS_PLUGIN_STATUS_PATH . 'status.php';

	/**
	 * Make Preauthorized Order editable
	 *
	 * @param bol $editable Order Editable (true/false).
	 * @param obj $order Order object.
	 */
	function redsys_preauthorized_is_editable( $editable, $order ) {

		if ( 'redsys-pre' === $order->get_status() ) {
			$editable = true;
		}
		return $editable;
	}
	add_filter( 'wc_order_is_editable', 'redsys_preauthorized_is_editable', 10, 2 );

	/**
	 * Add button to confirm preauthorization
	 *
	 * @param obj $order Order object.
	 */
	function redsys_add_buttom_preauthorization_ok( $order ) {
		if ( 'redsys-pre' === $order->get_status() ) {
			echo '<button type="button" class="button redsys-confirm-preauthorization">' . esc_html__( 'Confirm Preauthorization', 'woocommerce-redsys' ) . '</button>';
		} else {
			return;
		}
	}
	add_action( 'woocommerce_order_item_add_action_buttons', 'redsys_add_buttom_preauthorization_ok' );

	/**
	 * Add button to charge deposits.
	 *
	 * @param obj $order Order object.
	 */
	function redsys_add_buttom_charge_deposits( $order ) {
		if ( 'partial-payment' === $order->get_status() ) {
			$amount = 0;

			foreach ( $order->get_items() as $item ) {
				if ( ! empty( $item['is_deposit'] ) ) {
					$deposit_full_amount_ex_vat = '';
					$deposit_full_amount        = '';
					$deposit_full_amount_ex_vat = (float) $item['_deposit_full_amount_ex_tax'];
					$deposit_full_amount        = (float) $item['_deposit_full_amount'];

					if ( ! empty( $deposit_full_amount ) ) {
						$amount = $deposit_full_amount + $amount;
					} else {
						$amount = $deposit_full_amount_ex_vat + $amount;
					}
				}
			}
			$total     = $order->get_total();
			$remainder = $amount - $total;

			echo '<button type="button" class="button redsys-charge-full-deposit">' . esc_html__( 'Collect the remainder With Redsys: ', 'woocommerce-redsys' ) . esc_html( $remainder ) . '</button>';
		} else {
			return;
		}
	}
	add_action( 'woocommerce_order_item_add_action_buttons', 'redsys_add_buttom_charge_deposits' );

	/**
	 * Redsys CSS
	 */
	function redsys_css() {
		global $post_type;

		$current_screen = get_current_screen();

		if ( 'shop_order' === $post_type || 'woocommerce_page_wc-settings' === $current_screen->id || 'woocommerce_page_wc-orders' === $current_screen->id ) {
			wp_register_style( 'redsys-css', plugins_url( 'assets/css/redsys-css.css', __FILE__ ), array(), REDSYS_VERSION );
			wp_enqueue_style( 'redsys-css' );
		}

	}
	add_action( 'admin_enqueue_scripts', 'redsys_css' );

	/**
	 * Redsys Front CSS
	 */
	function redsys_add_front_css() {

		if ( is_wc_endpoint_url( 'add-payment-method' ) ) {
			wp_enqueue_style( 'redsys-style-front', REDSYS_PLUGIN_URL_P . 'assets/css/redsys-add-payment-method.css', array(), REDSYS_VERSION );
		}
	}
	add_action( 'wp_enqueue_scripts', 'redsys_add_front_css' );

	/**
	 * Redsys preauthorized JS
	 */
	function redsys_preauthorized_js() {
		global $post;

		$screen = get_current_screen();

		if ( is_admin() && ( 'shop_order' === $screen->id || 'woocommerce_page_wc-orders' === $screen->id ) ) {

			if ( isset( $_GET['id'] ) ) {
				$order_id = sanitize_text_field( wp_unslash( $_GET['id'] ) );
			}

			wp_enqueue_script( 'redsysajax-script', plugins_url( '/assets/js/preauthorizations-min.js', __FILE__ ), array( 'jquery', 'stupidtable', 'jquery-tiptip' ), REDSYS_VERSION, true );

			if ( isset( $post->ID ) ) {
				$post_id = $post->ID;
			} else {
				$post_id = $order_id;
			}
			$params = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'postid'   => $post_id,
			);
			wp_localize_script( 'redsysajax-script', 'redsys_preauthorizations', $params );
		}
	}
	add_action( 'admin_enqueue_scripts', 'redsys_preauthorized_js' );

	/**
	 * Redsys charge deposit JS
	 */
	function redsys_charge_deposit_js() {
		global $post;

		$screen = get_current_screen();

		if ( is_admin() && 'shop_order' === $screen->id ) {
			wp_enqueue_script( 'redsysajax-script-2', plugins_url( '/assets/js/woo-deposits-charge-min.js', __FILE__ ), array( 'jquery', 'stupidtable', 'jquery-tiptip' ), REDSYS_VERSION, true );

			if ( isset( $post->ID ) ) {
				$post_id = $post->ID;
			} else {
				$post_id = '';
			}
			$params = array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'postid'   => $post_id,
			);
			wp_localize_script( 'redsysajax-script-2', 'redsys_charge_depo', $params );
		}
	}
	add_action( 'admin_enqueue_scripts', 'redsys_charge_deposit_js' );

	// Adding all Redsys Gateways.

	$private_product     = WCRed()->get_redsys_option( 'privateproduct', 'redsys' );
	$sent_email_template = WCRed()->get_redsys_option( 'sentemailscustomers', 'redsys' );
	$thankyoucheck       = WCRed()->get_redsys_option( 'sendemailthankyou', 'redsys' );
	$thankyourecipe      = WCRed()->get_redsys_option( 'showthankyourecipe', 'redsys' );

	// Adding Private Products.
	if ( 'yes' === $private_product ) {
		require_once REDSYS_PLUGIN_PATH_P . 'includes/private-products.php';
	}

	// Adding emails Templates.
	if ( 'yes' === $sent_email_template ) {
		require_once REDSYS_PLUGIN_PATH_P . 'includes/emails/class-redsys-wc-email.php';
	}

	// Adding Thank you Check.
	if ( 'yes' === $thankyoucheck ) {
		require_once REDSYS_PLUGIN_PATH_P . 'includes/thank-you-checks.php';
	}

	// Adding Thank you Recipe.
	if ( 'yes' === $thankyourecipe ) {
		require_once REDSYS_PLUGIN_PATH_P . 'includes/thank-you-receipe.php';
	}

	// Adding Plugin List Links.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-plugin-list-links-redsys-premium.php'; // Version 16.1. Add Links to plugin list.

	// Adding Dashboard Widget.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-redsys-wp-dashboard.php'; // Version 16.1. WordPress Dashboard.

	// Adding all Redsys Gateways.

	// Adding Bizum.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-bizum-redsys.php'; // Bizum Version 6.0.

	// Adding MasterPass.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-masterpass-redsys.php'; // MasterPass Version 7.0.

	// Adding Redsys Bank Transfer.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-redsys-bank-transfer.php'; // Bank Transfer Version 9.0.

	// Adding InSIte.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-insite-redsys.php'; // Insite version 10.0. (version 15 refactoring).

	// Adding Direct Debit stand alone.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-direct-debit-redsys.php'; // Insite version 11.0.

	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-bizum-checkout-redsys.php'; // Bizum Checkout Version 21.0.

	// Adding Tokens in admin user profile.

	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-redsys-profile.php'; // Version 14.0.

	// Adding Pay Gold.

	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-paygold-redsys.php'; // Paygold Version 16.0.

	require_once REDSYS_PLUGIN_PATH_P . 'includes/banner-live.php';

	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-rest-redsys.php'; // Version 17.1.0.

	// Adding Google Pay redirection.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-googlepay-redirection-redsys.php'; // Google Pay version 21.2.0.

	// Adding Google Pay Checkout.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-googlepay-checkout.php'; // Google Pay version 22.0.0.

	// Adding Apple Pay Checkout.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-wc-gateway-apple-pay-checkout.php'; // Apple Pay version 23.0.0.

	// Pay with One Click.
	require_once REDSYS_PLUGIN_CLASS_PATH_P . 'class-redsys-pay-one-clic.php'; // Pay with one click 24.0.0.

	/**
	 * Add Paygold page.
	 */
	if ( WCRed()->is_gateway_enabled( 'paygold' ) ) {
		include_once REDSYS_PLUGIN_PATH_P . 'includes/paygold-page.php';
		add_action( 'admin_menu', 'paygold_menu' );
	}
	/**
	 * Add Paygold menu.
	 */
	function paygold_menu() {
		global $paygold_page;

		$paygold_page = add_submenu_page(
			'woocommerce',
			esc_html__( 'Pay Gold Tools', 'woocommerce-redsys' ),
			esc_html__( 'Pay Gold Tools', 'woocommerce-redsys' ),
			'manage_options',
			'paygold-page',
			'paygold_page'
		);
	}
	/**
	 * Paygold Ajax Callback.
	 */
	function redsys_paygond_ajax_callback() {

		if ( is_admin() ) {

			if ( ! isset( $_GET['q'] ) ) {
				wp_die();
			}

			$search = sanitize_text_field( wp_unslash( $_GET['q'] ) );
			$args   = array(
				'search'         => "*{$search}*",
				'fields'         => 'all',
				'search_columns' => array( 'user_login', 'user_email', 'user_nicename' ),
			);

			// The User Query.
			$user_query = new WP_User_Query( $args );
			$users      = $user_query->get_results();
			if ( ! empty( $users ) ) {
				$return = array();
				foreach ( $users as $user ) {
					$user_info = get_userdata( $user->ID );
					$return[]  = array( $user_info->ID, $user_info->user_email );
				}
				echo wp_json_encode( $return );
				die;
			} else {
				die;
			}
		}
	}
	add_action( 'wp_ajax_woo_search_users_paygold', 'redsys_paygond_ajax_callback' );

	/**
	 * Paygold CSS.
	 */
	function redsys_paygold_css() {
		wp_register_style( 'redsys_css_slect2', REDSYS_PLUGIN_URL_P . 'assets/css/select2.css', false, REDSYS_VERSION );
		wp_enqueue_style( 'redsys_css_slect2' );
	}
	add_action( 'admin_enqueue_scripts', 'redsys_paygold_css' );

	/**
	 * Add Ajax Actions.
	 */
	function redsys_add_ajax_actions() {
		if ( ! is_checkout() && ! is_wc_endpoint_url() ) {

			// Ajax Preautorizaciones.
			add_action( 'wp_ajax_redsys_preauth_action', array( 'WC_Gateway_Redsys', 'redsys_preauthorized_js_callback' ) );
			// Ajax carga deposits.
			add_action( 'wp_ajax_redsys_charge_depo_action', array( 'WC_Gateway_redsys', 'redsys_charge_depo_js_callback' ) );
		}

		add_action( 'wp_ajax_check_token_insite_from_action', array( 'WC_Gateway_InSite_Redsys', 'check_token_insite_from_action' ) );
		add_action( 'wp_ajax_nopriv_check_token_insite_from_action', array( 'WC_Gateway_InSite_Redsys', 'check_token_insite_from_action' ) );
		// Conservar.
		add_action( 'wp_ajax_check_token_insite_from_action_checkout', array( 'WC_Gateway_InSite_Redsys', 'check_token_insite_from_action_checkout' ) );
		add_action( 'wp_ajax_nopriv_check_token_insite_from_action_checkout', array( 'WC_Gateway_InSite_Redsys', 'check_token_insite_from_action_checkout' ) );

		// Add Ajax Apple Pay.
		add_action( 'wp_ajax_validate_merchant', array( 'WC_Gateway_Apple_Pay_Checkout', 'handle_ajax_request_applepay' ) );
		add_action( 'wp_ajax_nopriv_validate_merchant', array( 'WC_Gateway_Apple_Pay_Checkout', 'handle_ajax_request_applepay' ) );
		add_action( 'wp_ajax_check_payment_status', array( 'WC_Gateway_Apple_Pay_Checkout', 'check_payment_status' ) );
		add_action( 'wp_ajax_nopriv_check_payment_status', array( 'WC_Gateway_Apple_Pay_Checkout', 'check_payment_status' ) );
	}
	add_action( 'admin_init', 'redsys_add_ajax_actions' );

	if ( OrderUtil::custom_orders_table_usage_is_enabled() ) {
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', array( 'WC_Gateway_Redsys', 'redsys_add_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', array( 'WC_Gateway_Redsys', 'redsys_bulk_actions_handler' ), 10, 3 );
	} else {
		add_filter( 'bulk_actions-edit-shop_order', array( 'WC_Gateway_Redsys', 'redsys_add_bulk_actions' ) );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( 'WC_Gateway_Redsys', 'redsys_bulk_actions_handler' ), 10, 3 );
	}

	// Needed for allow "pay" 0€.
	add_filter( 'woocommerce_cart_needs_payment', array( 'WC_Gateway_Redsys_Global', 'cart_needs_payment' ), 10, 2 );
	add_filter( 'woocommerce_order_needs_payment', array( 'WC_Gateway_Redsys_Global', 'order_needs_payment' ), 10, 3 );
	// Add Pay Gold Bulk Actions.
	add_filter( 'bulk_actions-users', array( 'WC_Gateway_Paygold_Redsys', 'add_bulk_actions' ) );
	add_filter( 'handle_bulk_actions-users', array( 'WC_Gateway_Paygold_Redsys', 'paygold_bulk_actions_handler' ), 10, 3 );

	/**
	 * Add dns-prefetch to head.
	 */
	function redsys_woo_add_head_text() {
		echo '<!-- Added by WooCommerce Redsys Gateway v.' . esc_html( REDSYS_VERSION ) . ' - https://woocommerce.com/products/redsys-gateway/ -->';
		if ( WCRed()->is_gateway_enabled( 'insite' ) ) {
			echo '<link rel="dns-prefetch" href="https://sis.redsys.es:443">';
			echo '<link rel="dns-prefetch" href="https://sis-t.redsys.es:25443">';
			echo '<link rel="dns-prefetch" href="https://sis-i.redsys.es:25443">';
		}
		if ( WCRed()->is_gateway_enabled( 'googlepayredsys' ) ) {
			echo '<link rel="dns-prefetch" href="https://pay.google.com">';
		}
		if ( WCRed()->is_gateway_enabled( 'applepayredsys' ) ) {
			echo '<link rel="dns-prefetch" href="https://applepay.cdn-apple.com">';
		}
		echo '<!-- This site is powered by WooCommerce Redsys Gateway v.' . esc_html( REDSYS_VERSION ) . ' - https://woocommerce.com/products/redsys-gateway/ -->';
	}
	add_action( 'wp_head', 'redsys_woo_add_head_text' );
	add_action( 'parse_request', array( 'WC_Redsys_Profile', 'redsys_handle_requests_add_method' ) );

	// Customization of the checkout buttons.
	include_once REDSYS_PLUGIN_PATH_P . 'loader/checkout-buttons.php';

	/**
	 * Add One Click Buy Buttons.
	 */
	function add_one_click_buy_button() {
		global $product;

		$enabled = get_option( 'redsys_enable_one_click_button', false );

		if ( 'yes' === $enabled && is_user_logged_in() ) {

			// Inicializa $is_virtual a false.
			$is_virtual = false;
			$product_id = $product->get_id();

			// Verifica si el producto es una instancia de WC_Product_Variable.
			$is_variable = $product instanceof WC_Product_Variable;

			// Si el producto es variable, revisa si todas las variaciones son virtuales.
			if ( $is_variable ) {
				$variations = $product->get_available_variations();
				$is_virtual = true;  // Asume que todas las variaciones son virtuales hasta que se demuestre lo contrario.
				foreach ( $variations as $variation ) {
					$variation_product = wc_get_product( $variation['variation_id'] );
					if ( ! $variation_product->is_virtual() ) {
						$is_virtual = false;
						break;
					}
				}
			} else {
				// Si el producto no es variable, revisa si es virtual.
				$is_virtual = $product->is_virtual();
			}

			$token_type = WCRed()->check_product_for_subscription( $product_id );

			// Si el producto es virtual o si es un producto variable con todas las variaciones virtuales, ejecuta el código siguiente.
			if ( $is_virtual && 'C' === $token_type ) {
				$token_c = WCRed()->get_redsys_users_token( 'C', 'id' );
				if ( ! empty( $token_c ) ) {
					$user_id = get_current_user_id();
					$tokens  = WC_Payment_Tokens::get_customer_tokens( $user_id, 'redsys' );
					foreach ( $tokens as $token ) {
						if ( 'C' === WCRed()->get_token_type( $token->get_id() ) ) {
							$brand    = $token->get_card_type();
							$last4    = $token->get_last4();
							$token_id = $token->get_id();
						}
					}
					echo '
						<style>
							#one-click-buy-button {
								background-color: #f39c12;
								border: none;
								color: white;
								padding: 15px 32px;
								padding-left: 60px; /* Ajustado para dar espacio a la imagen */
								text-align: center;
								text-decoration: none;
								display: inline-block;
								font-size: 16px;
								margin: 4px 2px;
								cursor: pointer;
								background-image: url(' . esc_html( REDSYS_PLUGIN_URL_P ) . 'assets/images/visa-mastercard.svg); /* Ruta a tu imagen */
								background-position: 10px center; /* Ajusta la posición de la imagen */
								background-repeat: no-repeat; /* Evita que la imagen se repita */
								background-size:48px 24px; /* Ajusta el tamaño de la imagen */
							}
						</style>
					';
					echo '<input type="hidden" id="redsys_token_id" value="' . esc_html( $token_id ) . '">';
					echo '<input type="hidden" id="billing_agente_navegador" value="">';
					echo '<input type="hidden" id="billing_idioma_navegador" value="">';
					echo '<input type="hidden" id="billing_altura_pantalla" value="">';
					echo '<input type="hidden" id="billing_anchura_pantalla" value="">';
					echo '<input type="hidden" id="billing_profundidad_color" value="">';
					echo '<input type="hidden" id="billing_diferencia_horaria" value="">';
					echo '<input type="hidden" id="billing_http_accept_headers" value="">';
					echo '<input type="hidden" id="billing_tz_horaria" value="">';
					echo '<input type="hidden" id="billing_js_enabled_navegador" value="">';
					echo '<input type="hidden" id="redsys_token_type" value="' . esc_html( $token_type ) . '">';
					echo '<button type="button" id="one-click-buy-button">' . esc_html__( 'Buy now with', 'woocommerce-redsys' ) . ' ' . esc_html( $brand ) . ' ' . esc_html__( 'ending in', 'woocommerce-redsys' ) . ' ' . esc_html( $last4 ) . '</button>';
					?>
					<script type="text/javascript">
						// Script necesario para capturar los datos a enviar a Redsys por la PSD2
						var RedsysDate = new Date();
						if (document.getElementById('billing_agente_navegador')) {
							document.getElementById('billing_agente_navegador').value = btoa(navigator.userAgent);
						}
						if (document.getElementById('billing_idioma_navegador')) {
							document.getElementById('billing_idioma_navegador').value = navigator.language;
						}
						if (document.getElementById('billing_js_enabled_navegador')) {
							document.getElementById('billing_js_enabled_navegador').value = navigator.javaEnabled();
						}
						if (document.getElementById('billing_altura_pantalla')) {
							document.getElementById('billing_altura_pantalla').value = screen.height;
						}
						if (document.getElementById('billing_anchura_pantalla')) {
							document.getElementById('billing_anchura_pantalla').value = screen.width;
						}
						if (document.getElementById('billing_profundidad_color')) {
							document.getElementById('billing_profundidad_color').value = screen.colorDepth;
						}
						if (document.getElementById('billing_diferencia_horaria')) {
							document.getElementById('billing_diferencia_horaria').value = RedsysDate.getTimezoneOffset();
						}
						if (document.getElementById('billing_tz_horaria')) {
							document.getElementById('billing_tz_horaria').value = RedsysDate.getTimezoneOffset();
						}
					<?php
					if ( isset( $_SERVER['HTTP_ACCEPT'] ) ) {
						?>
						if ( document.getElementById( 'billing_http_accept_headers') ) {
							document.getElementById( 'billing_http_accept_headers').value = btoa( <?php echo '"' . esc_html( wp_unslash( $_SERVER['HTTP_ACCEPT'] ) ) . '"'; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized ?> );
						}
						<?php
					} else {
						?>
					if ( document.getElementById( 'billing_http_accept_headers') ) {
						document.getElementById( 'billing_http_accept_headers').value = btoa( "text\/html,application\/xhtml+xml,application\/xml;q=0.9,*\/*;q=0.8" );
					}
						<?php
					}
					?>
				</script>
					<?php
				}
			}
		}
	}
	add_action( 'woocommerce_after_add_to_cart_form', 'add_one_click_buy_button' );
	/**
	 * Enqueue custom scripts for Pay With one Click.
	 */
	function redsys_enqueue_custom_scripts() {
		if ( is_product() && ! is_admin() ) {
			wp_enqueue_script(
				'redsys-one-click-buy',
				REDSYS_PLUGIN_URL_P . 'assets/js/redsys-one-click-buy.js',
				array( 'jquery' ),
				REDSYS_VERSION,
				true
			);
			wp_localize_script(
				'redsys-one-click-buy',
				'redsys_pay_one',
				array(
					'ajax_url'   => admin_url( 'admin-ajax.php' ),
					'product_id' => get_the_ID(),
				)
			);
		}
	}
	add_action( 'wp_enqueue_scripts', 'redsys_enqueue_custom_scripts' );
	/**
	 * Handle One Click Buy.
	 */
	function redsys_handle_one_click_buy() {

		$data       = array();
		$product_id = intval( $_POST['product_id'] );
		$qty        = intval( $_POST['qty'] );
		$token      = sanitize_text_field( $_POST['token_id'] );
		$order_id   = false;

		if ( ! empty( $_POST['billing_http_accept_headers'] ) ) {
			$headers = base64_decode( sanitize_text_field( wp_unslash( $_POST['billing_http_accept_headers'] ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$data['_accept_haders'] = sanitize_text_field( $headers );
		}
		if ( ! empty( $_POST['billing_agente_navegador'] ) ) {
			$agente = base64_decode( sanitize_text_field( wp_unslash( $_POST['billing_agente_navegador'] ) ) ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_decode
			$data['_billing_agente_navegador_field'] = sanitize_text_field( $agente );
		}
		if ( ! empty( $_POST['billing_idioma_navegador'] ) ) {
			$data['_billing_idioma_navegador_field'] = sanitize_text_field( wp_unslash( $_POST['billing_idioma_navegador'] ) );
		}
		if ( ! empty( $_POST['billing_altura_pantalla'] ) ) {
			$data['_billing_altura_pantalla_field'] = sanitize_text_field( wp_unslash( $_POST['billing_altura_pantalla'] ) );
		}
		if ( ! empty( $_POST['billing_anchura_pantalla'] ) ) {
			$data['_billing_anchura_pantalla_field'] = sanitize_text_field( wp_unslash( $_POST['billing_anchura_pantalla'] ) );
		}
		if ( ! empty( $_POST['billing_profundidad_color'] ) ) {
			$data['_billing_profundidad_color_field'] = sanitize_text_field( wp_unslash( $_POST['billing_profundidad_color'] ) );
		}
		if ( ! empty( $_POST['billing_diferencia_horaria'] ) ) {
			$data['_billing_diferencia_horaria_field'] = sanitize_text_field( wp_unslash( $_POST['billing_diferencia_horaria'] ) );
		}
		if ( ! empty( $_POST['billing_tz_horaria'] ) ) {
			$data['_billing_tz_horaria_field'] = sanitize_text_field( wp_unslash( $_POST['billing_tz_horaria'] ) );
		}
		if ( ! empty( $_POST['billing_js_enabled_navegador'] ) ) {
			$data['_billing_js_enabled_navegador_field'] = sanitize_text_field( wp_unslash( $_POST['billing_js_enabled_navegador'] ) );
		}
		if ( ! empty( $_POST['redsys_token_type'] ) ) {
			$token_type = sanitize_text_field( wp_unslash( $_POST['redsys_token_type'] ) );
		} else {
			$token_type = 'no';
		}

		$order_id = WCRed_pay()->create_order( get_current_user_id(), $product_id, $qty );

		if ( $order_id ) {
			set_transient( $order_id . '_redsys_save_token', 'no', 36000 );
			set_transient( $order_id . '_redsys_token_type', $token_type, 36000 );
			set_transient( $order_id . '_redsys_use_token', $token, 36000 );
			WCRed()->update_order_meta( $order_id, $data );
			$payment_result = WCRed_pay()->process_payment( $order_id, $token );

			if ( 'success' === $payment_result['result'] || 'ChallengeRequest' === $payment_result['result'] || 'threeDSMethodURL' === $payment_result['result'] ) {
				wp_send_json_success(
					array(
						'order_id'     => $order_id,
						'redirect_url' => $payment_result['redirect'],
					)
				);
			} else {
				wp_send_json_error(
					array( 'message' => 'Hubo un problema al procesar el pago: ' . $payment_result['error_message'] )
				);
			}
		} else {
			wp_send_json_error( array( 'message' => 'No se pudo crear el pedido.' ) );
		}
	}
	add_action( 'wp_ajax_redsys_one_click_buy', 'redsys_handle_one_click_buy' );
	/**
	 * Refresh checkout on payment methods change.
	 */
	function redsys_refresh_checkout_on_payment_methods_change() {
		?>
		<script type="text/javascript">
			// Added by WooCommerce Redsys Gateway https://woocommerce.com/products/redsys-gateway/
			(function($){
				$('form.checkout').on( 'change', 'input[name^="payment_method"]', function() {
					var t = { updateTimer: !1,  dirtyInput: !1,
						reset_update_checkout_timer: function() {
							clearTimeout(t.updateTimer)
						},
						trigger_update_checkout: function() {
							t.reset_update_checkout_timer(), t.dirtyInput = !1,
							$(document.body).trigger("update_checkout")
						}
					};
					t.trigger_update_checkout();
				});
			} )(jQuery);
		</script>
		<?php
		if ( WCRed()->is_gateway_enabled( 'insite' ) ) {
			?>
			<script type="text/javascript">
				// Added by WooCommerce Redsys Gateway https://woocommerce.com/products/redsys-gateway/
				(function($){
					$('form.checkout').on( 'change', 'input[name^="payment_method"]', function() {
						var t = { updateTimer: !1,  dirtyInput: !1,
							reset_update_checkout_timer: function() {
								clearTimeout(t.updateTimer)
							},
							trigger_update_checkout: function() {
								t.reset_update_checkout_timer(), t.dirtyInput = !1,
								$(document.body).trigger("update_checkout")
							}
						};
						t.trigger_update_checkout();
					});
				} )(jQuery);
				jQuery( document.body ).one( 'checkout_error', function() {
					if (jQuery('#payment_method_insite').is(':checked')) {
						setTimeout(location.reload.bind(location), 4000);
					}
				} );
				( function( $ ) {
					var orderReviewSection = $('#order_review');
					function toggleInsiteFields( display ) {
						var fields = $('#redsys-submit,.redsys-new-card-data,#redsys_save_token');
						var paymentMethodInsiteCheckbox = $( '#payment_method_insite' );
						var checkoutButton = $( '#place_order' );
						if ( ! fields.length ) {
							return;
						}
						if ( paymentMethodInsiteCheckbox.attr( 'checked' ) ) {
							fields.css( { display: display ? 'block' : 'none' } );
							checkoutButton.css( {
								display: display ? 'none' : 'inline-block',
								visibility: display ? 'hidden' : 'visible',
							});
						}
					}
					// Order review event delegation (the input is still not there).
					orderReviewSection.on( 'change', 'input[name="token"]', function( e ) {
						toggleInsiteFields( e.target.value === 'add' );
					} );
				}( jQuery ) );
			</script>
			<?php
		}
		if ( WCRed()->is_gateway_enabled( 'redsys' ) ) {
			?>
			<script type="text/javascript">
				// Added by WooCommerce Redsys Gateway https://woocommerce.com/products/redsys-gateway/
				(function($){
					$('form.checkout').on( 'change', 'input[name^="payment_method"]', function() {
						var t = { updateTimer: !1,  dirtyInput: !1,
							reset_update_checkout_timer: function() {
								clearTimeout(t.updateTimer)
							},
							trigger_update_checkout: function() {
								t.reset_update_checkout_timer(), t.dirtyInput = !1,
								$(document.body).trigger("update_checkout")
							}
						};
						t.trigger_update_checkout();
					});
				} )(jQuery);				
				( function( $ ) {
					var orderReviewSection = $('#order_review');
					function toggleRedsysFields( display ) {
						var fields = $('#redsys_save_token');
						var paymentMethodRedsysCheckbox = $( '#payment_method_redsys' );
						if ( ! fields.length ) {
							return;
						}
						if ( paymentMethodRedsysCheckbox.attr( 'checked' ) ) {
							fields.css( { display: display ? 'block' : 'none' } );
						}
					}
					// Order review event delegation (the input is still not there).
					orderReviewSection.on( 'change', 'input[name="token"]', function( e ) {
						toggleRedsysFields( e.target.value === 'add' );
					} );
				}( jQuery ) );
				</script>
				<?php
		}
		if ( WCRed()->is_gateway_enabled( 'googlepayredsys' ) ) {
			?>
			<script type="text/javascript">
				// Added by WooCommerce Redsys Gateway https://woocommerce.com/products/redsys-gateway/
				(function($) {
					$('form.checkout').on('change', 'input[name^="payment_method"]', function() {
						var t = {
							updateTimer: false,
							dirtyInput: false,
							reset_update_checkout_timer: function() {
								clearTimeout(t.updateTimer);
							},
							trigger_update_checkout: function() {
								t.reset_update_checkout_timer();
								t.dirtyInput = false;
								$(document.body).trigger("update_checkout");
							}
						};
						var paymentMethod = $(this).attr('id');
						if (paymentMethod === 'payment_method_googlepayredsys') {
							onGooglePayLoaded();
						} else {
							t.trigger_update_checkout();
						}
					});
				})(jQuery);
			</script>
			<?php
		}
	}
	add_action( 'wp_footer', 'redsys_refresh_checkout_on_payment_methods_change' );

	/**
	 * Add Checkout Price to custom rest.
	 */
	function redsys_register_gpay_route_final_price() {

		if ( ! WC()->is_rest_api_request() || is_admin() ) {
			return;
		}

		WC()->frontend_includes();

		if ( null === WC()->cart && function_exists( 'wc_load_cart' ) ) {
			wc_load_cart();
		}
		register_rest_route(
			'redsysgpay',
			'/get-cart-total/(?P<id>\d+)',
			array(
				'methods'             => 'GET',
				'callback'            => 'redsys_gpay_get_cart_total',
				'permission_callback' => '__return_true',

			)
		);
	}
	// add_action( 'woocommerce_init', 'redsys_register_gpay_route_final_price' );
	/**
	 * Get Checkout Price to custom rest.
	 *
	 * @param WP_REST_Request $request Request.
	 * @return WP_REST_Response
	 */
	function redsys_gpay_get_cart_total( WP_REST_Request $request ) {

		WC()->frontend_includes();

		if ( null === WC()->cart && function_exists( 'wc_load_cart' ) ) {
			wc_load_cart();
		}

		$total = WC()->cart->total;

		return new WP_REST_Response(
			array(
				'total' => $total,
			)
		);
	}
	/*
	require_once REDSYS_PLUGIN_PATH_P . 'classes/class-wc-gateway-redsys-license.php';

	new WC_Gateway_Redsys_License(
		'https://redsys.joseconti.com/',
		__FILE__,
		array(
			'version'    => REDSYS_VERSION,
			'item_name'  => REDSYS_ITEM_NANE,
			'menu_slug'  => REDSYS_ITEM_NANE . '-license',
			'menu_title' => 'REDSYS License',
			'license'    => '', // current license key.
			'prefix'     => REDSYS_PREFIX,
		)
	);
	*/
}

/**
 * Add support for WooCommerce Blocks / Payments.
 */

require_once REDSYS_PLUGIN_PATH_P . 'includes/load-checkout-blocks.php';

// WooCommerce Redsys Gateway License.
