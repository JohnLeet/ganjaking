<?php
/**
 * WC_CP_Min_Max_Compatibility class
 *
 * @package  WooCommerce Composite Products
 * @since    3.13.0
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Min/Max Quantities Compatibility.
 *
 * @version  8.6.0
 */
class WC_CP_Min_Max_Compatibility {

	/**
	 * The composited item object whose qty input is being filtered.
	 * @var WC_CP_Product
	 */
	public static $composited_item = false;

	/**
	 * Unfiltered quantity input data used at restoration time.
	 * @var array
	 */
	public static $unfiltered_args = false;

	public static function init() {

		// Set global $component variable.
		add_action( 'woocommerce_composited_product_single', array( __CLASS__, 'restore_quantities_set' ), 29 );

		// Unset global $component variable.
		add_action( 'woocommerce_composited_product_single', array( __CLASS__, 'restore_quantities_unset' ), 31 );

		// Restore composited items quantities to the values they had before min/max interference.
		add_filter( 'woocommerce_quantity_input_args', array( __CLASS__, 'save_quantity_input_args' ), 0, 2 );
		add_filter( 'woocommerce_quantity_input_args', array( __CLASS__, 'restore_quantity_input_args' ), 11, 2 );

		// Double-check variation data quantities to account for "group of" option.
		add_filter( 'woocommerce_available_variation', array( __CLASS__, 'composited_variation_data' ), 15, 3 );

		// Restore allowed min quantity for composited items to empty, so min/max cart validation rules do not apply.
		add_filter( 'wc_min_max_quantity_minimum_allowed_quantity', array( __CLASS__, 'restore_allowed_quantity' ), 10, 4 );

		// Restore allowed max quantity for composited items to empty, so min/max cart validation rules do not apply.
		add_filter( 'wc_min_max_quantity_maximum_allowed_quantity', array( __CLASS__, 'restore_allowed_quantity' ), 10, 4 );

		// Save component instance on the product.
		add_filter( 'woocommerce_cart_item_product', array( __CLASS__, 'add_component_to_product' ), 10, 3 );

		// Filter Min bundled item Quantity based on "Group of" option on runtime.
		add_filter( 'woocommerce_composited_item_quantity_min', array( __CLASS__, 'filter_composited_item_min_quantity' ), 10, 3 );

		// Filter Max bundled item Quantity based on "Group of" option on runtime.
		add_filter( 'woocommerce_composited_item_quantity_max', array( __CLASS__, 'filter_composited_item_max_quantity' ), 10, 3 );

		// Trigger Min/Max Quantities validation script when components load.
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'load_scripts' ), 15, 3 );
	}

	/**
	 * Set global $component variable.
	 *
	 * @param  WC_CP_Product  $option
	 * @return void
	 */
	public static function restore_quantities_set( $option ) {
		self::$composited_item = $option;
	}

	/**
	 * Unset global $componentvariable.
	 *
	 * @param  WC_CP_Product  $option
	 * @return void
	 */
	public static function restore_quantities_unset( $option ) {
		self::$composited_item = false;
	}

	/**
	 * Save unmodified quantity args.
	 *
	 * @param  array   $data
	 * @param  object  $product
	 * @return array
	 */
	public static function save_quantity_input_args( $data, $product ) {

		if ( is_object( self::$composited_item ) || isset( $product->wc_mmq_composited_item ) ) {
			self::$unfiltered_args = $data;
		} else {
			self::$unfiltered_args = false;
		}

		return $data;
	}

	/**
	 * Restore min/max composited product quantities to the values they had before min/max interference.
	 *
	 * @param  array   $data
	 * @param  object  $product
	 * @return array
	 */
	public static function restore_quantity_input_args( $data, $product ) {

		if ( is_array( self::$unfiltered_args ) ) {

			$min_qty      = 0;
			$max_qty      = '';
			$input_qty    = 1;
			$group_of_qty = 0;

			if ( isset( self::$unfiltered_args[ 'min_value' ] ) ) {
				if ( self::$unfiltered_args[ 'min_value' ] > 0 || self::$unfiltered_args[ 'min_value' ] === 0 ) {
					$min_qty = absint( self::$unfiltered_args[ 'min_value' ] );
				}
			} elseif ( isset( $data[ 'min_value' ] ) && ( $data[ 'min_value' ] > 0 || $data[ 'min_value' ] === 0 ) ) {
				$min_qty = absint( $data[ 'min_value' ] );
			}

			if ( isset( self::$unfiltered_args[ 'max_value' ] ) ) {
				if ( self::$unfiltered_args[ 'max_value' ] > 0 || self::$unfiltered_args[ 'max_value' ] === 0 ) {
					$max_qty = absint( self::$unfiltered_args[ 'max_value' ] );
				}
			} elseif ( isset( $data[ 'max_value' ] ) && ( $data[ 'max_value' ] > 0 || $data[ 'max_value' ] === 0 ) ) {
				$max_qty = absint( $data[ 'max_value' ] );
			}

			if ( isset( self::$unfiltered_args[ 'input_value' ] ) ) {
				$input_qty = absint( self::$unfiltered_args[ 'input_value' ] );
			} elseif ( isset( $data[ 'input_value' ] ) ) {
				$input_qty = absint( $data[ 'input_value' ] );
			}


			if ( ! isset( $product->wc_mmq_composited_item ) ) {
				// Single product page context.
				if ( isset( $data[ 'group_of' ] ) ) {
					$group_of_qty = $data[ 'group_of' ];
				} elseif ( $product instanceof WC_Product ) {
					$group_of_qty = self::get_composited_item_group_of_qty( self::$composited_item );
				}

				if ( $group_of_qty ) {
					$min_qty = WC_Min_Max_Quantities::adjust_min_quantity( $min_qty, $group_of_qty );
				}

				$input_qty = max( $input_qty, $min_qty );
			} else {

				// Cart context.
				$group_of_qty = self::get_composited_item_group_of_qty( $product->wc_mmq_composited_item ) * $product->container_quantity;

				if ( $group_of_qty ) {
					$min_qty = WC_Min_Max_Quantities::adjust_min_quantity( $min_qty, $group_of_qty );
				}
			}

			if ( empty( $max_qty ) || $max_qty >= $min_qty ) {
				$data[ 'min_value' ]   = $min_qty;
				$data[ 'max_value' ]   = $max_qty;
				$data[ 'input_value' ] = $input_qty;
				$data[ 'step' ]        = $group_of_qty;
			} else {
				$data[ 'min_value' ]   = $min_qty;
				$data[ 'max_value' ]   = $min_qty;
				$data[ 'input_value' ] = $min_qty;
				$data[ 'step' ]        = 1;
			}
		}
		return $data;
	}

	/**
	 * Double-check composited variation data quantities to account for "group of" option.
	 *
	 * @param  array                 $variation_data
	 * @param  WC_Product            $composited_product
	 * @param  WC_Product_Variation  $composited_variation
	 * @return array
	 */
	public static function composited_variation_data( $variation_data, $composited_product, $composited_variation ) {

		if ( ! isset( $variation_data[ 'is_composited' ] ) ) {
			return $variation_data;
		}

		if ( 'yes' === $composited_variation->get_meta( 'min_max_rules', true ) ) {
			$group_of_quantity = $composited_variation->get_meta( 'variation_group_of_quantity', true );
		} else {
			$group_of_quantity = $composited_product->get_meta( 'group_of_quantity', true );
		}

		if ( $group_of_quantity > 1 ) {

			$data = array(
				'group_of'    => absint( $group_of_quantity ),
				'min_value'   => $variation_data[ 'min_qty' ],
				'max_value'   => $variation_data[ 'max_qty' ],
				'input_value' => isset( $variation_data[ 'input_value' ] ) ? $variation_data[ 'input_value' ] : $variation_data[ 'min_qty' ]
			);

			self::$unfiltered_args = $data;

			$fixed_args = self::restore_quantity_input_args( $data, false );

			self::$unfiltered_args = false;

			$variation_data[ 'min_qty' ]     = $fixed_args[ 'min_value' ];
			$variation_data[ 'max_qty' ]     = $fixed_args[ 'max_value' ];
			$variation_data[ 'input_value' ] = $fixed_args[ 'input_value' ];
			$variation_data[ 'step' ]        = $fixed_args[ 'group_of' ];
		}

		return $variation_data;
	}

	/**
	 * Restore allowed min/max quantity for composited items to empty, so min/max cart validation rules do not apply.
	 *
	 * @param  string  $qty_meta
	 * @param  string  $checking_id
	 * @param  string  $cart_item_key
	 * @param  array   $cart_item
	 * @return array
	 */
	public static function restore_allowed_quantity( $qty_meta, $checking_id, $cart_item_key, $cart_item) {

		if ( wc_cp_is_composited_cart_item( $cart_item ) ) {
			$qty_meta = '';
		}

		return $qty_meta;
	}

	/**
	 * Add composited item and input cart quantity to the product.
	 *
	 * @param  WC_Product  $product
	 * @param  array       $cart_item
	 * @param  string      $cart_item_key
	 * @return WC_Product
	 */
	public static function add_component_to_product( $product, $cart_item, $cart_item_key ) {

		if ( wc_cp_is_composited_cart_item( $cart_item ) ) {

			if ( $composited_container_item = wc_cp_get_composited_cart_item_container( $cart_item ) ) {

				$composite = $composited_container_item[ 'data' ];

				if ( 'composite' === $composite->get_type() && $composite->has_component( $cart_item[ 'composite_item' ] ) ) {
					$product->wc_mmq_composited_item = $cart_item[ 'data' ];
					$product->container_quantity     = $composited_container_item[ 'quantity' ];
				}
			}
		}

		return $product;
	}

	/**
	 * Filter Min composited item Quantity based on "Group of" option on runtime.
	 *
	 * @param  mixed            $qty
	 * @param  WC_CP_Product    $composited_item
	 * @param  WC_CP_Component  $component
	 *
	 * @return mixed
	 */
	public static function filter_composited_item_min_quantity( $qty, $composited_item, $component ) {

		$group_of_quantity = self::get_composited_item_group_of_qty( $composited_item );

		if ( $group_of_quantity ) {
			$qty = WC_Min_Max_Quantities::adjust_min_quantity( $qty, $group_of_quantity );
		}

		return $qty;
	}

	/**
	 * Filter Max composited item Quantity based on "Group of" option on runtime.
	 *
	 * @param  mixed            $qty
	 * @param  WC_CP_Product    $composited_item
	 * @param  WC_CP_Component  $component
	 *
	 * @return mixed
	 */
	public static function filter_composited_item_max_quantity( $qty, $composited_item, $component ) {

		$group_of_quantity = self::get_composited_item_group_of_qty( $composited_item );

		if ( $group_of_quantity ) {
			$qty = WC_Min_Max_Quantities::adjust_max_quantity( $qty, $group_of_quantity );
		}

		return $qty;
	}

	/**
	 * Returns the "Group of" quantity of a composited item.
	 *
	 * @param  WC_CP_Product  $composited_item
	 *
	 * @return int
	 */
	public static function get_composited_item_group_of_qty( $composited_item ) {

		$group_of_quantity = 0;

		if ( is_a( $composited_item, 'WC_Product' ) ) {
			$product = $composited_item;
		} else {
			$product = $composited_item->get_product();

		}

		if ( $product->is_type( 'simple' ) ) {
			$group_of_quantity = absint( $product->get_meta( 'group_of_quantity', true ) );
		}

		if ( $product->is_type( 'variation' ) ) {
			if ( 'yes' === $product->get_meta( 'min_max_rules', true ) ) {
				$group_of_quantity = absint( $product->get_meta( 'variation_group_of_quantity', true ) );
			} else {
				$parent_variable   = wc_get_product( $product->get_parent_id() );
				$group_of_quantity = absint( $parent_variable->get_meta( 'group_of_quantity', true ) );
			}
		}

		/**
		 * 'woocommerce_composited_item_group_of_qty' filter.
		 *
		 * @param  int            $group_of_quantity
		 * @param  WC_CP_Product  $composited_item
		 */
		return apply_filters( 'woocommerce_composited_item_group_of_qty', $group_of_quantity, $composited_item );
	}

	/**
	 * Trigger Min/Max Quantities validation script when components load.
	 */
	public static function load_scripts() {
		wp_add_inline_script( 'wc-add-to-cart-composite',
				"
					 jQuery( 'body .component' ).on( 'wc-composite-component-loaded', function () {
						jQuery(this)
							.find( '.cart:not( .cart_group )' )
							.each(function () {
								jQuery( 'body' ).trigger( 'wc-mmq-init-validation', [ jQuery(this) ] );
							});
					});
				"
		);
	}
}

WC_CP_Min_Max_Compatibility::init();
