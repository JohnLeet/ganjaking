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

use SkyVerge\WooCommerce\Google_Analytics_Pro\Tracking;
use SkyVerge\WooCommerce\Google_Analytics_Pro\Tracking\Events\Universal_Analytics_Event;

defined( 'ABSPATH' ) or exit;

/**
 * The "tracked order" event.
 *
 * @since 2.0.0
 */
class Tracked_Order_Event extends Universal_Analytics_Event {


	/** @var string the event ID */
	public const ID = 'tracked_order';

	/** @var string the event trigger action hook  */
	protected string $trigger_hook = 'woocommerce_track_order';


	/**
	 * @inheritdoc
	 */
	public function get_form_field_title(): string {

		return  __( 'Tracked Order', 'woocommerce-google-analytics-pro' );
	}


	/**
	 * @inheritdoc
	 */
	public function get_form_field_description(): string {

		return __( 'Triggered when a customer tracks an order.', 'woocommerce-google-analytics-pro' );
	}


	/**
	 * @inheritdoc
	 */
	public function get_default_name(): string {

		return 'tracked order';
	}


	/**
	 * @inheritdoc
	 *
	 * @param int $order_id ID of the order being tracked.
	 */
	public function track( $order_id = null ): void {

		$order = wc_get_order( $order_id );

		$this->record_via_api( [
			'eventCategory' => 'Orders',
			'eventLabel'    => $order->get_order_number(),
		] );
	}


}
