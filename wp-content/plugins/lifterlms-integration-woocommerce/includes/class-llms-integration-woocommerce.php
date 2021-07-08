<?php
/**
 * LifterLMS WooCommerce Integration Class
 *
 * @package LifterLMS_WooCommerce/Classes
 *
 * @since 1.0.0
 * @version 2.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_Integration_WooCommerce class.
 *
 * @since 1.0.0
 */
class LLMS_Integration_WooCommerce extends LLMS_Abstract_Integration {

	/**
	 * Integration ID
	 *
	 * @var string
	 */
	public $id = 'woocommerce';

	/**
	 * Available endpoints.
	 *
	 * @var array
	 */
	private $endpoints = array();

	/**
	 * Integration Constructor
	 *
	 * @since    1.0.0
	 * @version  2.0.0
	 */
	public function configure() {

		$this->title       = __( 'WooCommerce', 'lifterlms-woocommerce' );
		$this->description = __( 'Sell LifterLMS Courses and Memberships using WooCommerce', 'lifterlms-woocommerce' );
		// Translators: %1$s = opening anchor tag; %2$s = closing anchor tag.
		$this->description_missing = sprintf( __( 'You need to install the %1$sWooCommerce core%2$s plugin to use this integration.', 'lifterlms-woocommerce' ), '<a href="https://wordpress.org/plugins/woocommerce/" target="_blank">', '</a>' );

		add_action( 'lifterlms_settings_save_integrations', array( $this, 'after_settings_save' ), 10 );

		if ( $this->is_available() ) {

			add_action( 'init', array( $this, 'add_account_endpoints' ) );

			add_filter( 'query_vars', array( $this, 'add_account_query_vars' ), 0 );
			add_filter( 'woocommerce_account_menu_items', array( $this, 'add_account_menu_item' ) );

			// output message on core settings checkout pages.
			add_action( 'lifterlms_sections_checkout', array( $this, 'checkout_settings_message' ) );

			// hide the LLMS Core Payment Gateway notice when WC is active.
			add_filter( 'llms_admin_notice_no_payment_gateways', '__return_true' );

		}
	}

	/**
	 * Add LLMS page endpoint accessed via WC My ACcount Page
	 *
	 * @return   void
	 * @since    1.0.0
	 * @version  1.3.6
	 */
	public function add_account_endpoints() {

		$this->populate_account_endpoints();

		foreach ( $this->get_account_endpoints() as $endpoint ) {

			add_rewrite_endpoint( $endpoint['endpoint'], EP_ROOT | EP_PAGES );
			add_action( 'woocommerce_account_' . $endpoint['endpoint'] . '_endpoint', array( $this, $endpoint['content'] ) );

		}

	}

	/**
	 * Add an order note for enrollment/unenrollment actions based on status changes
	 *
	 * @param    obj    $order       WC_Order object.
	 * @param    int    $product_id  WP_Post ID of a course or membership.
	 * @param    string $type        note type [enrollment|unenrollment].
	 * @since    1.3.0
	 * @version  1.3.0
	 */
	public function add_order_note( $order, $product_id, $type = 'enrollment' ) {

		/**
		 * Filter: llms_wc_add_{$type}_notes
		 *
		 * Determine whether or not notes should be recorded on a WC Order when enrollment or unenrollment occurs.
		 * {$type} can be either "enrollment" or "unenrollment".
		 *
		 * @since    1.3.0
		 * @version  1.3.0
		 *
		 * @param  bool $bool Whether or not to record notes. Defaults to true.
		 */
		if ( apply_filters( 'llms_wc_add_' . $type . '_notes', true ) ) {

			$product = llms_get_post( $product_id );
			if ( is_a( $product, 'WP_Post' ) ) {
				return;
			}

			switch ( $type ) {

				case 'enrollment':
					// Translators: %1$s = course/membership title; %2$s = course or membership name/label.
					$msg = __( 'Customer was enrolled into the "%1$s" %2$s.', 'lifterlms-woocommerce' );
					break;

				case 'unenrollment':
					// Translators: %1$s = course/membership title; %2$s = course or membership name/label.
					$msg = __( 'Customer was unenrolled from the "%1$s" %2$s.', 'lifterlms-woocommerce' );
					break;

			}

			$order->add_order_note( sprintf( $msg, $product->get( 'title' ), strtolower( $product->get_post_type_label() ) ) );

		}

	}

	/**
	 * Add LLMS page links to the WC My Account Page
	 *
	 * @param    array $items  array of existing menu items.
	 * @since    1.0.0
	 * @version  1.3.6
	 */
	public function add_account_menu_item( $items ) {

		$logout = array();

		if ( isset( $items['customer-logout'] ) ) {

			$logout = array(
				'customer-logout' => $items['customer-logout'],
			);
			unset( $items['customer-logout'] );
		}

		$endpoints = array();

		foreach ( $this->get_account_endpoints() as $endpoint ) {
			$endpoints[ $endpoint['endpoint'] ] = $endpoint['title'];
		}

		$items = array_merge( $items, $endpoints, $logout );

		return $items;
	}

	/**
	 * Add LLMS query vars for the pages accessible via WC ACcount page
	 *
	 * @param    array $vars  existing query vars.
	 * @since    1.0.0
	 * @version  1.3.6
	 */
	public function add_account_query_vars( $vars ) {

		foreach ( $this->get_account_endpoints() as $endpoint ) {
			array_push( $vars, $endpoint['endpoint'] );
		}

		return $vars;

	}

	/**
	 * After saving settings, Flush rewrite rules
	 *
	 * @return   void
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function after_settings_save() {

		// only run actions on WC integration settings page.
		$section = filter_input( INPUT_GET, 'section', FILTER_SANITIZE_STRING );
		if ( $section && $section === $this->id ) {
			$this->add_account_endpoints();
		} else {
			return;
		}

		// this needs to run again because of the order with which options are set.
		if ( ! $this->is_available() ) {
			return;
		}

		flush_rewrite_rules();

	}

	/**
	 * Outputs a message on LifterLMS Core checkout / gateway settings screens
	 * to help orient users to the correct settings to use (WC settings) when integration is enabled
	 *
	 * @return   void
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function checkout_settings_message() {
		$settings   = admin_url( 'admin.php?page=wc-settings&tab=checkout' );
		$extensions = admin_url( 'admin.php?page=wc-addons&section=payment-gateways' );
		?>
		<div class="notice notice-info">
			<p>
			<?php
			printf(
				// Translators: %1$s = opening anchor tag; %2$s opening anchor tag; %3$s = closing anchor tag.
				__(
					'It looks like you\'re using WooCommerce for checkout. When using WooCommerce these LifterLMS settings do not apply, instead, use the equivalent settings on the %1$sWooCommerce settings panel%3$s and install and configure %2$sWooCommerce payment gateways%3$s.',
					'lifterlms-woocommerce'
				),
				'<a href="' . esc_url( $settings ) . '">',
				'<a href="' . esc_url( $extensions ) . '">',
				'</a>'
			);
			?>
			</p>
		</div>
		<?php
	}

	/**
	 * Get a list of custom endpoints to add to WC My Account page
	 *
	 * @param    bool $active_only  if true, returns only active endpoints.
	 * @return   array
	 * @since    1.0.0
	 * @version  2.0.0
	 */
	public function get_account_endpoints( $active_only = true ) {

		$endpoints = $this->endpoints;

		if ( $active_only ) {

			$active = $this->get_option( 'account_endpoints', array_keys( $endpoints ) );
			// if no endpoints are saved an empty string is returnde and we need an array for the comparison below.
			if ( '' === $active ) {
				return array();
			}
			foreach ( array_keys( $endpoints ) as $endpoint ) {
				if ( ! in_array( $endpoint, $active, true ) ) {
					unset( $endpoints[ $endpoint ] );
				}
			}
		}

		return $endpoints;

	}

	/**
	 * Retrieve integration settings
	 *
	 * @return   array
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function get_integration_settings() {

		$settings = array();

		if ( $this->is_available() ) {

			if ( function_exists( 'wc_get_order_statuses' ) ) {

				$settings[] = array(
					'class'   => 'llms-select2',
					// Translators: %1$s = opening anchor tag; %2$s = closing anchor tag.
					'desc'    => '<br>' . sprintf( __( 'Customers will be enrolled when a WooCommerce Order reaches this status. See how to create products which will automatically complete orders %1$shere%2$s.', 'lifterlms-woocommerce' ), '<a href="https://lifterlms.com/docs/woocommerce-automatic-order-completion/" target="_blank">', '</a>' ),
					'default' => 'wc-completed',
					'id'      => $this->get_option_name( 'enrollment_status' ),
					'options' => wc_get_order_statuses(),
					'type'    => 'select',
					'title'   => __( 'Order Enrollment Status', 'lifterlms-woocommerce' ),
				);

				$settings[] = array(
					'class'   => 'llms-select2',
					'desc'    => '<br>' . __( 'Customers will be unenrolled when a WooCommerce Order reaches any of these statuses', 'lifterlms-woocommerce' ),
					'default' => array( 'wc-refunded', 'wc-cancelled', 'wc-failed' ),
					'id'      => $this->get_option_name( 'unenrollment_statuses' ),
					'options' => wc_get_order_statuses(),
					'type'    => 'multiselect',
					'title'   => __( 'Order Unenrollment Status', 'lifterlms-woocommerce' ),
				);

			}

			$subs_available = function_exists( 'wcs_get_subscription_statuses' );

			if ( ! $subs_available ) {

				$settings[] = array(
					'type'  => 'custom-html',
					// Translators: %1$s = opening anchor tag; %2$s = closing anchor tag.
					'value' => '<em>' . sprintf( __( 'Install the %1$sWooCommerce Subscriptions%2$s extension to create recurring subscriptions or payment plans for your course and memberships.', 'lifterlms-woocommerce' ), '<a href="https://woocommerce.com/products/woocommerce-subscriptions/" target="_blank">', '</a>' ) . '</em>',
				);

			}

			$settings[] = array(
				'class'    => 'llms-select2',
				'desc'     => '<br>' . __( 'Customers will be enrolled when a WooCommerce Subscription reaches this status', 'lifterlms-woocommerce' ),
				'default'  => 'wc-active',
				'disabled' => ( ! $subs_available ),
				'id'       => $this->get_option_name( 'subscription_enrollment_status' ),
				'options'  => $subs_available ? wcs_get_subscription_statuses() : array(),
				'type'     => 'select',
				'title'    => __( 'Subscription Enrollment Status', 'lifterlms-woocommerce' ),
			);

			$settings[] = array(
				'class'    => 'llms-select2',
				'desc'     => '<br>' . __( 'Customers will be unenrolled when a WooCommerce Subscription reaches any of these statuses', 'lifterlms-woocommerce' ),
				'default'  => array( 'wc-cancelled', 'wc-expired', 'wc-on-hold' ),
				'disabled' => ( ! $subs_available ),
				'id'       => $this->get_option_name( 'subscription_unenrollment_statuses' ),
				'options'  => $subs_available ? wcs_get_subscription_statuses() : array(),
				'type'     => 'multiselect',
				'title'    => __( 'Subscription Unenrollment Status', 'lifterlms-woocommerce' ),
			);

			$endpoints = $this->get_account_endpoints( false );

			$display_eps = array();

			foreach ( $endpoints as $ep_key => $endpoint ) {
				$display_eps[ $ep_key ] = $endpoint['title'];
			}

			$settings[] = array(
				'class'   => 'llms-select2',
				'desc'    => '<br>' . __( 'The following LifterLMS Student Dashboard areas will be added to the WooCommerce My Account Page', 'lifterlms-woocommerce' ),
				'default' => array_keys( $display_eps ),
				'id'      => $this->get_option_name( 'account_endpoints' ),
				'options' => $display_eps,
				'type'    => 'multiselect',
				'title'   => __( 'My Account Endpoints', 'lifterlms-woocommerce' ),
			);

			$settings[] = array(
				'desc'         => __( 'Enable debug logging', 'lifterlms-woocommerce' ),
				// Translators: %s = log file path.
				'desc_tooltip' => sprintf( __( 'When enabled, debugging information will be logged to "%s"', 'lifterlms-woocommerce' ), llms_get_log_path( 'woocommerce' ) ),
				'id'           => $this->get_option_name( 'logging_enabled' ),
				'title'        => __( 'Debug Log', 'lifterlms-woocommerce' ),
				'type'         => 'checkbox',
			);

		}

		return $settings;

	}

	/**
	 * Retrieve the option prefix for the integration
	 * Overrides the defaults from core to prevent the necessity of an options migration
	 *
	 * @return   string
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	protected function get_option_prefix() {
		return 'lifterlms_woocommerce_';
	}

	/**
	 * Populate list of endpoints from LifterLMS Dashboard Settings
	 *
	 * @return  void
	 * @since   1.3.6
	 * @version 1.3.6
	 */
	private function populate_account_endpoints() {

		$exclude_llms_eps = array( 'dashboard', 'edit-account', 'orders', 'signout' );
		$endpoints        = array_diff_key( LLMS_Student_Dashboard::get_tabs(), array_flip( $exclude_llms_eps ) );

		foreach ( $endpoints as $ep_key => &$endpoint ) {

			unset( $endpoint['nav_item'] );
			unset( $endpoint['url'] );

			$endpoint['content'] = 'output_endpoint_' . str_replace( '-', '_', $ep_key );

		}

		/**
		 * Filter: llms_wc_account_endpoints
		 *
		 * Modify the LifterLMS dashboard endpoints which can be added to the WC My Account page as custom tabs.
		 *
		 * @since    1.3.6
		 * @version  1.3.6
		 *
		 * @param  array $endpoints Array of endpoint data.
		 */
		$this->endpoints = apply_filters( 'llms_wc_account_endpoints', $endpoints );

	}

	/**
	 * Determine if WooCommerce is installed & activated
	 *
	 * @return   boolean
	 * @since    1.0.0
	 * @version  2.0.0
	 */
	public function is_installed() {
		return ( function_exists( 'WC' ) );
	}

	/**
	 * Log data to the log woocommerce log file
	 * Only logs if logging is enabled so it's redundant to check logging berofe calling this
	 * accepts any number of arguments of various data types, each will be logged.
	 *
	 * @return   void
	 * @since    3.0.0
	 * @version  2.0.0
	 */
	public function log() {
		if ( llms_parse_bool( $this->get_option( 'logging_enabled', 'no' ) ) ) {
			foreach ( func_get_args() as $data ) {
				llms_log( $data, 'woocommerce' );
			}
		}
	}

	/**
	 * Output the "My Grades"
	 *
	 * @return   void
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function output_endpoint_my_grades() {
		echo '<h2 class="llms-sd-title">' . __( 'My Grades', 'lifterlms-woocommerce' ) . '</h2>';
		lifterlms_template_student_dashboard_my_grades();
	}

	/**
	 * Output Student Courses
	 *
	 * @return   void
	 * @since    1.3.6
	 * @version  1.3.6
	 */
	public function output_endpoint_view_courses() {
		if ( function_exists( 'LLMS' ) && version_compare( '3.14.0', LLMS()->version, '<=' ) ) {
			$this->setup_endpoint_pagination( 'courses' );
			lifterlms_template_student_dashboard_my_courses( false );
		} else {
			$student = new LLMS_Student();
			llms_get_template(
				'myaccount/my-courses.php',
				array(
					'student' => $student,
					'courses' => $student->get_courses(),
				)
			);
		}
	}

	/**
	 * Output student achievements
	 *
	 * @return   void
	 * @since    1.3.6
	 * @version  1.3.6
	 */
	public function output_endpoint_view_achievements() {
		if ( function_exists( 'LLMS' ) && version_compare( '3.14.0', LLMS()->version, '<=' ) ) {
			lifterlms_template_student_dashboard_my_achievements( false );
		} else {
			llms_get_template( 'myaccount/my-achievements.php' );
		}
	}

	/**
	 * Output student certificates
	 *
	 * @return   void
	 * @since    1.3.6
	 * @version  1.3.6
	 */
	public function output_endpoint_view_certificates() {
		if ( function_exists( 'LLMS' ) && version_compare( '3.14.0', LLMS()->version, '<=' ) ) {
			lifterlms_template_student_dashboard_my_certificates( false );
		} else {
			llms_get_template( 'myaccount/my-certificates.php' );
		}
	}

	/**
	 * Output student memberships
	 *
	 * @return   void
	 * @since    1.3.6
	 * @version  1.3.6
	 */
	public function output_endpoint_view_memberships() {
		if ( function_exists( 'LLMS' ) && version_compare( '3.14.0', LLMS()->version, '<=' ) ) {
			lifterlms_template_student_dashboard_my_memberships();
		} else {
			llms_get_template( 'myaccount/my-memberships.php' );
		}
	}

	/**
	 * Output student notifications
	 *
	 * @return   void
	 * @since    1.3.0
	 * @version  2.0.1
	 */
	public function output_endpoint_notifications() {
		echo '<h2 class="llms-sd-title">' . __( 'My Notifications', 'lifterlms-woocommerce' ) . '</h2>';
		if ( function_exists( 'lifterlms_template_student_dashboard_my_notifications' ) ) {
			lifterlms_template_student_dashboard_my_notifications();
		} else {
			LLMS_Student_Dashboard::output_notifications_content();
		}
	}

	/**
	 * Output voucher redemeption endpoint
	 *
	 * @return   void
	 * @since    1.3.6
	 * @version  1.3.6
	 */
	public function output_endpoint_redeem_voucher() {
		echo '<h2 class="llms-sd-title">' . __( 'Redeem a Voucher', 'lifterlms-woocommerce' ) . '</h2>';
		LLMS_Student_Dashboard::output_redeem_voucher_content();
	}

	/**
	 * Setup endpoint pagination variables on the $wp_query gloabl
	 *
	 * @param    string $endpoint  endpoint slug.
	 * @return   void
	 * @since    1.3.4
	 * @version  1.3.4
	 */
	private function setup_endpoint_pagination( $endpoint ) {
		global $wp_query;
		if ( ! empty( $wp_query->query[ $endpoint ] ) ) {
			$parts = explode( '/', $wp_query->query[ $endpoint ] );
			$page  = isset( $parts[1] ) && is_numeric( $parts[1] ) ? absint( $parts[1] ) : 1;
			$wp_query->set( 'paged', $page );
		}
	}

}
