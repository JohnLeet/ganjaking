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
 * The "viewed account" event.
 *
 * @since 2.0.0
 */
class Viewed_Account_Event extends Universal_Analytics_Event {


	/** @var string the event ID */
	public const ID = 'viewed_account';

	/** @var string the event trigger action hook  */
	protected string $trigger_hook = 'woocommerce_after_my_account';


	/**
	 * @inheritdoc
	 */
	public function get_form_field_title(): string {

		return __( 'Viewed Account', 'woocommerce-google-analytics-pro' );
	}


	/**
	 * @inheritdoc
	 */
	public function get_form_field_description(): string {

		return __( 'Triggered when a customer views the My Account page.', 'woocommerce-google-analytics-pro' );
	}


	/**
	 * @inheritdoc
	 */
	public function get_default_name(): string {

		return 'viewed account';
	}


	/**
	 * @inheritdoc
	 */
	public function track(): void {

		if ( Tracking::not_page_reload() ) {

			$this->get_frontend_handler_instance()->js_record_event( $this->get_name(), [
				'eventCategory'  => 'My Account',
				'nonInteraction' => true,
			] );
		}
	}


}
