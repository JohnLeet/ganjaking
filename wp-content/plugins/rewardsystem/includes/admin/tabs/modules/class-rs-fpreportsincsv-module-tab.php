<?php
/*
 * Reports in CSV Tab
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit ; // Exit if accessed directly.
}
if ( ! class_exists( 'RSReportsInCsv' ) ) {

	class RSReportsInCsv {

		public static function init() {
			add_action( 'woocommerce_rs_settings_tabs_fpreportsincsv' , array( __CLASS__ , 'reward_system_register_admin_settings' ) ) ; // Call to register the admin settings in the Reward System Submenu with general Settings tab        

			add_action( 'woocommerce_update_options_fprsmodules_fpreportsincsv' , array( __CLASS__ , 'reward_system_update_settings' ) ) ; // call the woocommerce_update_options_{slugname} to update the reward system                               

			add_action( 'rs_default_settings_fpreportsincsv' , array( __CLASS__ , 'set_default_value' ) ) ;

			add_action( 'woocommerce_admin_field_rs_select_users_report_in_csv' , array( __CLASS__ , 'selected_users_report_in_csv' ) ) ;

			add_action( 'woocommerce_admin_field_export_reports' , array( __CLASS__ , 'reward_system_page_customization_reports' ) ) ;

			add_action( 'admin_enqueue_scripts' , array( __CLASS__ , 'wp_enqueqe_for_datepicker' ) ) ;

			add_action( 'fp_action_to_reset_module_settings_fpreportsincsv' , array( __CLASS__ , 'reset_reports_in_csv_module' ) ) ;

			add_action( 'woocommerce_admin_field_rs_enable_disable_report_module' , array( __CLASS__ , 'enable_module' ) ) ;
		}

		/*
		 * Function label settings to Member Level Tab
		 */

		public static function reward_system_admin_fields() {

			return apply_filters( 'woocommerce_fpreportsincsv_settings' , array(
				array(
					'type' => 'rs_modulecheck_start' ,
				) ,
				array(
					'name' => __( 'Reports in CSV Module' , 'rewardsystem' ) ,
					'type' => 'title' ,
					'id'   => '_rs_activate_report_module'
				) ,
				array(
					'type' => 'rs_enable_disable_report_module' ,
				) ,
				array( 'type' => 'sectionend' , 'id' => '_rs_activate_report_module' ) ,
				array(
					'type' => 'rs_modulecheck_end' ,
				) ,
				array(
					'type' => 'rs_wrapper_start' ,
				) ,
				array(
					'name' => __( 'Reports in CSV Settings(CSV Exported from here cannot be Imported)' , 'rewardsystem' ) ,
					'type' => 'title' ,
					'id'   => '_rs_csvreport_setting'
				) ,
				array(
					'name'     => __( 'Export available Points for' , 'rewardsystem' ) ,
					'desc'     => __( 'Here you can set whether to Export Reward Points for All Users or Selected Users' , 'rewardsystem' ) ,
					'id'       => 'rs_export_user_report_option' ,
					'std'      => '1' ,
					'default'  => '1' ,
					'type'     => 'radio' ,
					'options'  => array( '1' => __('All Users', 'rewardsystem') , '2' => __('Selected Users', 'rewardsystem') ) ,
					'newids'   => 'rs_export_user_report_option' ,
					'desc_tip' => true ,
				) ,
				array(
					'name'     => __( 'Select the User(s) for whom you wish to Export Points' , 'rewardsystem' ) ,
					'desc'     => __( 'Here you select the users to whom you wish to Export Reward Points' , 'rewardsystem' ) ,
					'id'       => 'rs_export_users_report_list' ,
					'css'      => 'min-width:400px;' ,
					'std'      => '' ,
					'default'  => '' ,
					'type'     => 'rs_select_users_report_in_csv' ,
					'newids'   => 'rs_export_users_report_list' ,
					'desc_tip' => true ,
				) ,
				array(
					'name'     => __( 'Export User Points for' , 'rewardsystem' ) ,
					'desc'     => __( 'Here you can set whether to Export Reward Points for All Time or Selected Date' , 'rewardsystem' ) ,
					'id'       => 'rs_export_report_date_option' ,
					'class'    => 'rs_export_report_date_option' ,
					'std'      => '1' ,
					'default'  => '1' ,
					'type'     => 'radio' ,
					'options'  => array( '1' => __('All Time', 'rewardsystem') , '2' => __('Selected Date', 'rewardsystem' )) ,
					'newids'   => 'rs_export_report_date_option' ,
					'desc_tip' => true ,
				) ,
				array(
					'type' => 'export_reports' ,
				) ,
				array( 'type' => 'sectionend' , 'id' => '_rs_csvreport_setting' ) ,
				array(
					'type' => 'rs_wrapper_end' ,
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
			if ( isset( $_REQUEST[ 'rs_report_module_checkbox' ] ) ) {
				update_option( 'rs_report_activated' , wc_clean(wp_unslash($_REQUEST[ 'rs_report_module_checkbox' ] ) ));
			} else {
				update_option( 'rs_report_activated' , 'no' ) ;
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

		public static function enable_module() {
			RSModulesTab::checkbox_for_module( get_option( 'rs_report_activated' ) , 'rs_report_module_checkbox' , 'rs_report_activated' ) ;
		}

		public static function selected_users_report_in_csv() {
			$field_id    = 'rs_export_users_report_list' ;
			$field_label = __('Select the Users that you wish to Export Reward Points' , 'rewardsystem');
			$getuser     = get_option( 'rs_export_users_report_list' ) ;
			echo wp_kses_post(user_selection_field( $field_id , $field_label , $getuser ) );
		}

		public static function reward_system_page_customization_reports() {
			?>
			<tr valign="top">
				<th class="titledesc" scope="row">
					<label for="rs_point_export_report_start_date"><?php esc_html_e( 'Start Date' , 'rewardsystem' ) ; ?></label>
				</th>
				<td class="forminp forminp-select">
					<input type="text" class="rs_point_export_report_start_date" value="" name="rs_point_export_report_start_date" id="rs_point_export_report_start_date" />
				</td>
			</tr>
			<tr valign="top">
				<th class="titledesc" scope="row">
					<label for="rs_point_export_report_end_date"><?php esc_html_e( 'End Date' , 'rewardsystem' ) ; ?></label>
				</th>
				<td class="forminp forminp-select">
					<input type="text" class="rs_point_export_report_end_date" value="" name="rs_point_export_report_end_date" id="rs_point_export_report_end_date" />
				</td>
			</tr>
			<tr valign="top">
				<th class="titledesc" scope="row">
					<label for="rs_export_report_pointtype_option"><?php esc_html_e( 'Export User Points based on' , 'rewardsystem' ) ; ?></label>
				</th>
				<td class="forminp forminp-select">
					<input type="checkbox" class="rs_export_report_pointtype_option" value="1" name="rs_export_report_pointtype_option_earning" id="rs_export_report_pointtype_option_earning" /><?php esc_html_e( 'Total Earned Points' , 'rewardsystem' ) ; ?>
					<input type="checkbox" class="rs_export_report_pointtype_option" value="1" name="rs_export_report_pointtype_option_redeeming" id="rs_export_report_pointtype_option_redeeming" /><?php esc_html_e( 'Total Redeemed Points' , 'rewardsystem' ) ; ?>
					<input type="checkbox" class="rs_export_report_pointtype_option" value="1" name="rs_export_report_pointtype_option_total" id="rs_export_report_pointtype_option_total" checked="checked" /><?php esc_html_e( 'Available Points' , 'rewardsystem' ) ; ?>
				</td>
			</tr>
			<tr valign ="top">
				<th class="titledesc" scope="row">
					<label for="rs_export_user_points_report_csv"><?php esc_html_e( 'Export User Points Report as CSV' , 'rewardsystem' ) ; ?></label>
				</th>
				<td class="forminp forminp-select">
					<input type="button" id="rs_export_user_points_report_csv" class="rs_export_button" name="rs_export_user_points_report_csv" value="Export User Points Report"/>
				</td>
			</tr>
			<?php
			if ( isset( $_GET[ 'export_report' ] ) && 'yes' == wc_clean(wp_unslash($_GET[ 'export_report' ])) ) {
				ob_end_clean() ;
				header( 'Content-type: text/csv' ) ;
				header( 'Content-Disposition: attachment; filename=reward_points_report' . date_i18n( 'Y-m-d' ) . '.csv' ) ;
				header( 'Pragma: no-cache' ) ;
				header( 'Expires: 0' ) ;
				echo wp_kses_post(get_option( 'heading' ) );
				self::output_CSV_report( get_option( 'rs_export_report' ) ) ;
				exit() ;
			}
		}

		public static function output_CSV_report( $data ) {
			$output = fopen( 'php://output' , 'w' ) ;
			if ( is_array( $data ) && ! empty( $data ) ) {
				foreach ( $data as $row ) {
					if ( false != $row ) {
						fputcsv( $output , $row ) ; // here you can change delimiter/enclosure
					}
				}
			}
			fclose( $output ) ;
		}

		public static function wp_enqueqe_for_datepicker() {
			if (  '1' == get_option( 'rs_reward_point_enable_jquery' ) ) {
				if ( isset( $_GET[ 'page' ] ) && ( 'rewardsystem_callback' == wc_clean(wp_unslash($_GET[ 'page' ])) ) ) {
					if ( isset( $_GET[ 'rs_background_process' ] ) || ( isset( $_GET[ 'section' ] ) && 'fpmail' != wc_clean(wp_unslash($_GET[ 'section' ])) ) ) {
						wp_register_style( 'wp_reward_jquery_ui_css' , SRP_PLUGIN_DIR_URL . 'assets/css/jquery-ui.css', array(), SRP_VERSION ) ;
						wp_enqueue_script( 'wp_reward_jquery_ui' , SRP_PLUGIN_DIR_URL . 'assets/js/jquery-ui.js' , array( 'jquery' ) , SRP_VERSION ) ;
						wp_enqueue_style( 'wp_reward_jquery_ui_css' ) ;
					}
				}
			}
		}

		public static function reset_reports_in_csv_module() {
			$settings = self::reward_system_admin_fields() ;
			RSTabManagement::reset_settings( $settings ) ;
		}

	}

	RSReportsInCsv::init() ;
}
