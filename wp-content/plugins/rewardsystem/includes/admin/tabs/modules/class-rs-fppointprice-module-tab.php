<?php
/*
 * Support Tab Setting
 */


if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'RSPointPriceModule' ) ) {

	class RSPointPriceModule {

		public static function init() {

			add_action( 'rs_default_settings_fppointprice' , array( __CLASS__ , 'set_default_value' ) ) ;

			add_action( 'woocommerce_rs_settings_tabs_fppointprice' , array( __CLASS__ , 'reward_system_register_admin_settings' ) ) ; // Call to register the admin settings in the Reward System Submenu with general Settings tab        

			add_action( 'woocommerce_update_options_fprsmodules_fppointprice' , array( __CLASS__ , 'reward_system_update_settings' ) ) ; // call the woocommerce_update_options_{slugname} to update the reward system                               

			add_action( 'woocommerce_admin_field_rs_enable_disable_point_price_module' , array( __CLASS__ , 'enable_module' ) ) ;

			add_action( 'woocommerce_admin_field_selected_products_point' , array( __CLASS__ , 'rs_select_products_to_update_point_price' ) ) ;

			add_action( 'woocommerce_admin_field_button_point_price' , array( __CLASS__ , 'rs_save_button_for_update_point_price' ) ) ;

			add_action( 'woocommerce_admin_field_rs_include_products_for_point_pricing' , array( __CLASS__ , 'rs_include_products_for_point_pricing' ) ) ;

			add_action( 'woocommerce_admin_field_rs_exclude_products_for_point_pricing' , array( __CLASS__ , 'rs_exclude_products_for_point_pricing' ) ) ;

			add_action( 'fp_action_to_reset_module_settings_fppointprice' , array( __CLASS__ , 'reset_point_price_module' ) ) ;

			add_action( 'woocommerce_admin_field_rs_hide_bulk_update_for_point_price_start' , array( __CLASS__ , 'rs_hide_bulk_update_for_point_price_start' ) ) ;

			add_action( 'woocommerce_admin_field_rs_hide_bulk_update_for_point_price_end' , array( __CLASS__ , 'rs_hide_bulk_update_for_point_price_end' ) ) ;

			add_action( 'rs_display_save_button_fppointprice' , array( 'RSTabManagement' , 'rs_display_save_button' ) ) ;

			add_action( 'rs_display_reset_button_fppointprice' , array( 'RSTabManagement' , 'rs_display_reset_button' ) ) ;
		}

		/*
		 * Function label settings to Member Level Tab
		 */

		public static function reward_system_admin_fields() {
			global $woocommerce ;
			$categorylist = fp_product_category() ;
			return apply_filters( 'woocommerce_fppointprice' , array(
				array(
					'type' => 'rs_modulecheck_start' ,
				) ,
				array(
					'name' => __( 'Points Pricing Module' , 'rewardsystem' ) ,
					'type' => 'title' ,
					'id'   => '_rs_activate_point_price_module'
				) ,
				array(
					'type' => 'rs_enable_disable_point_price_module' ,
				) ,
				array(
					'name'    => __( 'Point Price will be visible for' , 'rewardsystem' ) ,
					'id'      => 'rs_point_price_visibility' ,
					'std'     => '1' ,
					'default' => '1' ,
					'type'    => 'radio' ,
					'options' => array(
						'1' => __( 'All User(s)' , 'rewardsystem' ) ,
						'2' => __( 'Logged in User(s)' , 'rewardsystem' )
					) ,
					'newids'  => 'rs_point_price_visibility' ,
				) ,
				array( 'type' => 'sectionend' , 'id' => '_rs_activate_point_price_module' ) ,
				array(
					'type' => 'rs_modulecheck_end' ,
				) ,
				array(
					'type' => 'rs_wrapper_start' ,
				) ,
				array(
					'name' => __( 'Point Priced Products Global Settings' , 'rewardsystem' ) ,
					'type' => 'title' ,
					'id'   => '_rs_global_Point_price'
				) ,
				array(
					'name'     => __( 'Point Pricing' , 'rewardsystem' ) ,
					'id'       => 'rs_enable_product_category_level_for_points_price' ,
					'class'    => 'rs_enable_product_category_level_for_points_price' ,
					'std'      => 'no' ,
					'default'  => 'no' ,
					'type'     => 'radio' ,
					'newids'   => 'rs_enable_product_category_level_for_points_price' ,
					'options'  => array(
						'no'  => __( 'Quick Setup (Global Level Settings will be enabled)' , 'rewardsystem' ) ,
						'yes' => __( 'Advanced Setup (Global,Category and Product Level wil be enabled)' , 'rewardsystem' ) ,
					) ,
					'desc_tip' => true ,
					'desc'     => __( 'Quick Setup - Global Level will be enabled<br>Advanced Setup - Global,Category and Product Level wil be enabled' , 'rewardsystem' )
				) ,
				array(
					'name'    => __( 'Point Pricing is applicable for' , 'rewardsystem' ) ,
					'id'      => 'rs_point_pricing_global_level_applicable_for' ,
					'std'     => '1' ,
					'class'   => 'rs_point_pricing_global_level_applicable_for' ,
					'default' => '1' ,
					'newids'  => 'rs_point_pricing_global_level_applicable_for' ,
					'type'    => 'select' ,
					'options' => array(
						'1' => __( 'All Product(s)' , 'rewardsystem' ) ,
						'2' => __( 'Include Product(s)' , 'rewardsystem' ) ,
						'3' => __( 'Exclude Product(s)' , 'rewardsystem' ) ,
						'4' => __( 'All Categories' , 'rewardsystem' ) ,
						'5' => __( 'Include Categories' , 'rewardsystem' ) ,
						'6' => __( 'Exclude Categories' , 'rewardsystem' ) ,
					) ,
				) ,
				array(
					'type' => 'rs_include_products_for_point_pricing' ,
				) ,
				array(
					'type' => 'rs_exclude_products_for_point_pricing' ,
				) ,
				array(
					'name'    => __( 'Include Categories' , 'rewardsystem' ) ,
					'id'      => 'rs_include_particular_categories_for_point_pricing' ,
					'css'     => 'min-width:350px;' ,
					'std'     => '' ,
					'class'   => 'rs_include_particular_categories_for_point_pricing' ,
					'default' => '' ,
					'newids'  => 'rs_include_particular_categories_for_point_pricing' ,
					'type'    => 'multiselect' ,
					'options' => $categorylist ,
				) ,
				array(
					'name'    => __( 'Exclude Categories' , 'rewardsystem' ) ,
					'id'      => 'rs_exclude_particular_categories_for_point_pricing' ,
					'css'     => 'min-width:350px;' ,
					'std'     => '' ,
					'class'   => 'rs_exclude_particular_categories_for_point_pricing' ,
					'default' => '' ,
					'newids'  => 'rs_exclude_particular_categories_for_point_pricing' ,
					'type'    => 'multiselect' ,
					'options' => $categorylist ,
				) ,
				array(
					'name'    => __( 'Point Pricing' , 'rewardsystem' ) ,
					'id'      => 'rs_enable_disable_point_priceing' ,
					'default' => '1' ,
					'std'     => '1' ,
					'newids'  => 'rs_enable_disable_point_priceing' ,
					'type'    => 'select' ,
					'options' => array(
						'1' => __( 'Enable' , 'rewardsystem' ) ,
						'2' => __( 'Disable' , 'rewardsystem' ) ,
					) ,
				) ,
				array(
					'name'    => __( 'Point Priced Product Identifier Label' , 'rewardsystem' ) ,
					'id'      => 'rs_label_for_point_value' ,
					'default' => '/Pt' ,
					'std'     => '/Pt' ,
					'newids'  => 'rs_label_for_point_value' ,
					'type'    => 'text' ,
				) ,
				array(
					'name'              => __( 'Enter Space between Points and Lable in pixel' , 'rewardsystem' ) ,
					'id'                => 'rs_pixel_val' ,
					'default'           => '5' ,
					'std'               => '5' ,
					'newids'            => 'rs_pixel_val' ,
					'type'              => 'number' ,
					'custom_attributes' => array(
						'min' => '0'
					) ,
				) ,
				array(
					'name'    => __( 'Point Priced Product Identifier Label Display Position' , 'rewardsystem' ) ,
					'id'      => 'rs_sufix_prefix_point_price_label' ,
					'default' => '1' ,
					'std'     => '1' ,
					'newids'  => 'rs_sufix_prefix_point_price_label' ,
					'type'    => 'select' ,
					'options' => array(
						'1' => __( 'Before' , 'rewardsystem' ) ,
						'2' => __( 'After' , 'rewardsystem' ) ,
					) ,
				) ,
				array(
					'name'     => __( 'Point Pricing Global Level Settings' , 'rewardsystem' ) ,
					'id'       => 'rs_local_enable_disable_point_price_for_product' ,
					'std'      => '2' ,
					'default'  => '2' ,
					'desc_tip' => true ,
					'newids'   => 'rs_local_enable_disable_point_price_for_product' ,
					'type'     => 'select' ,
					'options'  => array(
						'1' => __( 'Enable' , 'rewardsystem' ) ,
						'2' => __( 'Disable' , 'rewardsystem' ) ,
					) ,
				) ,
				array(
					'name'    => __( 'Pricing Type' , 'rewardsystem' ) ,
					'id'      => 'rs_pricing_type_global_level' ,
					'std'     => '1' ,
					'default' => '1' ,
					'newids'  => 'rs_pricing_type_global_level' ,
					'type'    => 'select' ,
					'options' => array(
						'1' => __( 'Currency and Point Price' , 'rewardsystem' ) ,
						'2' => __( 'Only Point Price' , 'rewardsystem' ) ,
					) ,
				) ,
				array(
					'name'     => __( 'Point Price Type' , 'rewardsystem' ) ,
					'id'       => 'rs_global_point_price_type' ,
					'std'      => '2' ,
					'default'  => '2' ,
					'desc_tip' => true ,
					'newids'   => 'rs_global_point_price_type' ,
					'type'     => 'select' ,
					'options'  => array(
						'1' => __( 'Fixed' , 'rewardsystem' ) ,
						'2' => __( 'Based On Conversion' , 'rewardsystem' ) ,
					) ,
				) ,
				array(
					'name'    => __( 'Pricing in Point(s)' , 'rewardsystem' ) ,
					'id'      => 'rs_local_price_points_for_product' ,
					'class'   => 'rs_local_price_points_for_product' ,
					'std'     => '' ,
					'default' => '' ,
					'type'    => 'text' ,
					'newids'  => 'rs_local_price_points_for_product' ,
				) ,
				array( 'type' => 'sectionend' , 'id' => '_rs_global_Point_price' ) ,
				array(
					'type' => 'rs_wrapper_end' ,
				) ,
				array(
					'type' => 'rs_hide_bulk_update_for_point_price_start' ,
				) ,
				array(
					'type' => 'rs_wrapper_start' ,
				) ,
				array(
					'name' => __( 'Point Pricing Bulk Update Settings' , 'rewardsystem' ) ,
					'type' => 'title' ,
					'id'   => '_rs_update_point_priceing'
				) ,
				array(
					'name'    => __( 'Product/Category Selection' , 'rewardsystem' ) ,
					'id'      => 'rs_which_point_precing_product_selection' ,
					'std'     => '1' ,
					'class'   => 'rs_which_point_precing_product_selection' ,
					'default' => '1' ,
					'newids'  => 'rs_which_point_precing_product_selection' ,
					'type'    => 'select' ,
					'options' => array(
						'1' => __( 'All Products' , 'rewardsystem' ) ,
						'2' => __( 'Selected Products' , 'rewardsystem' ) ,
						'3' => __( 'All Categories' , 'rewardsystem' ) ,
						'4' => __( 'Selected Categories' , 'rewardsystem' ) ,
					) ,
				) ,
				array(
					'type' => 'selected_products_point' ,
				) ,
				array(
					'name'    => __( 'Select Particular Categories' , 'rewardsystem' ) ,
					'id'      => 'rs_select_particular_categories_for_point_price' ,
					'css'     => 'min-width:350px;' ,
					'std'     => '1' ,
					'class'   => 'rs_select_particular_categories_for_point_price' ,
					'default' => '1' ,
					'newids'  => 'rs_select_particular_categories_for_point_price' ,
					'type'    => 'multiselect' ,
					'options' => $categorylist ,
				) ,
				array(
					'name'    => __( 'Enable Point Pricing' , 'rewardsystem' ) ,
					'id'      => 'rs_local_enable_disable_point_price' ,
					'std'     => '2' ,
					'default' => '2' ,
					'newids'  => 'rs_local_enable_disable_point_price' ,
					'type'    => 'select' ,
					'options' => array(
						'1' => __( 'Enable' , 'rewardsystem' ) ,
						'2' => __( 'Disable' , 'rewardsystem' ) ,
					) ,
				) ,
				array(
					'name'     => __( 'Pricing Type' , 'rewardsystem' ) ,
					'id'       => 'rs_local_point_pricing_type' ,
					'std'      => '1' ,
					'default'  => '1' ,
					'desc_tip' => true ,
					'desc'     => __( 'Enable will Turn On Points Price for Product Purchase and Product Settings will be considered if it is available. '
							. 'Disable will Turn Off Points Price for Product Purchase and Product Settings will be considered if it is available.' , 'rewardsystem' ) ,
					'newids'   => 'rs_local_point_pricing_type' ,
					'type'     => 'select' ,
					'options'  => array(
						'1' => __( 'Currency & Point Price' , 'rewardsystem' ) ,
						'2' => __( 'Only Point Price' , 'rewardsystem' ) ,
					) ,
				) ,
				array(
					'name'     => __( 'Points Prices Type ' , 'rewardsystem' ) ,
					'id'       => 'rs_local_point_price_type' ,
					'std'      => '1' ,
					'default'  => '1' ,
					'desc_tip' => true ,
					'desc'     => __( 'Enable will Turn On Points Price for Product Purchase and Product Settings will be considered if it is available. '
							. 'Disable will Turn Off Points Price for Product Purchase and Product Settings will be considered if it is available.' , 'rewardsystem' ) ,
					'newids'   => 'rs_local_point_price_type' ,
					'type'     => 'select' ,
					'options'  => array(
						'1' => __( 'By Fixed' , 'rewardsystem' ) ,
						'2' => __( 'Based On Conversion' , 'rewardsystem' ) ,
					) ,
				) ,
				array(
					'name'        => __( 'By Fixed Points' , 'rewardsystem' ) ,
					'desc'        => __( 'Please Enter Price Points' , 'rewardsystem' ) ,
					'id'          => 'rs_local_price_points' ,
					'class'       => 'show_if_price_enable_in_update' ,
					'std'         => '' ,
					'default'     => '' ,
					'type'        => 'text' ,
					'newids'      => 'rs_local_price_points' ,
					'placeholder' => '' ,
					'desc'        => __( 'When left empty, Product Settings will be considered in the same order and Current Settings (Global Settings) will be ignored. '
							. 'When value greater than or equal to 0 is entered then Current Settings (Global Settings) will be considered and Global Settings will be ignored.' , 'rewardsystem' ) ,
					'desc_tip'    => true ,
				) ,
				array(
					'name'     => __( 'Test Button' , 'rewardsystem' ) ,
					'desc'     => __( 'This is for testing button' , 'rewardsystem' ) ,
					'id'       => 'rs_sumo_point_price_button' ,
					'std'      => '' ,
					'default'  => '' ,
					'type'     => 'button_point_price' ,
					'desc_tip' => true ,
					'newids'   => 'rs_sumo_point_price_button' ,
				) ,
				array( 'type' => 'sectionend' , 'id' => '_rs_update_point_priceing' ) ,
				array(
					'type' => 'rs_wrapper_end' ,
				) ,
				array(
					'type' => 'rs_hide_bulk_update_for_point_price_end' ,
				) ,
					) ) ;
		}

		/**
		 * Registering Custom Field Admin Settings of SUMO Reward Points in woocommerce admin fields funtion
		 */
		public static function reward_system_register_admin_settings() {
			woocommerce_admin_fields( self::reward_system_admin_fields() ) ;
		}

		/**
		 * Update the Settings on Save Changes may happen in SUMO Reward Points
		 */
		public static function reward_system_update_settings() {
			woocommerce_update_options( self::reward_system_admin_fields() ) ;
			if ( isset( $_REQUEST[ 'rs_select_particular_products' ] ) ) {
				update_option( 'rs_select_particular_products' , wc_clean(wp_unslash($_REQUEST[ 'rs_select_particular_products' ] ))) ;
			} else {
				update_option( 'rs_select_particular_products' , '' ) ;
			}
			if ( isset( $_REQUEST[ 'rs_select_particular_products_for_point_price' ] ) ) {
				update_option( 'rs_select_particular_products_for_point_price' , wc_clean(wp_unslash($_REQUEST[ 'rs_select_particular_products_for_point_price' ] ))) ;
			} else {
				update_option( 'rs_select_particular_products_for_point_price' , '' ) ;
			}
			if ( isset( $_REQUEST[ 'rs_point_price_module_checkbox' ] ) ) {
				update_option( 'rs_point_price_activated' , wc_clean(wp_unslash($_REQUEST[ 'rs_point_price_module_checkbox' ] ))) ;
			} else {
				update_option( 'rs_point_price_activated' , 'no' ) ;
			}
			if ( isset( $_REQUEST[ 'rs_include_products_for_point_pricing' ] ) ) {
				update_option( 'rs_include_products_for_point_pricing' , wc_clean(wp_unslash($_REQUEST[ 'rs_include_products_for_point_pricing' ] ) ));
			} else {
				update_option( 'rs_include_products_for_point_pricing' , '' ) ;
			}
			if ( isset( $_REQUEST[ 'rs_exclude_products_for_point_pricing' ] ) ) {
				update_option( 'rs_exclude_products_for_point_pricing' , wc_clean(wp_unslash($_REQUEST[ 'rs_exclude_products_for_point_pricing' ] ) ));
			} else {
				update_option( 'rs_exclude_products_for_point_pricing' , '' ) ;
			}
		}

		/**
		 * Initialize the Default Settings by looping this function
		 */
		public static function set_default_value() {
			foreach ( self::reward_system_admin_fields() as $setting ) {
				if ( isset( $setting[ 'newids' ] ) && isset( $setting[ 'std' ] ) ) {
					add_option( $setting[ 'newids' ] , $setting[ 'std' ] ) ;
				}
			}
		}

		public static function reset_point_price_module() {
			$settings = self::reward_system_admin_fields() ;
			RSTabManagement::reset_settings( $settings ) ;
		}

		public static function enable_module() {
			RSModulesTab::checkbox_for_module( get_option( 'rs_point_price_activated' ) , 'rs_point_price_module_checkbox' , 'rs_point_price_activated' ) ;
		}

		public static function rs_hide_bulk_update_for_point_price_start() {
			?>
			<div class="rs_hide_bulk_update_for_point_price_start">
				<?php
		}

		public static function rs_hide_bulk_update_for_point_price_end() {
			?>
			</div>
			<?php
		}

		public static function rs_select_products_to_update_point_price() {
			$field_id    = 'rs_select_particular_products_for_point_price' ;
			$field_label = esc_html__('Select Particular Products', 'rewardsystem') ;
			$getproducts = get_option( 'rs_select_particular_products_for_point_price' ) ;
			echo wp_kses_post(rs_function_to_add_field_for_product_select( $field_id , $field_label , $getproducts ) );
		}

		public static function rs_save_button_for_update_point_price() {
			?>
			<tr valign="top">
				<th class="titledesc" scope="row"></th>
				<td class="forminp forminp-select">
					<input type="submit" class="rs_sumo_point_price_button button-primary" value="<?php esc_html_e('Save and Update', 'rewardsystem'); ?>"/>
				</td>
			</tr>
			<?php
		}

		public static function rs_include_products_for_point_pricing() {
			$field_id    = 'rs_include_products_for_point_pricing' ;
			$field_label = 'Include Product(s)' ;
			$getproducts = get_option( 'rs_include_products_for_point_pricing' ) ;
			echo wp_kses_post(rs_function_to_add_field_for_product_select( $field_id , $field_label , $getproducts ) );
		}

		public static function rs_exclude_products_for_point_pricing() {
			$field_id    = 'rs_exclude_products_for_point_pricing' ;
			$field_label = 'Exclude Product(s)' ;
			$getproducts = get_option( 'rs_exclude_products_for_point_pricing' ) ;
			echo wp_kses_post(rs_function_to_add_field_for_product_select( $field_id , $field_label , $getproducts ) );
		}

	}

	RSPointPriceModule::init() ;
}
