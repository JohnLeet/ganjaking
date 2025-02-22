<?php
/**
 * Composited Product Price template
 *
 * Override this template by copying it to 'yourtheme/woocommerce/composited-product/price.php'.
 *
 * On occasion, this template file may need to be updated and you (the theme developer) will need to copy the new files to your theme to maintain compatibility.
 * We try to do this as little as possible, but it does happen.
 * When this occurs the version of the template file will be bumped and the readme will list any important changes.
 *
 * @version 8.8.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<span class="price"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
