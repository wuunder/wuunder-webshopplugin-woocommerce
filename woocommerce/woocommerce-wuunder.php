<?php
/**
 * Plugin Name: WooCommerce Wuunder
 * Plugin URI: http://www.wuunder.co
 * Description: Wuunder shipping method
 * Version: 0.4
 * Author: MONSTER Internet & Marketing Solutions | Jeroen Branje
 * Author URI: http://mims.nl
 *
 *
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

		const VERSION = '0.1';

		public function __construct() {

			self::$plugin_basename = plugin_basename(__FILE__);
			self::$plugin_url = plugin_dir_url(self::$plugin_basename);
			self::$plugin_path = trailingslashit(dirname(__FILE__));
			
			add_action( 'admin_enqueue_scripts', array( &$this, 'add_admin_styles_scripts' ) );
			
			require_once(WW_PLUGIN_ADMIN_DIR.'/wcwuunder-admin.php');
			require_once(WW_PLUGIN_ADMIN_DIR .'/wcwuunder-nlpostcode-fields.php');

			//add_action('plugins_loaded', 'ww_load_textdomain');

		}

		public function ww_load_textdomain() {
			load_plugin_textdomain( 'woocommerce-wuunder', false, WW_PLUGIN_DIR . '/lang/' );
		}

		public function add_admin_styles_scripts(){
		 	global $post_type;
			if( $post_type == 'shop_order' ) {
				wp_enqueue_script( 'thickbox' );
				wp_enqueue_style( 'thickbox' );
				wp_enqueue_script( 'wcwuunder-export', plugin_dir_url(__FILE__) . 'assets/js/wcwuunder-script.js', array( 'jquery', 'thickbox' ) );

				if ( version_compare( WOOCOMMERCE_VERSION, '2.1', '<=' ) ) {
					// Old versions
					wp_register_style( 'wcwuunder-admin-styles', plugins_url( '/assets/css/wcwuunder-admin-styles.css', __FILE__ ), array(), '', 'all' );
				} else {
					// WC 2.1+, MP6 style with larger buttons
					wp_register_style( 'wcwuunder-admin-styles', plugins_url( '/assets/css/wcwuunder-admin-styles-wc21.css', __FILE__ ), array(), '', 'all' );
				}				

				wp_enqueue_style( 'wcwuunder-admin-styles' );  
			}
		}

	}
}

new Woocommerce_Wuunder();