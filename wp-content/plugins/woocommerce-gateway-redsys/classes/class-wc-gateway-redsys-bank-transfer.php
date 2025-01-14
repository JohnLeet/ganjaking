<?php
/**
 * Redsys Bank Transfer Gateway
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
 * WooCommerce Redsys Bank Transfer Gateway Class
 */
class WC_Gateway_Redsys_Bank_Transfer extends WC_Payment_Gateway {
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
		global $woocommerce, $checkfor254;
		$this->id = 'redsysbank';
		$logo_url = WCRed()->get_redsys_option( 'logo', 'redsysbank' );
		if ( ! empty( $logo_url ) ) {
			$logo_url   = WCRed()->get_redsys_option( 'logo', 'redsysbank' );
			$this->icon = apply_filters( 'woocommerce_bank_redsys_icon', $logo_url );
		} else {
			$this->icon = apply_filters( 'woocommerce_bank_redsys_icon', REDSYS_PLUGIN_URL_P . 'assets/images/redsys.png' );
		}
		$this->has_fields           = false;
		$this->liveurl              = 'https://sis.redsys.es/sis/realizarPago';
		$this->method_title         = __( 'Redsys Direct Bank Transfer (by José Conti)', 'woocommerce-redsys' );
		$this->method_description   = __( 'Redys bank transfer works by showing in Redsys the bank account where to make the transfer. Some banks offer to make it in the moment, so the order is marked as paid in WooCommerce.', 'woocommerce-redsys' );
		$this->not_use_https        = WCRed()->get_redsys_option( 'not_use_https', 'redsysbank' );
		$this->notify_url           = add_query_arg( 'wc-api', 'WC_Gateway_bankredsys', home_url( '/' ) );
		$this->notify_url_not_https = str_replace( 'https:', 'http:', add_query_arg( 'wc-api', 'WC_Gateway_redsysbank', home_url( '/' ) ) );
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		// Define user set variables.
		$this->title            = WCRed()->get_redsys_option( 'title', 'redsysbank' );
		$this->multisitesttings = WCRed()->get_redsys_option( 'multisitesttings', 'redsysbank' );
		$this->ownsetting       = WCRed()->get_redsys_option( 'ownsetting', 'redsysbank' );
		$this->hideownsetting   = WCRed()->get_redsys_option( 'hideownsetting', 'redsysbank' );
		$this->description      = WCRed()->get_redsys_option( 'description', 'redsysbank' );
		$this->logo             = WCRed()->get_redsys_option( 'logo', 'redsysbank' );
		$this->orderdo          = WCRed()->get_redsys_option( 'orderdo', 'redsysbank' );
		$this->customer         = WCRed()->get_redsys_option( 'customer', 'redsysbank' );
		$this->commercename     = WCRed()->get_redsys_option( 'commercename', 'redsysbank' );
		$this->terminal         = WCRed()->get_redsys_option( 'terminal', 'redsysbank' );
		$this->secret           = WCRed()->get_redsys_option( 'secret', 'redsysbank' );
		$this->secretsha256     = WCRed()->get_redsys_option( 'secretsha256', 'redsysbank' );
		$this->debug            = WCRed()->get_redsys_option( 'debug', 'redsysbank' );
		$this->hashtype         = WCRed()->get_redsys_option( 'hashtype', 'redsysbank' );
		$this->redsyslanguage   = WCRed()->get_redsys_option( 'redsyslanguage', 'redsysbank' );
		$this->codigoswift      = WCRed()->get_redsys_option( 'codigoswift', 'redsysbank' );
		$this->iban             = WCRed()->get_redsys_option( 'iban', 'redsysbank' );
		$this->beneficiario     = WCRed()->get_redsys_option( 'beneficiario', 'redsysbank' );
		$this->buttoncheckout   = WCRed()->get_redsys_option( 'buttoncheckout', 'redsysbank' );
		$this->butonbgcolor     = WCRed()->get_redsys_option( 'butonbgcolor', 'redsysbank' );
		$this->butontextcolor   = WCRed()->get_redsys_option( 'butontextcolor', 'redsysbank' );
		$this->descripredsys    = WCRed()->get_redsys_option( 'descripredsys', 'redsysbank' );
		$this->testshowgateway  = WCRed()->get_redsys_option( 'testshowgateway', 'redsysbank' );
		$this->testmode         = 'no';
		$this->log              = new WC_Logger();
		// Actions.
		add_action( 'valid_redsysbank_standard_ipn_request', array( $this, 'successful_request' ) );
		add_action( 'woocommerce_receipt_redsysbank', array( $this, 'receipt_page' ) );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_filter( 'woocommerce_available_payment_gateways', array( $this, 'show_payment_method' ) );
		// Payment listener/API hook.
		add_action( 'woocommerce_api_redsysbank', array( $this, 'check_ipn_response' ) );
		if ( ! $this->is_valid_for_use() ) {
			$this->enabled = false;
		}
	}

	/**
	 * Check if this gateway is enabled and available in the user's country
	 */
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
	 * @since 1.0.0
	 */
	/**
	 * Package: WooCommerce Redsys Gateway
	 * Plugin URI: https://woocommerce.com/es-es/products/redsys-gateway/
	 * Copyright: (C) 2013 - 2023 José Conti
	 */
	public function admin_options() {
		?>
		<h3><?php esc_html_e( 'Redsys Bank Transfer', 'woocommerce-redsys' ); ?></h3>
		<p><?php esc_html_e( 'Redys bank transfer works by showing in Redsys the bank account where to make the transfer. Some banks offer to make it in the moment, so the order is marked as paid in WooCommerce.', 'woocommerce-redsys' ); ?></p>
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
			</p>
	</div>
			<?php
			endif;
	}
	/**
	 * Initialise Gateway Settings Form Fields
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'          => array(
				'title'   => __( 'Enable/Disable', 'woocommerce-redsys' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable Redsys Bank Transfer', 'woocommerce-redsys' ),
				'default' => 'no',
			),
			'multisitesttings' => array(
				'title'       => __( 'Use in Network', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Use this setting around all Network', 'woocommerce-redsys' ),
				'description' => '',
				'default'     => 'no',
			),
			'hideownsetting'   => array(
				'title'       => __( 'Hide "NOT use Network" in subsites', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Hide "NOT use Network" in subsites', 'woocommerce-redsys' ),
				'description' => '',
				'default'     => 'no',
			),
			'ownsetting'       => array(
				'title'       => __( 'NOT use Network', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Do NOT use Network settings. Use settings of this page', 'woocommerce-redsys' ),
				'description' => '',
				'default'     => 'no',
			),
			'title'            => array(
				'title'       => __( 'Title', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce-redsys' ),
				'default'     => __( 'Example: If you use CaixaBank, you can use this Bank Transfer, you will be redirected to Redys and from there you will be able to log in to CaixaBank and make the bank transfer automatically.', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'description'      => array(
				'title'       => __( 'Description', 'woocommerce-redsys' ),
				'type'        => 'textarea',
				'description' => __( 'This controls the description which the user sees during checkout.', 'woocommerce-redsys' ),
				'default'     => __( 'Pay via RedSys Bank Transfer', 'woocommerce-redsys' ),
			),
			'logo'             => array(
				'title'       => __( 'Logo', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Add link to image logo.', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'buttoncheckout'   => array(
				'title'       => __( 'Button Checkout Text', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Add the button text at the checkout.', 'woocommerce-redsys' ),
			),
			'butonbgcolor'     => array(
				'title'       => __( 'Button Color Background', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'This if button Color Background Place Order at Checkout', 'woocommerce-redsys' ),
				'class'       => 'colorpick',
			),
			'butontextcolor'   => array(
				'title'       => __( 'Color text Button', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'This if button text color Place Order at Checkout', 'woocommerce-redsys' ),
				'class'       => 'colorpick',
			),
			'customer'         => array(
				'title'       => __( 'Commerce number (FUC)', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Commerce number (FUC) provided by your bank.', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'commercename'     => array(
				'title'       => __( 'Commerce Name', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Commerce Name', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'terminal'         => array(
				'title'       => __( 'Terminal number', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Terminal number provided by your bank.', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'descripredsys'    => array(
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
			'not_use_https'    => array(
				'title'       => __( 'HTTPS SNI Compatibility', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Activate SNI Compatibility.', 'woocommerce-redsys' ),
				'default'     => 'no',
				'description' => sprintf( __( 'If you are using HTTPS and Redsys don\'t support your certificate, example Lets Encrypt, you can deactivate HTTPS notifications. WARNING: If you are forcing redirection to HTTPS with htaccess, you need to add an exception for notification URL', 'woocommerce-redsys' ) ),
			),
			'orderdo'          => array(
				'title'       => __( 'What to do after payment?', 'woocommerce-redsys' ),
				'type'        => 'select',
				'description' => __( 'Chose what to do after the customer pay the order.', 'woocommerce-redsys' ),
				'default'     => 'processing',
				'options'     => array(
					'processing' => __( 'Mark as Processing (default & recommended)', 'woocommerce-redsys' ),
					'completed'  => __( 'Mark as Complete', 'woocommerce-redsys' ),
				),
			),
			'secretsha256'     => array(
				'title'       => __( 'Encryption secret passphrase SHA-256', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Encryption secret passphrase SHA-256 provided by your bank.', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'redsyslanguage'   => array(
				'title'       => __( 'Language Gateway', 'woocommerce-redsys' ),
				'type'        => 'select',
				'description' => __( 'Choose the language for the Gateway. Not all Banks accept all languages', 'woocommerce-redsys' ),
				'default'     => '001',
				'options'     => array(),
			),
			'codigoswift'      => array(
				'title'       => __( 'SWIFT Code', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Add SWIFT Code', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'iban'             => array(
				'title'       => __( 'IBAN', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Add IBAN Code', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'beneficiario'     => array(
				'title'       => __( 'Transferee', 'woocommerce-redsys' ),
				'type'        => 'text',
				'description' => __( 'Transferee', 'woocommerce-redsys' ),
				'desc_tip'    => true,
			),
			'debug'            => array(
				'title'       => __( 'Debug Log', 'woocommerce-redsys' ),
				'type'        => 'checkbox',
				'label'       => __( 'Enable logging', 'woocommerce-redsys' ),
				'default'     => 'no',
				'description' => __( 'Log Servired/RedSys events, such as notifications requests, inside <code>WooCommerce > Status > Logs > redsysbanktransfer-{date}-{number}.log</code>', 'woocommerce-redsys' ),
			),
		);

		$redsyslanguages = WCRed()->get_redsys_languages();

		foreach ( $redsyslanguages as $redsyslanguage => $valor ) {
			$this->form_fields['redsyslanguage']['options'][ $redsyslanguage ] = $valor;
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
	 * Get Redsys Arguments for passing to PP
	 *
	 * @param  WC_Order $order Order object.
	 */
	public function get_redsys_args( $order ) {
		global $woocommerce;
		$order_id       = $order->get_id();
		$currency_codes = WCRed()->get_currencies();
		$order->update_status( 'redsys-pbankt', __( 'Pending Redsys Bank Transfer', 'woocommerce-redsys' ) );
		$transaction_id2  = WCRed()->prepare_order_number( $order_id );
		$order_total_sign = WCRed()->redsys_amount_format( $order->get_total() );
		$transaction_type = '0';
		$secretsha256     = utf8_decode( $this->secretsha256 );
		if ( class_exists( 'SitePress' ) ) {
			$gatewaylanguage = WCRed()->get_lang_code( ICL_LANGUAGE_CODE );
		} elseif ( $this->redsyslanguage ) {
				$gatewaylanguage = $this->redsyslanguage;
		} else {
				$gatewaylanguage = '001';
		}
		$returnfromredsys   = add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $order ) );
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
		$miobj->setParameter( 'DS_MERCHANT_PAYMETHODS', $payment_option );
		$miobj->setParameter( 'DS_MERCHANT_TRANSACTIONTYPE', $transaction_type );
		$miobj->setParameter( 'DS_MERCHANT_TERMINAL', $dsmerchantterminal );
		$miobj->setParameter( 'DS_MERCHANT_MERCHANTURL', $final_notify_url );
		$miobj->setParameter( 'DS_MERCHANT_URLOK', add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $order ) ) );
		$miobj->setParameter( 'DS_MERCHANT_URLKO', $returnfromredsys );
		$miobj->setParameter( 'DS_MERCHANT_CONSUMERLANGUAGE', $gatewaylanguage );
		$miobj->setParameter( 'DS_MERCHANT_PRODUCTDESCRIPTION', WCRed()->product_description( $order, $this->id ) );
		$miobj->setParameter( 'DS_MERCHANT_MERCHANTNAME', $this->commercename );
		$miobj->setParameter( 'DS_MERCHANT_PAYMETHODS', 'R' );
		$version = 'HMAC_SHA256_V1';
		// Se generan los parámetros de la petición.
		$request     = '';
		$params      = $miobj->createMerchantParameters();
		$signature   = $miobj->createMerchantSignature( $secretsha256 );
		$redsys_args = array(
			'Ds_SignatureVersion'   => $version,
			'Ds_MerchantParameters' => $params,
			'Ds_Signature'          => $signature,
		);
		if ( 'yes' === $this->debug ) {
			$this->log->add( 'redsysbanktransfer', 'Generating payment form for order ' . $order->get_order_number() . '. Sent data: ' . print_r( $redsys_args, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
			$this->log->add( 'redsysbanktransfer', 'Helping to understand the encrypted code: ' );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_AMOUNT: ' . $order_total_sign );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_ORDER: ' . $transaction_id2 );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_MERCHANTCODE: ' . $this->customer );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_CURRENCY' . $currency_codes[ get_woocommerce_currency() ] );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_TRANSACTIONTYPE: ' . $transaction_type );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_TERMINAL: ' . $dsmerchantterminal );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_MERCHANTURL: ' . $final_notify_url );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_URLOK: ' . add_query_arg( 'utm_nooverride', '1', $this->get_return_url( $order ) ) );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_URLKO: ' . $returnfromredsys );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_CONSUMERLANGUAGE: ' . $gatewaylanguage );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_PRODUCTDESCRIPTION: ' . WCRed()->product_description( $order, $this->id ) );
			$this->log->add( 'redsysbanktransfer', 'DS_MERCHANT_PAYMETHODS: R' );
		}
		$redsys_args = apply_filters( 'woocommerce_redsys_args', $redsys_args );
		return $redsys_args;
	}
	/**
	 * Generate the redsys form
	 *
	 * @param string $order_id Order ID.
	 * @return string
	 */
	public function generate_redsys_form( $order_id ) {
		global $woocommerce;

		$usesecretsha256 = $this->secretsha256;
		if ( ! $usesecretsha256 ) {
			$order       = new WC_Order( $order_id );
			$redsys_adr  = $this->liveurl . '?';
			$redsys_args = $this->get_redsys_args( $order );
			$form_inputs = '';
			foreach ( $redsys_args as $key => $value ) {
				$form_inputs .= '<input type="hidden" name="' . $key . '" value="' . esc_attr( $value ) . '" />';
			}
			wc_enqueue_js(
				'
			$("body").block({
				message: "<img src=\"' . esc_url( apply_filters( 'woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/select2-spinner.gif' ) ) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />' . __( 'Thank you for your order. We are now redirecting you to Servired/RedSys to make the payment.', 'woocommerce-redsys' ) . '",
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
			' . $form_inputs . '
			<input type="submit" class="button-alt" id="submit_redsys_payment_form" value="' . __( 'Pay with Credit Card via Servired/RedSys', 'woocommerce-redsys' ) . '" /> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce-redsys' ) . '</a>
		</form>';
		} else {
			$order       = new WC_Order( $order_id );
			$redsys_adr  = $this->liveurl . '?';
			$redsys_args = $this->get_redsys_args( $order );
			$form_inputs = array();
			foreach ( $redsys_args as $key => $value ) {
				$form_inputs[] .= '<input type="hidden" name="' . $key . '" value="' . esc_attr( $value ) . '" />';
			}
			wc_enqueue_js(
				'
			$("body").block({
				message: "<img src=\"' . esc_url( apply_filters( 'woocommerce_ajax_loader_url', $woocommerce->plugin_url() . '/assets/images/select2-spinner.gif' ) ) . '\" alt=\"Redirecting&hellip;\" style=\"float:left; margin-right: 10px;\" />' . __( 'Thank you for your order. We are now redirecting you to Servired/RedSys to make the payment.', 'woocommerce-redsys' ) . '",
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
			<input type="submit" class="button-alt" id="submit_redsys_payment_form" value="' . __( 'Pay with Credit Card via Servired/RedSys', 'woocommerce-redsys' ) . '" /> <a class="button cancel" href="' . esc_url( $order->get_cancel_order_url() ) . '">' . __( 'Cancel order &amp; restore cart', 'woocommerce-redsys' ) . '</a>
		</form>';
		}
	}
	/**
	 * Process the payment and return the result
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		$order = new WC_Order( $order_id );
		return array(
			'result'   => 'success',
			'redirect' => $order->get_checkout_payment_url( true ),
		);
	}
	/**
	 * Output for the order receipt page.
	 *
	 * @param object $order Order object.
	 */
	public function receipt_page( $order ) {
		echo '<p>' . esc_html__( 'Thank you for your order, please click the button below to pay by Bank Transfer.', 'woocommerce-redsys' ) . '</p>';
		echo $this->generate_redsys_form( $order ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
		global $woocommerce;

		if ( 'yes' === $this->debug ) {
			$this->log->add( 'redsysbanktransfer', 'HTTP Notification received: ' . print_r( $_POST, true ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.PHP.DevelopmentFunctions.error_log_print_r
		}
		$usesecretsha256 = $this->secretsha256;
		if ( $usesecretsha256 ) {
			$version     = sanitize_text_field( $_POST['Ds_SignatureVersion'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
			$data        = sanitize_text_field( $_POST['Ds_MerchantParameters'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
			$remote_sign = sanitize_text_field( $_POST['Ds_Signature'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
			$miobj       = new WooRedsysAPI();
			$localsecret = $miobj->createMerchantSignatureNotif( $usesecretsha256, $data );

			if ( $localsecret === $remote_sign ) {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'redsysbanktransfer', 'Received valid notification from Servired/RedSys' );
					$this->log->add( 'redsysbanktransfer', $data );
				}
				return true;
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'redsysbanktransfer', 'Received INVALID notification from Servired/RedSys' );
				}
				return false;
			}
		} else {
			if ( 'yes' === $this->debug ) {
				$this->log->add( 'redsysbanktransfer', 'HTTP Notification received: ' . print_r( $_POST, true ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.PHP.DevelopmentFunctions.error_log_print_r
			}
			if ( $data ) {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'redsysbanktransfer', 'Received valid notification from Servired/RedSys' );
				}
				return true;
			} else {
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'redsysbanktransfer', 'Received INVALID notification from Servired/RedSys' );
				}
				return false;
			}
		}
	}
	/**
	 * Check for Servired/RedSys HTTP Notification
	 *
	 * @return void
	 */
	/**
	 * Package: WooCommerce Redsys Gateway
	 * Plugin URI: https://woocommerce.com/es-es/products/redsys-gateway/
	 * Copyright: (C) 2013 - 2023 José Conti
	 */
	public function check_ipn_response() {
		@ob_clean(); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		$_POST = stripslashes_deep( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( $this->check_ipn_request_is_valid() ) {
			header( 'HTTP/1.1 200 OK' );
			do_action( 'valid_redsysbank_standard_ipn_request', $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} else {
			wp_die( 'There is nothing to see here, do not access this page directly (Bank Transfer)' );
		}
	}
	/**
	 * Successful Payment!
	 *
	 * @param array $posted Post data after processing.
	 * @return void
	 */
	public function successful_request( $posted ) {
		global $woocommerce;
		$version           = sanitize_text_field( $_POST['Ds_SignatureVersion'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
		$data              = sanitize_text_field( $_POST['Ds_MerchantParameters'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
		$remote_sign       = sanitize_text_field( $_POST['Ds_Signature'] ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotValidated,WordPress.Security.ValidatedSanitizedInput.MissingUnslash,WordPress.Security.NonceVerification.Missing
		$miobj             = new WooRedsysAPI();
		$decodedata        = $miobj->decodeMerchantParameters( $data );
		$localsecret       = $miobj->createMerchantSignatureNotif( $usesecretsha256, $data );
		$total             = $miobj->getParameter( 'Ds_Amount' );
		$ordermi           = $miobj->getParameter( 'Ds_Order' );
		$dscode            = $miobj->getParameter( 'Ds_MerchantCode' );
		$currency_code     = $miobj->getParameter( 'Ds_Currency' );
		$response          = $miobj->getParameter( 'Ds_Response' );
		$id_trans          = $miobj->getParameter( 'Ds_AuthorisationCode' );
		$dsdate            = $miobj->getParameter( 'Ds_Date' );
		$dshour            = $miobj->getParameter( 'Ds_Hour' );
		$dstermnal         = $miobj->getParameter( 'Ds_Terminal' );
		$dsmerchandata     = $miobj->getParameter( 'Ds_MerchantData' );
		$dssucurepayment   = $miobj->getParameter( 'Ds_SecurePayment' );
		$dscardcountry     = $miobj->getParameter( 'Ds_Card_Country' );
		$dsconsumercountry = $miobj->getParameter( 'Ds_ConsumerLanguage' );
		$dscargtype        = $miobj->getParameter( 'Ds_Card_Type' );
		$order1            = $ordermi;
		$order2            = WCRed()->clean_order_number( $order1 );
		$order             = $this->get_redsys_order( (int) $order2 );
		if ( 'yes' === $this->debug ) {
				$this->log->add( 'redsysbanktransfer', 'Ds_Amount: ' . $total . ', Ds_Order: ' . $order1 . ',	Ds_MerchantCode: ' . $dscode . ', Ds_Currency: ' . $currency_code . ', Ds_Response: ' . $response . ', Ds_AuthorisationCode: ' . $id_trans . ', $order2: ' . $order2 );
		}
		$response = intval( $response );
		if ( $response <= 99 ) {
			// authorized.
			$order_total_compare_0 = number_format( $order->get_total(), 2, '', '' );
			// remove 0 from bigining.
			$order_total_compare = ltrim( $order_total_compare_0, '0' );
			if ( $order_total_compare !== $total ) {
				// amount does not match.
				if ( 'yes' === $this->debug ) {
					$this->log->add( 'redsysbanktransfer', 'Payment error: Amounts do not match (order: ' . $order_total_compare . ' - received: ' . $total . ')' );
				}
				// Put this order on-hold for manual checking.
				/* translators: order an received are the amount */
				$order->update_status( 'on-hold', sprintf( __( 'Validation error: Order vs. Notification amounts do not match (order: %1$s - received: %2$s).', 'woocommerce-redsys' ), $order_total_compare, $total ) );
				exit;
			}
			$authorisation_code = $id_trans;
			$data               = array();
			if ( ! empty( $order1 ) ) {
				$data['_payment_order_number_redsys'] = $order1;
			}
			if ( ! empty( $dsdate ) ) {
				$data['_payment_date_redsys'] = $dsdate;
			}
			if ( ! empty( $dshour ) ) {
				$data['_payment_hour_redsys'] = $dshour;
			}
			if ( ! empty( $id_trans ) ) {
				$data['_authorisation_code_redsys'] = $authorisation_code;
			}
			if ( ! empty( $dscardcountry ) ) {
				$data['_card_country_redsys'] = $dscardcountry;
			}
			WCRed()->update_order_meta( $order->get_id(), $data );
			// Payment completed.
			$order->add_order_note( __( 'HTTP Notification received - payment completed', 'woocommerce-redsys' ) );
			$order->add_order_note( __( 'Authorization code: ', 'woocommerce-redsys' ) . $authorisation_code );
			$order->payment_complete();
			if ( 'completed' === $this->orderdo ) {
				$order->update_status( 'completed', __( 'Order Completed by Redsys', 'woocommerce-redsys' ) );
			}

			if ( 'yes' === $this->debug ) {
				$this->log->add( 'redsysbanktransfer', 'Payment complete.' );
			}
		} elseif ( 101 === $response ) {
			// Tarjeta caducada.
			if ( 'yes' === $this->debug ) {
				$this->log->add( 'redsysbanktransfer', 'Order cancelled by Redsys: Expired credit card' );
			}
			// Order cancelled.
			$order->update_status( 'cancelled', __( 'Cancelled by Redsys', 'woocommerce-redsys' ) );
			$order->add_order_note( __( 'Order cancelled by Redsys: Expired credit card', 'woocommerce-redsys' ) );
			WC()->cart->empty_cart();
		} elseif ( '0930' === $response ) {
			if ( 'yes' === $this->debug ) {
				$this->log->add( 'redsysbanktransfer', 'Hay respuesta aparentemente' );
			}
		}
	}
	/**
	 * Get_redsys_order function.
	 *
	 * @param mixed $order_id Order ID.
	 */
	public function get_redsys_order( $order_id ) {
		$order = new WC_Order( $order_id );
		return $order;
	}
	/**
	 * Check show test or live payment method
	 *
	 * @param int $userid User ID.
	 */
	public function check_user_show_payment_method( $userid = false ) {

		$test_mode  = $this->testmode;
		$selections = (array) WCRed()->get_redsys_option( 'testshowgateway', 'redsysbank' );

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
	 * Show or hide payment method.
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
 * Add the gateway to woocommerce.
 *
 * @param array $methods Payment methods.
 */
function woocommerce_add_gateway_bank_transfer_gateway( $methods ) {
	$methods[] = 'WC_Gateway_Redsys_Bank_Transfer';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'woocommerce_add_gateway_bank_transfer_gateway' );
