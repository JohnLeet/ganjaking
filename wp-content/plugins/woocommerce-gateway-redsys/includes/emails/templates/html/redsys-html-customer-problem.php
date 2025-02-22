<?php
/**
 * Cancelled Order sent to Customer.
 *
 * @package WooCommerce Redsys Gateway WooCommerce.com
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}


do_action( 'woocommerce_email_header', $email_heading, $email );
$orderw      = new wc_order( $orderw_id );
$ds_error    = WCRed()->get_order_meta( $orderw->get_id(), '_redsys_error_payment_ds_error_value', true );
$ds_response = WCRed()->get_order_meta( $orderw->get_id(), '_redsys_error_payment_ds_response_value', true );
?>
<p><?php printf( esc_html__( 'The order #%d has been cancelled. Order Details:', 'woocommerce-redsys' ), esc_html__( $orderw->get_order_number() ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment ?></p>

<?php
if ( $ds_error ) {
	?>
	<p><?php printf( esc_html__( 'The error was: #%d, Order Details: ', 'woocommerce-redsys' ), esc_html__( $ds_error ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment ?></p>
	<?php
}
if ( $ds_response ) {
	?>
	<p><?php printf( esc_html__( 'The error was: #%d, Order Details: ', 'woocommerce-redsys' ), esc_html__( $ds_response ) ); // phpcs:ignore WordPress.WP.I18n.MissingTranslatorsComment ?></p>
	<?php
}

do_action( 'woocommerce_email_order_details', $orderw, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_order_meta', $orderw, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_customer_details', $orderw, $sent_to_admin, $plain_text, $email );

do_action( 'woocommerce_email_footer', $email );
