<?php
/**
 * Handle modification of product & shop loop templates for products with membership restrictions
 *
 * @package LifterLMS_WooCommerce/Classes
 *
 * @since    2.0.0
 * @version  2.0.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * LLMS_WC_Availability_Buttons class.
 */
class LLMS_WC_Availability_Buttons {

	/**
	 * Constructor
	 *
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public function __construct() {

		add_action( 'woocommerce_before_shop_loop_item', array( $this, 'before_product' ) ); // loop.
		add_action( 'woocommerce_before_single_product', array( $this, 'before_product' ) ); // single.

		add_action( 'woocommerce_after_add_to_cart_form', array( $this, 'output_membership_button' ) );

	}

	/**
	 * Before displaying a WC Product (single and in loops) check if it's a members only product
	 * and replace the button with a members only link instead
	 *
	 * @return   void
	 * @since    2.0.0
	 * @version  2.0.3
	 */
	public function before_product() {

		$product = wc_get_product();
		if ( ! $product ) {
			return;
		}
		if ( llms_wc_is_product_variable( $product ) ) {

			if ( ! is_singular() ) {
				return;
			}

			echo "<script>(function($){
				$( 'body' ).on( 'change', 'form.variations_form select[data-attribute_name]', function() {
					var attr_val = $( this ).val(),
						attr_name = $( this ).attr( 'data-attribute_name' ),
						btn = $( '.woocommerce-variation-add-to-cart.variations_button' ),
						show = true;
					$( 'span[data-variation]' ).hide();
					$.each( JSON.parse( $( 'form.variations_form' ).attr( 'data-product_variations' ) ), function( i, v ) {
						if ( attr_val === v.attributes[ attr_name ] && 'yes' === v.llms_restriction ) {
							btn.hide();
							$( 'span[data-variation=\"' + v.variation_id + '\"]').show();
							show = false;
						}
					} );
					if ( show ) {
						btn.show();
					}
				} );
			})(jQuery);</script>";

		} else {

			$membership_id = get_post_meta( $product->get_id(), '_llms_membership_id', true );
			if ( $membership_id && ! llms_is_user_enrolled( get_current_user_id(), $membership_id ) ) {

				remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 ); // loop.
				remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 ); // single.

				add_action( 'woocommerce_after_shop_loop_item', array( $this, 'output_membership_button' ), 10 ); // loop.
				add_action( 'woocommerce_single_product_summary', array( $this, 'output_membership_button' ), 30 ); // single.

			}
		}

	}

	/**
	 * Retrieve the HTML for a members only button
	 *
	 * @param    int $post_id  WP Post ID of the WC Prodct/Variation.
	 * @return   string
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	private function get_button_html( $post_id ) {

		$classes = 'llms-wc-members-only-button button';
		if ( is_singular() ) {
			$classes .= ' alt';
		}

		/**
		 * Filter: llms_wc_members_only_button_html
		 *
		 * Modify the HTML of a WC Product "Members Only" button.
		 *
		 * @since    2.0.0
		 * @version  2.0.0
		 *
		 * @param  string $html HTML of the button.
		 * @param  int $post_id WP_Post_ID of the WC product or product variation.
		 */
		return apply_filters(
			'llms_wc_members_only_button_html',
			sprintf(
				'<a class="%1$s" href="%2$s">%3$s</a>',
				$classes,
				esc_url( get_permalink( $post_id ) ),
				self::get_button_text( $post_id )
			),
			$post_id
		);

	}

	/**
	 * Get the text for a members only button
	 *
	 * @param    int $post_id  WP Post ID of the WC Prodct/Variation.
	 * @return   string
	 * @since    2.0.0
	 * @version  2.0.0
	 */
	public static function get_button_text( $post_id ) {

		$text = get_post_meta( $post_id, '_llms_membership_btn_txt', true );

		// if no text saved, output the default.
		if ( ! $text ) {
			/**
			 * Filter: llms_wc_members_only_button_default_text
			 *
			 * Modify the default text of a WC Product "Members Only" button.
			 * This text only shows up if the postmeta value is empty.
			 *
			 * @since    2.0.0
			 * @version  2.0.0
			 *
			 * @param  string $text Default text of the button.
			 * @param  int $post_id WP_Post_ID of the WC product or product variation.
			 */
			$text = apply_filters( 'llms_wc_members_only_button_default_text', __( 'Members Only', 'lifterlms-woocommerce' ), $post_id );
		}

		/**
		 * Filter: llms_wc_members_only_button_text
		 *
		 * Modify the text of a WC Product "Members Only" button.
		 *
		 * @since    2.0.0
		 * @version  2.0.0
		 *
		 * @param  string $text Saved text of the button.
		 * @param  int $post_id WP_Post_ID of the WC product or product variation.
		 */
		return apply_filters( 'llms_wc_members_only_button_text', $text, $post_id );

	}

	/**
	 * Output a members only button when a product is restricted to a membership
	 *
	 * @return   void
	 * @since    2.0.0
	 * @version  2.0.6
	 */
	public function output_membership_button() {

		$product = wc_get_product();
		if ( ! $product ) {
			return;
		}

		if ( llms_wc_is_product_variable( $product ) ) {
			foreach ( $product->get_available_variations() as $variation ) {

				$membership_id = get_post_meta( $variation['variation_id'], '_llms_membership_id', true );
				if ( $membership_id ) {
					echo '<span class="llms-wc-members-only-button-wrap" data-variation="' . $variation['variation_id'] . '" style="display:none;">';
					echo $this->get_button_html( $variation['variation_id'] );
					echo '</span>';
				}
			}
		} else {

			$membership_id = get_post_meta( $product->get_id(), '_llms_membership_id', true );
			if ( $membership_id && ! llms_is_user_enrolled( get_current_user_id(), $membership_id ) ) {
				echo '<span class="llms-wc-members-only-button-wrap">';
				echo $this->get_button_html( $product->get_id() );
				echo '</span>';
			}

			// add removed action back in to ensure the next item in the loop is checked.
			if ( ! is_singular() ) {
				add_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
			}
		}
	}

}

return new LLMS_WC_Availability_Buttons();
