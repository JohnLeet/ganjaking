<?php
/**
 * Mix and Match Options Wrapper
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/mnm/tabular/mnm-items-wrapper-open.php.
 *
 * HOWEVER, on occasion WooCommerce Mix and Match will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce Mix and Match/Templates
 * @since   1.3.0
 * @version 2.5.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<table cellspacing="0" class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>">
	<?php if ( count( $column_headers ) ) : ?>
	<thead>
		<tr>
			<?php foreach ( (array) $column_headers as $product_id => $product_title ) : ?>
			<th class="product-<?php echo esc_attr( $product_id ); ?>"><?php echo esc_html( $product_title ); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<?php endif; ?>

	<tbody>
