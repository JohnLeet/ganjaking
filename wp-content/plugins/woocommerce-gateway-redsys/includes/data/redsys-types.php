<?php
/**
 * Copyright: (C) 2013 - 2023 José Conti
 *
 * @package WooCommerce Redsys Gateway.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Copyright: (C) 2013 - 2023 José Conti
 */
function redsys_return_types() {

	return array(
		'redsys',
		'masterpass',
		'redsysbank',
		'bizumredsys',
		'iupay',
		'insite',
		'preauthorizationsredsys',
		'directdebitredsys',
		'webserviceredsys',
		'paygold',
	);
}
