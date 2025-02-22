<?php
/**
 * Refund email
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// allowed tags for escaping
$allowed_tags = array(
	'span'  => array(),
	'div'   => array(),
	'p'     => array(),
	'del'   => array(),
	'ins'   => array(),
	'small' => array(),
	'i'     => array(),
);

$text_align = is_rtl() ? 'right' : 'left';
?>

<p><?php printf( esc_html__( 'Hi %s,', 'woocommerce-advanced-notifications' ), esc_html( $recipient_name ) ); ?></p>

<p>
	<?php 
	if ( $partial_refund ) {
		printf( esc_html__( 'Order #%s has been partially refunded.', 'woocommerce-advanced-notifications' ), esc_html( $order->get_order_number() ) );
	} else {
		printf( esc_html__( 'Order #%s has been refunded.', 'woocommerce-advanced-notifications' ), esc_html( $order->get_order_number() ) );
	}
	?>
</p>

<h2>
	<?php
	$order_edit_url  = admin_url( 'post.php?post=' . $order->get_id() . '&action=edit' );
	$order_date_c    = $order->get_date_created()->format( 'c' );
	$order_date_text = wc_format_datetime( $order->get_date_created() );
	?>
	<a class="link" href="<?php echo esc_url( $order_edit_url ); ?>"><?php printf( esc_html__( 'Order #%s', 'woocommerce-advanced-notifications' ), esc_html( $order->get_order_number() ) ); ?></a> (<?php printf( '<time datetime="%1$s">%2$s</time>', esc_attr( $order_date_c ), esc_html( $order_date_text ) ); ?>)
</h2>


<?php do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, false, $email ); ?>

<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
	<thead>
		<tr>
			<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Product', 'woocommerce-advanced-notifications' ); ?></th>
			<th class="td" scope="col" style="text-align:left;" <?php if ( ! $show_prices ) : ?>colspan="2"<?php endif; ?>><?php esc_html_e( 'Quantity', 'woocommerce-advanced-notifications' ); ?></th>
			<?php if ( $show_prices ) : ?>
				<th class="td" scope="col" style="text-align:left;"><?php esc_html_e( 'Price', 'woocommerce-advanced-notifications' ); ?></th>
			<?php endif; ?>
		</tr>
	</thead>

	<tbody>
		<?php
		$displayed_total = 0;

		foreach ( $order->get_items() as $item_id => $item ) :

			if ( is_callable( array( $item, 'get_product' ) ) ) {
				$_product = $item->get_product();
			} else {
				$_product = $order->get_product_from_item( $item );
			}

			$display = false;

			$product_id = $_product->is_type( 'variation' ) ? $_product->get_parent_id() : $_product->get_id();

			if ( $triggers['all'] || in_array( $product_id, $triggers['product_ids'] ) || in_array( $_product->get_shipping_class_id(), $triggers['shipping_classes'] ) ) {
				$display = true;
			}

			if ( ! $display ) {

				$cats = wp_get_post_terms( $product_id, 'product_cat', array( "fields" => "ids" ) );

				if ( sizeof( array_intersect( $cats, $triggers['product_cats'] ) ) > 0 ) {
					$display = true;
				}

			}

			if ( ! $display && ! empty( $show_all_items ) ) {
				$display = true;
			}

			if ( ! $display )
				continue;

			$displayed_total += $order->get_line_total( $item, true );

			$item_meta = new WC_Order_Item_Product( $item_id );
			?>
			<tr>
				<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;"><?php

					// Product name.
					$product_name = apply_filters( 'woocommerce_order_product_title', $item['name'], $_product );
					echo esc_html( $product_name );

					// SKU.
					echo $_product->get_sku() ? ' (#' . esc_html( $_product->get_sku() ) . ')' : '';

					// Allow other plugins to add additional product information here.
					do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, $plain_text );

					// Variation.
					wc_display_item_meta( $item );

					// File URLs.
					if ( $show_download_links ) {
						wc_display_item_downloads( $item );
					}

					// Allow other plugins to add additional product information here.
					do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, $plain_text );
				?></td>
				<td class="td" style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" <?php if ( ! $show_prices ) : ?>colspan="2"<?php endif; ?>>
					<?php
					$qty          = is_a( $item, 'WC_Order_Item' ) ? $item->get_quantity() : $item['qty'];
					$refunded_qty = $order->get_qty_refunded_for_item( $item_id );

					if ( $refunded_qty ) {
						$qty_display = '<del>' . esc_html( $qty ) . '</del> <ins>' . esc_html( $qty - ( $refunded_qty * -1 ) ) . '</ins>';
					} else {
						$qty_display = esc_html( $qty );
					}
					echo wp_kses_post( apply_filters( 'woocommerce_email_order_item_quantity', $qty_display, $item ) );
					?>
				</td>

				<?php if ( $show_prices ) : ?>
					<td style="text-align:left; vertical-align:middle; border: 1px solid #eee; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;">
						<?php echo wp_kses( $order->get_formatted_line_subtotal( $item ), $allowed_tags ); ?>
					</td>
				<?php endif; ?>
			</tr>

		<?php endforeach; ?>
	</tbody>

	<tfoot>
		<?php
		if ( $show_totals ) :
			$totals = $order->get_order_item_totals();
			if ( $totals ) {
				$i = 0;
				foreach ( $totals as $total ) {
					$i++;
					?>
					<tr>
						<th class="td" scope="col" colspan="2" style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; text-align:left;">
							<?php echo esc_html( $total['label'] ); ?>
						</th>
						<td style="text-align:left;border: 1px solid #eee;">
							<?php echo wp_kses( $total['value'], $allowed_tags ); ?>
						</td>
					</tr>
					<?php
				}
			} else {
				// Only show the total for displayed items
				?>
				<tr>
					<th class="td" scope="col" colspan="2" style="font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; text-align:left;">
						<?php esc_html_e( 'Total', 'woocommerce-advanced-notifications' ); ?>
					</th>
					<td style="text-align:left;border: 1px solid #eee;">
						<?php echo wp_kses( wc_price( $displayed_total ), $allowed_tags ); ?>
					</td>
				</tr>
				<?php
			}
		endif;

		if ( $order->get_customer_note() ) {
			?><tr>
				<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;">
					<?php esc_html_e( 'Note:', 'woocommerce-advanced-notifications' ); ?>
				</th>
				<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;">
					<?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?>
				</td>
			</tr><?php
		}
		?>
	</tfoot>
</table>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email ); ?>

<?php
 /**
  * @hooked WC_Emails::customer_details() Shows customer details
  * @hooked WC_Emails::email_address() Shows email address
  */
 do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );
?>
