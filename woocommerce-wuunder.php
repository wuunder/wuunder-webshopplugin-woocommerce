<?php
/**
 * Plugin Name: Wuunder for-woocommerce
 * Plugin URI: https://wearewuunder.com/wuunder-voor-webshops/
 * Description: Wuunder shipping plugin
 * Version: 2.7.16
 * Author: Wuunder
 * Author URI: http://wearewuunder.com
 */

if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( !defined( 'WCWP_PLUGIN_DIR' ) ) {
    define( 'WCWP_PLUGIN_DIR', dirname( __FILE__ ) );
}

if ( !defined( 'WCWP_PLUGIN_ROOT_PHP' ) ) {
    define('WCWP_PLUGIN_ROOT_PHP', dirname( __FILE__ ) . '/' . basename( __FILE__ ) );
}

if ( !defined( 'WCWP_PLUGIN_ADMIN_DIR' ) ) {
    define( 'WCWP_PLUGIN_ADMIN_DIR', dirname( __FILE__ ) . '/includes' );
}

if ( !defined( 'WCWP_PLUGIN_TEMPLATE_DIR' ) ) {
    define( 'WCWP_PLUGIN_TEMPLATE_DIR', dirname( __FILE__ ) . '/template' );
}

if ( !defined( 'WOOCOMMERCE_VERSION' ) ) {
    if ( !function_exists( 'get_plugins' ) )
        require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    // Create the plugins folder and file variables
    $plugin_folder = get_plugins( '/' . 'woocommerce' );
    $plugin_file = 'woocommerce.php';

    // If the plugin version number is set, return it
    if ( isset( $plugin_folder[$plugin_file]['Version'] ) ) {
        define( 'WOOCOMMERCE_VERSION', $plugin_folder[$plugin_file]['Version'] );
    } else {
        // Otherwise return null
        define( 'WOOCOMMERCE_VERSION', NULL );
    }

}

require_once 'vendor/autoload.php';
require_once WCWP_PLUGIN_ADMIN_DIR . '/logger.php';

if ( !class_exists( 'Woocommerce_Wuunder' ) ) {

    class Woocommerce_Wuunder {

        public static $plugin_url;
        public static $plugin_path;
        public static $plugin_basename;

        const VERSION = '2.7.16';

        public function __construct() {

            self::$plugin_basename = plugin_basename( __FILE__ );
            self::$plugin_url = plugin_dir_url( self::$plugin_basename );
            self::$plugin_path = trailingslashit( dirname( __FILE__ ) );

            require_once( WCWP_PLUGIN_ADMIN_DIR . '/wcwuunder-admin.php' );
            include_once( 'includes/parcelshop.php' );
            include_once( 'includes/wcwuunder-settings.php' );
            include_once( 'includes/wcwuunder-shipping-method.php' );
            include_once( 'includes/checkout.php' );
            include_once( 'includes/wcwuunder-DPD-standard-shipping.php' );

            add_action('wp_ajax_wuunder_parcelshoplocator_get_parcelshop_address', 'wcwp_getParcelshopAddress');
            add_action('wp_ajax_nopriv_wuunder_parcelshoplocator_get_parcelshop_address', 'wcwp_getParcelshopAddress');
            add_action('wp_ajax_wuunder_parcelshoplocator_get_address', 'wcwp_getAddress');
            add_action('wp_ajax_nopriv_wuunder_parcelshoplocator_get_address', 'wcwp_getAddress');
            add_action('wp_ajax_wuunder_parcelshoplocator_set_selected_parcelshop', 'wcwp_setSelectedParcelshop');
            add_action('wp_ajax_nopriv_wuunder_parcelshoplocator_set_selected_parcelshop', 'wcwp_setSelectedParcelshop');
            add_action('wp_ajax_wuunder_parcelshoplocator_get_selected_parcelshop', 'wcwp_getSelectedParcelshop');
            add_action('wp_ajax_nopriv_wuunder_parcelshoplocator_get_selected_parcelshop', 'wcwp_getSelectedParcelshop');

            add_action( 'wp_loaded', function () {
                if ( false !== strpos( $_SERVER['REQUEST_URI'], '/wuunder/webhook' ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
                    $this->wcwp_webhook();
                    exit;
                }
            } );
            if ( version_compare( WOOCOMMERCE_VERSION, '3.7', '>=' )) {
                add_action( 'wp_loaded', array(WC_Wuunder_Settings::class, 'wcwp_save_action_for_update_settings' ) );
            }
            add_action('plugins_loaded', array( &$this, 'wcwp_load_textdomain' ) );
        }

        public function wcwp_load_textdomain() {
            $domain = 'woocommerce-wuunder';
            load_plugin_textdomain( $domain, FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
        }

        public function wcwp_webhook() {
            if ( !isset($_REQUEST['order'] ) || !isset( $_REQUEST['token'] ) ) {
                wp_redirect( '', 500 );
                return;
            }
            $orderId = sanitize_text_field($_REQUEST['order']);
            $bookingToken = sanitize_text_field($_REQUEST['token']);

            wcwp_log( 'info', 'Test webhook' );
            wcwp_log( 'info', $orderId );
            wcwp_log( 'info', $bookingToken );


            $data = json_decode(file_get_contents( 'php://input' ), true );
            $errorRedirect = true;

            $orderBookingToken = get_post_meta( $orderId, '_wuunder_label_booking_token' )[0];
            if ( 'shipment_booked' === $data['action'] ) {
                if ( $orderBookingToken === $bookingToken ) {
                    if ( ! empty( $data['shipment']['id'] ) || ! empty($data['shipment']['track_and_trace_url']) || ! empty( $data['shipment']['label_url'] ) ) {
                        update_post_meta( $orderId, '_wuunder_label_id', $data['shipment']['id'] );
                        update_post_meta( $orderId, '_wuunder_track_and_trace_url', $data['shipment']['track_and_trace_url'] );
                        update_post_meta( $orderId, '_wuunder_label_url', $data['shipment']['label_url'] );

                        $order = new WC_Order( $orderId );
                        $order->update_status( get_option( 'wc_wuunder_post_booking_status' ) );
                        $errorRedirect = false;
                    }
                }
            } elseif ( 'track_and_trace_updated' === $data['action'] ) {
                // This is the 2nd webhook
                $order = wc_get_order( $orderId );
                $note = __( 'Het pakket is aangemeld bij: ' . $data['carrier_name'] . '\n De track and trace code is: ' . $data['track_and_trace_code'] );
                $order->add_order_note( $note );
                $order->save();
                $errorRedirect = false;
            }

            if ( $errorRedirect ) {
                wp_redirect('', 500 );
            }
        }

    }
}

new Woocommerce_Wuunder();
