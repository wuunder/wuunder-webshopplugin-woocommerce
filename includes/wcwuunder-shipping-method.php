<?php

if (!defined('WPINC')) {
    die;
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    function wc_wuunder_parcelshop_method()
    {
        if (!class_exists('WC_wuunder_parcelshop')) {
            class WC_wuunder_parcelshop extends WC_Shipping_Method
            {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct($instance_id = 0)
                {
                    $this->id = 'wuunder_parcelshop';
                    $this->instance_id = absint($instance_id);
                    $this->method_title = __('Wuunder Parcelshop');
                    $this->method_description = __('Laat klanten zelf een locatie kiezen om hun pakketje op te halen.');

                    // $this->enabled            = ('yes' === $this->get_option('enabled') ) ? $this->get_option('enabled') : 'no';
                    $this->enabled = 'yes';
                    $this->title = "Wuunder Parcelshop Locator";
                    $this->supports = array(
                        'shipping-zones',
                        'instance-settings',
                        'instance-settings-modal',
                        'settings'
                    );

                    // These are the options set by the user
                    $this->cost = $this->get_option('cost');
                    $this->carriers = $this->get_option('select_carriers');
                    $this->google_api_key = $this->get_option('google_api_key');
                    $this->init();
                }

                /**
                 * Init your settings
                 *
                 * @access public
                 * @return void
                 */
                function init()
                {
                    // Load the settings API
                    $this->init_form_fields(); // This is part of the settings API. Override the method to add your own settings
                    $this->init_settings(); // This is part of the settings API. Loads settings you previously init.

                    // Save settings in admin if you have any defined
                    add_action('woocommerce_update_options_shipping_' . $this->id, array($this, 'process_admin_options'));
                }

                function init_form_fields()
                {
                    $this->form_fields = array(
                        // Commented for now until its working.
                        // 'enabled' => array(
                        //      'title' => __( 'Enable/Disable', 'woocommerce' ),
                        //      'type' => 'checkbox',
                        //      'description' => __( 'Enable this shipping.', 'woocommerce' ),
                        //      'default' => 'yes'
                        // 	),
                        'cost' => array(
                            'title' => __('Kosten', 'woocommerce'),
                            'type' => 'number',
                            'description' => __('Kosten voor gebruik Parcelshop pick-up', 'woocommerce'),
                            'default' => 5.0
                        ),
                        'select_carriers' => array(
                            'title' => __('Welke Carriers', 'woocommerce'),
                            'type' => 'multiselect',
                            'description' => __('Geef aan uit welke carriers de klant kan kiezen (cmd/ctrl + muis om meerdere te kiezen)', 'woocommerce'),
                            'options' => array(
                                'DHL' => __("DHL"),
                                'DPD' => __("DPD"),
                                'PostNL' => __("PostNL")
                            )
                        ),
                        'google_api_key' => array(
                            'title' => __('Google API key', 'woocommerce'),
                            'type' => 'text',
                            'description' => __('Google maps api key', 'woocommerce'),
                            'default' => ""
                        ),
                    );
                }

                /**
                 * calculate_shipping function.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping($package = array())
                {
                    $rate = array(
                        'id' => $this->id,
                        'label' => $this->title,
                        'cost' => $this->cost,
                        'calc_tax' => 'per_item'
                    );

                    // Register the rate
                    $this->add_rate($rate);
                }

            }
        }
    }

    add_action('woocommerce_shipping_init', 'wc_wuunder_parcelshop_method');

    function wuunder_parcelshop_locator($methods)
    {
        $methods['wuunder_parcelshop'] = 'WC_wuunder_parcelshop';
        return $methods;
    }

    add_filter('woocommerce_shipping_methods', 'wuunder_parcelshop_locator');
}
