<?php
/**
 * Composite add-to-cart panel template
 *
 * Override this template by copying it to 'yourtheme/woocommerce/single-product/composite-add-to-cart.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @since    1.0.0
 * @version  8.10.4
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?><div id="composite_data_<?php echo esc_attr( $product_id ); ?>" class="cart composite_data <?php echo isset( $_REQUEST[ 'add-to-cart' ] ) ? 'composite_added_to_cart' : ''; ?>" data-item_id="review" data-composite_settings="<?php echo wc_esc_json( json_encode( $product->add_to_cart_form_settings() ) ); ?>" data-nav_title="<?php echo esc_attr( __( 'Review and Purchase', 'woocommerce-composite-products' ) ); ?>" data-scenario_data="<?php echo wc_esc_json( json_encode( $product->get_current_scenario_data() ) ); ?>" data-price_data="<?php echo wc_esc_json( json_encode( $product->get_composite_price_data() ) ); ?>" data-container_id="<?php echo esc_attr( $product_id ); ?>" style="display:none;"><?php

	/**
	 * Action 'woocommerce_before_add_to_cart_button'.
	 *
	 * @hooked wc_cp_before_add_to_cart_button - 5
	 */
	do_action( 'woocommerce_before_add_to_cart_button' );

	?><div class="composite_wrap" style="<?php echo apply_filters( 'woocommerce_composite_button_behaviour', 'new', $product ) === 'new' ? '' : 'display:none'; ?>">
		<div class="composite_price"></div>
		<?php
			/**
			* 'woocommerce_composite_after_composite_price' action.
			*
			* @since 8.3.4
			*/
			do_action( 'woocommerce_composite_after_composite_price' );
		?>
		<div class="composite_message" style="display:none;">
			<?php if ( ! is_admin() && ! $is_block_editor_request ) { wc_print_notice('<ul class="msg"></ul>', 'notice' ); } ?>
		</div>
		<div class="composite_availability"><?php
			// Availability html.
			echo wp_kses_post( $availability_html );
		?></div>
		<div class="composite_button"><?php

			/**
			 * Action 'woocommerce_composite_add_to_cart_button'.
			 *
			 * @hooked wc_cp_add_to_cart_button - 10
			 */
			do_action( 'woocommerce_composite_add_to_cart_button', $product );

			// No longer needed as this has been moved to the 'add-to-cart/composite-button.php' template. Leaving this here for back-compat.
			?><input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product_id ); ?>" />
		</div>
	</div><?php

	do_action( 'woocommerce_after_add_to_cart_button' );

?></div><?php
