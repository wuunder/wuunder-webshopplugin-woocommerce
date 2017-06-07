<?php
/**
 * Plugin Name: WooCommerce Wuunder
 * Plugin URI: http://wearewuunder.com
 * Description: Wuunder shipping plugin
 * Version: 1.0
 * Author: Wuunder
 * Author URI: http://wearewuunder.com
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if (!defined('WW_PLUGIN_DIR'))
    define( 'WW_PLUGIN_DIR', dirname(__FILE__) );

if (!defined('WW_PLUGIN_ROOT_PHP'))
    define( 'WW_PLUGIN_ROOT_PHP', dirname(__FILE__).'/'.basename(__FILE__)  );

if (!defined('WW_PLUGIN_ADMIN_DIR'))
    define( 'WW_PLUGIN_ADMIN_DIR', dirname(__FILE__) . '/includes' );

if (!defined('WW_PLUGIN_TEMPLATE_DIR'))
    define( 'WW_PLUGIN_TEMPLATE_DIR', dirname(__FILE__) . '/template' );

if( !class_exists('Woocommerce_Wuunder') ) {

	class Woocommerce_Wuunder {

		public static $plugin_url;
		public static $plugin_path;
		public static $plugin_basename;

		const VERSION = '1.0';

		public function __construct() {

			self::$plugin_basename = plugin_basename(__FILE__);
			self::$plugin_url = plugin_dir_url(self::$plugin_basename);
			self::$plugin_path = trailingslashit(dirname(__FILE__));

			add_action( 'admin_enqueue_scripts', array( &$this, 'add_admin_styles_scripts' ) );

			require_once(WW_PLUGIN_ADMIN_DIR.'/wcwuunder-admin.php');
			require_once(WW_PLUGIN_ADMIN_DIR.'/wcwuunder-postcode-fields.php');

		}

		public function ww_load_textdomain() {
			load_plugin_textdomain( 'woocommerce-wuunder', false, WW_PLUGIN_DIR . '/lang/' );
		}

		public function add_admin_styles_scripts(){
		 	global $post_type;
			if( $post_type == 'shop_order' ) {
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );

				// wp_register_style( 'bootstrap-admin-styles', plugins_url( '/assets/css/bootstrap-simplex.min.css', __FILE__ ), array(), '', 'all' );
				// wp_enqueue_style( 'bootstrap-admin-styles' );

			}
		}

	}
}

new Woocommerce_Wuunder();
