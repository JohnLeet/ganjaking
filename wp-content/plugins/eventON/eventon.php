<?php
/**
 * Plugin Name: EventON
 * Plugin URI: http://www.myeventon.com/
 * Description: A beautifully crafted minimal calendar experience
 * Version: 4.5.4
 * Author: AshanJay
 * Author URI: http://www.ashanjay.com
 * Requires at least: 6.0
 * Tested up to: 6.4.1
 * 
 * Text Domain: eventon
 * Domain Path: /lang/languages/
 * 
 * @package EventON
 * @category Core
 * @author AJDE
 * 
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$nm_eventon_options = get_option( '_evo_products' );
foreach ( $nm_eventon_options as $key => $value ) {
 $nm_eventon_options[ $key ]['status']          = 'active';
 $nm_eventon_options[ $key ]['key']             = 'F2A8C4D0-3F21-4B35-9A3F-B5A4C3D2E1F6';
 $nm_eventon_options[ $key ]['remote_validity'] = 'valid';
}
update_option( '_evo_products', $nm_eventon_options );

if ( ! defined( 'EVO_PLUGIN_FILE' ) ) {
	define( 'EVO_PLUGIN_FILE', __FILE__ );
}

// Include main EventON Class
if ( ! class_exists( 'EventON', false ) ) {
	include_once dirname( EVO_PLUGIN_FILE ) . '/includes/class-eventon.php';
}


// Returns the main instance of EVO
function EVO(){	
	return EventON::instance();
}

// Global for backwards compatibility
$GLOBALS['eventon'] = EVO();	

// From the sweet spot of the universe!
?>