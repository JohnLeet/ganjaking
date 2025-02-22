<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WC_Gateway_Account_Funds class.
 *
 * @extends WC_Payment_Gateway
 */
class WC_Gateway_Account_Funds extends WC_Payment_Gateway {

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->id                 = 'accountfunds';
		$this->method_title       = wc_get_account_funds_name();
		$this->method_description = sprintf(
			/* translators: %s: Payment gateway title */
			__( 'This gateway takes full payment using a logged-in user\'s %s.', 'woocommerce-account-funds' ),
			$this->method_title
		);

		$this->supports = array(
			'products',
			'refunds',
			'subscriptions',
			'subscription_cancellation',
			'subscription_reactivation',
			'subscription_suspension',
			'subscription_amount_changes',
			'subscription_date_changes',
			'subscription_payment_method_change',
			'subscription_payment_method_change_customer',
			'subscription_payment_method_change_admin',
		);

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->title = $this->get_method_title();

		// Subscriptions.
		add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'process_subscription_payment' ), 10, 2 );
		add_filter( 'woocommerce_my_subscriptions_recurring_payment_method', array( $this, 'subscription_payment_method_name' ), 10, 3 );
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_subscriptions_paid_for_failed_renewal_order', array( $this, 'failed_renewal_order_paid' ), 5, 2 );
		add_action( 'subscriptions_activated_for_order', array( $this, 'subscriptions_activated_for_order' ), 5 );

		// Make sure this class is loaded before using any methods that depend on it.
		include_once __DIR__ . '/class-wc-account-funds-cart-manager.php';
	}

	/**
	 * Gets the default gateway's description.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function get_default_description() {
		return _x( 'Available balance: {funds_amount}', 'payment gateway description', 'woocommerce-account-funds' );
	}

	/**
	 * Gets the gateway's description.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function get_description() {
		// Initialize the description on demand.
		if ( ! $this->description ) {
			$description = $this->get_option( 'description', $this->get_default_description() );

			// Replace placeholder by the real value.
			$description = str_replace( '{funds_amount}', WC_Account_Funds::get_account_funds(), $description );

			if ( 'yes' === get_option( 'account_funds_give_discount' ) ) {
				$discount = wc_get_account_funds_discount_data();

				$amount = ( 'fixed' === $discount['type'] ? wc_price( $discount['amount'] ) : $discount['amount'] . '%' );

				$description .= '<br/><em>';
				$description .= sprintf(
					/* translators: 1: funds name, 2: funds amount */
					_x( 'Use your %1$s and get a %2$s discount on your order.', 'payment gateway description', 'woocommerce-account-funds' ),
					wc_get_account_funds_name(),
					$amount
				);
				$description .= '</em>';
			}

			$this->description = $description;
		}

		return parent::get_description();
	}

	/**
	 * Init form fields.
	 *
	 * @since 2.0.0
	 */
	public function init_form_fields() {
		// phpcs:disable WordPress.WP.I18n.TextDomainMismatch
		$this->form_fields = array(
			'enabled'     => array(
				'title'   => __( 'Enable/Disable', 'woocommerce' ),
				'type'    => 'checkbox',
				'default' => 'yes',
				'label'   => sprintf(
					/* translators: %s: Payment gateway title */
					__( 'Enable %s', 'woocommerce-account-funds' ),
					$this->get_method_title()
				),
			),
			'title'       => array(
				'title'             => __( 'Title', 'woocommerce' ),
				'type'              => 'text',
				'desc_tip'          => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
				'description'       => sprintf(
					/* translators: %s: Funds name */
					__( 'This value is defined by the %s setting.', 'woocommerce-account-funds' ),
					'<strong>' . __( 'Funds name', 'woocommerce-account-funds' ) . '</strong>'
				),
				'custom_attributes' => array(
					'disabled' => 'disabled',
				),
			),
			'description' => array(
				'title'       => __( 'Description', 'woocommerce' ),
				'type'        => 'textarea',
				'description' => sprintf(
					'%1$s %2$s',
					__( 'Payment method description that the customer will see on your checkout.', 'woocommerce' ),
					wc_account_funds_get_placeholder_text( array( '{funds_amount}' ) )
				),
				'placeholder' => $this->get_default_description(),
				'desc_tip'    => true,
			),
		);
		// phpcs:enable WordPress.WP.I18n.TextDomainMismatch
	}

	/**
	 * Init settings.
	 *
	 * @since 2.8.0
	 */
	public function init_settings() {
		parent::init_settings();

		$this->settings['title'] = $this->method_title;
	}

	/**
	 * Gets the order total in checkout and pay_for_order.
	 *
	 * @since 2.3.6
	 *
	 * @return float
	 */
	protected function get_order_total() {
		/*
		 * Use the subscription total on the subscription details page.
		 * This allows showing/hiding the action "Add payment/Change payment" when "Account Funds" is
		 * the unique available payment gateway for subscriptions.
		 */
		if ( function_exists( 'wcs_get_subscription' ) ) {
			$subscription_id = absint( get_query_var( 'view-subscription' ) );

			if ( ! $subscription_id ) {
				$subscription_id = absint( get_query_var( 'subscription-payment-method' ) );
			}

			if ( $subscription_id > 0 ) {
				$subscription = wcs_get_subscription( $subscription_id );

				return (float) $subscription->get_total();
			}
		}

		return parent::get_order_total();
	}

	/**
	 * Check if the gateway is available for use
	 *
	 * @return bool
	 */
	public function is_available() {
		if ( ! parent::is_available() || ! is_user_logged_in() ) {
			return false;
		}

		if (
			WC()->cart && (
				WC_Account_Funds_Cart_Manager::using_funds() ||
				WC_Account_Funds_Cart_Manager::cart_contains_deposit() ||
				WC_Account_Funds::get_account_funds( null, false ) < $this->get_order_total()
			)
		) {
			return false;
		}

		return true;
	}

	/**
	 * Process Payment.
	 *
	 * @param int $order_id Order ID.
	 * @return array
	 */
	public function process_payment( $order_id ) {
		if ( ! is_user_logged_in() ) {
			wc_add_notice( __( 'Payment error:', 'woocommerce-account-funds' ) . ' ' . __( 'You must be logged in to use this payment method', 'woocommerce-account-funds' ), 'error' );
			return array( 'result' => 'error' );
		}

		$order = wc_get_order( $order_id );

		// Changing the subscription's payment method.
		if ( $order instanceof WC_Subscription ) {
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}

		$result = wc_account_funds_pay_order_with_funds( $order, $this->get_order_total() );

		if ( is_wp_error( $result ) ) {
			wc_add_notice( __( 'Payment error:', 'woocommerce-account-funds' ) . ' ' . $result->get_error_message(), 'error' );
			return array( 'result' => 'error' );
		}

		// Payment complete.
		$order->payment_complete();

		// Remove cart.
		WC()->cart->empty_cart();

		// Return thankyou redirect.
		return array(
			'result'   => 'success',
			'redirect' => $this->get_return_url( $order ),
		);
	}

	/**
	 * Process scheduled subscription payment.
	 *
	 * @since 2.4.0
	 *
	 * @param float    $order_total Renewal order total.
	 * @param WC_Order $order       Renewal order.
	 */
	public function process_subscription_payment( $order_total, $order ) {
		$result = wc_account_funds_pay_order_with_funds( $order, $order_total );

		if ( is_wp_error( $result ) ) {
			$order->add_order_note( $result->get_error_message() );
			$this->payment_failed_for_subscriptions_on_order( $order );
			return;
		}

		$order->payment_complete();
	}

	/**
	 * Process refund.
	 *
	 * @since 2.4.0
	 *
	 * @param int        $order_id Order ID.
	 * @param float|null $amount Refund amount.
	 * @param string     $reason Refund reason.
	 * @return bool|WP_Error
	 */
	public function process_refund( $order_id, $amount = null, $reason = '' ) {
		$order = wc_get_order( $order_id );

		if ( ! $order || 0 >= $amount ) {
			return false;
		}

		WC_Account_Funds_Manager::increase_user_funds( $order->get_customer_id(), $amount );

		$funds_refunded = (float) $order->get_meta( '_funds_refunded' );

		$order->update_meta_data( '_funds_refunded', ( $funds_refunded + $amount ) );
		$order->save_meta_data();

		$order->add_order_note(
			sprintf(
				/* translators: 1: Refund amount, 2: Payment gateway title */
				_x( 'Refunded %1$s via %2$s.', 'order note', 'woocommerce-account-funds' ),
				wc_account_funds_format_order_price( $order, $amount ),
				$this->get_method_title()
			)
		);

		return true;
	}

	/**
	 * Failed payment for subscriptions in a given order.
	 *
	 * @since 2.1.7
	 * @version 2.1.7
	 *
	 * @param int|WC_Order $order Order ID or order object.
	 */
	protected function payment_failed_for_subscriptions_on_order( $order ) {
		foreach ( $this->get_subscriptions_for_order( $order ) as $subscription ) {
			/*
			 * If Account Funds is the unique payment gateway that support subscriptions, no payment gateways will be
			 * available during checkout. So, we set the subscription to manual renewal.
			 */
			if ( ! $subscription->is_manual() ) {
				$subscription->set_requires_manual_renewal( true );
				$subscription->add_meta_data( '_restore_auto_renewal', 'yes', true );
				$subscription->save();
			}

			$subscription->payment_failed();
		}
		do_action( 'processed_subscription_payment_failure_for_order', $order );
	}

	/**
	 * Get subscriptions from a given order.
	 *
	 * @since 2.1.7
	 * @version 2.1.7
	 *
	 * @param int|WC_Order $order Order ID or order object.
	 *
	 * @return array List of subscriptions.
	 */
	protected function get_subscriptions_for_order( $order ) {
		return wcs_get_subscriptions_for_order(
			$order,
			array(
				'order_type' => array( 'parent', 'renewal' ),
			)
		);
	}

	/**
	 * Payment method name
	 */
	public function subscription_payment_method_name( $payment_method_to_display, $subscription_details, $order ) {
		if ( ! $order->get_customer_id() || $this->id !== $order->get_meta( '_recurring_payment_method' ) ) {
			return $payment_method_to_display;
		}

		return sprintf(
			/* translators: %s: Payment gateway title */
			__( 'Via %s', 'woocommerce-account-funds' ),
			$this->get_method_title()
		);
	}

	/**
	 * Processes a subscription after its failed renewal order has been paid.
	 *
	 * @since 2.3.8
	 *
	 * @param WC_Order        $order        Renewal order successfully paid.
	 * @param WC_Subscription $subscription Subscription related to the renewed order.
	 */
	public function failed_renewal_order_paid( $order, $subscription ) {
		$this->restore_auto_renewal( $subscription );
	}

	/**
	 * Processes subscriptions after being activated due to the payment of a renewal order.
	 *
	 * @since 2.3.8
	 *
	 * @param int $order_id Order ID.
	 */
	public function subscriptions_activated_for_order( $order_id ) {
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		$subscriptions = $this->get_subscriptions_for_order( $order );

		foreach ( $subscriptions as $subscription ) {
			$this->restore_auto_renewal( $subscription );
		}
	}

	/**
	 * Restores the subscription auto-renew previously deactivated when the payment with funds failed.
	 *
	 * @since 2.3.8
	 *
	 * @param WC_Subscription $subscription Subscription object.
	 */
	protected function restore_auto_renewal( $subscription ) {
		if ( ! $subscription->get_meta( '_restore_auto_renewal' ) ) {
			return;
		}

		$subscription->set_requires_manual_renewal( false );
		$subscription->delete_meta_data( '_restore_auto_renewal' );
		$subscription->save();
	}
}
