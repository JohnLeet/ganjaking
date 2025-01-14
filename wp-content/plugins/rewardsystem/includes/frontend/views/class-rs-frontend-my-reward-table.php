<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly
}

/**
 * My Reward Table.
 */
?>
<form class="rs-my-reward-table-form" method ='POST'>
	<?php if ( '1' == get_option( 'rs_show_or_hide_date_filter' ) && $AvailablePoints ) { ?>
		<table class="rs-my-reward-date-filter">
			<tbody>
				<tr>
					<td class="rs-duration-type-label">
						<label><?php esc_html_e( 'Earned and Redeemed points during' , 'rewardsystem' ) ; ?></label>
					</td>
					<td>
						<select id="rs_duration_type" name="rs_duration_type">
							<option value="0" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '0' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Choose Option' , 'rewardsystem' ); ?></option>                                  
							<option value="1" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '1' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Last 1 Month' , 'rewardsystem' ); ?></option>
							<option value="2" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '2' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Last 3 Month' , 'rewardsystem' ) ; ?></option>
							<option value="3" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '3' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Last 6 Month' , 'rewardsystem' ) ; ?></option>
							<option value="4" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '4' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Last 12 Month' , 'rewardsystem' ) ; ?></option>
							<option value="5" <?php isset( $_REQUEST[ 'rs_duration_type' ] ) ? selected( '5' == wc_clean( wp_unslash( $_REQUEST[ 'rs_duration_type' ] ) ) , true ) : '' ; ?>><?php esc_html_e( 'Custom Duration' , 'rewardsystem' ) ; ?></option>
						</select>
					</td>
				</tr>
				<tr>
					<td class="rs-from-date-label">
						<label><?php echo wp_kses_post(__( 'From Date <span class="rs-mandatory-field">*</span>' , 'rewardsystem' ) ); ?></label>
					</td>
					<td>
						<input type="text" 
							   id="rs_custom_from_date_field"
							   name="rs_custom_from_date_field" 
							   class = "rs_custom_from_date_field"
							   value=""/>
					</td>
				</tr>
				<tr>
					<td class="rs-to-date-label">
						<label><?php echo wp_kses_post(__( 'To Date <span class="rs-mandatory-field">*</span>', 'rewardsystem' )) ; ?></label>
					</td>
					<td>
						<input type="text"
							   id="rs_custom_to_date_field"
							   name="rs_custom_to_date_field" 
							   class =""
							   value=""/>
					</td>
				</tr>  
				<tr>
					<td/>
					<td>
						<button type='submit'
								id = 'rs_submit'
								name ='rs_submit'
								class='rs_submit' >
									<?php esc_html_e( 'Apply' , 'rewardsystem' ) ; ?>
						</button> 
					</td>
				</tr>
				<?php
				if ( isset( $_REQUEST[ 'rs_duration_type' ] ) && '0' != $_REQUEST[ 'rs_duration_type' ] ) {
					?>
					<tr>
						<td class="rs-earned-points-label">
							<p>
								<label><b><?php esc_html_e( 'Earned Points:' , 'rewardsystem' ) ; ?></b></label>
								<span><?php echo esc_html( round_off_type( floatval( $selected_duration_earned_point ) ) ) ; ?></span>
							</p>
						</td>
						<td class="rs-redeemed-points-label">
							<p>
								<label><b><?php esc_html_e( 'Redeemed Points:' , 'rewardsystem' ) ; ?></b></label>
								<span><?php echo esc_html( round_off_type( floatval( $selected_duration_redeemed_point ) ) ) ; ?></span>
							</p>
						</td>
					</tr>
					<tr>
						<td colspan="2">
							<?php
							global $post ;
							$url = isset( $post->ID ) ? get_permalink( $post->ID ) : get_permalink() ;
							?>
							<a href ="<?php echo esc_url( $url ) ; ?>"
							   class="rs-previous-link">
								   <?php esc_html_e( 'Go Back' , 'rewardsystem' ) ; ?>
							</a>
						</td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	<?php } ?>
	<table class = "my_reward_table demo shop_table my_account_orders table-bordered" data-filter = "#filters" data-page-size="<?php echo esc_attr($per_page); ?>" data-page-previous-text = "prev" data-filter-text-only = "true" data-page-next-text = "next">
		<thead>
			<tr>
				<?php if ( '1' == $TableData[ 'sno' ] ) : ?>
					<th data-toggle=true data-sort-initial =true ><?php echo esc_html($TableData[ 'label_sno' ]) ; ?></th>
					<?php
				endif ;

				$i = 1 ;
				foreach ( $SortedColumn as $Column ) {
					if ( '1' == $TableData[ $Column ] ) {
						$data_hide = $i > 2 ? 'phone,tablet' : '' ;
						?>
						<th data-hide="<?php echo esc_attr($data_hide) ; ?>"><?php echo wp_kses_post($TableData[ "label_$Column" ] ); ?></th>
						<?php
						$i ++ ;
					}
				}
				?>
			</tr>
		</thead>
		<tbody>
			<?php
			if ( '1' == $TableData[ 'points_log_sort' ]  ) :
				krsort( $UserLog , SORT_NUMERIC ) ;
			endif ;

			$i = $offset + 1 ;
			foreach ( $UserLog as $Log ) :
				if ( ! srp_check_is_array( $Log ) ) :
					continue ;
				endif ;

				$CheckPoint = $Log[ 'checkpoints' ] ;
				if ( isset( $Log[ 'earnedpoints' ] ) && ! empty( $Log[ 'checkpoints' ] ) ) {
					$Points         = empty( $Log[ 'earnedpoints' ] ) ? 0 : round_off_type( $Log[ 'earnedpoints' ] ) ;
					$RedeemedPoints = empty( $Log[ 'redeempoints' ] ) ? 0 : ( ( 'yes' == get_option( 'rs_enable_round_off_type_for_calculation' ) ) ? $Log[ 'redeempoints' ] : round_off_type( $Log[ 'redeempoints' ] ) ) ;
					$TotalPoints    = empty( $Log[ 'totalpoints' ] ) ? 0 : round_off_type( $Log[ 'totalpoints' ] ) ;
					$Username       = get_user_meta( $Log[ 'userid' ] , 'nickname' , true ) ;
					$RefUsername    = get_user_meta( $Log[ 'refuserid' ] , 'nickname' , true ) ;
					$NomineeName    = get_user_meta( $Log[ 'nomineeid' ] , 'nickname' , true ) ;
					$Reason         = RSPointExpiry::msg_for_log( false , true , true , $Log[ 'earnedpoints' ] , $CheckPoint , $Log[ 'productid' ] , $Log[ 'orderid' ] , $Log[ 'variationid' ] , $Log[ 'userid' ] , $RefUsername , $Log[ 'reasonindetail' ] , $Log[ 'redeempoints' ] , false , $NomineeName , $Username , $Log[ 'nomineepoints' ] ) ;
				} else {
					$Points         = empty( $Log[ 'points_earned_order' ] ) ? 0 : round_off_type( $Log[ 'points_earned_order' ] ) ;
					$RedeemedPoints = empty( $Log[ 'points_redeemed' ] ) ? 0 : ( ( 'yes' == get_option( 'rs_enable_round_off_type_for_calculation' ) ) ? $Log[ 'points_redeemed' ] : round_off_type( $Log[ 'points_redeemed' ] ) ) ;
					$TotalPoints    = empty( $Log[ 'totalpoints' ] ) ? 0 : round_off_type( $Log[ 'totalpoints' ] ) ;
					$Reason         = empty( $Log[ 'rewarder_for_frontend' ] ) ? 0 : $Log[ 'rewarder_for_frontend' ] ;
				}

				if ( ( ( 0 != $Points ) && ( 0!=$RedeemedPoints ) ) || ( ( ( 0!=$Points ) && ( 0 == $RedeemedPoints ) ) || ( ( 0 == $Points ) && ( 0 != $RedeemedPoints ) ) ) || ( ! empty( $Reason ) ) ) {
					$DefaultColumnValues = array(
						'sno'             => $i ,
						'points_expiry'   => 999999999999 != $Log[ 'expirydate' ] ? date_display_format( $Log[ 'expirydate' ] ) : '-' ,
						'username'        => $Username ,
						'reward_for'      => $Reason ,
						'earned_points'   => $Points ,
						'redeemed_points' => $RedeemedPoints ,
						'total_points'    => $TotalPoints ,
						'earned_date'     => date_display_format( $Log[ 'earneddate' ] ) ,
							) ;
					?>
					<tr>
						<?php if ( '1' == $TableData[ 'sno' ] ) { ?>
							<td data-value="<?php echo esc_attr($DefaultColumnValues[ 'sno' ]) ; ?>"><?php echo esc_html($DefaultColumnValues[ 'sno' ]) ; ?></td>
							<?php
						}

						foreach ( $SortedColumn as $Column ) {
							if ( '1' == $TableData[ $Column ] ) {
								?>
								<td><?php echo wp_kses_post($DefaultColumnValues[ $Column ]) ; ?></td>
								<?php
							}
						}
						?>
					</tr>
					<?php
				}
				$i ++ ;
			endforeach ;
			?>
		</tbody>


		<tfoot>
			<tr>
				<?php if ( '1' == get_option( 'rs_enable_footable_js' ) ) : ?>
					<td colspan="7">
						<div class="pagination pagination-centered"></div>
					</td>
				<?php else : ?>
					<?php if ( $page_count > 1 ) : ?>
						<td colspan="<?php echo esc_attr( '8' ) ; ?>" class="footable-visible">
							<?php
							srp_get_template( 'pagination.php' , $pagination ) ;
							?>
						</td>
					<?php endif ; ?>
				<?php endif ; ?>
			</tr>
		</tfoot>
	</table>
</form>
