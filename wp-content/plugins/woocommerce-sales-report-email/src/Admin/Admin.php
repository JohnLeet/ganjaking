<?php
/**
 * WooCommerce Sales Report Email Admin.
 *
 * @since 1.2.0
 */

namespace Themesquad\WC_Sales_Report_Email\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin {

	/**
	 * Admin init.
	 *
	 * @since 1.2.0
	 */
	public static function init() {
		add_filter( 'plugin_action_links_' . WC_SALES_REPORT_EMAIL_BASENAME, array( __CLASS__, 'action_links' ) );
		add_filter( 'plugin_row_meta', array( __CLASS__, 'plugin_row_meta' ), 10, 2 );
	}

	/**
	 * Adds custom links to the plugins page.
	 *
	 * @since 1.2.0
	 *
	 * @param array $links The plugin links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$settings_link = sprintf(
			'<a href="%1$s" aria-label="%2$s">%3$s</a>',
			esc_url( admin_url( 'admin.php?page=wc-settings&tab=email&section=wc_sre_sales_report_email' ) ),
			_x( 'View WooCommerce Sales Report Email settings', 'aria-label: settings link', 'woocommerce-sales-report-email' ),
			_x( 'Settings', 'plugin action link', 'woocommerce-sales-report-email' )
		);

		array_unshift( $links, $settings_link );

		return $links;
	}

	/**
	 * Adds custom links to this plugin on the plugins screen.
	 *
	 * @since 1.2.0
	 *
	 * @param mixed $links Plugin Row Meta.
	 * @param mixed $file  Plugin Base file.
	 * @return array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if ( WC_SALES_REPORT_EMAIL_BASENAME !== $file ) {
			return $links;
		}

		$links['docs'] = sprintf(
			'<a href="%1$s" aria-label="%2$s" target="_blank">%3$s</a>',
			esc_url( 'https://woocommerce.com/products/woocommerce-sales-report-email/' ),
			esc_attr_x( 'View WooCommerce Sales Report Email documentation', 'aria-label: documentation link', 'woocommerce-sales-report-email' ),
			esc_html_x( 'Docs', 'plugin row link', 'woocommerce-sales-report-email' )
		);

		$links['support'] = sprintf(
			'<a href="%1$s" aria-label="%2$s" target="_blank">%3$s</a>',
			esc_url( 'https://woocommerce.com/my-account/create-a-ticket?select=473579' ),
			esc_attr_x( 'Open a support ticket at WooCommerce.com', 'aria-label: support link', 'woocommerce-sales-report-email' ),
			esc_html_x( 'Support', 'plugin row link', 'woocommerce-sales-report-email' )
		);

		return $links;
	}
}
