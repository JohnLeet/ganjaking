<?php
/**
 * Mix and Match Product Add to Cart button wrapper template
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/mnm-add-to-cart-wrap.php.
 *
 * HOWEVER, on occasion WooCommerce Mix and Match will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce Mix and Match/Templates
 * @since   1.3.0
 * @deprecated 2.2.0
 *
 * @version  2.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<?php
// @todo = use woo's implode_attributes function for
?>
<div class="mnm_cart mnm_data cart mnm_status" data-for_container="<?php echo esc_attr( $product->get_id() ); ?>" <?php echo $product->get_data_attributes(); // phpcs:ignore WordPress.Security.EscapeOutput ?> >

<?php

if ( $product->is_purchasable() ) {
	/**
	 * Hook: `wc_mnm_before_add_to_cart_button_wrap`
	 */
	do_action( 'wc_mnm_before_add_to_cart_button_wrap' );
	?>

		<p class="mnm_price" style="display:none;"></p>



	<?php

	// MnM Availability.
	?>
		<div class="mnm_availability">

		<?php

		// Availability html.
		echo wp_kses_post( wc_get_stock_html( $product ) );

		?>
			
		</div>
		<?php
		/**
		 * Hook: `wc_mnm_add_to_cart_button`
		 *
		 * @hooked wc_mnm_template_add_to_cart_button - 10
		 */
		do_action( 'wc_mnm_add_to_cart_button', $product );

		?>

		<?php
		/**
		 * Hook: `wc_mnm_after_add_to_cart_button_wrap`
		 */
		do_action( 'wc_mnm_after_add_to_cart_button_wrap' );

		?>

<?php } else { ?>

	<p class="mnm_container_unavailable stock out-of-stock"><?php echo wp_kses_post( $purchasable_notice ); ?></p>

<?php } ?>

</div>
