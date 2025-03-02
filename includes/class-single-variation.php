<?php
/**
 * Single Variation Handler Class
 *
 * @package Simple_Woo_Events
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class to handle single variation products
 */
class Simple_Woo_Events_Single_Variation {

    /**
     * Constructor
     */
    public function __construct() {
        // Initialize hooks
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add custom script to auto-select single variations
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        
        // Hide variation dropdowns for single variation products
        add_filter( 'woocommerce_dropdown_variation_attribute_options_html', array( $this, 'maybe_hide_variation_dropdown' ), 10, 2 );
    }

    /**
     * Enqueue scripts
     */
    public function enqueue_scripts() {
        // Only on single product pages
        if ( ! is_product() ) {
            return;
        }
        
        global $product;
        
        // Get the product
        if ( ! is_object( $product ) ) {
            $product = wc_get_product( get_the_ID() );
        }
        
        // Only for variable products
        if ( ! $product || ! $product->is_type( 'variable' ) ) {
            return;
        }
        
        // Get available variations
        $variations = $product->get_available_variations();
        
        // Only if there's exactly one variation
        if ( count( $variations ) !== 1 ) {
            return;
        }
        
        // Enqueue the CSS
        wp_enqueue_style(
            'simple-woo-events-single-variation',
            SIMPLE_WOO_EVENTS_PLUGIN_URL . 'assets/css/single-variation.css',
            array(),
            SIMPLE_WOO_EVENTS_VERSION
        );
        
        // Enqueue the script
        wp_enqueue_script(
            'simple-woo-events-single-variation',
            SIMPLE_WOO_EVENTS_PLUGIN_URL . 'assets/js/single-variation.js',
            array( 'jquery', 'wc-add-to-cart-variation' ),
            SIMPLE_WOO_EVENTS_VERSION,
            true
        );
        
        // Pass the variation data to the script
        wp_localize_script(
            'simple-woo-events-single-variation',
            'simple_woo_events_single_variation',
            array(
                'variation_id' => $variations[0]['variation_id'],
                'attributes'   => $variations[0]['attributes'],
            )
        );
    }
    
    /**
     * Maybe hide variation dropdown for single variation products
     *
     * @param string $html     Dropdown HTML.
     * @param array  $args     Dropdown args.
     * @return string
     */
    public function maybe_hide_variation_dropdown( $html, $args ) {
        global $product;
        
        // Get the product
        if ( ! is_object( $product ) ) {
            $product = wc_get_product( get_the_ID() );
        }
        
        // Only for variable products
        if ( ! $product || ! $product->is_type( 'variable' ) ) {
            return $html;
        }
        
        // Get available variations
        $variations = $product->get_available_variations();
        
        // Only if there's exactly one variation
        if ( count( $variations ) !== 1 ) {
            return $html;
        }
        
        // Get the attribute value for this variation
        $attribute_name = $args['attribute'];
        $attribute_key = 'attribute_' . $attribute_name;
        
        // If the attribute doesn't exist in the variation or is empty, return the original dropdown
        if ( ! isset( $variations[0]['attributes'][ $attribute_key ] ) || '' === $variations[0]['attributes'][ $attribute_key ] ) {
            return $html;
        }
        
        $attribute_value = $variations[0]['attributes'][ $attribute_key ];
        
        // If the attribute value is empty (any attribute), return the original dropdown
        if ( '' === $attribute_value ) {
            return $html;
        }
        
        // Create a hidden input instead of the dropdown
        $hidden_input = sprintf(
            '<input type="hidden" name="attribute_%s" value="%s" data-attribute_name="attribute_%s" />',
            esc_attr( $attribute_name ),
            esc_attr( $attribute_value ),
            esc_attr( $attribute_name )
        );
        
        return $hidden_input;
    }
}

// Initialize the class
new Simple_Woo_Events_Single_Variation();
