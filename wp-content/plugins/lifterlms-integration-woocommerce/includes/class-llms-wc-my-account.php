<?php
/**
 * Handle additions to the WC My Account page.
 *
 * @package LifterLMS_WooCommerce/Classes
 *
 * @since 2.0.1
 * @version 2.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_WC_My_Account class.
 *
 * @since 2.0.1
 * @since 2.0.10 Add endpoint rewrite handler.
 */
class LLMS_WC_My_Account {

	/**
	 * Constructor.
	 *
	 * @since 2.0.1
	 * @since 2.0.10 Add endpoint rewrite handler.
	 */
	public function __construct() {

		add_action( 'wp', array( $this, 'add_actions' ) );
		add_filter( 'lifterlms_get_endpoint_url', array( $this, 'modify_endpoint_urls' ), 10, 2 );

	}

	/**
	 * Add actions after WP setup.
	 *
	 * @since 2.0.1
	 *
	 * @return void
	 */
	public function add_actions() {

		if ( is_account_page() ) {

			// Disables pagination rewriting on the Student Dashboard.
			add_filter( 'llms_modify_dashboard_pagination_links_disable', '__return_true' );

		}

	}

	/**
	 * Modify LifterLMS Dashboard Endpoint URLs
	 *
	 * @since 2.0.10
	 *
	 * @param string $url Endpoint url.
	 * @param string $endpoint Endpoint query var.
	 * @return string
	 */
	public function modify_endpoint_urls( $url, $endpoint ) {

		// Resolve WC 3.6 & later conflict causing LifterLMS Dashboard lost password endpoint to stop working.
		if ( 'lost-password' === $endpoint ) {
			$url = wc_lostpassword_url();
		}
		return $url;

	}

}

return new LLMS_WC_My_Account();
