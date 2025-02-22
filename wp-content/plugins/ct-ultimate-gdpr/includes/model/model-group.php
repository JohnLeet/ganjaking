<?php

/**
 * Class CT_Ultimate_GDPR_Model_Group
 */
class CT_Ultimate_GDPR_Model_Group {

	/**
	 *
	 */
	const LEVEL_BLOCK_ALL = 1;
	/**
	 *
	 */
	const LEVEL_NECESSARY = 2;
	/**
	 *
	 */
	const LEVEL_CONVENIENCE = 3;
	/**
	 *
	 */
	const LEVEL_STATISTICS = 4;
	/**
	 *
	 */
	const LEVEL_TARGETTING = 5;
	/**
	 *
	 */
	const LEVEL_PRIVATE_DATA = 6;
 
	/**
	 * @var array
	 */
	protected $levels;

	/**
	 * new levels for granular cookie level => [values]
	 */
	public static $level_id = [
		1 => ['1'],
		2 => ['5'],
		3 => ['5','6'],
		4 => ['5','6','7'],
		5 => ['5','6','7','8'],
		6 =>  ['6'],
		7 =>  ['7'],
		8 =>  ['8'],
		9 =>  ['5','7'],
		10 => ['5','8'],
		11 => ['6','7'],
		12 => ['6','8'],
		13 => ['7','8'],
		14 => ['5','7','8'],
		15 => ['5','6','8'],
		16 => ['6','7','8']
	];

	/**
	 * @param $level
	 *
	 * @return mixed
	 */
	public static function get_label( $level ) {

		$admin = CT_Ultimate_GDPR::instance()->get_admin_controller();

		switch ( $level ) :

			case self::LEVEL_NECESSARY:

				$label = __( 'Essential', 'ct-ultimate-gdpr' );
				$label = $admin->get_option_value_escaped( 'cookie_group_popup_label_essentials', $label, CT_Ultimate_GDPR_Controller_Cookie::ID );

				break;

			case self::LEVEL_CONVENIENCE:

				$label = __( 'Functionality', 'ct-ultimate-gdpr' );
				$label = $admin->get_option_value_escaped( 'cookie_group_popup_label_functionality', $label, CT_Ultimate_GDPR_Controller_Cookie::ID );
				break;

			case self::LEVEL_STATISTICS:

				$label = __( 'Analytics', 'ct-ultimate-gdpr' );
				$label = $admin->get_option_value_escaped( 'cookie_group_popup_label_analytics', $label, CT_Ultimate_GDPR_Controller_Cookie::ID );
				break;

			case self::LEVEL_TARGETTING:

				$label = __( 'Advertising', 'ct-ultimate-gdpr' );
				$label = $admin->get_option_value_escaped( 'cookie_group_popup_label_advertising', $label, CT_Ultimate_GDPR_Controller_Cookie::ID );
				break;

			case self::LEVEL_BLOCK_ALL:

				$label = __( 'Block all cookies with personal data', 'ct-ultimate-gdpr' );
				$label = $admin->get_option_value_escaped( 'cookie_group_popup_label_block_all', $label, CT_Ultimate_GDPR_Controller_Cookie::ID );
				break;

			default:
				$label = '';

		endswitch;

		return apply_filters( 'ct_ultimate_gdpr_model_group_get_label', $label, $level );
	}

	/**
	 * @return int
	 */
	public function get_level_block_all(  ) {
		return self::LEVEL_BLOCK_ALL;
	}

	/**
	 * @return int
	 */
	public function get_level_necessary(  ) {
		return self::LEVEL_NECESSARY;
	}

	/**
	 * @return int
	 */
	public function get_level_convenience(  ) {
		return self::LEVEL_CONVENIENCE;
	}

	/**
	 * @return int
	 */
	public function get_level_statistics(  ) {
		return self::LEVEL_STATISTICS;
	}

	/**
	 * @return int
	 */
	public function get_level_targetting(  ) {
		return self::LEVEL_TARGETTING;
	}

	/**
	 * @return array
	 */
	public static function get_all_labels() {
		$labels = array(
			self::LEVEL_NECESSARY => __( 'Essential', 'ct-ultimate-gdpr' ),
			self::LEVEL_CONVENIENCE => __( 'Functionality', 'ct-ultimate-gdpr' ),
			self::LEVEL_STATISTICS => __( 'Analytics', 'ct-ultimate-gdpr' ),
			self::LEVEL_TARGETTING => __( 'Advertising', 'ct-ultimate-gdpr' ),
			self::LEVEL_BLOCK_ALL => __( 'Block all cookies with personal data', 'ct-ultimate-gdpr' ),

		);
		return $labels;
	}

	/**
	 * CT_Ultimate_GDPR_Model_Services constructor.
	 */
	public function __construct() {
		$this->levels = array();
	}

	/**
	 * @param $level
	 *
	 * @return $this
	 */
	public function add_level( $level ) {

		switch ( $level ) :

			case self::LEVEL_CONVENIENCE:
			case self::LEVEL_NECESSARY:
			case self::LEVEL_TARGETTING:
			case self::LEVEL_STATISTICS:
				$this->levels[ $level ] = $level;

		endswitch;

		return $this;
	}

	/**
	 * @param $levels
	 *
	 * @return $this
	 */
	public function add_levels( $levels ) {

		if ( is_array( $levels ) ) {

			foreach ( $levels as $level ) {
				$this->add_level( $level );
			}

		}

		return $this;
	}

	/**
	 * @param $level
	 *
	 * @return bool
	 */
	public function is_level( $level ) {

		return isset( $this->levels[ $level ] );

	}

	/**
	 * @param $level
	 *
	 * @return bool
	 */
	public function is_level_or_lower( $level ) {

		for ( $i = 1; $i <= $level; $i ++ ) {

			if ( $this->is_level( $i ) ) {
				return true;
			}

		}

		return false;

	}

	/**
	 * @param $level
	 *
	 * @return bool
	 */
	public function is_level_or_higher( $level ) {

		for ( $i = $level; $i <= self::LEVEL_TARGETTING; $i ++ ) {

			if ( $this->is_level( $i ) ) {
				return true;
			}

		}

		return false;

	}

	/**
	 * @return int
	 */
	public function get_current_level() {
		$cookie = CT_Ultimate_GDPR::instance()->get_controller_by_id( CT_Ultimate_GDPR_Controller_Cookie::ID );

		return $cookie ? $cookie->get_group_level() : self::LEVEL_CONVENIENCE;
	}

	/**
	 * @return string
	 */
	public static function is_level_checked($level, $id) {

		$arr = [];
		$level_id = self::$level_id;

		if( array_key_exists($level, $level_id) ) {

			foreach( $level_id as $k => $v) {
			
				if($level == $k) {
					$arr = $v;
				}	 
			}

			if( in_array($id, $arr) ) {
				return 'checked';
			}
		}
	}

	/**
	 * @return string
	 */
	public static function is_level_checked_active($level, $id) {

		$arr = [];
		$level_id = self::$level_id;

		if( array_key_exists($level, $level_id) ) {

			foreach( $level_id as $k => $v) {
			
				if($level == $k) {
					$arr = $v;
				}	 
			}

			if( in_array($id, $arr) ) {
				return true;
			}
		}
	}

}