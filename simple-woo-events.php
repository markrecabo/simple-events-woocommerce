<?php
/**
 * Plugin Name: Simple WooCommerce Events
 * Plugin URI: #
 * Description: Adds event functionality to WooCommerce variable products
 * Version: 1.0.0
 * Author: Mark Recabo
 * Author URI: #
 * Text Domain: simple-woo-events
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 *
 * @package Simple_Woo_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// Define plugin constants
define( 'SIMPLE_WOO_EVENTS_VERSION', '1.0.0' );
define( 'SIMPLE_WOO_EVENTS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SIMPLE_WOO_EVENTS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

/**
 * Declare HPOS compatibility
 */
add_action(
    'before_woocommerce_init',
    function() {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    }
);

/**
 * Check if WooCommerce is active
 */
function simple_woo_events_check_woocommerce() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        add_action( 'admin_notices', 'simple_woo_events_missing_wc_notice' );
        return;
    }
}
add_action( 'plugins_loaded', 'simple_woo_events_check_woocommerce' );

/**
 * Display WooCommerce missing notice
 */
function simple_woo_events_missing_wc_notice() {
    ?>
    <div class="error">
        <p><?php esc_html_e( 'Simple WooCommerce Events requires WooCommerce to be installed and active.', 'simple-woo-events' ); ?></p>
    </div>
    <?php
}

/**
 * Initialize the plugin
 */
function simple_woo_events_init() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        return;
    }

    // Include the main plugin class
    require_once SIMPLE_WOO_EVENTS_PLUGIN_DIR . 'includes/class-simple-woo-events.php';

    // Initialize the plugin
    Simple_Woo_Events::instance();
}
add_action( 'plugins_loaded', 'simple_woo_events_init', 11 );

/**
 * Activation hook
 */
function simple_woo_events_activate() {
    if ( ! class_exists( 'WooCommerce' ) ) {
        deactivate_plugins( plugin_basename( __FILE__ ) );
        wp_die( esc_html__( 'Simple WooCommerce Events requires WooCommerce to be installed and active.', 'simple-woo-events' ) );
    }
}
register_activation_hook( __FILE__, 'simple_woo_events_activate' );
