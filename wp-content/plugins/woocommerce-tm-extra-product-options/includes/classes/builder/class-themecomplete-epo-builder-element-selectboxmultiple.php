<?php
/**
 * Multi-Select Field Element
 *
 * @package Extra Product Options/Classes/Builder
 * @version 6.4
 */

defined( 'ABSPATH' ) || exit;

/**
 * Multi-Select Field Element
 *
 * @package Extra Product Options/Classes/Builder
 * @version 6.4
 */
class THEMECOMPLETE_EPO_BUILDER_ELEMENT_SELECTBOXMULTIPLE extends THEMECOMPLETE_EPO_BUILDER_ELEMENT {

	/**
	 * Class Constructor
	 *
	 * @param string $name The element name.
	 * @since 6.0
	 */
	public function __construct( $name = '' ) {
		$this->element_name     = $name;
		$this->is_addon         = false;
		$this->namespace        = $this->elements_namespace;
		$this->name             = esc_html__( 'Multi-Select Field', 'woocommerce-tm-extra-product-options' );
		$this->description      = '';
		$this->width            = 'w100';
		$this->width_display    = '100%';
		$this->icon             = 'tcfa-caret-square-down';
		$this->is_post          = 'post';
		$this->type             = 'singlemultiple';
		$this->post_name_prefix = 'selectmultiple';
		$this->fee_type         = 'multiple';
		$this->tags             = 'price content';
		$this->show_on_backend  = true;
	}

	/**
	 * Initialize element properties
	 *
	 * @since 6.0
	 * @return void
	 */
	public function set_properties() {
		$this->properties = $this->add_element(
			$this->element_name,
			[
				'enabled',
				'required',
				'fee',
				'hide_amount',
				'text_before_price',
				'text_after_price',
				'quantity',
				'options',
			]
		);
	}
}
