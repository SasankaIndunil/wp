<?php
/**
 * Handle order actions such as enrollments and expirations
 *
 * @package LifterLMS_WooCommerce/Classes
 *
 * @since 2.0.0
 * @version 2.0.10
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_WC_Order_Actions class.
 *
 * @since 2.0.0
 * @since 2.0.10 Fix issues encountered when Subscriptions with no parent order are passed into enrollment & unenrollment actions.
 */
class LLMS_WC_Order_Actions {

	/**
	 * Constructor
	 *
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function __construct() {

		$this->integration = LLMS_WooCommerce()->get_integration();
		add_action( 'init', array( $this, 'add_status_actions' ) );
		add_action( 'llms_wc_access_plan_expiration', array( $this, 'expire_access' ), 10, 2 );
		add_action( 'woocommerce_before_order_item_object_save', array( $this, 'add_order_item_meta' ) );

	}

	/**
	 * Add order item meta data to qualifying orders when a new order is created during checkout.
	 *
	 * @param   obj $item WC_Order_Item.
	 * @return  void
	 * @since   2.0.0
	 * @version 2.0.8
	 */
	public function add_order_item_meta( $item ) {

		if ( ! is_a( $item, 'WC_Order_Item_Product' ) ) {
			return;
		}

		$this->integration->log( sprintf( 'Adding product associations $item %1$d', $item->get_id() ) );

		$pid   = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
		$plans = llms_get_llms_plans_by_wc_product_id( $pid );

		if ( ! $plans ) {
			$this->integration->log( 'No $plans found.' );
			return;
		}

		$this->integration->log( '$plans found:', $plans );

		// Don't proceed if meta already exists.
		if ( $item->meta_exists( '_llms_access_plan' ) ) {
			return;
		}

		foreach ( $plans as $plan_id ) {

			$plan = llms_get_post( $plan_id );
			if ( ! $plan ) {
				continue;
			}

			// save the access plan, used for expiration info during enrollment.
			$item->add_meta_data( '_llms_access_plan', $plan->get( 'id' ) );

			// save the related llms products.
			$links = wp_list_pluck( $item->get_meta( '_llms_pid', false ), 'value' );
			if ( ! in_array( $plan->get( 'product_id' ), $links ) ) {
				$item->add_meta_data( '_llms_pid', $plan->get( 'product_id' ), false );
			}
		}

	}

	/**
	 * Add enrollment and unenrollment actions based on integration settings
	 *
	 * @return   void
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function add_status_actions() {

		$enroll = $this->integration->get_option( 'enrollment_status', 'wc-completed' );
		add_action( 'woocommerce_order_status_' . $this->unprefix_status( $enroll ), array( $this, 'do_order_enrollments' ), 10, 1 );

		$unenrolls = $this->integration->get_option( 'unenrollment_statuses', array() );
		foreach ( $unenrolls as $status ) {
			add_action( 'woocommerce_order_status_' . $this->unprefix_status( $status ), array( $this, 'do_order_unenrollments' ), 10, 1 );
		}

		// add subscription actions.
		if ( class_exists( 'WC_Subscriptions' ) ) {

			$sub_enroll = $this->integration->get_option( 'subscription_enrollment_status', 'wc-active' );
			add_action( 'woocommerce_subscription_status_' . $this->unprefix_status( $sub_enroll ), array( $this, 'do_order_enrollments' ), 10, 1 );

			$sub_unenrolls = $this->integration->get_option( 'subscription_unenrollment_statuses', array() );
			foreach ( $sub_unenrolls as $status ) {
				add_action( 'woocommerce_subscription_status_' . $this->unprefix_status( $status ), array( $this, 'do_order_unenrollments' ), 10, 1 );
			}
		}

	}

	/**
	 * Enroll the customer in all llms products associated with all items in the order
	 * Called upon order status change to the user-defined "Enrollment Status" setting
	 *
	 * @since 2.0.0
	 * @since 2.0.10 Return early when a subscription with no parent order is passed.
	 *
	 * @param mixed $order_id WP_Order ID (int) or WC_Subscription (obj).
	 * @return void
	 */
	public function do_order_enrollments( $order_id ) {

		$order = $this->get_order_from_action_args( $order_id );

		if ( ! $order ) {
			$this->integration->log( '`do_order_enrollments()` failed, no order found.' );
			$this->integration->log( $order_id );
			return;
		}

		$user_id = $order->get_user_id();

		$this->integration->log( '`do_order_enrollments()` started for order_id "' . $order->get_id() . '"' );

		// if no user id exists we do nothing. Gotta have a user to assign the course to.
		if ( empty( $user_id ) ) {
			$this->integration->log( '`do_order_enrollments()` ended for order_id "' . $order->get_id() . '". No user ID was supplied.' );
			return;
		}

		foreach ( $order->get_items() as $item ) {

			$products = llms_wc_get_order_item_products( $item );

			$this->integration->log( sprintf( '$products found for $item %s', $item->get_id() ), $products );

			foreach ( $products as $product_id ) {

				if ( ! llms_enroll_student( $user_id, $product_id, 'wc_order_' . $order->get_id() ) ) {
					continue;
				}

				$this->integration->add_order_note( $order, $product_id, 'enrollment' );

				$plans = wc_get_order_item_meta( $item->get_id(), '_llms_access_plan', false );

				foreach ( $plans as $plan ) {

					$plan = $plan ? llms_get_post( $plan ) : false;
					if ( ! $plan || ! $plan->can_expire() ) {
						continue;
					}

					$time = $this->get_expiration_time_from_plan( $plan );
					if ( ! $time ) {
						continue;
					}

					$this->schedule_expiration( $time, $order->get_id(), $plan->get( 'product_id' ) );

				}
			}
		}

		$this->integration->log( '`do_order_enrollments()` finished for order_id "' . $order->get_id() . '"' );

	}

	/**
	 * Unenroll the customer from all llms products associated with all items in the order
	 * Called upon order status change to any status in the user-defined "Unenrollment Statuses" setting
	 *
	 * @since 2.0.0
	 * @since 2.0.10 Return early when a subscription with no parent order is passed.
	 *
	 * @param mixed $order_id WP_Order ID (int) or WC_Subscription (obj).
	 * @return void
	 */
	public function do_order_unenrollments( $order_id ) {

		$order = $this->get_order_from_action_args( $order_id );

		if ( ! $order ) {
			$this->integration->log( '`do_order_unenrollments()` failed, no order found.' );
			$this->integration->log( $order_id );
			return;
		}

		$user_id = $order->get_user_id();

		$this->integration->log( '`do_order_unenrollments()` started for order_id "' . $order->get_id() . '"' );

		// if no user id exists we do nothing. Gotta have a user to assign the course to.
		if ( empty( $user_id ) ) {
			return;
		}

		foreach ( $order->get_items() as $item ) {

			$products = llms_wc_get_order_item_products( $item );
			if ( $products ) {
				$this->integration->log( '$products: ', $products );
				foreach ( $products as $product_id ) {
					/**
					 * Filter: llms_wc_unenrollment_new_status
					 *
					 * Customize the student unenrollment status when the student is unenrolled as a result of WC order status changes.
					 *
					 * @since    2.0.0
					 * @version  2.0.0
					 *
					 * @example  add_filter( 'llms_wc_plan_has_wc_product', '__return_false' );
					 *
					 * @param  string $status The new status, should be a valid LifterLMS enrollment status. Defaults to 'expired'.
					 * @param  int  $order_id WC_Post ID of the WooCommerce Order.
					 */
					if ( llms_unenroll_student( $user_id, $product_id, apply_filters( 'llms_wc_unenrollment_new_status', 'expired', $order->get_id() ), 'wc_order_' . $order->get_id() ) ) {
						$this->integration->add_order_note( $order, $product_id, 'unenrollment' );
					}
				}
			}
		}

		$this->integration->log( '`do_order_unenrollments()` finished for order_id "' . $order->get_id() . '"' );

	}

	/**
	 * Get the timestamp of a scheduled expiration for a given order & product (course or membership)
	 *
	 * @param    int $order_id    WP Post ID of the WC order.
	 * @param    int $product_id  WP Post ID of the LLMS course or membership.
	 * @return   int|false            timestamp of the scheduled event or false if none is scheduled
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public static function get_expiration( $order_id, $product_id ) {

		$order_id   = absint( $order_id );
		$product_id = absint( $product_id );

		if ( function_exists( 'as_next_scheduled_action' ) ) {
			return as_next_scheduled_action( 'llms_wc_access_plan_expiration', compact( 'order_id', 'product_id' ) );
		}

		return wc_next_scheduled_action( 'llms_wc_access_plan_expiration', compact( 'order_id', 'product_id' ) );

	}

	/**
	 * Retrieve a WC_Order from either an order_id or a WC_Subscription obj
	 *
	 * @since   2.0.0
	 *
	 * @param   mixed $order_id_or_subscription WP_Order ID (int) or WC_Subscription (obj).
	 * @return  mixed|false WC_Order or false if it's a subscription order with no parent.
	 */
	public function get_order_from_action_args( $order_id_or_subscription ) {

		if ( ! is_numeric( $order_id_or_subscription ) && $order_id_or_subscription instanceof WC_Subscription ) {
			$order = $order_id_or_subscription->get_parent();
		} else {
			$order = wc_get_order( $order_id_or_subscription );
		}

		return $order;

	}

	/**
	 * Retrieve expiration timestamp for a plan.
	 *
	 * @param   obj $plan LLMS_Access_Plan instance.
	 * @return  int|false
	 * @since   2.0.0
	 * @version 2.0.0
	 */
	public function get_expiration_time_from_plan( $plan ) {

		$time       = false;
		$expiration = $plan->get( 'access_expiration' );

		if ( 'limited-date' === $expiration ) {

			$time = $plan->get_date( 'access_expires', 'U' );

		} elseif ( 'limited-period' === $expiration ) {

			$time = strtotime(
				sprintf( '+%1$d %2$s', $plan->get( 'access_length' ), $plan->get( 'access_period' ) ),
				strtotime( llms_current_time( 'Y-m-d' ), llms_current_time( 'timestamp' ) ) + ( DAY_IN_SECONDS - 1 )
			);

		}

		return $time;

	}

	/**
	 * Expires access for a given order & product
	 *
	 * @param    int $order_id    WP Post ID of the WC order.
	 * @param    int $product_id  WP Post ID of the LLMS course or membership.
	 * @return   void
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function expire_access( $order_id, $product_id ) {

		$order = wc_get_order( $order_id );

		$this->integration->log( sprintf( 'Access expiration called for order %1$d & product %2$d', date( 'Y-m-d H:i', $time ), $order_id, $product_id ) );

		if ( llms_unenroll_student( $order->get_customer_id(), $product_id, 'expired', 'wc_order_' . $order_id ) ) {

			// Translators: %s = Title of the course or membership.
			$note = sprintf( __( 'Student unenrolled from "%s" due to automatic access plan expiration.', 'lifterlms-woocommerce' ), get_the_title( $product_id ) );
			$order->add_order_note( $note );

		}

	}

	/**
	 * Schedule access expiration for an order & product (course or membership)
	 *
	 * @param    int $time        timestamp.
	 * @param    int $order_id    WP Post ID of the WC order.
	 * @param    int $product_id  WP Post ID of the LLMS course or membership.
	 * @return   void
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public static function schedule_expiration( $time, $order_id, $product_id ) {

		$order_id   = absint( $order_id );
		$product_id = absint( $product_id );

		self::unschedule_expiration( $order_id, $product_id );

		if ( function_exists( 'as_schedule_single_action' ) ) {
			as_schedule_single_action( $time, 'llms_wc_access_plan_expiration', compact( 'order_id', 'product_id' ) );
		} else {
			wc_schedule_single_action( $time, 'llms_wc_access_plan_expiration', compact( 'order_id', 'product_id' ) );
		}

		$integration = LLMS_WooCommerce()->get_integration();
		$integration->log( sprintf( 'Expiration scheduled at %1$s for order %2$d & product %3$d', date( 'Y-m-d H:i', $time ), $order_id, $product_id ) );

	}

	/**
	 * Utility to remove "wc-" prefix from a status string
	 *
	 * @param    string $status  prefixed string.
	 * @return   string
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	private function unprefix_status( $status ) {
		return str_replace( 'wc-', '', $status );
	}

	/**
	 * Removes a scheduled expiration
	 *
	 * @param    int $order_id    WP Post ID of the WC order.
	 * @param    int $product_id  WP Post ID of the LLMS course or membership.
	 * @return   void
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public static function unschedule_expiration( $order_id, $product_id ) {

		$order_id   = absint( $order_id );
		$product_id = absint( $product_id );

		if ( self::get_expiration( $order_id, $product_id ) ) {
			if ( function_exists( 'as_unschedule_action' ) ) {
				as_unschedule_action( 'llms_wc_access_plan_expiration', compact( 'order_id', 'product_id' ) );
			} else {
				wc_unschedule_action( 'llms_wc_access_plan_expiration', compact( 'order_id', 'product_id' ) );
			}
			$integration = LLMS_WooCommerce()->get_integration();
			$integration->log( sprintf( 'Expiration unscheduled for order %1$d & product %2$d', $order_id, $product_id ) );
		}

	}

}

return new LLMS_WC_Order_Actions();
