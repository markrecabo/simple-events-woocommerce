<?php
/**
 * Admin class
 *
 * @package Simple_Woo_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Admin class
 */
class Simple_Woo_Events_Admin {

    /**
     * Constructor
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Add custom product tab
        add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_event_settings_tab' ) );
        add_action( 'woocommerce_product_data_panels', array( $this, 'add_event_settings_panel' ) );

        // Save custom fields
        add_action( 'woocommerce_process_product_meta', array( $this, 'save_event_settings' ) );

        // Show custom fields only for variable products
        add_action( 'admin_footer', array( $this, 'show_event_settings_for_variable_products' ) );
        
        // Ensure stock management fields are displayed in variation admin
        add_action( 'admin_footer', array( $this, 'ensure_variation_stock_fields' ) );
    }

    /**
     * Add event settings tab
     *
     * @param array $tabs Product data tabs.
     * @return array
     */
    public function add_event_settings_tab( $tabs ) {
        $tabs['event_settings'] = array(
            'label'    => __( 'Event Settings', 'simple-woo-events' ),
            'target'   => 'event_settings_data',
            'class'    => array( 'show_if_variable' ),
            'priority' => 21,
        );
        return $tabs;
    }

    /**
     * Add event settings panel
     */
    public function add_event_settings_panel() {
        global $post;

        ?>
        <div id="event_settings_data" class="panel woocommerce_options_panel">
            <?php
            // Is this product an event?
            woocommerce_wp_select(
                array(
                    'id'            => '_is_event',
                    'label'         => __( 'Is this product an event?', 'simple-woo-events' ),
                    'description'   => '',
                    'desc_tip'      => false,
                    'options'       => array(
                        'no'  => __( 'No', 'simple-woo-events' ),
                        'yes' => __( 'Yes', 'simple-woo-events' ),
                    ),
                    'value'         => get_post_meta( $post->ID, '_is_event', true ) ?: 'no',
                    'wrapper_class' => 'show_if_variable',
                )
            );

            // Start of event settings that can be hidden
            $is_event = get_post_meta( $post->ID, '_is_event', true ) ?: 'no';
            $event_settings_class = $is_event === 'yes' ? '' : 'event-settings-hidden';
            ?>
            <div class="event-settings-wrapper <?php echo esc_attr( $event_settings_class ); ?>">
            <?php
            // Is this a multiple days event?
            woocommerce_wp_select(
                array(
                    'id'            => '_is_multiple_days',
                    'label'         => __( 'Is this a multiple days event?', 'simple-woo-events' ),
                    'options'       => array(
                        'no'  => __( 'No', 'simple-woo-events' ),
                        'yes' => __( 'Yes', 'simple-woo-events' ),
                    ),
                    'value'         => get_post_meta( $post->ID, '_is_multiple_days', true ) ?: 'no',
                    'wrapper_class' => 'show_if_variable',
                    'custom_attributes' => array(
                        'disabled' => $is_event === 'no' ? 'disabled' : false,
                    ),
                )
            );

            // Event Venue
            woocommerce_wp_text_input(
                array(
                    'id'            => '_event_venue',
                    'label'         => __( 'Event Venue', 'simple-woo-events' ),
                    'placeholder'   => __( 'Enter event venue', 'simple-woo-events' ),
                    'desc_tip'      => true,
                    'description'   => __( 'Enter the venue/place where the event will be held.', 'simple-woo-events' ),
                    'wrapper_class' => 'show_if_variable',
                    'value'         => get_post_meta( $post->ID, '_event_venue', true ),
                    'custom_attributes' => array(
                        'disabled' => $is_event === 'no' ? 'disabled' : false,
                    ),
                )
            );

            // Event Date Fields Container
            ?>
            <div class="event-date-fields">
                <?php
                // Event Start/Event Date
                woocommerce_wp_text_input(
                    array(
                        'id'            => '_event_start_date',
                        'label'         => __( 'Event Date', 'simple-woo-events' ),
                        'placeholder'   => __( 'YYYY-MM-DD', 'simple-woo-events' ),
                        'desc_tip'      => true,
                        'description'   => __( 'The date of the event.', 'simple-woo-events' ),
                        'type'          => 'date',
                        'wrapper_class' => 'show_if_variable',
                        'value'         => get_post_meta( $post->ID, '_event_start_date', true ),
                        'custom_attributes' => array(
                            'disabled' => $is_event === 'no' ? 'disabled' : false,
                        ),
                    )
                );

                // Event Start Time
                woocommerce_wp_text_input(
                    array(
                        'id'            => '_event_start_time',
                        'label'         => __( 'Event Start Time', 'simple-woo-events' ),
                        'placeholder'   => __( 'HH:MM', 'simple-woo-events' ),
                        'desc_tip'      => true,
                        'description'   => __( 'The start time of the event.', 'simple-woo-events' ),
                        'type'          => 'time',
                        'wrapper_class' => 'show_if_variable',
                        'value'         => get_post_meta( $post->ID, '_event_start_time', true ),
                        'custom_attributes' => array(
                            'disabled' => $is_event === 'no' ? 'disabled' : false,
                        ),
                    )
                );

                // Event End Date (hidden by default for single day events)
                woocommerce_wp_text_input(
                    array(
                        'id'            => '_event_end_date',
                        'label'         => __( 'Event End Date', 'simple-woo-events' ),
                        'placeholder'   => __( 'YYYY-MM-DD', 'simple-woo-events' ),
                        'desc_tip'      => true,
                        'description'   => __( 'The end date of the event.', 'simple-woo-events' ),
                        'type'          => 'date',
                        'wrapper_class' => 'show_if_variable multiple-days',
                        'value'         => get_post_meta( $post->ID, '_event_end_date', true ),
                        'custom_attributes' => array(
                            'disabled' => $is_event === 'no' ? 'disabled' : false,
                        ),
                    )
                );

                // Event End Time
                woocommerce_wp_text_input(
                    array(
                        'id'            => '_event_end_time',
                        'label'         => __( 'Event End Time', 'simple-woo-events' ),
                        'placeholder'   => __( 'HH:MM', 'simple-woo-events' ),
                        'desc_tip'      => true,
                        'description'   => __( 'The end time of the event.', 'simple-woo-events' ),
                        'type'          => 'time',
                        'wrapper_class' => 'show_if_variable',
                        'value'         => get_post_meta( $post->ID, '_event_end_time', true ),
                        'custom_attributes' => array(
                            'disabled' => $is_event === 'no' ? 'disabled' : false,
                        ),
                    )
                );
                ?>
            </div>

            <style>
                .form-field._event_end_date_field {
                    display: none !important;
                }
                .is-multiple-days .form-field._event_end_date_field {
                    display: block !important;
                }
                /* Hide event settings when not an event */
                .event-settings-hidden {
                    display: none !important;
                }
            </style>

            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    var $container = $('.event-date-fields');
                    var $startDateLabel = $container.find('label[for="_event_start_date"]');
                    var $endDateField = $('.form-field._event_end_date_field');
                    var $eventSettings = $('.form-field._is_multiple_days_field, .form-field._event_venue_field, .event-date-fields');
                    var originalStartLabel = '<?php echo esc_js( __( 'Event Start Date', 'simple-woo-events' ) ); ?>';
                    var singleDayLabel = '<?php echo esc_js( __( 'Event Date', 'simple-woo-events' ) ); ?>';
                    
                    function updateMultipleDaysFields() {
                        var isMultipleDays = $('#_is_multiple_days').val() === 'yes';
                        $container.toggleClass('is-multiple-days', isMultipleDays);
                        $startDateLabel.text(isMultipleDays ? originalStartLabel : singleDayLabel);
                        
                        // Clear end date when switching to single day
                        if (!isMultipleDays) {
                            $('#_event_end_date').val('');
                        }
                    }

                    function updateEventFields() {
                        var isEvent = $('#_is_event').val() === 'yes';
                        
                        // Toggle visibility of all event settings
                        $('.event-settings-wrapper').toggleClass('event-settings-hidden', !isEvent);
                        
                        // Disable/enable all event fields
                        var $allEventFields = $('#_is_multiple_days, #_event_venue, #_event_start_date, #_event_start_time, #_event_end_date, #_event_end_time');
                        $allEventFields.prop('disabled', !isEvent);
                        
                        // If switching to "No", clear all event fields
                        if (!isEvent) {
                            $allEventFields.val('');
                        }
                        
                        // If it's an event, ensure multiple days fields are updated
                        if (isEvent) {
                            updateMultipleDaysFields();
                        }
                    }

                    $('#_is_event').on('change', updateEventFields);
                    $('#_is_multiple_days').on('change', updateMultipleDaysFields);
                    
                    // Run on page load
                    updateEventFields();
                });
            </script>
            </div>
        </div>
        <?php
    }

    /**
     * Save event settings
     *
     * @param int $post_id Product ID.
     */
    public function save_event_settings( $post_id ) {
        $is_event = isset( $_POST['_is_event'] ) ? sanitize_text_field( wp_unslash( $_POST['_is_event'] ) ) : 'no';
        update_post_meta( $post_id, '_is_event', $is_event );

        // Save multiple days setting
        $is_multiple_days = isset( $_POST['_is_multiple_days'] ) ? sanitize_text_field( wp_unslash( $_POST['_is_multiple_days'] ) ) : 'no';
        update_post_meta( $post_id, '_is_multiple_days', $is_multiple_days );

        if ( isset( $_POST['_event_venue'] ) ) {
            update_post_meta( $post_id, '_event_venue', sanitize_text_field( wp_unslash( $_POST['_event_venue'] ) ) );
        }

        // Save start date and time
        if ( isset( $_POST['_event_start_date'] ) ) {
            update_post_meta( $post_id, '_event_start_date', sanitize_text_field( wp_unslash( $_POST['_event_start_date'] ) ) );
        }
        if ( isset( $_POST['_event_start_time'] ) ) {
            update_post_meta( $post_id, '_event_start_time', sanitize_text_field( wp_unslash( $_POST['_event_start_time'] ) ) );
        }

        // Save end date and time
        if ( isset( $_POST['_event_end_date'] ) ) {
            update_post_meta( $post_id, '_event_end_date', sanitize_text_field( wp_unslash( $_POST['_event_end_date'] ) ) );
        }
        if ( isset( $_POST['_event_end_time'] ) ) {
            update_post_meta( $post_id, '_event_end_time', sanitize_text_field( wp_unslash( $_POST['_event_end_time'] ) ) );
        }
    }

    /**
     * Show event settings only for variable products
     */
    public function show_event_settings_for_variable_products() {
        if ( ! isset( $_GET['post'] ) ) {
            return;
        }

        $post_id = absint( $_GET['post'] );
        $product = wc_get_product( $post_id );

        if ( ! $product || $product->get_type() !== 'variable' ) {
            ?>
            <style type="text/css">
                #event_settings_data { display: none !important; }
            </style>
            <?php
        }
    }

    /**
     * Ensure stock management fields are displayed in variation admin
     */
    public function ensure_variation_stock_fields() {
        global $pagenow, $post;
        
        // Only run on product edit page
        if ( ! ( $pagenow === 'post.php' && isset( $_GET['post'] ) ) ) {
            return;
        }
        
        $post_id = absint( $_GET['post'] );
        $product = wc_get_product( $post_id );
        
        if ( ! $product || $product->get_type() !== 'variable' ) {
            return;
        }
        
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Function to ensure stock fields are visible
                function ensureStockFieldsVisible() {
                    // Target the stock quantity fields in variations
                    $('.woocommerce_variations .woocommerce_variation').each(function() {
                        var $variation = $(this);
                        
                        // Check if manage stock is checked
                        var $manageStock = $variation.find('.variable_manage_stock');
                        
                        // If stock fields are missing but manage stock is checked
                        if ($manageStock.is(':checked') && $variation.find('.stock_fields').length === 0) {
                            // Create stock fields if they don't exist
                            var $stockFields = $('<div class="stock_fields show_if_variation_manage_stock"></div>');
                            
                            // Add stock quantity field if it doesn't exist
                            if ($variation.find('input[name^="variable_stock"]').length === 0) {
                                var variationId = $variation.find('input[name^="variable_post_id"]').val();
                                $stockFields.append('<p class="form-field variable_stock_field"><label>Stock quantity</label><input type="number" name="variable_stock[' + variationId + ']" value="0" step="1"></p>');
                            }
                            
                            // Add low stock threshold field if it doesn't exist
                            if ($variation.find('input[name^="variable_low_stock_amount"]').length === 0) {
                                var variationId = $variation.find('input[name^="variable_post_id"]').val();
                                $stockFields.append('<p class="form-field variable_low_stock_amount_field"><label>Low stock threshold</label><input type="number" name="variable_low_stock_amount[' + variationId + ']" value="" step="1" placeholder="Store-wide threshold"></p>');
                            }
                            
                            // Insert stock fields after manage stock checkbox
                            $manageStock.closest('label').after($stockFields);
                        }
                        
                        // Ensure stock fields are visible when manage stock is checked
                        if ($manageStock.is(':checked')) {
                            $variation.find('.stock_fields').show();
                        }
                    });
                }
                
                // Run when variations are loaded or changed
                $(document.body).on('woocommerce_variations_loaded woocommerce_variations_added', ensureStockFieldsVisible);
                
                // Run when manage stock checkbox is changed
                $(document.body).on('change', '.variable_manage_stock', function() {
                    var $variation = $(this).closest('.woocommerce_variation');
                    $variation.find('.stock_fields').toggle($(this).is(':checked'));
                });
                
                // Run on page load with a delay to ensure WooCommerce has initialized
                setTimeout(ensureStockFieldsVisible, 1000);
            });
        </script>
        <?php
    }
}

// Initialize admin
new Simple_Woo_Events_Admin();
