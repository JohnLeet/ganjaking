<?php
	$upw_default_options = new UPWDefaultOptions();
	$upw_api = new UPWoocmmerceApi();
	$customer_orders = $upw_api->upw_get_customer_orders();
	$order_count = count( $customer_orders  );
	$order_total = $upw_api->upw_get_order_amount( $customer_orders );
?>
<div id="upw-recent-order" class="dashboardRight">
<div class="userpro-section userpro-column userpro-collapsible-1 userpro-collapsed-0" >
	<div class="upw-orders-tab-text">
		<?php _e( $upw_default_options->userpro_woocommerce_get_option( 'upw_order_tab_text' ), 'userpro-woocommerce');?>
	</div>
	<div class="upw-order-snap">
		<?php echo $order_count." Orders | ".$order_total;?>
	</div>
</div>
<div class='userpro-field userpro-field-all-media userpro-field-view'>
	<?php if( count( $customer_orders) <= 0  ){?>
	<p><?php _e( 'No orders availaible yet.', 'userpro-woocommerce'); ?></p>
	<?php }else{
			?>
	
		<table class="upw-orders">

			<thead>
				<tr>
					<th class="upw-date-label" style="text-align:center;"><span class=""><?php _e( 'Date', 'userpro-woocommerce' ); ?></span></th>
					<th class="upw-status-label" style="text-align:center;"><span class=""><?php _e( 'Status', 'userpro-woocommerce' ); ?></span></th>
					<th class="upw-total-label" style="text-align:center;"><span class=""><?php _e( 'Total', 'userpro-woocommerce' ); ?></span></th>
					<th class="upw-quickview-label" style="text-align:center;"><span class=""><?php _e( 'Quick View', 'userpro-woocommerce' ); ?></span></th>
				</tr>
			</thead>

			<tbody><?php
				foreach ( $customer_orders as $customer_order ) {
					$order      = new WC_Order();
					$order->populate( $customer_order );
					$item_count = $order->get_item_count();

					?><tr class="" >
						<td class="upw-date-data" data-title="<?php _e( 'Date', 'woocommerce' ); ?>">
							<time datetime="<?php echo date( 'Y-m-d', strtotime( $order->order_date ) ); ?>" title=""><?php echo date_i18n( 'j M y', strtotime( $order->order_date ) ); ?></time>
						</td>
						<td class="upw-status-data" data-title="<?php _e( 'Status', 'woocommerce' ); ?>">
							<span class="um-woo-status <?php echo $order->get_status(); ?>"><?php echo wc_get_order_status_name( $order->get_status() ); ?></span>
						</td>
						<td class="upw-total-data" data-title="<?php _e( 'Total', 'woocommerce' ); ?>"><?php echo $order->get_formatted_order_total() ?></td>
						<td class="upw-view-order">
							<a href="#" class="" title="<?php _e( 'View order', 'userpro-woocommerce' );?>" data-order_id = "<?php echo $order->get_order_number();?>" onclick = "show_order_details(this)"><i class="userpro-icon-eye-open"></i></a>
						</td>
					</tr><?php
				}
			?></tbody>

		</table>
		
	<?php
	}?>
</div></div>