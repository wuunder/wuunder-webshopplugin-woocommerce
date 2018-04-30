<?php
/**
 * Plugin Name: WooCommerce Wuunder
 * Plugin URI: http://wearewuunder.com
 * Description: Wuunder shipping plugin
 * Version: 2.3.1
 * Author: Wuunder
 * Author URI: http://wearewuunder.com
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!defined('WW_PLUGIN_DIR'))
    define('WW_PLUGIN_DIR', dirname(__FILE__));

if (!defined('WW_PLUGIN_ROOT_PHP'))
    define('WW_PLUGIN_ROOT_PHP', dirname(__FILE__) . '/' . basename(__FILE__));

if (!defined('WW_PLUGIN_ADMIN_DIR'))
    define('WW_PLUGIN_ADMIN_DIR', dirname(__FILE__) . '/includes');

if (!defined('WW_PLUGIN_TEMPLATE_DIR'))
    define('WW_PLUGIN_TEMPLATE_DIR', dirname(__FILE__) . '/template');

if (!defined('WOOCOMMERCE_VERSION')) {
    if (!function_exists('get_plugins'))
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');

    // Create the plugins folder and file variables
    $plugin_folder = get_plugins('/' . 'woocommerce');
    $plugin_file = 'woocommerce.php';

    // If the plugin version number is set, return it
    if (isset($plugin_folder[$plugin_file]['Version'])) {
        define('WOOCOMMERCE_VERSION', $plugin_folder[$plugin_file]['Version']);
    } else {
        // Otherwise return null
        define('WOOCOMMERCE_VERSION', NULL);
    }

}

require_once 'vendor/autoload.php';

if (!class_exists('Woocommerce_Wuunder')) {

    class Woocommerce_Wuunder
    {

        public static $plugin_url;
        public static $plugin_path;
        public static $plugin_basename;

        const VERSION = '2.3.1';

        public function __construct()
        {

            self::$plugin_basename = plugin_basename(__FILE__);
            self::$plugin_url = plugin_dir_url(self::$plugin_basename);
            self::$plugin_path = trailingslashit(dirname(__FILE__));

            add_action('admin_enqueue_scripts', array(&$this, 'add_admin_styles_scripts'));

            require_once(WW_PLUGIN_ADMIN_DIR . '/wcwuunder-admin.php');
            include_once('includes/checkout.php');

            add_action('wp_loaded', function () {
                if (strpos($_SERVER['REQUEST_URI'], "/wuunder/webhook") === 0 && $_SERVER['REQUEST_METHOD'] === 'POST') {
                    $this->webhook();
                    exit;
                }
            });
            add_action('load-edit.php', array(&$this, 'webhook'));
        }

        public function ww_load_textdomain()
        {
            load_plugin_textdomain('woocommerce-wuunder', false, WW_PLUGIN_DIR . '/lang/');
        }

        public function add_admin_styles_scripts()
        {
            global $post_type;
            if ($post_type == 'shop_order') {
//                wp_enqueue_script('thickbox');
//                wp_enqueue_style('thickbox');
//
//                wp_register_style('bootstrap-admin-styles', plugins_url('/assets/css/bootstrap-simplex.min.css', __FILE__), array(), '', 'all');
//                wp_enqueue_style('bootstrap-admin-styles');
            }
        }

        public function webhook()
        {
            if (!isset($_REQUEST['order']) || !isset($_REQUEST['token'])) {
                wp_redirect("", 500);
                return;
            }
            $orderId = $_REQUEST['order'];
            $bookingToken = $_REQUEST['token'];
            $data = json_decode(file_get_contents('php://input'), true);
            $errorRedirect = true;

            $orderBookingToken = get_post_meta($orderId, '_wuunder_label_booking_token')[0];
            if ($data['action'] === "shipment_booked") {
              if ($bookingToken === $orderBookingToken) {
                  if (!empty($data['shipment']['id']) || !empty($data['shipment']['track_and_trace_url']) || !empty($data['shipment']['label_url'])) {
                      update_post_meta($orderId, '_wuunder_label_id', $data['shipment']['id']);
                      update_post_meta($orderId, '_wuunder_track_and_trace_url', $data['shipment']['track_and_trace_url']);
                      update_post_meta($orderId, '_wuunder_label_url', $data['shipment']['label_url']);

                      $order = new WC_Order($orderId);
                      $order->update_status(get_option("wc_wuunder_post_booking_status"));
                      $errorRedirect = false;
                  }
              }
            } elseif ($data['action'] === "track_and_trace_updated") {
              // This is the 2nd webhook
              $order = wc_get_order($orderId);
              $note = __("Het pakket is aangemeld bij: ". $data["carrier_name"] ."\n De track and trace code is: ".$data["track_and_trace_code"]);
              $order->add_order_note($note);
              $order->save();
              $errorRedirect = false;
            }

            if($errorRedirect){wp_redirect("", 500);}
        }

    }
}

new Woocommerce_Wuunder();
