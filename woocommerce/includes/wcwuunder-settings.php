<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if( !class_exists('WC_Wuunder_Settings') ) {

	class WC_Wuunder_Settings {

		public function __construct() {
			// Add setting tab "Reatiler" on woocommerce settings page
			add_filter( 'woocommerce_settings_tabs_array', array(&$this, 'add_settings_tab'), 50 );
        	add_action( 'woocommerce_settings_tabs_wuunder', array(&$this, 'settings_tab' ) );
        	add_action( 'woocommerce_update_options_wuunder', array(&$this, 'update_settings' ) );
			
		}

		public static function add_settings_tab( $settings_tabs ) {
	        $settings_tabs['wuunder'] = __( 'Wuunder', 'woocommerce-wuunder' );
	        return $settings_tabs;
	    }
	    
	    public static function settings_tab() {
	        woocommerce_admin_fields( self::get_settings() );
	    }
	    
	    public static function update_settings() {
	        woocommerce_update_options( self::get_settings() );
	    }
	    
	    public static function get_settings() {
	        $settings = array(
	            'section_title' => array(
	                'name'     => __( 'Wuunder instellingen', 'woocommerce-wuunder' ),
	                'type'     => 'title',
	                'desc'     => '',
	                'id'       => 'wc_wuunder_section_title'
	            ),
	            'api' => array(
	                'name' => __( 'API Key', 'woocommerce-wuunder' ),
	                'type' => 'text',
					//'desc' => __( '', 'woocommerce-wuunder' ),
	                'id'   => 'wc_wuunder_api'
	            ),
	            'company' => array(
	                'name' => __( 'Bedrijfsnaam', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_name'
	            ),
	            'street' => array(
	                'name' => __( 'Straat', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_street'
	            ),
	            'housenumber' => array(
	                'name' => __( 'Huisnummer', 'woocommerce-wuunder' ),
	                'type' => 'number',
	                'id'   => 'wc_wuunder_company_housenumber'
	            ),
	            'postcode' => array(
	                'name' => __( 'Postcode', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'desc' => __( '1234AB', 'woocommerce-wuunder' ),
	                'id'   => 'wc_wuunder_company_postode'
	            ),
	            'city' => array(
	                'name' => __( 'Plaats', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_city'
	            ),
	            'firstname' => array(
	                'name' => __( 'Voornaam', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_firstname'
	            ),
	            'lastname' => array(
	                'name' => __( 'Achternaam', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_lastname'
	            ),
	            'email' => array(
	                'name' => __( 'Email', 'woocommerce-wuunder' ),
	                'type' => 'email',
	                'id'   => 'wc_wuunder_company_email'
	            ),
	            'phone' => array(
	                'name' => __( 'Telefoonnummer', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'desc' => __( '+31612345678', 'woocommerce-wuunder' ),
	                'id'   => 'wc_wuunder_company_phone'
	            ),
	        );
	        return apply_filters( 'wc_wuunder_settings', $settings );
	    }
	}

}

new WC_Wuunder_Settings();