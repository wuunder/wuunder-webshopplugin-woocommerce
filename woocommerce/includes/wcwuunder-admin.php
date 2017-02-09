<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('WC_Wuunder_Admin') ) {

	class WC_Wuunder_Admin {

		public static $ajax_nonce;

		public function __construct() {

			add_action( 'admin_init', array( &$this, 'init_admin' ) );
			//add_action( 'admin_notices',  array( &$this, 'display_admin_notices' ) );

		}

		public function init_admin() {

			if( array_key_exists('woocommerce', $GLOBALS) == false) {
				return;
			}

			//self::$ajax_nonce = wp_create_nonce( 'ww_ajax_nonce' );
/*
			//add capability to administrator
			$role = get_role( 'administrator' );
			$role->add_cap( Woocommerce_Retailer_Mass_Order::CAPABILITY );
*/			
			//require_once(WW_PLUGIN_ADMIN_DIR . '/wcwuunder-nlpostcode-fields.php' );
			require_once(WW_PLUGIN_ADMIN_DIR . '/wcwuunder-settings.php' );
			require_once(WW_PLUGIN_ADMIN_DIR . '/wcwuunder-create.php' );
			//
			//include_once(WW_PLUGIN_ADMIN_DIR . '/wcwuunder-nlpostcode-fields.php' );

		}
		
	}

}

new WC_Wuunder_Admin();