<?php
/**
 * WooCommerce Redsys Gateway Direct Debit
 *
 * @package WooCommerce Redsys Gateway WooCommerce.com > https://woocommerce.com/products/redsys-gateway/
 * @since 13.0.0
 * @author José Conti.
 * @link https://joseconti.com
 * @license GNU General Public License v3.0
 * @license URI: http://www.gnu.org/licenses/gpl-3.0.html
 * @copyright 2013-2023 José Conti.
 */

defined( 'ABSPATH' ) || exit;
/**
 * Copyright: (C) 2013 - 2023 José Conti
 */
class WC_Gateway_Direct_Debit_Redsys extends WC_Payment_Gateway {
	var $notify_url;

	/**
	 * Constructor for the gateway.
	 *
	 * @return void
	 */
	/**
	 * Package: WooCommerce Redsys Gateway
	 * Plugin URI: https://woocommerce.com/es-es/products/redsys-gateway/
	 * Copyright: (C) 2013 - 2023 José Conti
	 */
	public function __construct() {

		$this->id                   = 'directdebitredsys';
		$this->icon                 = apply_filters( 'woocommerce_' . $this->id . '_icon', REDSYS_PLUGIN_URL_P . 'assets/images/redsys.png' );
		$this->has_fields           = false;
		$this->liveurl              = 'https://sis.redsys.es/sis/realizarPago';
		$this->testurl              = 'https://sis-t.redsys.es:25443/sis/realizarPago';
		$this->liveurlws            = 'https://sis.redsys.es:443/sis/services/SerClsWSEntrada?wsdl';
		$this->testurlws            = 'https://sis-t.redsys.es:25443/sis/services/SerClsWSEntrada?wsdl';
		$this->testsha256           = 'sq7HjrUOBfKmC576ILgskD5srU870gJ7';
		$this->testmode             = WCRed()->get_redsys_option( 'testmode', 'directdebitredsys' );
		$this->method_title         = __( 'Direct Debit (by José Conti)', 'woocommerce-redsys' );
		$this->method_description   = __( 'Direct Debit works redirecting customers to Direct Debit.', 'woocommerce-redsys' );
		$this->not_use_https        = WCRed()->get_redsys_option( 'not_use_https', 'directdebitredsys' );
		$this->notify_url           = add_query_arg( 'wc-api', 'WC_Gateway_' . $this->id, home_url( '/' ) );
		$this->notify_url_not_https = str_replace( 'https:', 'http:', add_query_arg( 'wc-api', 'WC_Gateway_' . $this->id, home_url( '/' ) ) );
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		// Define user set variables.
		$this->title                     = WCRed()->get_redsys_option( 'title', 'directdebitredsys' );
		$this->multisitesttings          = WCRed()->get_redsys_option( 'multisitesttings', 'directdebitredsys' );
		$this->ownsetting                = WCRed()->get_redsys_option( 'ownsetting', 'directdebitredsys' );
		$this->hideownsetting            = WCRed()->get_redsys_option( 'hideownsetting', 'directdebitredsys' );
		$this->description               = WCRed()->get_redsys_option( 'description', 'directdebitredsys' );
		$this->customer                  = WCRed()->get_redsys_option( 'customer', 'directdebitredsys' );
		$this->commercename              = WCRed()->get_redsys_option( 'commercename', 'directdebitredsys' );
		$this->terminal                  = WCRed()->get_redsys_option( 'terminal', 'directdebitredsys' );
		$this->secretsha256              = WCRed()->get_redsys_option( 'secretsha256', 'directdebitredsys' );
		$this->customtestsha256          = WCRed()->get_redsys_option( 'customtestsha256', 'directdebitredsys' );
		$this->directdebitredsyslanguage = WCRed()->get_redsys_option( 'directdebitredsyslanguage', 'directdebitredsys' );
		$this->debug                     = WCRed()->get_redsys_option( 'debug', 'directdebitredsys' );
		$this->testforuser               = WCRed()->get_redsys_option( 'testforuser', 'directdebitredsys' );
		$this->testforuserid             = WCRed()->get_redsys_option( 'testforuserid', 'directdebitredsys' );
		$this->buttoncheckout            = WCRed()->get_redsys_option( 'buttoncheckout', 'directdebitredsys' );
		$this->butonbgcolor              = WCRed()->get_redsys_option( 'butonbgcolor', 'directdebitredsys' );
		$this->butontextcolor            = WCRed()->get_redsys_option( 'butontextcolor', 'directdebitredsys' );
		$this->descripredsys             = WCRed()->get_redsys_option( 'descripredsys', 'directdebitredsys' );
		$this->testshowgateway           = WCRed()->get_redsys_option( 'testshowgateway', 'directdebitredsys' );
		$this->log                       = new WC_Logger();
		$this->supports                  = array(
			'products',
		);
		// Actions.
		add_action( 'valid_' . $this->id . '_standard_ipn_request', array( $this, 'successful_request' ) );
		add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_before_checkout_form', array( $this, 'warning_checkout_test_mode_directdebit' ) );
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'show_payment_method' ) );

		// Payment listener/API hook.
		add_action( 'woocommerce_api_wc_gateway_' . $this->id, array( $this, 'check_ipn_response' ) );

		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}
	}

	/**
	 * Package: WooCommerce Redsys Gateway
	 * Plugin URI: https://woocommerce.com/es-es/products/redsys-gateway/
	 * Copyright: (C) 2013 - 2023 José Conti
	 */
	public function is_valid_for_use() {
		if ( ! in_array( get_woocommerce_currency(), WCRed()->allowed_currencies(), true ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Admin Panel Options
	 *
	 * @since 6.0.0
	 */
	/**
	 * Package: WooCommerce Redsys Gateway
	 * Plugin URI: https://woocommerce.com/es-es/products/redsys-gateway/
	 * Copyright: (C) 2013 - 2023 José Conti
	 */
	public function admin_options() {
		?>
		<h3><?php esc_html_e( 'Direct Debit', 'woocommerce-redsys' ); ?></h3>
		<p><?php esc_html_e( 'Direct Debit works by sending the user to Direct Debit Gateway', 'woocommerce-redsys' ); ?></p>
		<?php
			WCRed()->return_help_notice();
		if ( class_exists( 'SitePress' ) ) {
			?>
			<div class="updated fade"><h4><?php esc_html_e( 'Attention! WPML detected.', 'woocommerce-redsys' ); ?></h4>
				<p><?php esc_html_e( 'The Gateway will be shown in the customer language. The option "Language Gateway" is not taken into consideration', 'woocommerce-redsys' ); ?></p>
			</div>
		<?php } ?>
		<?php if ( $this->is_valid_for_use() ) : ?>
			<table class="form-table">
				<?php
				// Generate the HTML For the settings form.
				$this->generate_settings_html();
				?>
			</table><!--/.form-table-->
			<?php
		else :
			$currencies          = WCRed()->allowed_currencies();
			$formated_currencies = '';

			foreach ( $currencies as $currency ) {
				$formated_currencies .= $currency . ', ';
			}
			?>
	<div class="inline error"><p><strong><?php esc_html_e( 'Gateway Disabled', 'woocommerce-redsys' ); ?></strong>: 
			<?php
			esc_html_e( 'Servired/RedSys only support ', 'woocommerce-redsys' );
			echo esc_html( $formated_currencies );
			?>
		</p></div>
			<?php
		endif;
	}

	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {

		$options    = array();
		$selections = (array) WCRed()->get_redsys_option( 'testforuserid', 'directdebitredsys' );

		if ( count( $selections ) !== 0 ) {
			foreach ( $selections as $user_id ) {
				if ( ! empty( $user_id ) ) {
					$user_data = get_userdata( $user_id );
					if ( ! empty( $user_data ) ) {
						$user_email = $user_data->user_email;
						if ( ! empty( esc_html( $user_email ) ) ) {
							$options[ esc_html( $user_id ) ] = esc_html( $user_email );
						}
					}
				}
			}
		}

		$options_show    = array();
		$selections_show = (array) WCRed()->get_redsys_option( 'testshowgateway', 'directdebitredsys' );
		if ( count( $selections_show ) !== 0 ) {
			foreach ( $selections_show as $user_id ) {
				if ( ! empty( $user_id ) ) {
					$user_data  = get_userdata( $user_id );
					$user_email = $user_data->user_email;
					if ( ! empty( esc_html( $user_email ) ) ) {
						$options_show[ esc_html( $user_id ) ] = esc_html( $user_email );
					}
				}
			}
		}
		$this->form_fields = array(
			'enabled'                   => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-redsys' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Direct Debit', 'woocommerce-redsys' ),
				'default' => 'no',
			),
			'multisitesttings'          => array(
				'title'       => __( 'Use in Network', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Use this setting around all Network', 'woocommerce-redsys' ),
				'description' => '',
				'default'     => 'no',
			),
			'hideownsetting'            => array(
				'title'       => __( 'Hide "NOT use Network" in subsites', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Hide "NOT use Network" in subsites', 'woocommerce-redsys' ),
				'description' => '',
				'default'     => 'no',
			),
			'ownsetting'                => array(
				'title'       => __( 'NOT use Network', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Do NOT use Network settings. Use settings of this page', 'woocommerce-redsys' ),
				'description' => '',
				'default'     => 'no',
			),
			'title'                     => array(
				'title'       => __( 'Title', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-redsys' ),
				'default'     => __( 'Direct Debit', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'description'               => array(
				'title'       => __( 'Description', 'woocommerce-redsys' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-redsys' ),
				'default'     => __( 'Pay via Direct Debit you can pay with your Direct Debit account.', 'woocommerce-redsys' ),
			),
			'buttoncheckout'            => array(
				'title'       => __( 'Button Checkout Text', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Add the button text at the checkout.', 'woocommerce-redsys' ),
			),
			'butonbgcolor'              => array(
				'title'       => __( 'Button Color Background', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'This if button Color Background Place Order at Checkout', 'woocommerce-redsys' ),
				'class'       => 'colorpick',
			),
			'butontextcolor'            => array(
				'title'       => __( 'Color text Button', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'This if button text color Place Order at Checkout', 'woocommerce-redsys' ),
				'class'       => 'colorpick',
			),
			'customer'                  => array(
				'title'       => __( 'Commerce number (FUC)', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Commerce number (FUC) provided by your bank.', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'commercename'              => array(
				'title'       => __( 'Commerce Name', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Commerce Name', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'terminal'                  => array(
				'title'       => __( 'Terminal number', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Terminal number provided by your bank.', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'descripredsys'             => array(
				'title'       => __( 'Redsys description', 'woocommerce-redsys' ),
				'type'        => 'select',
				'description' => __( 'Chose what to show in Redsys as description.', 'woocommerce-redsys' ),
				'default'     => 'order',
				'options'     => array(
					'order' => __( 'Order ID', 'woocommerce-redsys' ),
					'id'    => __( 'List of products ID', 'woocommerce-redsys' ),
					'name'  => __( 'List of products name', 'woocommerce-redsys' ),
					'sku'   => __( 'List of products SKU', 'woocommerce-redsys' ),
				),
			),
			'not_use_https'             => array(
				'title'       => __( 'HTTPS SNI Compatibility', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Activate SNI Compatibility.', 'woocommerce-redsys' ),
				'default'     => 'no',
				'description' => sprintf( __( 'If you are using HTTPS and Redsys don\'t support your certificate, example Lets Encrypt, you can deactivate HTTPS notifications. WARNING: If you are forcing redirection to HTTPS with htaccess, you need to add an exception for notification URL', 'woocommerce-redsys' ) ),
			),
			'secretsha256'              => array(
				'title'       => __( 'Encryption secret passphrase SHA-256', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Encryption secret passphrase SHA-256 provided by your bank.', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'customtestsha256'          => array(
				'title'       => __( 'TEST MODE: Encryption secret passphrase SHA-256', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Encryption secret passphrase SHA-256 provided by your bank for test mode.', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'directdebitredsyslanguage' => array(
				'title'       => __( 'Language Gateway', 'woocommerce-redsys' ),
				'type'        => 'select',
				'description' => __( 'Choose the language for the Gateway. Not all Banks accept all languages', 'woocommerce-redsys' ),
				'default'     => '001',
				'options'     => array(),
			),
			'testmode'                  => array(
				'title'       => __( 'Running in test mode', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Running in test mode', 'woocommerce-redsys' ),
				'default'     => 'yes',
				'description' => sprintf( __( 'Select this option for the initial testing required by your bank, deselect this option once you pass the required test phase and your production environment is active.', 'woocommerce-redsys' ) ),
			),
			'testshowgateway'           => array(
				'title'       => __( 'Show to this users', 'woocommerce-redsys' ),
				'type'        => 'multiselect',
				'label'       => __( 'Show the gateway in the chcekout when it is in test mode', 'woocommerce-redsys' ),
				'class'       => 'js-woo-show-gateway-test-settings',
				'id'          => 'woocommerce_redsys_showtestforuserid',
				'options'     => $options_show,
				'default'     => '',
				'description' => sprintf( __( 'Select users that will see the gateway when it is in test mode. If no users are selected, will be shown to all users', 'woocommerce-redsys' ) ),
			),
			'testforuser'               => array(
				'title'       => __( 'Running in test mode for a user', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Running in test mode for a user', 'woocommerce-redsys' ),
				'default'     => 'yes',
				'description' => sprintf( __( 'The user selected below will use the terminal in test mode. Other users will continue to use live mode unless you have the "Running in test mode" option checked.', 'woocommerce-redsys' ) ),
			),
			'testforuserid'             => array(
				'title'       => __( 'Users', 'woocommerce-redsys' ),
				'type'        => 'multiselect',
				'label'       => __( 'Users running in test mode', 'woocommerce-redsys' ),
				'class'       => 'js-woo-allowed-users-settings',
				'id'          => 'woocommerce_redsys_testforuserid',
				'options'     => $options,
				'default'     => '',
				'description' => sprintf( __( 'Select users running in test mode', 'woocommerce-redsys' ) ),
			),
			'debug'                     => array(
				'title'       => __( 'Debug Log', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Running in test mode', 'woocommerce-redsys' ),
				'label'       => __( 'Enable logging', 'woocommerce-redsys' ),
				'default'     => 'no',
				'description' => __( 'Log Direct Debit events, such as notifications requests, inside <code>WooCommerce > Status > Logs > directdebit-{date}-{number}.log</code>', 'woocommerce-redsys' ),
			),
		);
		$redsyslanguages   = WCRed()->get_redsys_languages();

		foreach ( $redsyslanguages as $redsyslanguage => $valor ) {
			$this->form_fields['directdebitredsyslanguage']['options'][ $redsyslanguage ] = $valor;
		}
		if ( ! is_multisite() ) {
			unset( $this->form_fields['multisitesttings'] );
			unset( $this->form_fields['ownsetting'] );
			unset( $this->form_fields['hideownsetting'] );
		} else {
			if ( is_main_site() ) {
				unset( $this->form_fields['ownsetting'] );
			} else {
				unset( $this->form_fields['multisitesttings'] );
				unset( $this->form_fields['hideownsetting'] );
				$globalsettings = WCRed()->get_redsys_option( 'multisitesttings', $this->id );
				$hide           = WCRed()->get_redsys_option( 'hideownsetting', $this->id );
				if ( 'yes' === $hide || 'yes' !== $globalsettings ) {
					unset( $this->form_fields['ownsetting'] );
				}
			}
		}
	}
	/**
	 * Check User test mode
	 *
	 * @param int $userid User ID.
	 *
	 * @return bool
	 */
	public function check_user_test_mode( $userid ) {

		$usertest_active = $this->testforuser;
		$selections      = (array) WCRed()->get_redsys_option( 'testforuserid', 'directdebitredsys' );
		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', '/****************************/' );
			$this->log->add( 'directdebitredsys', '     Checking user test       ' );
			$this->log->add( 'directdebitredsys', '/****************************/' );
			$this->log->add( 'directdebitredsys', ' ' );
		}

		if ( 'yes' === $usertest_active ) {

			if ( ! empty( $selections ) ) {
				foreach ( $selections as $user_id ) {
					if ( 'yes' === $this->debug ) {
						$this->log->add( 'directdebitredsys', ' ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', '   Checking user ' . $userid );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', ' ' );
						$this->log->add( 'directdebitredsys', ' ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', '  User in forach ' . $user_id );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', ' ' );
					}
					if ( (string) $user_id === (string) $userid ) {
						if ( 'yes' === $this->debug ) {
							$this->log->add( 'directdebitredsys', ' ' );
							$this->log->add( 'directdebitredsys', '/****************************/' );
							$this->log->add( 'directdebitredsys', '   Checking user test TRUE    ' );
							$this->log->add( 'directdebitredsys', '/****************************/' );
							$this->log->add( 'directdebitredsys', ' ' );
							$this->log->add( 'directdebitredsys', ' ' );
							$this->log->add( 'directdebitredsys', '/********************************************/' );
							$this->log->add( 'directdebitredsys', '  User ' . $userid . ' is equal to ' . $user_id );
							$this->log->add( 'directdebitredsys', '/********************************************/' );
							$this->log->add( 'directdebitredsys', ' ' );
						}
						return true;
					}
					if ( 'yes' === $this->debug ) {
						$this->log->add( 'directdebitredsys', ' ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', '  Checking user test continue ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', ' ' );
					}
					continue;
				}
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', '  Checking user test FALSE    ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
				return false;
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', '  Checking user test FALSE    ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
				return false;
			}
		} else {
			if ( 'yes' === $this->debug ) {
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', '/****************************/' );
				$this->log->add( 'directdebitredsys', '     User test Disabled.      ' );
				$this->log->add( 'directdebitredsys', '/****************************/' );
				$this->log->add( 'directdebitredsys', ' ' );
			}
			return false;
		}
	}
	/**
	 * Get Redsys URL Gateway
	 *
	 * @param  string $user_id User ID.
	 * @param  string $type    Type.
	 * @return string
	 */
	public function get_redsys_url_gateway( $user_id, $type = 'rd' ) {

		if ( 'yes' === $this->testmode ) {
			if ( 'rd' === $type ) {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', '          URL Test RD         ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
				$url = $this->testurl;
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', '          URL Test WS         ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
				$url = $this->testurlws;
			}
		} else {
			$user_test = $this->check_user_test_mode( $user_id );
			if ( $user_test ) {
				if ( 'rd' === $type ) {
					if ( 'yes' === $this->debug ) {
						$this->log->add( 'directdebitredsys', ' ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', '          URL Test RD         ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', ' ' );
					}
					$url = $this->testurl;
				} else {
					if ( 'yes' === $this->debug ) {
						$this->log->add( 'directdebitredsys', ' ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', '          URL Test WS         ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', ' ' );
					}
					$url = $this->testurlws;
				}
			} else {
				if ( 'rd' === $type ) {
					if ( 'yes' === $this->debug ) {
						$this->log->add( 'directdebitredsys', ' ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', '          URL Live RD         ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', ' ' );
					}
					$url = $this->liveurl;
				} else {
					if ( 'yes' === $this->debug ) {
						$this->log->add( 'directdebitredsys', ' ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', '          URL Live WS         ' );
						$this->log->add( 'directdebitredsys', '/****************************/' );
						$this->log->add( 'directdebitredsys', ' ' );
					}
					$url = $this->liveurlws;
				}
			}
		}
		return $url;
	}
	/**
	 * Get Redsys SHA256
	 *
	 * @param  string $user_id User ID.
	 * @return string
	 */
	public function get_redsys_sha256( $user_id ) {

		if ( 'yes' === $this->testmode ) {
			if ( 'yes' === $this->debug ) {
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', '/****************************/' );
				$this->log->add( 'directdebitredsys', '         SHA256 Test.         ' );
				$this->log->add( 'directdebitredsys', '/****************************/' );
				$this->log->add( 'directdebitredsys', ' ' );
			}
			$customtestsha256 = utf8_decode( $this->customtestsha256 );
			if ( ! empty( $customtestsha256 ) ) {
				$sha256 = $customtestsha256;
			} else {
				$sha256 = utf8_decode( $this->testsha256 );
			}
		} else {
			$user_test = $this->check_user_test_mode( $user_id );
			if ( $user_test ) {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', '      USER SHA256 Test.       ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
				$customtestsha256 = utf8_decode( $this->customtestsha256 );
				if ( ! empty( $customtestsha256 ) ) {
					$sha256 = $customtestsha256;
				} else {
					$sha256 = utf8_decode( $this->testsha256 );
				}
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', '     USER SHA256 NOT Test.    ' );
					$this->log->add( 'directdebitredsys', '/****************************/' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
				$sha256 = utf8_decode( $this->secretsha256 );
			}
		}
		return $sha256;
	}
	/**
	 * Get Redsys Args for passing to PP
	 *
	 * @param  WC_Order $order Order object.
	 * @return array
	 */
	public function get_redsys_args( $order ) {

		$order_id         = $order->get_id();
		$currency_codes   = WCRed()->get_currencies();
		$transaction_id2  = WCRed()->prepare_order_number( $order_id );
		$order_total_sign = WCRed()->redsys_amount_format( $order->get_total() );
		$transaction_type = '0';
		$user_id          = $order->get_user_id();
		$secretsha256     = $this->get_redsys_sha256( $user_id );
		if ( class_exists( 'SitePress' ) ) {
			$gatewaylanguage = WCRed()->get_lang_code( ICL_LANGUAGE_CODE );
		} elseif ( $this->directdebitredsyslanguage ) {
			$gatewaylanguage = $this->directdebitredsyslanguage;
		} else {
			$gatewaylanguage = '001';
		}
		$returnfromredsys   = $order->get_cancel_order_url();
		$dsmerchantterminal = $this->terminal;
		if ( 'yes' === $this->not_use_https ) {
				$final_notify_url = $this->notify_url_not_https;
		} else {
			$final_notify_url = $this->notify_url;
		}
		// redsys Args.
		$miobj = new WooRedsysAPI();
		$miobj->setParameter( 'DS_MERCHANT_AMOUNT', $order_total_sign );
		$miobj->setParameter( 'DS_MERCHANT_ORDER', $transaction_id2 );
		$miobj->setParameter( 'DS_MERCHANT_MERCHANTCODE', $this->customer );
		$miobj->setParameter( 'DS_MERCHANT_CURRENCY', $currency_codes[ get_woocommerce_currency() ] );
		$miobj->setParameter( 'DS_MERCHANT_TRANSACTIONTYPE', $transaction_type );
		$miobj->setParameter( 'DS_MERCHANT_TERMINAL', $dsmerchantterminal );
		$miobj->setParameter( 'DS_MERCHANT_MERCHANTURL', $final_notify_url );
		$miobj->setParameter( 'DS_MERCHANT_URLOK', add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $order ) ) );
		$miobj->setParameter( 'DS_MERCHANT_URLKO', $returnfromredsys );
		$miobj->setParameter( 'DS_MERCHANT_CONSUMERLANGUAGE', $gatewaylanguage );
		$miobj->setParameter( 'DS_MERCHANT_PRODUCTDESCRIPTION', WCRed()->product_description( $order, 'directdebitredsys' ) );
		$miobj->setParameter( 'DS_MERCHANT_MERCHANTNAME', $this->commercename );
		$miobj->setParameter( 'DS_MERCHANT_PAYMETHODS', 'D' );

		$version = 'HMAC_SHA256_V1';
		// Se generan los parámetros de la petición.
		$request      = '';
		$params       = $miobj->createMerchantParameters();
		$signature    = $miobj->createMerchantSignature( $secretsha256 );
		$order_id_set = $transaction_id2;
		set_transient( 'redsys_signature_' . sanitize_text_field( $order_id_set ), $secretsha256, 600 );
		$redsys_args = array(
			'Ds_SignatureVersion'   => $version,
			'Ds_MerchantParameters' => $params,
			'Ds_Signature'          => $signature,
		);
		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', 'Generating payment form for order ' . $order->get_order_number() . '. Sent data: ' . print_r( $redsys_args, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			$this->log->add( 'directdebitredsys', 'Helping to understand the encrypted code: ' );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_AMOUNT: ' . $order_total_sign );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_ORDER: ' . $transaction_id2 );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_MERCHANTCODE: ' . $this->customer );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_CURRENCY' . $currency_codes[ get_woocommerce_currency() ] );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_TRANSACTIONTYPE: ' . $transaction_type );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_TERMINAL: ' . $dsmerchantterminal );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_MERCHANTURL: ' . $final_notify_url );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_URLOK: ' . add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $order ) ) );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_URLKO: ' . $returnfromredsys );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_CONSUMERLANGUAGE: ' . $gatewaylanguage );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_PRODUCTDESCRIPTION: ' . WCRed()->product_description( $order, 'directdebitredsys' ) );
			$this->log->add( 'directdebitredsys', 'DS_MERCHANT_PAYMETHODS: D' );
		}
		$redsys_args = apply_filters( 'woocommerce_redsys_args', $redsys_args );
		return $redsys_args;
	}

	/**
	 * Generate the redsys form
	 *
	 * @param int $order_id Order ID.
	 * @return string
	 */
	public function generate_redsys_form( $order_id ) {
		global $woocommerce;

		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', '/****************************/' );
			$this->log->add( 'directdebitredsys', '   Generating Redsys Form     ' );
			$this->log->add( 'directdebitredsys', '/****************************/' );
			$this->log->add( 'directdebitredsys', ' ' );
		}

		$order           = WCRed()->get_order( $order_id );
		$user_id         = $order->get_user_id();
		$usesecretsha256 = $this->get_redsys_sha256( $user_id );
		$redsys_adr      = $this->get_redsys_url_gateway( $user_id );
		$redsys_args     = $this->get_redsys_args( $order );
		$form_inputs     = array();

		foreach ( $redsys_args as $key => $value ) {
			$form_inputs[] .= '<input type="hidden" name="' . $key . '" value="' . esc_attr( $value ) . '" />';
		}
		wc_enqueue_js(
			'
		$("body").block({
			message: "<img src=\"' . esc_url( apply_filters( 'woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/select2-spinner.gif' ) ) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />' . __( 'Thank you for your order. We are now redirecting you to Direct Debit to make the payment.', 'woocommerce-redsys' ) . '",
			overlayCSS:
			{
				background: "#fff",
				opacity: 0.6
			},
			css: {
				padding:		20,
				textAlign:		"center",
				color:			"#555",
				border:			"3px solid #aaa",
				backgroundColor:"#fff",
				cursor:			"wait",
				lineHeight:		"32px"
			}
		});
	jQuery("#submit_redsys_payment_form").click();
	'
		);
		return '<form action="' . esc_url( $redsys_adr ) . '" method="post" id="redsys_payment_form" target="_top">
		' . implode( '', $form_inputs ) . '
		<input type="submit" class="button-alt" id="submit_redsys_payment_form" value="' . __( 'Pay with Direct Debit', 'woocommerce-redsys' ) . '" /> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce-redsys' ) . '</a>
	</form>';
	}

	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id Order ID.
	 *
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = WCRed()->get_order( $order_id );
		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);
	}
	/**
	 * Output for the order received page.
	 *
	 * @param obj $order Order object.
	 */
	public function receipt_page( $order ) {
		echo '<p>' . esc_html__( 'Thank you for your order, please click the button below to pay with Direct Debit.', 'woocommerce-redsys' ) . '</p>';
		$allowed_html = array(
			'input' => array(
				'type'  => array(),
				'name'  => array(),
				'value' => array(),
				'class' => array(),
				'id'    => array(),
			),
			'form'  => array(
				'action' => array(),
				'method' => array(),
				'id'     => array(),
				'target' => array(),
			),
			'a'     => array(
				'href'  => array(),
				'class' => array(),
			),
		);
		echo wp_kses( $this->generate_redsys_form( $order ), $allowed_html );
	}

	/**
	 * Check redsys IPN validity
	 **/
	/**
	 * Package: WooCommerce Redsys Gateway
	 * Plugin URI: https://woocommerce.com/es-es/products/redsys-gateway/
	 * Copyright: (C) 2013 - 2023 José Conti
	 */
	public function check_ipn_request_is_valid() {

		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', 'HTTP Notification received: ' . print_r( $_POST, true ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
		$usesecretsha256 = $this->secretsha256;
		if ( $usesecretsha256 ) {
			$version           = sanitize_text_field( wp_unslash( $_POST['Ds_SignatureVersion'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.NonceVerification.Missing
			$data              = sanitize_text_field( wp_unslash( $_POST['Ds_MerchantParameters'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.NonceVerification.Missing
			$remote_sign       = sanitize_text_field( wp_unslash( $_POST['Ds_Signature'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.NonceVerification.Missing
			$mi_obj            = new WooRedsysAPI();
			$decodec           = $mi_obj->decodeMerchantParameters( $data );
			$order_id          = $mi_obj->getParameter( 'Ds_Order' );
			$secretsha256      = get_transient( 'redsys_signature_' . sanitize_title( $order_id ) );
			$order1            = $order_id;
			$order2            = WCRed()->clean_order_number( $order1 );
			$secretsha256_meta = WCRed()->get_order_meta( $order2, '_redsys_secretsha256', true );

			if ( 'yes' === $this->debug ) {
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', 'Signature from Redsys: ' . $remote_sign );
				$this->log->add( 'directdebitredsys', 'Name transient remote: redsys_signature_' . sanitize_title( $order_id ) );
				$this->log->add( 'directdebitredsys', 'Secret SHA256 transcient: ' . $secretsha256 );
				$this->log->add( 'directdebitredsys', ' ' );
			}

			if ( 'yes' === $this->debug ) {
				$order_id = $mi_obj->getParameter( 'Ds_Order' );
				$this->log->add( 'directdebitredsys', 'Order ID: ' . $order_id );
			}
			$order           = WCRed()->get_order( $order2 );
			$user_id         = $order->get_user_id();
			$usesecretsha256 = $this->get_redsys_sha256( $user_id );
			if ( empty( $secretsha256 ) && ! $secretsha256_meta ) {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', 'Using $usesecretsha256 Settings' );
					$this->log->add( 'directdebitredsys', 'Secret SHA256 Settings: ' . $usesecretsha256 );
					$this->log->add( 'directdebitredsys', ' ' );
				}
				$usesecretsha256 = $usesecretsha256;
			} elseif ( $secretsha256_meta ) {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', 'Using $secretsha256_meta Meta' );
					$this->log->add( 'directdebitredsys', 'Secret SHA256 Meta: ' . $secretsha256_meta );
					$this->log->add( 'directdebitredsys', ' ' );
				}
				$usesecretsha256 = $secretsha256_meta;
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', 'Using $secretsha256 Transcient' );
					$this->log->add( 'directdebitredsys', 'Secret SHA256 Transcient: ' . $secretsha256 );
					$this->log->add( 'directdebitredsys', ' ' );
				}
				$usesecretsha256 = $secretsha256;
			}
			$localsecret = $mi_obj->createMerchantSignatureNotif( $usesecretsha256, $data );
			if ( $localsecret === $remote_sign ) {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', 'Received valid notification from Servired/RedSys' );
					$this->log->add( 'directdebitredsys', $data );
				}
				return true;
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', 'Received INVALID notification from Servired/RedSys' );
				}
				delete_transient( 'redsys_signature_' . sanitize_title( $order_id ) );
				return false;
			}
		} else {
			if ( 'yes' === $this->debug ) {
				$this->log->add( 'directdebitredsys', 'HTTP Notification received: ' . print_r( $_POST, true ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.PHP.DevelopmentFunctions.error_log_print_r
			}
			if ( $_POST['Ds_MerchantCode'] === $this->customer ) { // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.NonceVerification.Missing
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', 'Received valid notification from Servired/RedSys' );
				}
				return true;
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', 'Received INVALID notification from Servired/RedSys' );
					$this->log->add( 'directdebitredsys', '$remote_sign: ' . $remote_sign );
					$this->log->add( 'directdebitredsys', '$localsecret: ' . $localsecret );
				}
				return false;
			}
		}
	}
	/**
	 * Check for valid server callback.
	 */
	public function check_ipn_response() {
		@ob_clean(); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$_POST = stripslashes_deep( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $this->check_ipn_request_is_valid() ) {
			header( 'HTTP/1.1 200 OK' );
			do_action( 'valid_' . $this->id . '_standard_ipn_request', $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} else {
			wp_die( 'There is nothing to see here, do not access this page directly (Direct Debit)' );
		}
	}
	/**
	 * Successful Payment!
	 *
	 * @param array $posted Post data after notify.
	 */
	public function successful_request( $posted ) {

		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', '/****************************/' );
			$this->log->add( 'directdebitredsys', '      successful_request      ' );
			$this->log->add( 'directdebitredsys', '/****************************/' );
			$this->log->add( 'directdebitredsys', ' ' );
		}

		$version     = sanitize_text_field( wp_unslash( $_POST['Ds_SignatureVersion'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.NonceVerification.Missing
		$data        = sanitize_text_field( wp_unslash( $_POST['Ds_MerchantParameters'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.NonceVerification.Missing
		$remote_sign = sanitize_text_field( wp_unslash( $_POST['Ds_Signature'] ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.NonceVerification.Missing

		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', '$version: ' . $version );
			$this->log->add( 'directdebitredsys', '$data: ' . $data );
			$this->log->add( 'directdebitredsys', '$remote_sign: ' . $remote_sign );
			$this->log->add( 'directdebitredsys', ' ' );
		}

		$mi_obj            = new WooRedsysAPI();
		$usesecretsha256   = $this->secretsha256;
		$dscardnumbercompl = '';
		$dsexpiration      = '';
		$dsmerchantidenti  = '';
		$dscardnumber4     = '';
		$dsexpiryyear      = '';
		$dsexpirymonth     = '';
		$decodedata        = $mi_obj->decodeMerchantParameters( $data );
		$localsecret       = $mi_obj->createMerchantSignatureNotif( $usesecretsha256, $data );
		$total             = $mi_obj->getParameter( 'Ds_Amount' );
		$ordermi           = $mi_obj->getParameter( 'Ds_Order' );
		$dscode            = $mi_obj->getParameter( 'Ds_MerchantCode' );
		$currency_code     = $mi_obj->getParameter( 'Ds_Currency' );
		$response          = $mi_obj->getParameter( 'Ds_Response' );
		$id_trans          = $mi_obj->getParameter( 'Ds_AuthorisationCode' );
		$dsdate            = htmlspecialchars_decode( $mi_obj->getParameter( 'Ds_Date' ) );
		$dshour            = htmlspecialchars_decode( $mi_obj->getParameter( 'Ds_Hour' ) );
		$dstermnal         = $mi_obj->getParameter( 'Ds_Terminal' );
		$dsmerchandata     = $mi_obj->getParameter( 'Ds_MerchantData' );
		$dssucurepayment   = $mi_obj->getParameter( 'Ds_SecurePayment' );
		$dscardcountry     = $mi_obj->getParameter( 'Ds_Card_Country' );
		$dsconsumercountry = $mi_obj->getParameter( 'Ds_ConsumerLanguage' );
		$dstransactiontype = $mi_obj->getParameter( 'Ds_TransactionType' );
		$dsmerchantidenti  = $mi_obj->getParameter( 'Ds_Merchant_Identifier' );
		$dscardbrand       = $mi_obj->getParameter( 'Ds_Card_Brand' );
		$dsmechandata      = $mi_obj->getParameter( 'Ds_MerchantData' );
		$dscargtype        = $mi_obj->getParameter( 'Ds_Card_Type' );
		$dserrorcode       = $mi_obj->getParameter( 'Ds_ErrorCode' );
		$dpaymethod        = $mi_obj->getParameter( 'Ds_PayMethod' ); // D o R, D: Domiciliacion, R: Transferencia. Si se paga por Iupay o TC, no se utiliza.
		$response          = intval( $response );
		$secretsha256      = get_transient( 'redsys_signature_' . sanitize_title( $ordermi ) );
		$order1            = $ordermi;
		$order2            = WCRed()->clean_order_number( $order1 );
		$order             = WCRed()->get_order( (int) $order2 );

		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', 'SHA256 Settings: ' . $usesecretsha256 );
			$this->log->add( 'directdebitredsys', 'SHA256 Transcient: ' . $secretsha256 );
			$this->log->add( 'directdebitredsys', 'decodeMerchantParameters: ' . $decodedata );
			$this->log->add( 'directdebitredsys', 'createMerchantSignatureNotif: ' . $localsecret );
			$this->log->add( 'directdebitredsys', 'Ds_Amount: ' . $total );
			$this->log->add( 'directdebitredsys', 'Ds_Order: ' . $ordermi );
			$this->log->add( 'directdebitredsys', 'Ds_MerchantCode: ' . $dscode );
			$this->log->add( 'directdebitredsys', 'Ds_Currency: ' . $currency_code );
			$this->log->add( 'directdebitredsys', 'Ds_Response: ' . $response );
			$this->log->add( 'directdebitredsys', 'Ds_AuthorisationCode: ' . $id_trans );
			$this->log->add( 'directdebitredsys', 'Ds_Date: ' . $dsdate );
			$this->log->add( 'directdebitredsys', 'Ds_Hour: ' . $dshour );
			$this->log->add( 'directdebitredsys', 'Ds_Terminal: ' . $dstermnal );
			$this->log->add( 'directdebitredsys', 'Ds_MerchantData: ' . $dsmerchandata );
			$this->log->add( 'directdebitredsys', 'Ds_SecurePayment: ' . $dssucurepayment );
			$this->log->add( 'directdebitredsys', 'Ds_Card_Country: ' . $dscardcountry );
			$this->log->add( 'directdebitredsys', 'Ds_ConsumerLanguage: ' . $dsconsumercountry );
			$this->log->add( 'directdebitredsys', 'Ds_Card_Type: ' . $dscargtype );
			$this->log->add( 'directdebitredsys', 'Ds_TransactionType: ' . $dstransactiontype );
			$this->log->add( 'directdebitredsys', 'Ds_Merchant_Identifiers_Amount: ' . $response );
			$this->log->add( 'directdebitredsys', 'Ds_Card_Brand: ' . $dscardbrand );
			$this->log->add( 'directdebitredsys', 'Ds_MerchantData: ' . $dsmechandata );
			$this->log->add( 'directdebitredsys', 'Ds_ErrorCode: ' . $dserrorcode );
			$this->log->add( 'directdebitredsys', 'Ds_PayMethod: ' . $dpaymethod );
		}

		// refund.

		if ( '3' === $dstransactiontype ) {
			if ( 900 === $response ) {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', 'Response 900 (refund)' );
				}
				set_transient( $order->get_id() . '_redsys_refund', 'yes' );

				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', 'WCRed()->update_order_meta to "refund yes"' );
				}
				$status = $order->get_status();
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', 'New Status in request: ' . $status );
				}
				$order->add_order_note( __( 'Order Payment refunded by Redsys', 'woocommerce-redsys' ) );
				return;
			}
			$order->add_order_note( __( 'There was an error refunding', 'woocommerce-redsys' ) );
			exit;
		}

		$response = intval( $response );
		if ( $response <= 99 ) {
			// authorized.
			$order_total_compare = number_format( $order->get_total(), 2, '', '' );
			// remove 0 from bigining.
			$order_total_compare = ltrim( $order_total_compare, '0' );
			$total               = ltrim( $total, '0' );
			if ( $order_total_compare !== $total ) {
				// amount does not match.
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', 'Payment error: Amounts do not match (order: ' . $order_total_compare . ' - received: ' . $total . ')' );
				}
				// Put this order on-hold for manual checking.
				/* translators: order an received are the amount */
				$order->update_status( 'on-hold', sprintf( __( 'Validation error: Order vs. Notification amounts do not match (order: %1$s - received: %2&s).', 'woocommerce-redsys' ), $order_total_compare, $total ) );
				exit;
			}
			$authorisation_code = $id_trans;
			$dsdate             = date( 'd/m/Y', current_time( 'timestamp', 0 ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested,WordPress.DateTime.RestrictedFunctions.date_date
			$dshour             = date( 'H:i', current_time( 'timestamp', 0 ) ); // phpcs:ignore WordPress.DateTime.CurrentTimeTimestamp.Requested,WordPress.DateTime.RestrictedFunctions.date_date

			if ( 'yes' === $this->debug ) {
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', '/****************************/' );
				$this->log->add( 'directdebitredsys', '      Saving Order Meta       ' );
				$this->log->add( 'directdebitredsys', '/****************************/' );
				$this->log->add( 'directdebitredsys', ' ' );
			}
			$data = array();
			if ( ! empty( $order1 ) ) {
				$data['_payment_order_number_redsys'] = $order1;
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', '_payment_order_number_redsys saved: ' . $order1 );
				}
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '_payment_order_number_redsys NOT SAVED!!!' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
			}
			if ( ! empty( $dsdate ) ) {
				$data['_payment_date_redsys'] = $dsdate;
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', '_payment_date_redsys saved: ' . $dsdate );
				}
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '_payment_date_redsys NOT SAVED!!!' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
			}
			if ( ! empty( $dsdate ) ) {
				$data['_payment_terminal_redsys'] = $dstermnal;
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', '_payment_terminal_redsys saved: ' . $dstermnal );
				}
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '_payment_terminal_redsys NOT SAVED!!!' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
			}
			if ( ! empty( $dshour ) ) {
				$data['_payment_hour_redsys'] = $dshour;
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', '_payment_hour_redsys saved: ' . $dshour );
				}
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '_payment_hour_redsys NOT SAVED!!!' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
			}
			if ( ! empty( $id_trans ) ) {
				$data['_authorisation_code_redsys'] = $authorisation_code;
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', '_authorisation_code_redsys saved: ' . $authorisation_code );
				}
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '_authorisation_code_redsys NOT SAVED!!!' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
			}
			if ( ! empty( $currency_code ) ) {
				$data['_corruncy_code_redsys'] = $currency_code;
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', '_corruncy_code_redsys saved: ' . $currency_code );
				}
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '_corruncy_code_redsys NOT SAVED!!!' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
			}
			if ( ! empty( $dscardcountry ) ) {
				$data['_card_country_redsys'] = $dscardcountry;
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', '_card_country_redsys saved: ' . $dscardcountry );
				}
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '_card_country_redsys NOT SAVED!!!' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
			}
			// This meta is essential for later use.
			if ( ! empty( $secretsha256 ) ) {
				$data['_redsys_secretsha256'] = $secretsha256;
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', '_redsys_secretsha256 saved: ' . $secretsha256 );
				}
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '_redsys_secretsha256 NOT SAVED!!!' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
			}
			WCRed()->update_order_meta( $order->get_id(), $data );
			// Payment completed.
			$order->add_order_note( __( 'HTTP Notification received - payment completed', 'woocommerce-redsys' ) );
			$order->add_order_note( __( 'Authorization code: ', 'woocommerce-redsys' ) . $authorisation_code );
			$order->payment_complete();
			if ( 'completed' === $this->orderdo ) {
				$order->update_status( 'completed', __( 'Order Completed by Direct Debit', 'woocommerce-redsys' ) );
			}

			if ( 'yes' === $this->debug ) {
				$this->log->add( 'directdebitredsys', 'Payment complete.' );
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', '/******************************************/' );
				$this->log->add( 'directdebitredsys', '  The final has come, this story has ended  ' );
				$this->log->add( 'directdebitredsys', '/******************************************/' );
				$this->log->add( 'directdebitredsys', ' ' );
			}
			do_action( 'directdeb_post_payment_complete', $order->get_id() );
		} else {

			$ds_response_value = WCRed()->get_error( $response );
			$ds_error_value    = WCRed()->get_error( $dserrorcode );

			if ( $ds_response_value ) {
				$order->add_order_note( __( 'Order cancelled by Redsys: ', 'woocommerce-redsys' ) . $ds_response_value );
				WCRed()->update_order_meta( $order->get_id(), '_redsys_error_payment_ds_response_value', $ds_response_value );
			}

			if ( $ds_error_value ) {
				$order->add_order_note( __( 'Order cancelled by Redsys: ', 'woocommerce-redsys' ) . $ds_error_value );
				WCRed()->update_order_meta( $order->get_id(), '_redsys_error_payment_ds_response_value', $ds_error_value );
			}
			if ( 'yes' === $this->debug ) {
				if ( $ds_response_value ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', $ds_response_value );
				}
				if ( $ds_error_value ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', $ds_error_value );
				}
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', '/******************************************/' );
				$this->log->add( 'directdebitredsys', '  The final has come, this story has ended  ' );
				$this->log->add( 'directdebitredsys', '/******************************************/' );
				$this->log->add( 'directdebitredsys', ' ' );
			}
			// Order cancelled.
			$order->update_status( 'cancelled', __( 'Order cancelled by Redsys Direct Debit', 'woocommerce-redsys' ) );
			$order->add_order_note( __( 'Order cancelled by Redsys Direct Debit', 'woocommerce-redsys' ) );
			WC()->cart->empty_cart();
			if ( ! $ds_response_value ) {
				$ds_response_value = '';
			}
			if ( ! $ds_error_value ) {
				$ds_error_value = '';
			}
			$error = $ds_response_value . ' ' . $ds_error_value;
			do_action( 'directdeb_post_payment_error', $order->get_id(), $error );
		}
	}
	/**
	 * Ask for refund.
	 *
	 * @param  int    $order_id Order ID.
	 * @param  string $transaction_id Transaction ID.
	 * @param  float  $amount Amount.
	 * @return bool
	 */
	public function ask_for_refund( $order_id, $transaction_id, $amount ) {

		// post code to REDSYS.
		$order          = WCRed()->get_order( $order_id );
		$terminal       = WCRed()->get_order_meta( $order_id, '_payment_terminal_redsys', true );
		$currency_codes = WCRed()->get_currencies();
		$user_id        = $order->get_user_id();
		$secretsha256   = $this->get_redsys_sha256( $user_id );

		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', '/**************************/' );
			$this->log->add( 'directdebitredsys', __( 'Starting asking for Refund', 'woocommerce-redsys' ) );
			$this->log->add( 'directdebitredsys', '/**************************/' );
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', __( 'Terminal : ', 'woocommerce-redsys' ) . $terminal );
		}
		$transaction_type  = '3';
		$secretsha256_meta = WCRed()->get_order_meta( $order_id, '_redsys_secretsha256', true );
		if ( $secretsha256_meta ) {
			$secretsha256 = $secretsha256_meta;
			if ( 'yes' === $this->debug ) {
				$this->log->add( 'directdebitredsys', __( 'Using meta for SHA256', 'woocommerce-redsys' ) );
				$this->log->add( 'directdebitredsys', __( 'The SHA256 Meta is: ', 'woocommerce-redsys' ) . $secretsha256 );
			}
		} else {
			$secretsha256 = $secretsha256;
			if ( 'yes' === $this->debug ) {
				$this->log->add( 'directdebitredsys', __( 'Using settings for SHA256', 'woocommerce-redsys' ) );
				$this->log->add( 'directdebitredsys', __( 'The SHA256 settings is: ', 'woocommerce-redsys' ) . $secretsha256 );
			}
		}
		if ( 'yes' === $this->not_use_https ) {
			$final_notify_url = $this->notify_url_not_https;
		} else {
			$final_notify_url = $this->notify_url;
		}
		$redsys_adr        = $this->get_redsys_url_gateway( $user_id );
		$autorization_code = WCRed()->get_order_meta( $order_id, '_authorisation_code_redsys', true );
		$autorization_date = WCRed()->get_order_meta( $order_id, '_payment_date_redsys', true );
		$currencycode      = WCRed()->get_order_meta( $order_id, '_corruncy_code_redsys', true );

		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', __( 'All data from meta', 'woocommerce-redsys' ) );
			$this->log->add( 'directdebitredsys', '**********************' );
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', __( 'If something is empty, the data was not saved', 'woocommerce-redsys' ) );
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', __( 'All data from meta', 'woocommerce-redsys' ) );
			$this->log->add( 'directdebitredsys', __( 'Authorization Code : ', 'woocommerce-redsys' ) . $autorization_code );
			$this->log->add( 'directdebitredsys', __( 'Authorization Date : ', 'woocommerce-redsys' ) . $autorization_date );
			$this->log->add( 'directdebitredsys', __( 'Currency Codey : ', 'woocommerce-redsys' ) . $currencycode );
			$this->log->add( 'directdebitredsys', __( 'Terminal : ', 'woocommerce-redsys' ) . $terminal );
			$this->log->add( 'directdebitredsys', __( 'SHA256 : ', 'woocommerce-redsys' ) . $secretsha256_meta );

		}

		if ( ! empty( $currencycode ) ) {
			$currency = $currencycode;
		} else {
			if ( ! empty( $currency_codes ) ) {
				$currency = $currency_codes[ get_woocommerce_currency() ];
			}
		}

		$mi_obj = new WooRedsysAPI();
		$mi_obj->setParameter( 'DS_MERCHANT_AMOUNT', $amount );
		$mi_obj->setParameter( 'DS_MERCHANT_ORDER', $transaction_id );
		$mi_obj->setParameter( 'DS_MERCHANT_MERCHANTCODE', $this->customer );
		$mi_obj->setParameter( 'DS_MERCHANT_CURRENCY', $currency );
		$mi_obj->setParameter( 'DS_MERCHANT_TRANSACTIONTYPE', $transaction_type );
		$mi_obj->setParameter( 'DS_MERCHANT_TERMINAL', $terminal );
		$mi_obj->setParameter( 'DS_MERCHANT_MERCHANTURL', $final_notify_url );
		$mi_obj->setParameter( 'DS_MERCHANT_URLOK', add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $order ) ) );
		$mi_obj->setParameter( 'DS_MERCHANT_URLKO', $order->get_cancel_order_url() );
		$mi_obj->setParameter( 'DS_MERCHANT_CONSUMERLANGUAGE', '001' );
		$mi_obj->setParameter( 'DS_MERCHANT_PRODUCTDESCRIPTION', WCRed()->product_description( $order, 'directdebitredsys' ) );
		$mi_obj->setParameter( 'DS_MERCHANT_MERCHANTNAME', $this->commercename );

		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', __( 'Data sent to Redsys for refund', 'woocommerce-redsys' ) );
			$this->log->add( 'directdebitredsys', '*********************************' );
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', __( 'URL to Redsys : ', 'woocommerce-redsys' ) . $redsys_adr );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_AMOUNT : ', 'woocommerce-redsys' ) . $amount );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_ORDER : ', 'woocommerce-redsys' ) . $transaction_id );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_MERCHANTCODE : ', 'woocommerce-redsys' ) . $this->customer );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_CURRENCY : ', 'woocommerce-redsys' ) . $currency );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_TRANSACTIONTYPE : ', 'woocommerce-redsys' ) . $transaction_type );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_TERMINAL : ', 'woocommerce-redsys' ) . $terminal );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_MERCHANTURL : ', 'woocommerce-redsys' ) . $final_notify_url );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_URLOK : ', 'woocommerce-redsys' ) . add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $order ) ) );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_URLKO : ', 'woocommerce-redsys' ) . $order->get_cancel_order_url() );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_CONSUMERLANGUAGE : 001', 'woocommerce-redsys' ) );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_PRODUCTDESCRIPTION : ', 'woocommerce-redsys' ) . WCRed()->product_description( $order, 'directdebitredsys' ) );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_MERCHANTNAME : ', 'woocommerce-redsys' ) . $this->commercename );
			$this->log->add( 'directdebitredsys', __( 'DS_MERCHANT_AUTHORISATIONCODE : ', 'woocommerce-redsys' ) . $autorization_code );
			$this->log->add( 'directdebitredsys', __( 'Ds_Merchant_TransactionDate : ', 'woocommerce-redsys' ) . $autorization_date );
			$this->log->add( 'directdebitredsys', __( 'ask_for_refund Asking por order #: ', 'woocommerce-redsys' ) . $order_id );
			$this->log->add( 'directdebitredsys', ' ' );
		}

		$version   = 'HMAC_SHA256_V1';
		$request   = '';
		$params    = $mi_obj->createMerchantParameters();
		$signature = $mi_obj->createMerchantSignature( $secretsha256 );

		$post_arg = wp_remote_post(
			$redsys_adr,
			array(
				'method'      => 'POST',
				'timeout'     => 45,
				'httpversion' => '1.0',
				'user-agent'  => 'WooCommerce',
				'body'        => array(
					'Ds_SignatureVersion'   => $version,
					'Ds_MerchantParameters' => $params,
					'Ds_Signature'          => $signature,
				),
			)
		);
		if ( is_wp_error( $post_arg ) ) {
			if ( 'yes' === $this->debug ) {
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', __( 'There is an error', 'woocommerce-redsys' ) );
				$this->log->add( 'directdebitredsys', '*********************************' );
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', __( 'The error is : ', 'woocommerce-redsys' ) . $post_arg );
			}
			return $post_arg;
		}
		return true;
	}
	/**
	 * Check if the pingback for refunds is valid
	 *
	 * @param  int $order_id Order ID.
	 */
	public function check_redsys_refund( $order_id ) {
		// check postmeta.
		$order        = WCRed()->get_order( (int) $order_id );
		$order_refund = get_transient( $order->get_id() . '_redsys_refund' );
		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', __( 'Checking and waiting ping from Redsys', 'woocommerce-redsys' ) );
			$this->log->add( 'directdebitredsys', '*****************************************' );
			$this->log->add( 'directdebitredsys', ' ' );
			$this->log->add( 'directdebitredsys', __( 'Check order status #: ', 'woocommerce-redsys' ) . $order->get_id() );
			$this->log->add( 'directdebitredsys', __( 'Check order status with get_transient: ', 'woocommerce-redsys' ) . $order_refund );
		}
		if ( 'yes' === $order_refund ) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * Process a refund if supported
	 *
	 * @param  int    $order_id Order ID.
	 * @param  float  $amount Refund amount.
	 * @param  string $reason Refund reason.
	 * @return  boolean True or false based on success, or a WP_Error object
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		// Do your refund here. Refund $amount for the order with ID $order_id _transaction_id.
		set_time_limit( 0 );
		$order = wc_get_order( $order_id );

		$transaction_id = WCRed()->get_redsys_order_number( $order_id );
		if ( 'yes' === $this->debug ) {
			$this->log->add( 'directdebitredsys', __( '$order_id#: ', 'woocommerce-redsys' ) . $transaction_id );
		}
		if ( ! $amount ) {
			$order_total_sign = WCRed()->redsys_amount_format( $order->get_total() );
		} else {
			$order_total_sign = number_format( $amount, 2, '', '' );
		}

		if ( ! empty( $transaction_id ) ) {
			if ( 'yes' === $this->debug ) {
				$this->log->add( 'directdebitredsys', __( 'check_redsys_refund Asking for order #: ', 'woocommerce-redsys' ) . $order_id );
			}

			$refund_asked = $this->ask_for_refund( $order_id, $transaction_id, $order_total_sign );

			if ( is_wp_error( $refund_asked ) ) {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'directdebitredsys', __( 'Refund Failed: ', 'woocommerce-redsys' ) . $refund_asked->get_error_message() );
				}
				return new WP_Error( 'error', $refund_asked->get_error_message() );
			}
			$x = 0;
			do {
				sleep( 5 );
				$result = $this->check_redsys_refund( $order_id );
				$x++;
			} while ( $x <= 20 && false === $result );
			if ( 'yes' === $this->debug && $result ) {
				$this->log->add( 'directdebitredsys', __( 'check_redsys_refund = true ', 'woocommerce-redsys' ) . $result );
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', '/********************************/' );
				$this->log->add( 'directdebitredsys', '  Refund complete by Redsys   ' );
				$this->log->add( 'directdebitredsys', '/********************************/' );
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', '/******************************************/' );
				$this->log->add( 'directdebitredsys', '  The final has come, this story has ended  ' );
				$this->log->add( 'directdebitredsys', '/******************************************/' );
				$this->log->add( 'directdebitredsys', ' ' );
			}
			if ( 'yes' === $this->debug && ! $result ) {
				$this->log->add( 'directdebitredsys', __( 'check_redsys_refund = false ', 'woocommerce-redsys' ) . $result );
			}
			if ( $result ) {
				delete_transient( $order->get_id() . '_redsys_refund' );
				return true;
			} else {
				if ( 'yes' === $this->debug && $result ) {
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!' );
					$this->log->add( 'directdebitredsys', __( '!!!!Refund Failed, please try again!!!!', 'woocommerce-redsys' ) );
					$this->log->add( 'directdebitredsys', '!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!' );
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', ' ' );
					$this->log->add( 'directdebitredsys', '/******************************************/' );
					$this->log->add( 'directdebitredsys', '  The final has come, this story has ended  ' );
					$this->log->add( 'directdebitredsys', '/******************************************/' );
					$this->log->add( 'directdebitredsys', ' ' );
				}
				return false;
			}
		} else {
			if ( 'yes' === $this->debug && $result ) {
				$this->log->add( 'directdebitredsys', __( 'Refund Failed: No transaction ID', 'woocommerce-redsys' ) );
				$this->log->add( 'directdebitredsys', ' ' );
				$this->log->add( 'directdebitredsys', '/******************************************/' );
				$this->log->add( 'directdebitredsys', '  The final has come, this story has ended  ' );
				$this->log->add( 'directdebitredsys', '/******************************************/' );
				$this->log->add( 'directdebitredsys', ' ' );
			}
			return new WP_Error( 'error', __( 'Refund Failed: No transaction ID', 'woocommerce-redsys' ) );
		}
	}
	/**
	 * Package: WooCommerce Redsys Gateway
	 * Plugin URI: https://woocommerce.com/es-es/products/redsys-gateway/
	 * Copyright: (C) 2013 - 2023 José Conti
	 */
	public function warning_checkout_test_mode_directdebit() {
		if ( 'yes' === $this->testmode && WCRed()->is_gateway_enabled( $this->id ) ) {
			echo '<div class="checkout-message" style="
			background-color: rgb(3, 166, 120);
			padding: 1em 1.618em;
			margin-bottom: 2.617924em;
			margin-left: 0;
			border-radius: 2px;
			color: #fff;
			clear: both;
			border-left: 0.6180469716em solid rgb(1, 152, 117);
			">';
			echo esc_html__( 'Warning: WooCommerce Redsys Gateway Direct Debit is in test mode. Remember to uncheck it when you go live', 'woo-redsys-gateway-light' );
			echo '</div>';
		}
	}
	/**
	 * Check if this gateway is enabled and available in the user's test mode selection
	 *
	 * @param int $userid User ID.
	 */
	public function check_user_show_payment_method( $userid = false ) {

		$test_mode  = $this->testmode;
		$selections = (array) WCRed()->get_redsys_option( 'testshowgateway', 'directdebitredsys' );

		if ( 'yes' !== $test_mode ) {
			return true;
		}
		if ( '' !== $selections[0] || empty( $selections ) ) {
			if ( ! $userid ) {
				return false;
			}
			foreach ( $selections as $user_id ) {
				if ( (int) $user_id === (int) $userid ) {
					return true;
				}
				continue;
			}
			return false;
		} else {
			return true;
		}
	}
	/**
	 * Check if this gateway is enabled and available for current user.
	 *
	 * @param array $available_gateways Available gateways.
	 */
	public function show_payment_method( $available_gateways ) {

		if ( ! is_admin() ) {
			if ( is_user_logged_in() ) {
				$user_id = get_current_user_id();
				$show    = $this->check_user_show_payment_method( $user_id );
				if ( ! $show ) {
					unset( $available_gateways[ $this->id ] );
				}
			} else {
				$show = $this->check_user_show_payment_method();
				if ( ! $show ) {
					unset( $available_gateways[ $this->id ] );
				}
			}
		}
		return $available_gateways;
	}
}
/**
 * Add the gateway to woocommerce
 *
 * @param array $methods WooCommerce payment methods.
 */
function woocommerce_add_gateway_directdebit_redsys( $methods ) {
		$methods[] = 'WC_Gateway_Direct_Debit_Redsys';
		return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_gateway_directdebit_redsys' );
