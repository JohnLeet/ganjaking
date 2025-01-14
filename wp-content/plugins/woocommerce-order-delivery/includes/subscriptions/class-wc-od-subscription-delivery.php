<?php
/**
 * Class to manage the delivery preferences of a subscription.
 *
 * @package WC_OD
 * @since   1.3.0
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_OD_Subscription_Delivery' ) ) {
	/**
	 * Class WC_OD_Subscription_Delivery
	 */
	class WC_OD_Subscription_Delivery {

		/**
		 * Constructor.
		 *
		 * @since 1.3.0
		 */
		public function __construct() {
			// Edit delivery endpoint.
			add_filter( 'woocommerce_get_query_vars', array( $this, 'query_vars' ) );
			add_filter( 'woocommerce_endpoint_edit-delivery_title', array( $this, 'edit_delivery_title' ) );
			add_action( 'woocommerce_account_edit-delivery_endpoint', array( $this, 'edit_delivery_content' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// View Subscription hooks.
			add_filter( 'wcs_view_subscription_actions', array( $this, 'view_subscription_actions' ), 10, 2 );
			add_action( 'woocommerce_after_template_part', array( $this, 'insert_delivery_content' ) );

			// Edit Delivery content.
			add_action( 'wp_ajax_wc_od_refresh_subscription_delivery_content', array( $this, 'refresh_delivery_content' ) );
			add_action( 'template_redirect', array( $this, 'save_delivery' ) );
			add_filter( 'woocommerce_form_field_wc_od_subscription_section_start', 'wc_od_subscription_section_start_field', 10, 3 );
			add_filter( 'woocommerce_form_field_wc_od_subscription_section_end', 'wc_od_subscription_section_end_field' );
			add_filter( 'woocommerce_form_field_wc_od_subscription_delivery_days', 'wc_od_subscription_delivery_days_field', 10, 4 );

			add_filter( 'wc_od_validate_subscription_field_delivery_date', array( $this, 'validate_delivery_date' ), 10, 3 );
			add_filter( 'wc_od_sanitize_subscription_field_delivery_date', array( $this, 'sanitize_delivery_date' ) );
			add_filter( 'wc_od_sanitize_subscription_field_delivery_days', array( $this, 'sanitize_delivery_days' ), 10, 2 );
			add_action( 'wc_od_updated_subscription_fields', array( $this, 'updated_subscription_delivery' ), 10, 3 );
		}

		/**
		 * Registers custom query vars.
		 *
		 * @since 1.3.0
		 *
		 * @param array $query_vars The query vars.
		 * @return array
		 */
		public function query_vars( $query_vars ) {
			$query_vars['edit-delivery'] = 'edit-delivery';

			return $query_vars;
		}

		/**
		 * Enqueue scripts.
		 *
		 * @since 1.3.0
		 */
		public function enqueue_scripts() {
			if ( ! wc_od_is_edit_delivery_endpoint() ) {
				return;
			}

			$suffix = wc_od_get_scripts_suffix();

			wc_od_enqueue_datepicker( 'subscription' );
			wp_enqueue_script( 'wc-od-subscription', WC_OD_URL . "assets/js/wc-od-subscription{$suffix}.js", array( 'woocommerce', 'wc-od-datepicker' ), WC_OD_VERSION, true );
			wp_localize_script( 'wc-od-subscription', 'wc_od_subscription_l10n', $this->get_calendar_settings( wc_od_get_current_subscription_id() ) );
		}

		/**
		 * Gets the calendar settings.
		 *
		 * @since 1.3.0
		 *
		 * @param mixed $the_subscription Post object or post ID of the subscription. Use the current subscription if empty.
		 * @return array An array with the calendar settings.
		 */
		public function get_calendar_settings( $the_subscription = null ) {
			$subscription = wc_od_get_subscription( ( $the_subscription ? $the_subscription : wc_od_get_current_subscription_id() ) );
			$date_format  = wc_od_get_date_format( 'php' );
			$args         = wc_od_get_subscription_delivery_date_args( $subscription );

			// The 'delivery_days' parameter already contains the right statuses.
			$delivery_days_status = wp_list_pluck( $args['delivery_days'], 'enabled' );

			return wc_od_get_calendar_settings(
				array(
					'startDate'          => wc_od_localize_date( $args['start_date'], $date_format ),
					'endDate'            => wc_od_localize_date( ( wc_od_get_timestamp( $args['end_date'] ) - DAY_IN_SECONDS ), $date_format ), // Inclusive.
					'daysOfWeekDisabled' => array_keys( $delivery_days_status, 'no', true ),
					'datesDisabled'      => wc_od_get_disabled_days( $args['disabled_days_args'], 'subscription' ),
				),
				'subscription'
			);
		}

		/**
		 * Filter the actions in the view-subscription page.
		 *
		 * @since 1.3.0
		 *
		 * @param array           $actions      The subscription actions.
		 * @param WC_Subscription $subscription The subscription object.
		 * @return array An array with the subscription actions.
		 */
		public function view_subscription_actions( $actions, $subscription ) {
			if ( wc_od_subscription_has_delivery_preferences( $subscription ) ) {
				if ( wc_od_order_is_local_pickup( $subscription ) ) {
					$name = __( 'Change pickup', 'woocommerce-order-delivery' );
				} else {
					$name = __( 'Change delivery', 'woocommerce-order-delivery' );
				}

				$actions['change_delivery'] = array(
					'url'  => wc_od_edit_delivery_endpoint( $subscription->get_id() ),
					'name' => $name,
				);
			}

			return $actions;
		}

		/**
		 * Inserts the delivery content at the end of the view-subscription page.
		 *
		 * @since 1.3.0
		 *
		 * @param string $template_name The template name.
		 */
		public function insert_delivery_content( $template_name ) {
			if ( 'myaccount/view-subscription.php' === $template_name ) {
				$this->view_subscription_delivery_content();
			}
		}

		/**
		 * Prints the delivery content.
		 *
		 * @since 1.3.0
		 */
		public function view_subscription_delivery_content() {
			$subscription_id = intval( wc_od_get_current_subscription_id() );
			$subscription    = wcs_get_subscription( $subscription_id );

			if ( ! $subscription || ! wc_od_user_has_subscription_delivery_caps( $subscription ) ||
				! wc_od_subscription_needs_delivery_details( $subscription ) ) {
				return;
			}

			$args = array(
				'order'        => $subscription,
				'subscription' => $subscription, // Deprecated.
			);

			if ( wc_od_subscription_needs_delivery_date( $subscription ) ) {
				$delivery_date = $subscription->get_meta( '_delivery_date' );

				if ( $delivery_date ) {
					$args['date']          = $delivery_date;
					$args['delivery_date'] = wc_od_localize_date( $delivery_date ); // Deprecated.

					$time_frame_id = $subscription->get_meta( '_delivery_time_frame' );

					if ( $time_frame_id ) {
						$time_frame = wc_od_get_time_frame_for_date( $delivery_date, $time_frame_id );

						$args['time_frame']          = $time_frame;
						$args['delivery_time_frame'] = $time_frame; // Deprecated.
					}
				}
			}

			if ( empty( $args['date'] ) ) {
				$shipping_method = wc_od_get_order_shipping_method( $subscription );
				$range           = WC_OD_Delivery_Ranges::get_range_matching_shipping_method( $shipping_method );

				$shipping_date = wc_od_localize_date( wc_od_get_subscription_first_shipping_date( $subscription ) );
				$range_data    = array(
					'min' => $range->get_from(),
					'max' => $range->get_to(),
				);

				$args['details_type'] = 'estimated';

				if ( wc_od_shipping_method_is_local_pickup( $shipping_method ) ) {
					$args['pickup_date']  = $shipping_date;
					$args['pickup_range'] = $range_data;
				} else {
					$args['shipping_date']  = $shipping_date;
					$args['delivery_range'] = $range_data;
				}
			}

			wc_od_order_delivery_details( $args );
		}

		/**
		 * Change the edit-delivery endpoint title.
		 *
		 * @since 1.3.0
		 * @since 2.1.0 Deprecated `$title` argument.
		 *
		 * @param string $deprecated Deprecated argument.
		 * @return string
		 */
		public function edit_delivery_title( $deprecated = '' ) {
			$subscription_id = wc_od_get_current_subscription_id();

			if ( wc_od_order_is_local_pickup( $subscription_id ) ) {
				/* translators: %s: subscription ID. */
				$title = _x( 'Subscription pickup #%s', 'edit subscription delivery title', 'woocommerce-order-delivery' );
			} else {
				/* translators: %s: subscription ID. */
				$title = _x( 'Subscription delivery #%s', 'edit subscription delivery title', 'woocommerce-order-delivery' );
			}

			return sprintf( $title, $subscription_id );
		}

		/**
		 * Prints the content for the 'edit-delivery' page.
		 *
		 * @since 1.3.0
		 */
		public function edit_delivery_content() {
			$subscription_id = wc_od_get_current_subscription_id();

			$args = array(
				'subscription' => wcs_get_subscription( $subscription_id ),
			);

			wc_od_get_template( 'myaccount/edit-delivery.php', $args );
		}

		/**
		 * Refreshes the content of the 'edit-delivery' page.
		 *
		 * @since 1.5.0
		 */
		public function refresh_delivery_content() {
			ob_start();
			$this->edit_delivery_content();
			$result = ob_get_clean();

			wp_send_json(
				array(
					'content' => $result,
				)
			);
		}

		/**
		 * Save the subscription delivery preferences.
		 *
		 * @since 1.3.0
		 */
		public function save_delivery() {
			if (
				empty( $_POST['action'] ) || 'edit_delivery' !== $_POST['action'] ||
				empty( $_POST['subscription_id'] ) ||
				empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'wc_od_edit_delivery' ) // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			) {
				return;
			}

			$subscription = wcs_get_subscription( intval( $_POST['subscription_id'] ) );

			if ( ! $subscription || ! wc_od_user_has_subscription_delivery_caps( $subscription ) ||
				! wc_od_subscription_has_delivery_preferences( $subscription ) ) {
				return;
			}

			$fields = wc_od_get_subscription_delivery_fields( $subscription );
			$values = array();
			$valid  = true;

			foreach ( $fields as $key => $field ) {
				$value = null;

				// Ignore section fields.
				if ( in_array( $field['type'], array( 'wc_od_subscription_section_start', 'wc_od_subscription_section_end' ), true ) ) {
					unset( $fields[ $key ] ); // Remove it from future loops.
					continue;
				}

				// Validate required.
				if ( ! empty( $field['required'] ) && empty( $_POST[ $key ] ) ) {
					/* translators: %s: field name */
					wc_add_notice( sprintf( __( '%s is a required field.', 'woocommerce-order-delivery' ), '<strong>' . esc_html( $field['label'] ) . '</strong>' ), 'error' );
					$valid = false;
				} else {
					$value = wc_clean( wp_unslash( $_POST[ $key ] ) );

					/**
					 * Validate the subscription delivery field.
					 *
					 * NOTE: Return Null to abort the save process.
					 *
					 * @since 1.3.0
					 *
					 * @param mixed           $value        The field value.
					 * @param array           $field        The field parameters.
					 * @param WC_Subscription $subscription The subscription instance.
					 */
					$value = apply_filters( "wc_od_validate_subscription_field_{$key}", $value, $field, $subscription );

					if ( is_null( $value ) ) {
						$valid = false;
					}
				}

				$values[ $key ] = $value;
			}

			if ( ! $valid ) {
				$values = null;
			}

			/**
			 * Global validation for the subscription delivery fields.
			 *
			 * NOTE: Return Null to abort the save process.
			 *
			 * @since 1.3.0
			 *
			 * @param array|null      $values       An array with the fields values. Null if at least one single field validation failed.
			 * @param array           $fields       The fields parameters.
			 * @param WC_Subscription $subscription The subscription instance.
			 */
			$values = apply_filters( 'wc_od_validate_subscription_fields', $values, $fields, $subscription );

			if ( ! is_null( $values ) ) {
				$previous_values = array();

				// Sanitize and save the fields.
				foreach ( $fields as $key => $field ) {
					$previous_values[ $key ] = $subscription->get_meta( "_{$key}" );

					/**
					 * Sanitize the subscription delivery field.
					 *
					 * @since 1.3.0
					 *
					 * @param mixed           $value        The field value.
					 * @param array           $field        The field parameters.
					 * @param WC_Subscription $subscription The subscription instance.
					 */
					$values[ $key ] = apply_filters( "wc_od_sanitize_subscription_field_{$key}", $values[ $key ], $field, $subscription );

					if ( empty( $values[ $key ] ) ) {
						wc_od_delete_order_meta( $subscription, "_{$key}", true );
					} else {
						wc_od_update_order_meta( $subscription, "_{$key}", $values[ $key ], true );
					}
				}

				/**
				 * Fires immediately after updating the delivery preferences of a subscription.
				 *
				 * @since 1.3.0
				 *
				 * @param array           $values       The fields' values.
				 * @param array           $previous     The previous fields values.
				 * @param WC_Subscription $subscription The subscription instance.
				 */
				do_action( 'wc_od_updated_subscription_fields', $values, $previous_values, $subscription );

				if ( wc_od_order_is_local_pickup( $subscription ) ) {
					wc_add_notice( __( 'Pickup preferences changed successfully.', 'woocommerce-order-delivery' ) );
				} else {
					wc_add_notice( __( 'Delivery preferences changed successfully.', 'woocommerce-order-delivery' ) );
				}

				wp_safe_redirect( $subscription->get_view_order_url() );
				exit;
			}
		}

		/**
		 * Validates the delivery_date field.
		 *
		 * @since 1.3.0
		 *
		 * @param mixed           $value        The field value.
		 * @param array           $field        The field arguments.
		 * @param WC_Subscription $subscription The subscription instance.
		 * @return mixed|null The field value. Null on failure.
		 */
		public function validate_delivery_date( $value, $field, $subscription ) {
			if ( is_null( $value ) || ! wc_od_validate_subscription_delivery_date( $subscription, $value ) ) {
				$value = null;

				/* translators: %s: field label */
				wc_add_notice( sprintf( __( '%s is not valid.', 'woocommerce-order-delivery' ), '<strong>' . esc_html( $field['label'] ) . '</strong>' ), 'error' );
			}

			return $value;
		}

		/**
		 * Validates the delivery time frame field.
		 *
		 * @since 1.5.0
		 *
		 * @param mixed $value The field value.
		 * @param array $field The field data.
		 * @return mixed|null The field value. Null on failure.
		 */
		public function validate_delivery_time_frame( $value, $field ) {
			if ( $value && ! in_array( $value, array_keys( $field['options'] ), true ) ) {
				$value = null;

				/* translators: %s: field label */
				wc_add_notice( sprintf( __( '%s is not valid.', 'woocommerce-order-delivery' ), '<strong>' . esc_html( $field['label'] ) . '</strong>' ), 'error' );
			}

			return $value;
		}

		/**
		 * Sanitizes the subscription delivery_date field.
		 *
		 * @since 1.3.0
		 *
		 * @param mixed $value The field value.
		 * @return string The sanitized field value.
		 */
		public function sanitize_delivery_date( $value ) {
			// Stores the date in the ISO 8601 format.
			return (string) wc_od_localize_date( sanitize_text_field( $value ), 'Y-m-d' );
		}

		/**
		 * Sanitizes the subscription delivery_days field.
		 *
		 * @since 1.3.0
		 *
		 * @param mixed $value The field value.
		 * @param array $field The field arguments.
		 * @return array The sanitized field value.
		 */
		public function sanitize_delivery_days( $value, $field ) {
			$clean_value   = array();
			$delivery_days = wc_od_get_subscription_delivery_days( $field['subscription_id'] );

			foreach ( $delivery_days as $index => $delivery_day ) {
				$enabled    = false;
				$time_frame = '';

				if ( ! empty( $value[ $index ] ) ) {
					$enabled    = ( $delivery_day->is_enabled() && isset( $value[ $index ]['enabled'] ) );
					$time_frame = ( $delivery_day->has_time_frames() && ! empty( $value[ $index ]['time_frame'] ) ? $value[ $index ]['time_frame'] : '' );
				}

				$clean_value[ $index ] = array(
					'enabled'    => wc_bool_to_string( $enabled ),
					'time_frame' => $time_frame,
				);
			}

			return $clean_value;
		}

		/**
		 * Processes the updated delivery fields of a subscription.
		 *
		 * @since 1.3.0
		 *
		 * @param array           $values       The fields values.
		 * @param array           $previous     The previous fields values.
		 * @param WC_Subscription $subscription The subscription instance.
		 */
		public function updated_subscription_delivery( $values, $previous, $subscription ) {
			if ( $values['delivery_date'] !== $previous['delivery_date'] ) {
				$delivery_details = wc_od_localize_date( $values['delivery_date'] );
				$time_frame_id    = $subscription->get_meta( '_delivery_time_frame' );

				if ( $time_frame_id ) {
					$time_frame = wc_od_get_time_frame_for_date( $values['delivery_date'], $time_frame_id );

					if ( $time_frame ) {
						$delivery_details .= ' [' . wc_od_time_frame_to_string( $time_frame ) . ']';
					}
				}

				// Adds an internal note to the subscription to notify to the merchant.
				wc_od_add_order_note(
					$subscription,
					sprintf(
						/* translators: %s: delivery details */
						__( 'The customer changed the delivery details for the next order to: %s', 'woocommerce-order-delivery' ),
						"<strong>{$delivery_details}</strong>"
					)
				);
			}
		}
	}
}

return new WC_OD_Subscription_Delivery();
