<?php
/**
 * LifterLMS WooCommerce Main Class
 *
 * @package  LifterLMS_WooCommerce/Classes
 * @since    1.0.0
 * @version  2.0.1
 */

defined( 'ABSPATH' ) || exit;

/**
 * LifterLMS WooCommerce Main Class
 */
final class LifterLMS_WooCommerce {

	/**
	 * Plugin Version
	 *
	 * @var string
	 */
	public $version = '2.0.12';

	/**
	 * Singleton Instance
	 *
	 * @var null
	 */
	protected static $_instance = null;

	/**
	 * Main Instance of LifterLMS_WooCommerce
	 * Ensures only one instance of LifterLMS_WooCommerce is loaded or can be loaded.
	 *
	 * @since    1.0.0
	 * @version  1.0.0
	 * @see      LLMS_WooCommerce().
	 * @return   LifterLMS_WooCommerce - Main instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Constructor
	 *
	 * @since    1.0.0
	 * @version  2.0.0
	 * @return   void
	 */
	private function __construct() {

		if ( ! defined( 'LLMS_WC_VERSION' ) ) {
			define( 'LLMS_WC_VERSION', $this->version );
		}

		add_action( 'init', array( $this, 'load_textdomain' ), 0 );

		register_activation_hook( __FILE__, array( $this, 'install' ) );
		add_action( 'plugins_loaded', array( $this, 'init' ), 10 );

	}

	/**
	 * Access the integration class
	 *
	 * @example  $integration = LLMS_WooCommerce()->get_integration();
	 * @return   obj
	 * @since    [verson]
	 * @version  [verson]
	 */
	public function get_integration() {
		return LLMS()->integrations()->get_integration( 'woocommerce' );
	}

	/**
	 * Include files
	 *
	 * @return   void
	 * @since    2.0.0
	 * @version  2.0.1
	 */
	public function includes() {

		$integration = $this->get_integration();

		if ( ! $integration ) {
			return;
		}

		require_once 'includes/class-llms-wc-install.php';

		require_once 'includes/class-llms-wc-access-plans.php';
		require_once 'includes/class-llms-wc-availability-buttons.php';
		require_once 'includes/class-llms-wc-my-account.php';
		require_once 'includes/class-llms-wc-order-actions.php';
		require_once 'includes/class-llms-wc-product-meta.php';
		require_once 'includes/class-llms-wc-relationship-display.php';
		require_once 'includes/class-llms-wc-sales-pages.php';
		require_once 'includes/class-llms-wc-user-information.php';

		require_once 'includes/functions-llms-integration-woocommerce.php';

		if ( is_admin() ) {

			require_once 'includes/admin/class-llms-wc-admin-order-item-meta.php';

		}

	}

	/**
	 * Initialize, require, add hooks & filters
	 *
	 * @since    1.0.0
	 * @version  2.0.0
	 * @return   void
	 */
	public function init() {

		if ( function_exists( 'LLMS' ) ) {

			// require integration.
			require_once 'includes/class-llms-integration-woocommerce.php';

			// register integration.
			add_filter( 'lifterlms_integrations', array( $this, 'register_integration' ), 10, 1 );

			// load includes.
			add_action( 'plugins_loaded', array( $this, 'includes' ), 9999 );

		}

	}

	/**
	 * Called during plugin activation
	 * This is necessary for b/c of the endpoints added to WC Account Page.
	 *
	 * @return   void
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function install() {
		flush_rewrite_rules();
	}

	/**
	 * Load Localization files
	 *
	 * The first loaded file takes priority
	 *
	 * Files can be found in the following order:
	 *      WP_LANG_DIR/lifterlms/lifterlms-woocommerce-LOCALE.mo
	 *      wp_content/plugins/lifterlms-integration-woocommerce/i18n/lifterlms-woocommerce-LOCALE.mo
	 *
	 * @return   void
	 * @since    1.1.0
	 * @version  1.1.0
	 */
	public function load_textdomain() {

		// load locale.
		$locale = apply_filters( 'plugin_locale', get_locale(), 'lifterlms-woocommerce' );

		// load a lifterlms specific locale file if one exists.
		load_textdomain( 'lifterlms-woocommerce', WP_LANG_DIR . '/lifterlms/lifterlms-woocommerce-' . $locale . '.mo' );

		// load localization files.
		load_plugin_textdomain( 'lifterlms-woocommerce', false, dirname( plugin_basename( __FILE__ ) ) . '/i18n' );

	}

	/**
	 * Register the integration with LifterLMS
	 *
	 * @param    array $integrations  array of LifterLMS Integration Classes.
	 * @return   array
	 * @since    1.0.0
	 * @version  1.0.0
	 */
	public function register_integration( $integrations ) {
		$integrations[] = 'LLMS_Integration_WooCommerce';
		return $integrations;
	}

}
