<?php
/**
 * Dropdown and button for various actions for waitlists
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$archive = get_option( 'woocommerce_waitlist_archive_on' );
?>
<div class="wcwl_actions">
	<select name="wcwl_action_<?php echo esc_attr( $product_id ); ?>" class="wcwl_action">
		<option disabled selected value="0"><?php _e( 'Actions', 'woocommerce-waitlist' ); ?></option>
		<option value="wcwl_remove_waitlist"><?php $archive ? _e( 'Move to archive', 'woocommerce-waitlist' ) : _e( 'Remove', 'woocommerce-waitlist' ); ?></option>
		<option value="wcwl_email_instock"><?php _e( 'Send instock email', 'woocommerce-waitlist' ); ?></option>
		<option value="wcwl_email_custom"><?php _e( 'Send custom email', 'woocommerce-waitlist' ); ?></option>
		<option value="wcwl_export"><?php _e( 'Export emails', 'woocommerce-waitlist' ); ?></option>
	</select>
	<button type="button" class="button wcwl_action" data-nonce="<?php echo esc_attr( wp_create_nonce( 'wcwl-action-nonce' ) ); ?>">
		<?php _e( 'Go', 'woocommerce-waitlist' ); ?>
	</button>
</div>