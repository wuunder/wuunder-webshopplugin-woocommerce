<?php
if ( !defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
if (!class_exists('WC_Wuunder_Settings' ) ) {

    class WC_Wuunder_Settings {

        public function __construct() {
            // Add setting tab "Reatiler" on woocommerce settings page
            add_filter( 'woocommerce_settings_tabs_array', array( &$this, 'wcwp_add_settings_tab' ), 50 );
            add_action( 'woocommerce_settings_tabs_wuunder', array( &$this, 'wcwp_settings_tab' ) );
            if ( version_compare( WOOCOMMERCE_VERSION, '3.7', '<' )) {
                add_action('woocommerce_update_options_wuunder', array(&$this, 'wcwp_update_settings'));
            }
        }

        public static function wcwp_save_action_for_update_settings() {
            add_action( 'woocommerce_update_options_wuunder', array(WC_Wuunder_Settings::class,'wcwp_update_settings' ));
        }

        public static function wcwp_add_settings_tab($settings_tabs ) {
            $settings_tabs['wuunder'] = __( 'Wuunder', 'woocommerce-wuunder' );
            return $settings_tabs;
        }

        public static function wcwp_settings_tab() {
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
				<h4>' . __('Hello, we are Wuunder', 'woocommerce-wuunder') . '</h4>
				<p>' . __('and we make sending and receiving parcels easy and cheap. With our platform you can book via mobile, Mac, PC and webshop plug-ins.', 'woocommerce-wuunder') . '</p>
				<p>' . __('Want to know more? Visit our website', 'woocommerce-wuunder') . ' <a href="http://www.wearewuunder.com/" target="_blank">www.wearewuunder.com</a> ' . __('or send an e-mail to', 'woocommerce-wuunder') . ' <a href="mailto:info@WeAreWuunder.com" target="_blank">Info@WeAreWuunder.com</a>.</p>
            </div>';

            woocommerce_admin_fields( self::wcwp_get_settings() );
            echo '<h1>' . __('Pickupaddresses', 'woocommerce-wuunder') . '</h1>';
            echo '<p>' . __('On this address your shipment will be picked-up or send back to.', 'woocommerce-wuunder') . '</p>';
            echo '<div class="row" style="overflow:hidden;">';
            echo '<div class="address">';
            woocommerce_admin_fields(self::wcwp_get_address() );
            echo '</div>';
            echo '<div class="mappings">';
            woocommerce_admin_fields(self::wcwp_get_mappings() );
            echo '</div>';
            echo '</div>';
        }

        public static function wcwp_update_settings() {
            woocommerce_update_options(self::wcwp_get_settings() );
            woocommerce_update_options(self::wcwp_get_mappings() );
            woocommerce_update_options(self::wcwp_get_address() );
        }

        public static function wcwp_get_settings() {
            $statuses = wc_get_order_statuses();
            $mappedStatuses = array();
            foreach ( $statuses as $key => $value ) {
                $mappedStatuses[$key] = __( $value, 'woocommerce-wuunder' );
            }
	        $settings =
                array(
                    'section_title'     => array(
                        'name'      => __( 'Wuunder settings', 'woocommerce-wuunder' ),
                        'type'      => 'title',
                        'desc'      => __('General settings', 'woocommerce-wuunder' ),
                        'id'        => 'wc_wuunder_section_title'
                    ),
                    'api'               => array(
                        'name'      => __( 'Live / production API Key', 'woocommerce-wuunder' ),
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
                        'desc'      => __( 'Yes = Test / staging, No = Live / production', 'woocommerce-wuunder' ),
                        'options'   => array(
                            'staging'   => __( 'Yes', 'woocommerce-wuunder' ),
                            'productie' => __( 'No', 'woocommerce-wuunder' )
                        ),
                        'id'        => 'wc_wuunder_api_status'
                    ),
                    'post_booking_status' => array(
                        'name'      => __( 'Set order status after booking to:', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'options'   => $mappedStatuses,
                        'id'        => 'wc_wuunder_post_booking_status'
                    ),
                    'default_image_base64' => array(
                        'name'      => __( 'Default order image (base64 string), Empty for none:', 'woocommerce-wuunder' ),
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

	    public static function wcwp_get_mappings() {
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
                        'name'      => __( 'Mapping shippingmethod #1', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'options'   => $options,
                        'id'        => 'wc_wuunder_mapping_method_1'
                    ),
                    'mapping_filter_1'  => array(
                        'name'      => __( 'Mapping filtername #1', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_mapping_filter_1'
                    ),
                    'mapping_method_2'  => array(
                        'name'      => __( 'Mapping shippingmethod #2', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'options'   => $options,
                        'id'        => 'wc_wuunder_mapping_method_2'
                    ),
                    'mapping_filter_2'  => array(
                        'name'      => __( 'Mapping filtername #2', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_mapping_filter_2'
                    ),
                    'mapping_method_3'  => array(
                        'name'      => __( 'Mapping shippingmethod #3', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'options'   => $options,
                        'id'        => 'wc_wuunder_mapping_method_3'
                    ),
                    'mapping_filter_3'  => array(
                        'name'      => __( 'Mapping filtername #3', 'woocommerce-wuunder' ),
                        'type'      => 'text',
                        'id'        => 'wc_wuunder_mapping_filter_3'
                    ),
                    'mapping_method_4'  => array(
                        'name'      => __( 'Mapping shippingmethod #4', 'woocommerce-wuunder' ),
                        'type'      => 'select',
                        'options'   => $options,
                        'id'        => 'wc_wuunder_mapping_method_4'
                    ),
                    'mapping_filter_4'  => array(
                        'name'      => __( 'Mapping filtername #4', 'woocommerce-wuunder' ),
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

        public static function wcwp_get_address() {

            $settings =
                array(
                    'section_title_1' => array(
                        'name'  => __( 'Default pickupaddress', 'woocommerce-wuunder' ),
                        'type'  => 'title',
                        'desc'  => 'Adres hoofdkantoor',
                        'id'    => 'wc_wuunder_section_title'
                    ),
                    'company'       => array(
                        'name'  => __( 'Company name', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_name'
                    ),
                    'firstname'     => array(
                        'name'  => __( 'Firstname', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_firstname'
                    ),
                    'lastname'      => array(
                        'name'  => __( 'Lastname', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_lastname'
                    ),
                    'email'         => array(
                        'name'  => __( 'E-mail', 'woocommerce-wuunder' ),
                        'type'  => 'email',
                        'id'    => 'wc_wuunder_company_email'
                    ),
                    'phone'         => array(
                        'name'  => __( 'Phonenumber', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'desc'  => __( 'Phonenumber including land prefix: NL +31612345678', 'woocommerce-wuunder' ),
                        'id'    => 'wc_wuunder_company_phone'
                    ),
                    'street'        => array(
                        'name'  => __( 'Streetname', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_street'
                    ),
                    'housenumber'   => array(
                        'name'  => __( 'Housenumber', 'woocommerce-wuunder' ),
                        'type'  => 'number',
                        'id'    => 'wc_wuunder_company_housenumber'
                    ),
                    'postcode'      => array(
                        'name'  => __( 'Zipcode', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_postode'
                    ),
                    'city'          => array(
                        'name'  => __( 'Locality', 'woocommerce-wuunder' ),
                        'type'  => 'text',
                        'id'    => 'wc_wuunder_company_city'
                    ),
                    'country'       => array(
                        'name'  => __( 'Landcode ISO-2', 'woocommerce-wuunder' ),
                        'desc'  => __( 'Landcode in ISO 3166-1 alpha-2 format: NL', 'woocommerce-wuunder' ),
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