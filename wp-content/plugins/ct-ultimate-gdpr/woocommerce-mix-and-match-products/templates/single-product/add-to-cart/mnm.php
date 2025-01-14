<?php
/**
 * Mix and Match Product Add to Cart
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/add-to-cart/mnm.php.
 *
 * HOWEVER, on occasion WooCommerce Mix and Match will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce Mix and Match/Templates
 * @since   1.0.0
 * @version 2.3.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

/**
 * woocommerce_before_add_to_cart_form hook.
 */
do_action( 'woocommerce_before_add_to_cart_form' );
?>

<form class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype="multipart/form-data" <?php echo wc_implode_html_attributes( $product->get_data_attributes() ); ?>>

	<?php

	/**
	 * 'wc_mnm_content_loop' action.
	 *
	 * @param  WC_Mix_and_Match  $product
	 * @since  1.8.0
	 *
	 * @hooked wc_mnm_content_loop - 10
	 * @hooked wc_mnm_template_reset_link         - 20
	 * @hooked wc_mnm_template_container_status   - 30
	 * @hooked wc_mnm_template_add_to_cart_button - 40
	 */
	do_action( 'wc_mnm_content_loop', $product );

	/**
	 * 'wc_mnm_add_to_cart_wrap' action.
	 *
	 * @param  WC_Mix_and_Match  $product
	 * @since  1.3.0
	 * @deprecated 2.2.0
	 */
	do_action( 'wc_mnm_add_to_cart_wrap', $product );

	?>

</form>

<?php
/**
 * woocommerce_after_add_to_cart_form hook.
 */
do_action( 'woocommerce_after_add_to_cart_form' );
?>
