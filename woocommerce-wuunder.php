<?php
/**
 * Plugin Name: WooCommerce Wuunder
 * Plugin URI: https://wearewuunder.com/wuunder-voor-webshops/
 * Description: Wuunder shipping plugin
 * Version: 2.6.3
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

        const VERSION = '2.6.3';

        public function __construct() {

            self::$plugin_basename = plugin_basename( __FILE__ );
            self::$plugin_url = plugin_dir_url( self::$plugin_basename );
            self::$plugin_path = trailingslashit( dirname( __FILE__ ) );

            add_action( 'admin_enqueue_scripts', array( &$this, 'wcwp_add_admin_styles_scripts' ) );

            require_once( WCWP_PLUGIN_ADMIN_DIR . '/wcwuunder-admin.php' );
            include_once( 'includes/parcelshop.php' );
            include_once( 'includes/wcwuunder-shipping-method.php' );
            include_once( 'includes/checkout.php' );
            include_once( 'includes/wcwuunder-DPD-standard-shipping.php' );


            add_action('wp_ajax_wuunder_parcelshoplocator_get_parcelshop_address', 'wcwp_getParcelshopAddress');
            add_action('wp_ajax_nopriv_wuunder_parcelshoplocator_get_parcelshop_address', 'wcwp_getParcelshopAddress');
            add_action('wp_ajax_wuunder_parcelshoplocator_get_address', 'wcwp_getAddress');
            add_action('wp_ajax_nopriv_wuunder_parcelshoplocator_get_address', 'wcwp_getAddress');

            add_action( 'wp_loaded', function () {
                if ( false !== strpos( $_SERVER['REQUEST_URI'], '/wuunder/webhook' ) && 'POST' === $_SERVER['REQUEST_METHOD'] ) {
                    $this->wcwp_webhook();
                    exit;
                }
            } );
//            add_action('load-edit.php', array( &$this, 'webhook' ) );
        }

        public function wcwp_load_textdomain() {
            load_plugin_textdomain( 'woocommerce-wuunder', false, WCWP_PLUGIN_DIR . '/lang/' );
        }

        public function wcwp_add_admin_styles_scripts() {
            global $post_type;
            if ( 'shop_order' == $post_type ) {
//                wp_enqueue_script( 'thickbox' );
//                wp_enqueue_style( 'thickbox' );
//
//                wp_register_style( 'bootstrap-admin-styles', plugins_url( '/assets/css/bootstrap-simplex.min.css', __FILE__ ), array(), '', 'all' );
//                wp_enqueue_style( 'bootstrap-admin-styles' );
            }
        }

        public function wcwp_webhook() {
            wcwp_log( 'info', 'Test webhook' );
            wcwp_log( 'info', $_REQUEST['order'] );
            wcwp_log( 'info', $_REQUEST['token'] );

            if ( !isset($_REQUEST['order'] ) || !isset( $_REQUEST['token'] ) ) {
                wp_redirect( '', 500 );
                return;
            }
            $orderId = $_REQUEST['order'];
            $bookingToken = $_REQUEST['token'];
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
