<?php
/**
 * Main plugin class
 *
 * @package Simple_Woo_Events
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Main plugin class
 */
class Simple_Woo_Events {

    /**
     * Single instance of the class
     *
     * @var Simple_Woo_Events
     */
    protected static $instance = null;

    /**
     * Main plugin instance
     *
     * @return Simple_Woo_Events
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        $this->includes();
        $this->init_hooks();
    }

    /**
     * Include required files
     */
    private function includes() {
        require_once SIMPLE_WOO_EVENTS_PLUGIN_DIR . 'admin/class-admin.php';
        require_once SIMPLE_WOO_EVENTS_PLUGIN_DIR . 'includes/class-single-variation.php';
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ) );
        add_action( 'woocommerce_single_product_summary', array( $this, 'display_event_details' ), 25 );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain( 'simple-woo-events', false, dirname( plugin_basename( SIMPLE_WOO_EVENTS_PLUGIN_DIR ) ) . '/languages' );
    }

    /**
     * Display event details on single product page
     */
    public function display_event_details() {
        global $product;

        if ( ! $product || $product->get_type() !== 'variable' ) {
            return;
        }

        $is_event = get_post_meta( $product->get_id(), '_is_event', true );
        $is_multiple_days = get_post_meta( $product->get_id(), '_is_multiple_days', true );

        if ( 'yes' !== $is_event ) {
            return;
        }

        $venue = get_post_meta( $product->get_id(), '_event_venue', true );
        $start_date = get_post_meta( $product->get_id(), '_event_start_date', true );
        $start_time = get_post_meta( $product->get_id(), '_event_start_time', true );
        $end_date = get_post_meta( $product->get_id(), '_event_end_date', true );
        $end_time = get_post_meta( $product->get_id(), '_event_end_time', true );

        if ( ! $venue && ! $start_date ) {
            return;
        }

        echo '<div class="event-details">';
        echo '<h2>' . esc_html__( 'Event Details', 'simple-woo-events' ) . '</h2>';

        if ( $start_date ) {
            $start_datetime = new DateTime( $start_date . ' ' . ($start_time ?: '00:00') );
            
            echo '<p class="event-date">';
            echo '<span class="dashicons dashicons-calendar-alt"></span> ';
            
            if ( 'yes' === $is_multiple_days && $end_date ) {
                $end_datetime = new DateTime( $end_date . ' ' . ($end_time ?: '00:00') );
                echo esc_html( $start_datetime->format( 'F d' ) ) . ' - ' . 
                     esc_html( $end_datetime->format( 'F d, Y' ) );
            } else {
                echo esc_html( $start_datetime->format( 'F d, Y' ) );
            }
            echo '</p>';
            
            if ( $start_time && $end_time ) {
                echo '<p class="event-time">';
                echo '<span class="dashicons dashicons-clock"></span> ';
                echo esc_html( date( 'h:i A', strtotime( $start_time ) ) );
                echo ' - ';
                echo esc_html( date( 'h:i A', strtotime( $end_time ) ) );
                echo '</p>';
            }
        }

        if ( $venue ) {
            echo '<p class="event-venue"><strong>' . esc_html__( 'Venue:', 'simple-woo-events' ) . '</strong> ' . esc_html( $venue ) . '</p>';
        }

        echo '</div>';

        // Add some basic styling
        ?>
        <style>
            .event-details {
                margin: 1em 0;
                padding: 1.2em;
                background: #f8f8f8;
                border-radius: 4px;
            }
            .event-details h2 {
                margin-top: 0;
                margin-bottom: 1em;
                font-size: 1.5em;
            }
            .event-details p {
                margin-bottom: 0.8em;
                display: flex;
                align-items: center;
                gap: 8px;
            }
            .event-details p:last-child {
                margin-bottom: 0;
            }
            .event-details .dashicons {
                color: #666;
                font-size: 1.2em;
                width: auto;
                height: auto;
            }
            .event-date, .event-time {
                font-size: 1.1em;
                color: #333;
            }
        </style>
        <?php
    }

    /**
     * Enqueue required styles
     */
    public function enqueue_styles() {
        wp_enqueue_style( 'dashicons' );
    }
}
