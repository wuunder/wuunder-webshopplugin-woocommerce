<?php

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

	function your_shipping_method_init() {
		if ( ! class_exists( 'WC_wuunder_parcelshop' ) ) {
			class WC_wuunder_parcelshop extends WC_Shipping_Method {
				/**
				 * Constructor for your shipping class
				 *
				 * @access public
				 * @return void
				 */
				public function __construct() {
					$this->id                 = 'wuunder_parcelshop';
					$this->method_title       = __( 'Wuunder Parcelshop' );
					$this->method_description = __( 'Wuunder Parcelshop locator, laat klanten zelf een locatie kiezen om hun pakketje op te halen.' );

					$this->enabled            = isset( $this->settings['enabled'] ) ? $this->settings['enabled'] : 'yes';
					$this->title              = "Wuunder Parcelshop Locator";
          $this->supports           = array(
                                  			'shipping-zones',
                                  			'instance-settings',
                                  			'instance-settings-modal',
                                        'settings'
                                  		);
					$this->init();
				}

				/**
				 * Init your settings
				 *
				 * @access public
				 * @return void
				 */
				function init() {
					// Load the settings API
					$this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
					$this->init_settings(); // This is part of the settings API. Loads settings you previously init.

					// Save settings in admin if you have any defined
					add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
				}

        function init_form_fields() {
            $this->form_fields = array(
             'enabled' => array(
                  'title' => __( 'Enable', 'wuunder_parcelshop' ),
                  'type' => 'checkbox',
                  'description' => __( 'Enable this shipping.', 'wuunder_parcelshop' ),
                  'default' => 'no'
								),
             'cost' => array(
                  'title' => __( 'Kosten', 'wuunder_parcelshop' ),
                  'type' => 'number',
                  'description' => __( 'Kosten voor gebruik Parcelshop pick-up', 'wuunder_parcelshop' ),
                  'default' => 5.0
								)

             );
        }

				/**
				 * calculate_shipping function.
				 *
				 * @access public
				 * @param mixed $package
				 * @return void
				 */
				public function calculate_shipping( $package = array() ) {
					$rate = array(
						'id' => $this->id,
						'label' => $this->title,
						'cost' => '10.99',
						'calc_tax' => 'per_item'
					);

					// Register the rate
					$this->add_rate( $rate );
				}

			}
		}
	}

	add_action( 'woocommerce_shipping_init', 'your_shipping_method_init' );

	function wuunder_parcelshop_locator( $methods ) {
		$methods['your_shipping_method'] = 'WC_wuunder_parcelshop';
		return $methods;
	}

	add_filter( 'woocommerce_shipping_methods', 'wuunder_parcelshop_locator' );
}
