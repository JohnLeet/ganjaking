<?php
/**
 * WooCommerce Google Analytics Pro
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade WooCommerce Google Analytics Pro to newer
 * versions in the future. If you wish to customize WooCommerce Google Analytics Pro for your
 * needs please refer to http://docs.woocommerce.com/document/woocommerce-google-analytics-pro/ for more information.
 *
 * @author      SkyVerge
 * @copyright   Copyright (c) 2015-2023, SkyVerge, Inc.
 * @license     http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

namespace SkyVerge\WooCommerce\Google_Analytics_Pro\Tracking\Events\Universal_Analytics;

use SkyVerge\WooCommerce\Google_Analytics_Pro\Tracking\Events\Universal_Analytics_Event;

defined( 'ABSPATH' ) or exit;

/**
 * The "signed up" event.
 *
 * @since 2.0.0
 */
class Signed_Up_Event extends Universal_Analytics_Event {


	/** @var string the event ID */
	public const ID = 'signed_up';

	/** @var string the event trigger action hook  */
	protected string $trigger_hook = 'user_register';


	/**
	 * @inheritdoc
	 */
	public function get_form_field_title(): string {

		return __( 'Signed Up', 'woocommerce-google-analytics-pro' );
	}


	/**
	 * @inheritdoc
	 */
	public function get_form_field_description(): string {

		return __( 'Triggered when a customer registers a new account.', 'woocommerce-google-analytics-pro' );
	}


	/**
	 * @inheritdoc
	 */
	public function get_default_name(): string {

		return 'signed up';
	}


	/**
	 * @inheritdoc
	 *
	 * @param int $user_id user ID
	 */
	public function track( $user_id = null ): void {

		$this->record_via_api( [ 'eventCategory' => 'My Account' ], [], [ 'uid' => $user_id ] );
	}


}
