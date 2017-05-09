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
	    
	    public static function settings_tab() { ?>
			<style>
	        	.address{
	        		width:350px;
	        		padding:0 20px 20px 20px;
	        		border:1px solid #ccc;
	        		background-color:#fff;
	        		margin-right:20px;
	        		float:left;
	        	}
	        	.address h2{margin-bottom:5px;}
	        	.address p{margin-top:5px;}
	        	.address .form-table{}
	        	.address .form-table th{
	        		width:90px;
	        	}
	        	.address .form-table th, .address .form-table td{
	        		padding-top:0;
	        		padding-bottom:0;
	        	}
	        	.address .form-table{}
	        </style>
	    	<?php 

	    	echo '<div style="background-color:#fff; border:1px solid #CCCCCC; margin-bottom:10px; padding:10px;">
	    		<img src="'. Woocommerce_Wuunder::$plugin_url. 'assets/images/wuunder_logo.png" style="float:left; display:inline-block; padding:20px 30px 20px 20px; width:80px;">
				<h4>Hallo, wij zijn Wuunder</h4>
				<p>En we maken het versturen en ontvangen van documenten, pakketten en pallets makkelijk en voordelig. Met ons platform boek je een zending of retour via mobiel, Mac, PC en webshop plug-in. Wij vergelijken de bekende vervoerders, kiezen de beste prijs en halen de zending bij jou of iemand anders op. En daarna volg je de zending in het overzichtsscherm en klik je op de track & trace link voor meer details. Een foto sturen, vraag stellen of iets toelichten? Dat doe je via de Wuunder-chat. Wel zo persoonlijk.</p>
				<p>Meer weten? Bezoek onze website <a href="http://www.wearewuunder.com/" target="_blank">www.wearewuunder.com</a> of stuur een e-mail naar <a href="mailto:info@WeAreWuunder.com" target="_blank">Info@WeAreWuunder.com</a>.</p>
            </div>';
	
	        woocommerce_admin_fields( self::get_settings() );
	        echo '<h1>Afhaaladressen</h1>';
	        echo '<p>Op onderstaande adressen worden je zendingen opgehaald of retouren weer afgeleverd. Gebruik de Bedrijfsnaam, contactpersoon en telefoonnummer waar de chauffeur van de vervoerder terecht kan met vragen. Naar het e-mail adres dat je hier gebruikt sturen we ook de verzendlabels mocht je deze niet willen downloaden via WooCommerce. Gebruik voor retouren de knop in het scherm met de “bestellingen”.</p>';
	        echo '<div class="row" style="overflow:hidden;">';
	        	echo '<div class="address">';
	       		 	woocommerce_admin_fields( self::get_address() );
	        	echo '</div>';
	        	echo '<div class="address">';
	       		 	woocommerce_admin_fields( self::get_extra_address_1() );
	        	echo '</div>';
	        	echo '<div class="address">';
	        		woocommerce_admin_fields( self::get_extra_address_2() );
	        	echo '</div>';
	        echo '</div>';
	    }
	    
	    public static function update_settings() {
	        woocommerce_update_options( self::get_settings() );
	        woocommerce_update_options( self::get_address() );
	        woocommerce_update_options( self::get_extra_address_1() );
	        woocommerce_update_options( self::get_extra_address_2() );
	    }
	    
	    public static function get_settings() {
	
	        $settings =
	         array(
	            'section_title' => array(
	                'name'     => __( 'Wuunder instellingen', 'woocommerce-wuunder' ),
	                'type'     => 'title',
	                'desc'     => 'Algemene instellingen voor wuunder',
	                'id'       => 'wc_wuunder_section_title'
	            ),
	            'api' => array(
	                'name' => __( 'Live / productie API Key', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_api'
	            ),
	            'test_api' => array(
	                'name' => __( 'Test / staging API Key', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_test_api'
	            ),
	            'api_status' => array(
	                'name' => __( 'Testmode', 'woocommerce-wuunder' ),
	                'type' => 'select',
	                'desc' => __( 'Ja = Test / staging, Nee = Live / productie', 'woocommerce-retailer' ),
	                'options' => array(
	                	'staging' => __( 'Ja', 'woocommerce-wuunder' ),
	                	'productie' => __( 'Nee', 'woocommerce-wuunder' )
	                ),
	                'id'   => 'wc_wuunder_api_status'
	            ),
	            'section_end' => array(
                 	'type' => 'sectionend',
                	'id' => 'wc_wuunder_section_end'
            	),
	        );
	        return apply_filters( 'wc_wuunder_settings', $settings );

	    }

	    public static function get_address() {

	    	$settings =
	         array(
	         	'section_title_1' => array(
	                'name'     => __( 'Standaard afhaaladres', 'woocommerce-wuunder' ),
	                'type'     => 'title',
	                'desc'     => 'Adres hoofdkantoor',
	                'id'       => 'wc_wuunder_section_title'
	            ),
	            'company' => array(
	                'name' => __( 'Bedrijfsnaam', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_name'
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
	                'desc' => __( 'Telefoonnummer inclusief landnummer, bv NL +31612345678', 'woocommerce-wuunder' ),
	                'id'   => 'wc_wuunder_company_phone'
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
	            'country' => array(
	                'name' => __( 'Landcode', 'woocommerce-wuunder' ),
	                'desc' => 'Landcode in ISO 3166-1 alpha-2 formaat, bv NL',
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_country'
	            ),
	            'section_end' => array(
                 	'type' => 'sectionend',
                	'id' => 'wc_wuunder_section_end'
            	),
	        );
	        return apply_filters( 'wc_wuunder_settings_address', $settings );

	    }

	    public static function get_extra_address_1() {

	        $settings =
	         array(
	            'section_title_1' => array(
	                'name'     => __( 'Extra adres 1', 'woocommerce-wuunder' ),
	                'type'     => 'title',
	                'desc'     => 'Een extra afhaaladres',
	                'id'       => 'wc_wuunder_section_title'
	            ),
	            'company_1' => array(
	                'name' => __( 'Bedrijfsnaam', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_name_1'
	            ),
	            'firstname_1' => array(
	                'name' => __( 'Voornaam', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_firstname_1'
	            ),
	            'lastname_1' => array(
	                'name' => __( 'Achternaam', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_lastname_1'
	            ),
	            'email_1' => array(
	                'name' => __( 'Email', 'woocommerce-wuunder' ),
	                'type' => 'email',
	                'id'   => 'wc_wuunder_company_email_1'
	            ),
	            'phone_1' => array(
	                'name' => __( 'Telefoonnummer', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'desc' => __( 'Telefoonnummer inclusief landnummer, bv NL +31612345678', 'woocommerce-wuunder' ),
	                'id'   => 'wc_wuunder_company_phone_1'
	            ),
            	'street_extra_1' => array(
	                'name' => __( 'Straat', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_street_1'
	            ),
	            'housenumber_extra_1' => array(
	                'name' => __( 'Huisnummer', 'woocommerce-wuunder' ),
	                'type' => 'number',
	                'id'   => 'wc_wuunder_company_housenumber_1'
	            ),
	            'postcode_extra_1' => array(
	                'name' => __( 'Postcode', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'desc' => __( '1234AB', 'woocommerce-wuunder' ),
	                'id'   => 'wc_wuunder_company_postode_1'
	            ),
	            'city_extra_1' => array(
	                'name' => __( 'Plaats', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_city_1'
	            ),
	            'country_1' => array(
	                'name' => __( 'Landcode', 'woocommerce-wuunder' ),
	                'desc' => 'Landcode in ISO 3166-1 alpha-2 formaat, bv NL',
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_country_1'
	            ),
	            'section_end_1' => array(
                 	'type' => 'sectionend',
                	'id' => 'wc_wuunder_section_end'
            	),
	        );
	        return apply_filters( 'wc_wuunder_settings_extra_address_1', $settings );
	    }

	    public static function get_extra_address_2() {

	        $settings =
	         array(
	            'section_title_2' => array(
	                'name'     => __( 'Extra adres 2', 'woocommerce-wuunder' ),
	                'type'     => 'title',
	                'desc'     => 'Een extra afhaaladres',
	                'id'       => 'wc_wuunder_section_title'
	            ),
	            'company_2' => array(
	                'name' => __( 'Bedrijfsnaam', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_name_2'
	            ),
	            'firstname_2' => array(
	                'name' => __( 'Voornaam', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_firstname_2'
	            ),
	            'lastname_2' => array(
	                'name' => __( 'Achternaam', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_lastname_2'
	            ),
	            'email_2' => array(
	                'name' => __( 'Email', 'woocommerce-wuunder' ),
	                'type' => 'email',
	                'id'   => 'wc_wuunder_company_email_2'
	            ),
	            'phone_2' => array(
	                'name' => __( 'Telefoonnummer', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'desc' => __( 'Telefoonnummer inclusief landnummer, bv NL +31612345678', 'woocommerce-wuunder' ),
	                'id'   => 'wc_wuunder_company_phone_2'
	            ),
            	'street_extra_2' => array(
	                'name' => __( 'Straat', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_street_2'
	            ),
	            'housenumber_extra_2' => array(
	                'name' => __( 'Huisnummer', 'woocommerce-wuunder' ),
	                'type' => 'number',
	                'id'   => 'wc_wuunder_company_housenumber_2'
	            ),
	            'postcode_extra_2' => array(
	                'name' => __( 'Postcode', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'desc' => __( '1234AB', 'woocommerce-wuunder' ),
	                'id'   => 'wc_wuunder_company_postode_2'
	            ),
	            'city_extra_2' => array(
	                'name' => __( 'Plaats', 'woocommerce-wuunder' ),
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_city_2'
	            ),
	            'country_2' => array(
	                'name' => __( 'Landcode', 'woocommerce-wuunder' ),
	                'desc' => 'Landcode in ISO 3166-1 alpha-2 formaat, bv NL',
	                'type' => 'text',
	                'id'   => 'wc_wuunder_company_country_2'
	            ),
	            'section_end_2' => array(
                 	'type' => 'sectionend',
                	'id' => 'wc_wuunder_section_end'
            	),
	        );
	        return apply_filters( 'wc_wuunder_settings_extra_address_2', $settings );
	    }
	}

}

new WC_Wuunder_Settings();