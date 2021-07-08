<?php
/**
 * Modify LLMS core access plans
 *
 * @package LifterLMS_WooCommerce/Classes
 *
 * @since 2.0.0
 * @version 2.0.12
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_WC_Access_Plans class.
 *
 * @since 2.0.0
 * @since 2.0.9 Add support for checkout redirect settings.
 * @since 2.0.12 Made sure `wc_print_notices()` is defined before adding it as hook callback.
 */
class LLMS_WC_Access_Plans {

	/**
	 * Constructor
	 *
	 * @since 2.0.0
	 * @since 2.0.12 Made sure `wc_print_notices()` is defined before adding it as hook callback.
	 */
	public function __construct() {

		// hide access plan metabox rows in the most elegant way possible.
		add_action( 'admin_head', array( $this, 'output_css' ) );
		add_action( 'admin_footer', array( $this, 'output_js' ) );

		// output custom metabox fields.
		add_action( 'llms_access_plan_mb_after_row_one', array( $this, 'output_fields' ), 10, 3 );

		// prevent validation issues since no price will be saved.
		add_filter( 'llms_access_before_save_plan', array( $this, 'before_save_plan' ), 10, 2 );

		// add custom plan props to the plan model.
		add_filter( 'llms_get_access_plan_properties', array( $this, 'register_properties' ), 10, 1 );

		// remove display of trial information.
		remove_action( 'llms_acces_plan_footer', 'llms_template_access_plan_trial', 10 );

		add_filter( 'llms_plan_get_checkout_url', array( $this, 'get_checkout_url' ), 25, 2 );
		add_filter( 'llms_plan_get_price', array( $this, 'get_price' ), 25, 5 );
		add_filter( 'llms_get_product_schedule_details', array( $this, 'get_schedule_details' ), 25, 2 );
		add_filter( 'llms_get_access_plan_availability', array( $this, 'get_availability' ), 25, 2 );
		add_filter( 'llms_get_access_plan_availability_restrictions', array( $this, 'get_availability_restrictions' ), 25, 2 );
		add_filter( 'llms_product_is_purchasable', array( $this, 'is_purchasable' ), 25, 2 );

		// replace sale dates.
		add_filter( 'llms_plan_is_on_sale', array( $this, 'get_is_on_sale' ), 25, 2 );
		add_filter( 'llms_get_access_plan_sale_start', array( $this, 'get_sale_start' ), 10, 2 );
		add_filter( 'llms_get_access_plan_sale_end', array( $this, 'get_sale_end' ), 10, 2 );

		// Show WC Notices on Course & Membership Pages.
		if ( function_exists( 'wc_print_notices' ) ) {
			add_action( 'lifterlms_single_course_before_summary', 'wc_print_notices' );
			add_action( 'lifterlms_single_membership_before_summary', 'wc_print_notices' );
		}

	}

	/**
	 * Validate access plan data on save.
	 *
	 * @since 2.0.0
	 * @since 2.0.7 Unknown.
	 *
	 * @param array $data    Array of posted plan data.
	 * @param obj   $metabox LLMS_Admin_Metabox instance.
	 * @return array
	 */
	public function before_save_plan( $data, $metabox ) {

		if ( empty( $data['wc_pid'] ) ) {
			return $data;
		}

		// Set a price so validation errors from core don't get thrown.
		if ( empty( $data['price'] ) ) {
			$data['price'] = 1;
		}

		return $data;

	}

	/**
	 * Filter return of access plan availability getter.
	 *
	 * @since 2.0.0
	 *
	 * @param string           $availability Default availability.
	 * @param LLMS_Access_Plan $plan         LLMS_Access_Plan.
	 * @return string
	 */
	public function get_availability( $availability, $plan ) {
		if ( llms_wc_plan_has_wc_product( $plan ) ) {
			if ( get_post_meta( $plan->get( 'wc_pid' ), '_llms_membership_id', true ) ) {
				return 'members';
			}
		}
		return $availability;
	}

	/**
	 * Filter return of access plan availability restriction getter.
	 *
	 * @since 2.0.0
	 *
	 * @param array            $restrictions Default restrictions.
	 * @param LLMS_Access_Plan $plan         LLMS_Access_Plan.
	 * @return string
	 */
	public function get_availability_restrictions( $restrictions, $plan ) {
		if ( llms_wc_plan_has_wc_product( $plan ) ) {
			$restrictions = array();
			$meta         = get_post_meta( $plan->get( 'wc_pid' ), '_llms_membership_id', true );
			if ( $meta ) {
				$restrictions[] = $meta;
			}
		}
		return $restrictions;
	}

	/**
	 * Modify the access plan checkout button URL for access plans with a WC product association.
	 *
	 * @since 2.0.0
	 *
	 * @param string           $url  Default checkout URL.
	 * @param LLMS_Access_Plan $plan LLMS_Access_Plan.
	 * @return string
	 */
	public function get_checkout_url( $url, $plan ) {
		if ( llms_wc_plan_has_wc_product( $plan ) && ! $plan->has_availability_restrictions() ) {

			$product = wc_get_product( $plan->get( 'wc_pid' ) );
			return $product->add_to_cart_url();

		}

		return $url;

	}

	/**
	 * Modify the access plan checkout button URL for access plans with a WC product association.
	 *
	 * @since 2.0.0
	 *
	 * @param bool             $bool Default result of `$plan->is_on_sale()`.
	 * @param LLMS_Access_Plan $plan LLMS_Access_Plan.
	 * @return string
	 */
	public function get_is_on_sale( $bool, $plan ) {

		if ( llms_wc_plan_has_wc_product( $plan ) ) {

			$product = wc_get_product( $plan->get( 'wc_pid' ) );
			return $product->is_on_sale();

		}

		return $bool;

	}

	/**
	 * Modify the access plan price for access plans with a WC product association.
	 *
	 * @since 2.0.0
	 *
	 * @param string           $price      Default price.
	 * @param string           $key        Price field key name.
	 * @param array            $price_args Price display arguments.
	 * @param string           $format     Price display format.
	 * @param LLMS_Access_Plan $plan       LLMS_Access_Plan.
	 * @return bool
	 */
	public function get_price( $price, $key, $price_args, $format, $plan ) {

		if ( llms_wc_plan_has_wc_product( $plan ) ) {

			$product = wc_get_product( $plan->get( 'wc_pid' ) );

			if ( 'html' === $format ) {
				if ( ( 'price' === $key && ! $plan->is_on_sale() ) || 'sale_price' === $key ) {
					$price = $product->get_price_html();
				} else {
					$price = '';
				}
			}
		}

		return $price;

	}

	/**
	 * Modify the access plan sale end date.
	 *
	 * @since 2.0.5
	 *
	 * @param string           $sale_end Default sale end date of the access plan.
	 * @param LLMS_Access_Plan $plan     LLMS_Access_Plan.
	 * @return string
	 */
	public function get_sale_end( $sale_end, $plan ) {
		if ( ! llms_wc_plan_has_wc_product( $plan ) ) {
			return $sale_end;
		}

		$product = wc_get_product( $plan->get( 'wc_pid' ) );
		return $product->get_date_on_sale_to();
	}

	/**
	 * Modify the access plan sale start date.
	 *
	 * @since 2.0.5
	 *
	 * @param string           $sale_start Default sale start date of the access plan.
	 * @param LLMS_Access_Plan $plan       LLMS_Access_Plan.
	 * @return string
	 */
	public function get_sale_start( $sale_start, $plan ) {
		if ( ! llms_wc_plan_has_wc_product( $plan ) ) {
			return $sale_start;
		}

		$product = wc_get_product( $plan->get( 'wc_pid' ) );
		return $product->get_date_on_sale_from();
	}

	/**
	 * Modify the access plan schedule details string for access plans with a WC product association.
	 *
	 * @since 2.0.0
	 *
	 * @param string           $string Default schedule details string.
	 * @param LLMS_Access_Plan $plan   LLMS_Access_Plan.
	 * @return bool
	 */
	public function get_schedule_details( $string, $plan ) {

		if ( llms_wc_plan_has_wc_product( $plan ) ) {

			return '';

		}

		return $string;

	}

	/**
	 * Filter the return of the `LLMS_Product->is_purchasable()` method to skip checking for the presence of LifterLMS gateways.
	 *
	 * @since 2.0.0
	 *
	 * @param bool         $bool    True if purchaseable, false otherwise.
	 * @param LLMS_Product $product LLMS_Product.
	 * @return bool
	 */
	public function is_purchasable( $bool, $product ) {
		return ( 0 !== count( $product->get_access_plans( false, false ) ) );
	}

	/**
	 * Output inline CSS to modify access plan templates.
	 *
	 * @since 2.0.0
	 * @since 2.0.9 Hide access plan redirect settings.
	 *
	 * @return void
	 */
	public function output_css() {

		$screen = get_current_screen();
		if ( ! in_array( $screen->post_type, array( 'course', 'llms_membership' ), true ) ) {
			return;
		}

		echo '<style type="text/css">
			.llms-plan-row-2,.llms-plan-row-5,.llms-plan-row-4,.llms-plan-row-wc .llms-button-secondary.small{ display: none !important; }
			.llms-plan-row-7 { display: none; }
		</style>';

	}

	/**
	 * Output inline JS to handle UX for access plans.
	 *
	 * @since 2.0.0
	 * @since 2.0.9 Show redirect settings only for free plans.
	 *
	 * @return void
	 */
	public function output_js() {

		$screen = get_current_screen();
		if ( ! in_array( $screen->post_type, array( 'course', 'llms_membership' ), true ) ) {
			return;
		}
		?>
		<script>( function( $ ){

			// when a new plan is initialized automatically remove the "required" attribute from the hidden price box.
			$( document ).on( 'llms-plan-init', function( event, html ) {
				var $clone = $( html );
				$clone.find( 'input.llms-plan-price' ).removeAttr( 'required' );
				window.llms.metaboxes.post_select( $clone.find( 'select.llms-wc-plan-pid' ) );
			} );

			// when "is free" is checked, toggle the visibility of the plan availability options.
			// free items use availability from here, product connections use availability of the product/variation in WC settings.
			$( document ).on( 'change', 'input[type="checkbox"][name^="_llms_plans"][name*="is_free"]', function() {
				var $box = $( this ),
					$plan = $box.closest( '.llms-access-plan' ),
					$price = $plan.find( 'input.llms-plan-price' );
					$availability = $plan.find( 'select[name^="_llms_plans"][name*="availability"]').closest( '.d-1of2' ),
					$redirects = $plan.find( '.llms-plan-row-7' );
					console.log( $redirects );
				if ( $box.is( ':checked' ) ) {
					$availability.show();
					$redirects.show();
				} else {
					$availability.hide();
					$redirects.hide();
					$price.val( 1 ); // set a price to prevent validation issues
				}
			} );

		} )( jQuery );

		</script>
		<?php
	}

	/**
	 * Output custom access plan fields.
	 *
	 * @since 2.0.0
	 * @since 2.0.6 Unknown.
	 *
	 * @param LLMS_Access_Plan $plan  LLMS_Access_Plan.
	 * @param int              $id    Access Plan ID.
	 * @param int              $order Access Plan order.
	 * @return void
	 */
	public function output_fields( $plan, $id, $order ) {

		$selected = array();
		if ( $plan ) {
			$pid = $plan->get( 'wc_pid' );
			if ( $pid ) {
				$selected = llms_make_select2_post_array( $pid );
			}
		}
		?>

		<div class="llms-plan-row-wc" data-controller="llms-plan-is-free" data-value-is-not="yes">

			<div class="llms-metabox-field d-all">
				<label><?php _e( 'WooCommerce Product', 'lifterlms-woocommerce' ); ?> <span class="llms-required">*</span></label>
				<select class="llms-wc-plan-pid<?php echo $plan ? ' llms-select2-post' : ''; ?>" data-placeholder="<?php esc_attr_e( 'Select a product or product variation', 'lifterlms-woocommerce' ); ?>" data-post-type="product,product_variation" name="_llms_plans[<?php echo $order; ?>][wc_pid]">
					<?php foreach ( $selected as $opt ) : ?>
						<option value="<?php echo absint( $opt['key'] ); ?>" selected="selected"><?php echo esc_attr( $opt['title'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>

		</div>

		<div class="clear"></div>

		<?php
	}

	/**
	 * Register custom access plan properties with the model.
	 *
	 * @since 2.0.12
	 *
	 * @param array $props Existing properties.
	 * @return array
	 */
	public function register_properties( $props ) {

		$props['wc_pid'] = 'absint';
		return $props;

	}


}

return new LLMS_WC_Access_Plans();
