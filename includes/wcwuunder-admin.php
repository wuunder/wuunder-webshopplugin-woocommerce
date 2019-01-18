<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'WC_Wuunder_Admin' ) ) {

	class WC_Wuunder_Admin {

		public static $ajax_nonce;

		public function __construct() {

			add_action( 'admin_init', array( &$this, 'init_admin' ) );

		}

		public function init_admin() {

			if( false == array_key_exists( 'woocommerce', $GLOBALS ) ) {
				return;
			}

			require_once( WW_PLUGIN_ADMIN_DIR . '/wcwuunder-settings.php' );
			require_once( WW_PLUGIN_ADMIN_DIR . '/wcwuunder-create.php' );

		}
		
	}

}

new WC_Wuunder_Admin();