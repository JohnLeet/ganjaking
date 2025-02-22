<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
if ( ! class_exists( 'Pie_WCWL_Frontend_Ajax' ) ) {
	/**
	 * Class Pie_WCWL_Frontend_Ajax
	 */
	class Pie_WCWL_Frontend_Ajax {

		/**
		 * Initialise ajax class
		 */
		public function init() {
			$this->load_ajax();
		}

		/**
		 * Hook up ajax
		 */
		public function load_ajax() {
			// Single.
			add_action( 'wp_ajax_wcwl_process_user_waitlist_request', array( $this, 'process_user_waitlist_request' ) );
			add_action( 'wp_ajax_nopriv_wcwl_process_user_waitlist_request', array( $this, 'process_user_waitlist_request' ) );
			// Account.
			add_action( 'wp_ajax_wcwl_user_remove_self_waitlist', array( $this, 'remove_user_from_waitlist' ) );
			add_action( 'wp_ajax_wcwl_user_remove_self_archives', array( $this, 'remove_user_from_archives' ) );
		}

		/**
		 * Process the frontend user request to join/leave the given waitlist
		 * Required for simple/variable products
		 */
		public function process_user_waitlist_request() {
			wcwl_switch_locale();
			$product_id = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
			$this->verify_product( $product_id );
			$products = isset( $_POST['products'] ) && is_array( $_POST['products'] ) ? $_POST['products'] : array();
			$lang     = isset( $_POST['language'] ) ? sanitize_text_field( $_POST['language'] ) : '';
			$email    = isset( $_POST['email'] ) ? sanitize_email( $_POST['email'] ) : '';
			$context  = isset( $_POST['context'] ) ? sanitize_text_field( $_POST['context'] ) : '';
			if ( 'yes' === get_option( 'woocommerce_waitlist_double_optin' ) &&
					( $context && 'leave' !== $context ) &&
					( ! get_current_user_id() && ! email_exists( $email ) ) ) {
				$response = $this->send_optin_email( $email, $product_id, $products, $lang );
				if ( ! $response ) {
					$response = sprintf( __( 'Failed to send optin email on %s.' ), gmdate( 'd M, y' ) );
				} else {
					$response = apply_filters( 'wcwl_notice_message_double_optin', __( 'Please check your inbox and confirm your email address to be added to the waitlist', 'woocommerce-waitlist' ) );
				}
				$context = '';
			} elseif ( 'leave' === $context ) {
				$response = wcwl_remove_user_from_waitlist( $email, $product_id );
				$context  = '';
			} elseif ( 'update' === $context ) {
				$response = $this->process_grouped_product_request( $email, $products );
			} else {
				$response = wcwl_add_user_to_waitlist( $email, $product_id, $lang );
				$context  = '';
			}
			if ( is_wp_error( $response ) ) {
				$response = $response->get_error_message();
			}
			if ( isset( $_POST['archive'] ) && 'true' === $_POST['archive'] ) {
				$html = wcwl_get_waitlist_for_archive( $product_id, $context, $response );
			} elseif ( wcwl_is_event( $product_id ) ) {
				$html = wcwl_get_waitlist_for_event( $product_id, $context, $response );
			} else {
				$html = wcwl_get_waitlist_fields( $product_id, $context, $response, $lang );
			}

			if ( get_transient( 'waitlist_user_logged_in_' . get_current_user_id() ) === true ) {
				wp_send_json_success( array( 'redirectUrl' => apply_filters( 'wcwl_redirect_after_account_auto_create', "" ) ) );
			}
			wp_send_json_success( array( 'html' => $html ));
		}

		/**
		 * Process the frontend user request to join/leave the given waitlist/s
		 * Required for grouped products (and events)
		 */
		public function process_grouped_product_request( $email, $products ) {
			if ( ! $products || empty( $products ) ) {
				return new WP_Error( 'wcwl_error', __( 'No products selected', 'woocommerce-waitlist' ) );
			}
			foreach ( $products as $product_id => $data ) {
				if ( 'true' === $data['checked'] ) {
					wcwl_add_user_to_waitlist( $email, $product_id, $data['lang'] );
				} else {
					wcwl_remove_user_from_waitlist( $email, $product_id );
				}
			}

			return apply_filters( 'wcwl_grouped_product_joined_message_text', __( 'You have successfully updated the waitlists for the selected items', 'woocommerce-waitlist' ) );
		}

		/**
		 * Verify the given product is valid
		 */
		public function verify_product( $product_id ) {
			if ( ! wp_verify_nonce( $_POST['nonce'], 'wcwl-ajax-process-user-request-nonce' ) ) {
				wp_send_json_error( apply_filters( 'wcwl_error_message_invalid_nonce', __( 'There was a problem with your request: nonce could not be verified.  Please try again or contact a site administrator for help', 'woocommerce-waitlist' ) ) );
			}
			$product = wc_get_product( $product_id );
			if ( $product ) {
				return $product;
			}
			if ( wcwl_is_event( $product_id ) ) {
				return tribe_events_get_event( $product_id );
			}
			wp_send_json_error( apply_filters( 'wcwl_error_message_invalid_product', __( 'There was a problem with your request: the selected product could not be found.  Please try again or contact a site administrator for help', 'woocommerce-waitlist' ) ) );
		}

		/**
		 * Send optin email (double optin) for customer to confirm email address
		 *
		 * @param string $email
		 * @param int    $product_id
		 * @param array  $products
		 * @param string $lang
		 */
		public function send_optin_email( $email, $product_id, $products, $lang ) {
			$products = $this->get_product_ids_to_join( $products );
			WC_Emails::instance();
			require_once WCWL_FILE_PATH . 'classes/class-pie-wcwl-waitlist-optin-email.php';
			$mailer = new Pie_WCWL_Waitlist_Optin_Email();
			return $mailer->trigger( $email, $product_id, $products, $lang );
		}

		/**
		 * Organise products to join in a csv for email templates
		 */
		public function get_product_ids_to_join( $products ) {
			if ( ! $products ) {
				return $products;
			}
			$to_join = '';
			foreach ( $products as $product_id => $data ) {
				if ( 'true' === $data['checked'] ) {
					$to_join .= $product_id . ',';
				}
			}
			return $to_join;
		}

		/**
		 * Process ajax request for user removing themselves from a waitlist on account pages
		 */
		public function remove_user_from_waitlist() {
			wcwl_switch_locale();
			ob_start();
			if ( ! wp_verify_nonce( $_POST['wcwl_remove_user_nonce'], 'wcwl-ajax-remove-user-nonce' ) ) {
				$message     = apply_filters( 'wcwl_error_message_invalid_nonce', __( 'There was a problem with your request: nonce could not be verified.  Please try again or contact a site administrator for help', 'woocommerce-waitlist' ) );
				wc_get_template(
					"notices/error.php",
					array(
						'notices'  => array( array( 'notice' => $message ) ),
						'messages' => array( $message ),
					)
				);
				$html = ob_get_clean();
				wp_send_json_error( $html );
			} else {
				$message     = '';
				$product     = isset( $_POST['product_id'] ) ? wc_get_product( absint( $_POST['product_id'] ) ) : false;
				$user        = isset( $_POST['user_id'] ) ? get_user_by( 'id', absint( $_POST['user_id'] ) ) : 0;
				if ( ! $product ) {
					$message     = apply_filters( 'wcwl_error_message_invalid_product', __( 'There was a problem with your request: the selected product could not be found.  Please try again or contact a site administrator for help', 'woocommerce-waitlist' ) );
					wc_get_template(
						"notices/error.php",
						array(
							'notices'  => array( array( 'notice' => $message ) ),
							'messages' => array( $message ),
						)
					);
					$html = ob_get_clean();
					wp_send_json_error( $html );
				}
				$message = wcwl_remove_user_from_waitlist( $user->user_email, $product->get_id() );
				if ( is_wp_error( $message ) ) {
					$message     = $message->get_error_message();
					wc_get_template(
						"notices/error.php",
						array(
							'notices'  => array( array( 'notice' => $message ) ),
							'messages' => array( $message ),
						)
					);
					$html = ob_get_clean();
					wp_send_json_error( $html );
				}
				wc_get_template(
					"notices/success.php",
					array(
						'notices'  => array( array( 'notice' => $message ) ),
						'messages' => array( $message ),
					)
				);
				$html = ob_get_clean();
				wp_send_json_success( $html );
			}
		}

		/**
		 * Process ajax request for user removing themselves from all archives on account pages
		 */
		public function remove_user_from_archives() {
			wcwl_switch_locale();
			ob_start();
			if ( ! wp_verify_nonce( $_POST['wcwl_remove_user_archive_nonce'], 'wcwl-ajax-remove-user-archive-nonce' ) ) {
				$message = apply_filters( 'wcwl_error_message_invalid_nonce', __( 'There was a problem with your request: nonce could not be verified.  Please try again or contact a site administrator for help', 'woocommerce-waitlist' ) );
				wc_get_template(
					"notices/error.php",
					array(
						'notices'  => array( array( 'notice' => $message ) ),
						'messages' => array( $message ),
					)
				);
				$html = ob_get_clean();
				wp_send_json_error( $html );
			}
			$message  = apply_filters( 'wcwl_account_removed_archives_message', __( 'You have been removed from all waitlist archives.', 'woocommerce-waitlist' ) );
			$user     = isset( $_POST['user_id'] ) ? get_user_by( 'id', absint( $_POST['user_id'] ) ) : 0;
			$archives = WooCommerce_Waitlist_Plugin::get_waitlist_archives_for_user( $user );
			WooCommerce_Waitlist_Plugin::remove_user_from_archives( $archives, $user );
			wc_get_template(
				"notices/success.php",
				array(
					'notices'  => array( array( 'notice' => $message ) ),
					'messages' => array( $message ),
				)
			);
			$html = ob_get_clean();
			wp_send_json_success( $html );
		}
	}
}
