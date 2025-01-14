<?php
/**
 * Functions
 *
 * @package YITH\CustomOrderStatus
 */

defined( 'YITH_WCCOS' ) || exit; // Exit if accessed directly.

if ( ! function_exists( 'yith_wccos_get_statuses' ) ) {
	/**
	 * Get statuses.
	 *
	 * @param array $args Arguments.
	 *
	 * @return array|int[]|WP_Post[]
	 */
	function yith_wccos_get_statuses( $args = array() ) {
		$default_args = array(
			'posts_per_page' => - 1,
			'post_type'      => 'yith-wccos-ostatus',
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		$args = wp_parse_args( $args, $default_args );

		$statuses = get_posts( $args );

		return ! ! $statuses ? $statuses : array();
	}
}
if ( ! function_exists( 'yith_wccos_get_recipients' ) ) {
	/**
	 * Get recipients.
	 *
	 * @param int $id Status ID.
	 *
	 * @return array|mixed
	 */
	function yith_wccos_get_recipients( $id ) {
		$status_type = get_post_meta( $id, 'status_type', true );
		if ( 'custom' === $status_type || ! $status_type ) {
			$recipients = get_post_meta( $id, 'recipients', true );
		} else {
			$recipients = array();
		}

		return ! ! $recipients && is_array( $recipients ) ? $recipients : array();
	}
}

if ( ! function_exists( 'yith_wccos_get_allowed_recipients' ) ) {
	/**
	 * Retrieve allowed recipients.
	 *
	 * @return array
	 */
	function yith_wccos_get_allowed_recipients() {
		$recipients = array(
			'admin'        => __( 'Administrator', 'yith-woocommerce-custom-order-status' ),
			'customer'     => __( 'Customer', 'yith-woocommerce-custom-order-status' ),
			'custom-email' => __( 'Custom Email Address', 'yith-woocommerce-custom-order-status' ),
		);

		return apply_filters( 'yith_wccos_get_allowed_recipients', $recipients );
	}
}

if ( ! function_exists( 'yith_wccos_is_true' ) ) {
	/**
	 * Check if something is true.
	 *
	 * @param bool|int|string $value The value to check.
	 *
	 * @return bool
	 */
	function yith_wccos_is_true( $value ) {
		return true === $value || 1 === $value || '1' === $value || 'yes' === $value || 'true' === $value;
	}
}
