<?php
/**
 * The template for displaying warranty options.
 *
 * @package WooCommerce_Warranty\Templates
 * @version 2.0.0
 */

defined( 'ABSPATH' ) || exit;
?>
<table class="wp-list-table widefat fixed posts permissions-table">
	<thead>
	<tr>
		<th scope="col" id="status" class="manage-column column-status" width="200"><?php esc_html_e( 'Status', 'wc_warranty' ); ?></th>
		<th scope="col" id="users" class="manage-column column-users" style=""><?php esc_html_e( 'Users with Access', 'wc_warranty' ); ?></th>
	</tr>
	</thead>
	<tbody id="permissions_tbody">
	<?php
	foreach ( $all_statuses as $warranty_status ) :
		$slug = $warranty_status->slug;
		?>
		<tr>
			<td><?php echo esc_html( $warranty_status->name ); ?></td>
			<td>
				<select name="permission[<?php echo esc_attr( $slug ); ?>][]" class="multi-select2" multiple data-placeholder="All Managers and Administrators" style="width: 500px;">
					<?php
					foreach ( $all_permitted_users as $user ) :
						$selected = ( isset( $permissions[ $slug ] ) && in_array( $user->ID, $permissions[ $slug ] ) ) ? true : false;
						?>
						<option value="<?php echo esc_attr( $user->ID ); ?>" <?php selected( $selected ); ?>><?php echo esc_html( $user->display_name ); ?></option>
					<?php endforeach; ?>
				</select>
			</td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<style type="text/css">
	table.permissions-table.widefat th, table.permissions-table.widefat td {overflow: visible;}
</style>
<script type="text/javascript">
	jQuery( 'select.multi-select2' ).selectWoo();
</script>
