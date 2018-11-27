<?php
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if (!class_exists('WC_Wuunder_Settings' ) ) {

    class WC_Wuunder_Settings {

        public function __construct() {
            // Add setting tab "Reatiler" on woocommerce settings page
            add_filter( 'woocommerce_settings_tabs_array', array( &$this, 'add_settings_tab' ), 50 );
            add_action( 'woocommerce_settings_tabs_wuunder', array( &$this, 'settings_tab' ) );
            add_action( 'woocommerce_update_options_wuunder', array(&$this, 'update_settings' ) );

        }

        public static function add_settings_tab( $settings_tabs ) {
            $settings_tabs['wuunder'] = __( 'Wuunder', 'woocommerce-wuunder' );
            return $settings_tabs;
        }

        public static function settings_tab() {
            ?>
            <style>
                .address {
                    width: 550px;
                    padding: 0 20px 20px 20px;
                    border: 1px solid #ccc;
                    background-color: #fff;
                    margin-right: 20px;
                    float: left;
                }

                .mappings {
                    width: calc(100% - 700px);
                    min-width: 400px;
                    padding: 0 20px 20px 20px;
                    border: 1px solid #ccc;
                    background-color: #fff;
                    margin-right: 20px;
                    float: left;
                }

                .address h2, .mappings h2 {
                    margin-bottom: 5px;
                }

                .address p, .mappings p {
                    margin-top: 5px;
                }

                .address .form-table th {
                    width: 90px;
                }

                .address .form-table th, .address .form-table td, .mappings .form-table th, .mappings .form-table td {
                    padding-top: 0;
                    padding-bottom: 0;
                }

                .mappings .form-table th {
                    width: 240px;
                }
                .mappings .form-table select, .mappings .form-table input {
                    width: 100%;
                }
            </style>
            <?php

            echo '<div style="background-color:#fff; border:1px solid #CCCCCC; margin-bottom:10px; padding:10px;">
	    		<img src="' . Woocommerce_Wuunder::$plugin_url . 'assets/images/wuunder_logo.png" style="float:left; display:inline-block; padding:20px 30px 20px 20px; width:80px;">
				<h4>Hallo, wij zijn Wuunder</h4>
				<p>En we maken het versturen en ontvangen van documenten, pakketten en pallets makkelijk en voordelig. Met ons platform boek je een zending of retour via mobiel, Mac, PC en webshop plug-in. Wij vergelijken de bekende vervoerders, kiezen de beste prijs en halen de zending bij jou of iemand anders op. En daarna volg je de zending in het overzichtsscherm en klik je op de track & trace link voor meer details. Een foto sturen, vraag stellen of iets toelichten? Dat doe je via de Wuunder-chat. Wel zo persoonlijk.</p>
				<p>Meer weten? Bezoek onze website <a href="http://www.wearewuunder.com/" target="_blank">www.wearewuunder.com</a> of stuur een e-mail naar <a href="mailto:info@WeAreWuunder.com" target="_blank">Info@WeAreWuunder.com</a>.</p>
            </div>';

            woocommerce_admin_fields( self::get_settings() );
            echo '<h1>Afhaaladressen</h1>';
            echo '<p>Op onderstaande adressen worden je zendingen opgehaald of retouren weer afgeleverd. Gebruik de Bedrijfsnaam, contactpersoon en telefoonnummer waar de chauffeur van de vervoerder terecht kan met vragen. Naar het e-mail adres dat je hier gebruikt sturen we ook de verzendlabels mocht je deze niet willen downloaden via WooCommerce. Gebruik voor retouren de knop in het scherm met de “bestellingen”.</p>';
            echo '<div class="row" style="overflow:hidden;">';
            echo '<div class="address">';
            woocommerce_admin_fields(self::get_address() );
            echo '</div>';
            echo '<div class="mappings">';
            woocommerce_admin_fields(self::get_mappings() );
            echo '</div>';
            echo '</div>';
        }

        public static function update_settings() {
            woocommerce_update_options(self::get_settings() );
            woocommerce_update_options(self::get_mappings() );
            woocommerce_update_options(self::get_address() );
        }

        public static function get_settings() {
            $statuses = wc_get_order_statuses();
            $mappedStatuses = array();
            foreach ( $statuses as $key => $value ) {
                $mappedStatuses[$key] = __( $value, 'woocommerce-wuunder' );
            }
	        $settings =
                array(
                    'section_title'     => array(
                        'name'      => __( 'Wuunder instellingen', 'woocommerce-wuunder' ),
                        'type'      => 'title',
                        'desc'      => 'Algemene instellingen voor wuunder',
                        'id'        => 'wc_wuunder_section_title'
                    ),
                    'api'               => array(
                        'name'      => __( 'Live / productie API Key', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_api'
                    ),
                    'test_api'          => array(
                        'name'      => __( 'Test / staging API Key', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_test_api'
                    ),
                    'api_status'        => array(
                        'name'      => __( 'Testmode', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'desc'      => __( 'Ja = Test / staging, Nee = Live / productie', 'woocommerce-retailer' ),
                        'options'   => array(
                            'staging'   => __( 'Ja', 'woocommerce-wuunder' ),
                            'productie' => __( 'Nee', 'woocommerce-wuunder' )
                        ),
                        'id'        => 'wc_wuunder_api_status'
                    ),
                    'post_booking_status' => array(
                        'name'      => __( 'Set order status after booking to:', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'options'   => $mappedStatuses,
                        'id'        => 'wc_wuunder_post_booking_status'
                    ),
                    'google_maps_api_key' => array(
                        'name'      => __( 'Google maps api key, voor parcelshoppicker:', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_google_maps_api_key'
                    ),
                    'default_image_base64' => array(
                        'name'      => __( 'Standaard order image (base64 string), Leeg voor geen:', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_default_image_base64'
                    ),
                    'section_end'           => array(
                        'type'      => 'sectionend',
                        'id'        => 'wc_wuunder_section_end'
                    ),
                );
	        return apply_filters( 'wc_wuunder_settings', $settings );

	    }

	    public static function get_mappings() {
            global $woocommerce;
            $shipping_methods = $woocommerce->shipping->get_shipping_methods();
            $options = array(
                'empty' => __( '--', 'woocommerce-wuunder' )
            );
            foreach ( $shipping_methods as $id => $shipping_method ) {
                $options[$id] = __( $shipping_method->method_title, 'woocommerce-wuunder' );
            }

            $settings =
                array(
                    'section_title'     => array(
                        'name'      => __( 'Mappings', 'woocommerce-wuunder' ),
                        'type'      => 'title',
                        'desc'      => 'Algemene instellingen voor wuunder',
                        'id'        => 'wc_wuunder_section_title'
                    ),
                    'mapping_method_1'  => array(
                        'name'      => __( 'Mapping verzendmethode #1', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'options'   => $options,
                        'id'        => 'wc_wuunder_mapping_method_1'
                    ),
                    'mapping_filter_1'  => array(
                        'name'      => __( 'Mapping filternaam #1', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_mapping_filter_1'
                    ),
                    'mapping_method_2'  => array(
                        'name'      => __( 'Mapping verzendmethode #2', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'options'   => $options,
                        'id'        => 'wc_wuunder_mapping_method_2'
                    ),
                    'mapping_filter_2'  => array(
                        'name'      => __( 'Mapping filternaam #2', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_mapping_filter_2'
                    ),
                    'mapping_method_3'  => array(
                        'name'      => __( 'Mapping verzendmethode #3', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'options'   => $options,
                        'id'        => 'wc_wuunder_mapping_method_3'
                    ),
                    'mapping_filter_3'  => array(
                        'name'      => __( 'Mapping filternaam #3', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_mapping_filter_3'
                    ),
                    'mapping_method_4'  => array(
                        'name'      => __( 'Mapping verzendmethode #4', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'options'   => $options,
                        'id'        => 'wc_wuunder_mapping_method_4'
                    ),
                    'mapping_filter_4'  => array(
                        'name'      => __( 'Mapping filternaam #4', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_mapping_filter_4'
                    ),
                    'section_end'       => array(
                        'type'      => 'sectionend',
                        'id'        => 'wc_wuunder_section_end'
                    ),
                );
            return apply_filters( 'wc_wuunder_settings', $settings );
        }

        public static function get_address() {

            $settings =
                array(
                    'section_title_1' => array(
                        'name'  => __( 'Standaard afhaaladres', 'woocommerce-wuunder' ),
                        'type'  => 'title',
                        'desc'  => 'Adres hoofdkantoor',
                        'id'    => 'wc_wuunder_section_title'
                    ),
                    'company'       => array(
                        'name'  => __( 'Bedrijfsnaam', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_name'
                    ),
                    'firstname'     => array(
                        'name'  => __( 'Voornaam', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_firstname'
                    ),
                    'lastname'      => array(
                        'name'  => __( 'Achternaam', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_lastname'
                    ),
                    'email'         => array(
                        'name'  => __( 'Email', 'woocommerce-wuunder' ),
                        'type'  => 'email',
                        'id'    => 'wc_wuunder_company_email'
                    ),
                    'phone'         => array(
                        'name'  => __( 'Telefoonnummer', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'desc'  => __( 'Telefoonnummer inclusief landnummer, bv NL +31612345678', 'woocommerce-wuunder' ),
                        'id'    => 'wc_wuunder_company_phone'
                    ),
                    'street'        => array(
                        'name'  => __( 'Straat', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_street'
                    ),
                    'housenumber'   => array(
                        'name'  => __( 'Huisnummer', 'woocommerce-wuunder' ),
                        'type'  => 'number',
                        'id'    => 'wc_wuunder_company_housenumber'
                    ),
                    'postcode'      => array(
                        'name'  => __( 'Postcode', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'desc'  => __( '1234AB', 'woocommerce-wuunder' ),
                        'id'    => 'wc_wuunder_company_postode'
                    ),
                    'city'          => array(
                        'name'  => __( 'Plaats', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_city'
                    ),
                    'country'       => array(
                        'name'  => __( 'Landcode', 'woocommerce-wuunder' ),
                        'desc'  => 'Landcode in ISO 3166-1 alpha-2 formaat, bv NL',
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_country'
                    ),
                    'section_end'   => array(
                        'type'  => 'sectionend',
                        'id'    => 'wc_wuunder_section_end'
                    ),
                );
            return apply_filters( 'wc_wuunder_settings_address', $settings );

        }
    }

}

new WC_Wuunder_Settings();