<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit ;
}
if ( ! class_exists( 'SRP_Update_Purchased_Points' ) ) {

	/**
	 * SRP_Update_Purchased_Points Class.
	 */
	class SRP_Update_Purchased_Points extends WP_Background_Process {

		/**
				 * Action Name.
				 * 
		 * @var string
		 */
		protected $action = 'rs_bulk_update_for_purchase_points' ;

		/**
		 * Task
		 *
		 * Override this method to perform any actions required on each
		 * queue item. Return the modified item for further processing
		 * in the next pass through. Or, return false to remove the
		 * item from the queue.
		 *
		 * @param mixed $item Queue item to iterate over
		 *
		 * @return mixed
		 */
		protected function task( $item ) {
			$this->update_points_for_product( $item ) ;
			return false ;
		}
		
		/**
		 * Complete
		 *
		 * Override if applicable, but ensure that the below actions are
		 * performed, or, call parent::complete().
		 */
		protected function complete() {
			parent::complete() ;
			$offset = get_option( 'fp_bulk_update_points_for_product' ) ;
			if (2 ==  get_option( 'fp_product_selection_type' ) ) {
				$ProductIds = srp_check_is_array( get_option( 'fp_selected_products' ) ) ? get_option( 'fp_selected_products' ) : explode( ',' , get_option( 'fp_selected_products' ) ) ;
			} else {
				$args       = array( 'post_type' => 'product' , 'posts_per_page' => '-1' , 'post_status' => 'publish' , 'fields' => 'ids' , 'cache_results' => false ) ;
				$ProductIds = get_posts( $args ) ;
			}
			$SlicedArray = array_slice( $ProductIds , $offset , 1000 ) ;
			if ( srp_check_is_array( $SlicedArray ) ) {
				SRP_Background_Process::callback_to_update_points_for_product( $offset ) ;
				SRP_Background_Process::$rs_progress_bar->fp_increase_progress( 75 ) ;
			} else {
				SRP_Background_Process::$rs_progress_bar->fp_increase_progress( 100 ) ;
				FP_WooCommerce_Log::log( 'Points for Product(s) Updated Successfully' ) ;
				delete_option( 'fp_bulk_update_points_for_product' ) ;
			}
		}

		public function update_points_for_product( $ProductId ) {
			if ( 'no_products' == $ProductId ) {
				return $ProductId ;
			}

			$checkproduct = srp_product_object( $ProductId ) ;
			if ( ! is_object( $checkproduct ) ) {
				return $ProductId ;
			}

			if ( 1 == get_option( 'fp_product_selection_type' ) || 2 == get_option( 'fp_product_selection_type' ) ) {
				if ( srp_check_is_array( get_variation_id( $ProductId ) ) ) {
					foreach ( get_variation_id( $ProductId ) as $VariationId ) {
						$ProductLevelMetaKey = array(
							'enablereward'                      => '_enable_reward_points' ,
							'rewardtype'                        => '_select_reward_rule' ,
							'rewardpoints'                      => '_reward_points' ,
							'rewardpercent'                     => '_reward_percent' ,
							'enablereferralreward'              => '_enable_referral_reward_points' ,
							'referralrewardtype'                => '_select_referral_reward_rule' ,
							'referralrewardpoint'               => '_referral_reward_points' ,
							'referralrewardpercent'             => '_referral_reward_percent' ,
							'referralrewardtyperefer'           => '_select_referral_reward_rule_getrefer' ,
							'referralpointforgettingrefer'      => '_referral_reward_points_getting_refer' ,
							'referralrewardpercentgettingrefer' => '_referral_reward_percent_getting_refer'
								) ;
						$this->update_product_meta_for_bulk_update( $VariationId , $ProductLevelMetaKey ) ;
					}
				} else {
					if ( ( check_if_variable_product( $checkproduct ) || ( ( 'variable' == srp_product_type( $ProductId ) ) || ( 'variation' == srp_product_type( $ProductId ) ) ) ) ) {
						$ProductLevelMetaKey = array(
							'enablereward'                      => '_enable_reward_points' ,
							'rewardtype'                        => '_select_reward_rule' ,
							'rewardpoints'                      => '_reward_points' ,
							'rewardpercent'                     => '_reward_percent' ,
							'enablereferralreward'              => '_enable_referral_reward_points' ,
							'referralrewardtype'                => '_select_referral_reward_rule' ,
							'referralrewardpoint'               => '_referral_reward_points' ,
							'referralrewardpercent'             => '_referral_reward_percent' ,
							'referralrewardtyperefer'           => '_select_referral_reward_rule_getrefer' ,
							'referralpointforgettingrefer'      => '_referral_reward_points_getting_refer' ,
							'referralrewardpercentgettingrefer' => '_referral_reward_percent_getting_refer'
								) ;
					} else {
						$ProductLevelMetaKey = array(
							'enablereward'                      => '_rewardsystemcheckboxvalue' ,
							'rewardtype'                        => '_rewardsystem_options' ,
							'rewardpoints'                      => '_rewardsystempoints' ,
							'rewardpercent'                     => '_rewardsystempercent' ,
							'enablereferralreward'              => '_rewardsystemreferralcheckboxvalue' ,
							'referralrewardtype'                => '_referral_rewardsystem_options' ,
							'referralrewardpoint'               => '_referralrewardsystempoints' ,
							'referralrewardpercent'             => '_referralrewardsystempercent' ,
							'referralrewardtyperefer'           => '_referral_rewardsystem_options_getrefer' ,
							'referralpointforgettingrefer'      => '_referralrewardsystempoints_for_getting_referred' ,
							'referralrewardpercentgettingrefer' => '_referralrewardsystempercent_for_getting_referred'
								) ;
					}
					$this->update_product_meta_for_bulk_update( $ProductId , $ProductLevelMetaKey ) ;
				}
			} elseif ( 3 == get_option( 'fp_product_selection_type' ) || 4 == get_option( 'fp_product_selection_type' ) ) {
				$ProductCat = get_the_terms( $ProductId , 'product_cat' ) ;
				if ( srp_check_is_array( $ProductCat ) ) {
					$CategoryList = ( 3 == get_option( 'fp_product_selection_type' ) ) ? get_terms( 'product_cat' ) : get_option( 'fp_selected_categories' ) ;
					if ( srp_check_is_array( $CategoryList ) ) {
						foreach ( $CategoryList as $CategoryId ) {
							if ( ! $this->check_if_product_is_in_selected_category( $CategoryId , $ProductCat ) ) {
								continue ;
							}

							$CategoryId = is_object( $CategoryId ) ? $CategoryId->term_id : $CategoryId ;
							if ( ( check_if_variable_product( $checkproduct ) || ( ( 'variable' == srp_product_type( $ProductId ) ) || ( 'variation'  == srp_product_type( $ProductId ) ) ) ) ) {
								update_post_meta( $getvariation[ 'variation_id' ] , '_enable_reward_points' , get_option( 'fp_enable_reward' ) ) ;

								update_post_meta( $getvariation[ 'variation_id' ] , '_enable_referral_reward_points' , get_option( 'fp_enable_referral_reward' ) ) ;

								update_post_meta( $getvariation[ 'variation_id' ] , '_select_reward_rule' , '' ) ;
								update_post_meta( $getvariation[ 'variation_id' ] , '_reward_points' , '' ) ;
								update_post_meta( $getvariation[ 'variation_id' ] , '_reward_percent' , '' ) ;
								update_post_meta( $getvariation[ 'variation_id' ] , '_select_referral_reward_rule' , '' ) ;
								update_post_meta( $getvariation[ 'variation_id' ] , '_referral_reward_points' , '' ) ;
								update_post_meta( $getvariation[ 'variation_id' ] , '_referral_reward_percent' , '' ) ;
								update_post_meta( $getvariation[ 'variation_id' ] , '_referral_reward_points_getting_refer' , '' ) ;
								update_post_meta( $getvariation[ 'variation_id' ] , '_referral_reward_percent_getting_refer' , '' ) ;
								update_post_meta( $getvariation[ 'variation_id' ] , '_select_referral_reward_rule_getrefer' , '' ) ;
							} else {
								$enablereward = ( 1 ==  get_option( 'fp_enable_reward' ) ) ? 'yes' : 'no' ;
								update_post_meta( $ProductId , '_rewardsystemcheckboxvalue' , $enablereward ) ;

								$enablereferralreward = ( 1 == get_option( 'fp_enable_referral_reward' ) ) ? 'yes' : 'no' ;
								update_post_meta( $ProductId , '_rewardsystemreferralcheckboxvalue' , $enablereferralreward ) ;

								update_post_meta( $ProductId , '_rewardsystem_options' , '' ) ;
								update_post_meta( $ProductId , '_rewardsystempoints' , '' ) ;
								update_post_meta( $ProductId , '_rewardsystempercent' , '' ) ;
								update_post_meta( $ProductId , '_referral_rewardsystem_options' , '' ) ;
								update_post_meta( $ProductId , '_referralrewardsystempoints' , '' ) ;
								update_post_meta( $ProductId , '_referralrewardsystempercent' , '' ) ;
								update_post_meta( $ProductId , '_referral_rewardsystem_options_getrefer' , '' ) ;
								update_post_meta( $ProductId , '_referralrewardsystempoints_for_getting_referred' , '' ) ;
								update_post_meta( $ProductId , '_referralrewardsystempercent_for_getting_referred' , '' ) ;
							}

							$ProductLevelMetaKey = array(
								'rewardtype'                        => 'enable_rs_rule' ,
								'rewardpoints'                      => 'rs_category_points' ,
								'rewardpercent'                     => 'rs_category_percent' ,
								'referralrewardtype'                => 'referral_enable_rs_rule' ,
								'referralrewardpoint'               => 'referral_rs_category_points' ,
								'referralrewardpercent'             => 'referral_rs_category_percent' ,
								'referralrewardtyperefer'           => 'referral_enable_rs_rule_refer' ,
								'referralpointforgettingrefer'      => 'referral_rs_category_points_get_refered' ,
								'referralrewardpercentgettingrefer' => 'referral_rs_category_percent_get_refer'
									) ;
							$this->update_category_meta_for_bulk_update( $CategoryId , $ProductLevelMetaKey ) ;
						}
					}
				}
			}
		}

		public function check_if_product_is_in_selected_category( $CategoryId, $ProductCat ) {
			if ( 3 == get_option( 'fp_product_selection_type' ) ) {
				return true ;
			}

			foreach ( $ProductCat as $Category ) {
				if ( $CategoryId == $Category->term_id ) {
					return true ;
				}
			}

			return false ;
		}

		public function update_product_meta_for_bulk_update( $ProductId, $ProductLevelMetaKey ) {
			$enablereward = ( '_enable_reward_points' == $ProductLevelMetaKey[ 'enablereward' ] ) ? get_option( 'fp_enable_reward' ) : ( ( 1 == get_option( 'fp_enable_reward' ) ) ? 'yes' : 'no' ) ;
			update_post_meta( $ProductId , $ProductLevelMetaKey[ 'enablereward' ] , $enablereward ) ;

			$enablereferralreward = ( '_enable_referral_reward_points' == $ProductLevelMetaKey[ 'enablereferralreward' ] ) ? get_option( 'fp_enable_referral_reward' ) : ( 1 == get_option( 'fp_enable_referral_reward' ) ? 'yes' : 'no' ) ;
			update_post_meta( $ProductId , $ProductLevelMetaKey[ 'enablereferralreward' ] , $enablereferralreward ) ;

			if ( get_option( 'fp_reward_type' ) ) {
				update_post_meta( $ProductId , $ProductLevelMetaKey[ 'rewardtype' ] , get_option( 'fp_reward_type' ) ) ;
			}

			if ( get_option( 'fp_reward_points' ) ) {
				update_post_meta( $ProductId , $ProductLevelMetaKey[ 'rewardpoints' ] , get_option( 'fp_reward_points' ) ) ;
			}

			if ( get_option( 'fp_reward_percent' ) ) {
				update_post_meta( $ProductId , $ProductLevelMetaKey[ 'rewardpercent' ] , get_option( 'fp_reward_percent' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_type' ) ) {
				update_post_meta( $ProductId , $ProductLevelMetaKey[ 'referralrewardtype' ] , get_option( 'fp_referral_reward_type' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_points' ) ) {
				update_post_meta( $ProductId , $ProductLevelMetaKey[ 'referralrewardpoint' ] , get_option( 'fp_referral_reward_points' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_percent' ) ) {
				update_post_meta( $ProductId , $ProductLevelMetaKey[ 'referralrewardpercent' ] , get_option( 'fp_referral_reward_percent' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_type_for_gettingrefer' ) ) {
				update_post_meta( $ProductId , $ProductLevelMetaKey[ 'referralrewardtyperefer' ] , get_option( 'fp_referral_reward_type_for_gettingrefer' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_points_for_gettingrefer' ) ) {
				update_post_meta( $ProductId , $ProductLevelMetaKey[ 'referralpointforgettingrefer' ] , get_option( 'fp_referral_reward_points_for_gettingrefer' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_percent_for_gettingrefer' ) ) {
				update_post_meta( $ProductId , $ProductLevelMetaKey[ 'referralrewardpercentgettingrefer' ] , get_option( 'fp_referral_reward_percent_for_gettingrefer' ) ) ;
			}
		}

		public function update_category_meta_for_bulk_update( $CategoryId, $ProductLevelMetaKey ) {
			$enablereward = 1 == get_option( 'fp_enable_reward' ) ? 'yes' : 'no' ;
			srp_update_term_meta( $CategoryId , 'enable_reward_system_category' , $enablereward ) ;

			$enablereferralreward = 1 == get_option( 'fp_enable_referral_reward' ) ? 'yes' : 'no' ;
			srp_update_term_meta( $CategoryId , 'enable_referral_reward_system_category' , $enablereferralreward ) ;

			if ( get_option( 'fp_reward_type' ) ) {
				srp_update_term_meta( $CategoryId , $ProductLevelMetaKey[ 'rewardtype' ] , get_option( 'fp_reward_type' ) ) ;
			}

			if ( get_option( 'fp_reward_points' ) ) {
				srp_update_term_meta( $CategoryId , $ProductLevelMetaKey[ 'rewardpoints' ] , get_option( 'fp_reward_points' ) ) ;
			}

			if ( get_option( 'fp_reward_percent' ) ) {
				srp_update_term_meta( $CategoryId , $ProductLevelMetaKey[ 'rewardpercent' ] , get_option( 'fp_reward_percent' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_type' ) ) {
				srp_update_term_meta( $CategoryId , $ProductLevelMetaKey[ 'referralrewardtype' ] , get_option( 'fp_referral_reward_type' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_points' ) ) {
				srp_update_term_meta( $CategoryId , $ProductLevelMetaKey[ 'referralrewardpoint' ] , get_option( 'fp_referral_reward_points' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_percent' ) ) {
				srp_update_term_meta( $CategoryId , $ProductLevelMetaKey[ 'referralrewardpercent' ] , get_option( 'fp_referral_reward_percent' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_type_for_gettingrefer' ) ) {
				srp_update_term_meta( $CategoryId , $ProductLevelMetaKey[ 'referralrewardtyperefer' ] , get_option( 'fp_referral_reward_type_for_gettingrefer' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_points_for_gettingrefer' ) ) {
				srp_update_term_meta( $CategoryId , $ProductLevelMetaKey[ 'referralpointforgettingrefer' ] , get_option( 'fp_referral_reward_points_for_gettingrefer' ) ) ;
			}

			if ( get_option( 'fp_referral_reward_percent_for_gettingrefer' ) ) {
				srp_update_term_meta( $CategoryId , $ProductLevelMetaKey[ 'referralrewardpercentgettingrefer' ] , get_option( 'fp_referral_reward_percent_for_gettingrefer' ) ) ;
			}
		}

	}

}
