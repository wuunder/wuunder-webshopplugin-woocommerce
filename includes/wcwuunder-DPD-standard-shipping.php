<?php

if ( !defined( 'WPINC' ) ) {
    die;
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins') ) ) ) {

    function WC_wuunder_DPD_standard_method()
    {
        if ( !class_exists( 'WC_wuunder_DPD_standard' ) ) {
            class WC_wuunder_DPD_standard extends WC_Shipping_Method
            {
                /**
                 * Constructor for your shipping class
                 *
                 * @access public
                 * @return void
                 */
                public function __construct($instance_id = 0) {
                    $this->id = 'wuunder_DPD_standard';
                    $this->instance_id = absint( $instance_id );
                    $this->method_title = __( 'Aflevering op huisadres via DPD' );
                    $this->method_description = __( 'Gratis verzending vanaf ingestelde waarde per zone' );
                    $this->enabled = 'yes';
                    $this->supports = array(
                        'shipping-zones',
                        'instance-settings',
                        'instance-settings-modal'
                    );

                    // These are the options set by the user
                    $this->cost = $this->get_option( 'cost' );
                    $this->carriers = $this->get_option( 'select_carriers');
                    $this->instance_form_fields = array(
                        'title' => array(
                            'title'         => __( 'Naam van de verzendmethode' ),
                            'type'          => 'text',
                            'description'   => __( 'Dit stelt de naam van de verzendmethode in op de check-out pagina.' ),
                            'default'       => __( 'Aflevering op huisadres via DPD' ),
                            'desc_tip'      => true
                        ),
                        'cost'      => array(
                            'title'         => __( 'Kosten', 'woocommerce' ),
                            'type'          => 'number',
                            'description'   => __( 'Kosten voor gebruik van de verzendmethode', 'woocommerce' ),
                            'default'       => 3.50,
                            'desc_tip'      => true
                        ),
                        'free_from' => array(
                            'title'         => __( 'Gratis verzending vanaf', 'woocommerce' ),
                            'type'          => 'number',
                            'description'   => __( 'Vanaf welk bestelbedrag is de verzending gratis. Stel 0 in voor nooit.', 'woocommerce' ),
                            'default'       => 50.00,
                            'desc_tip'      => true
                        )
                    );
                    $this->title = $this->get_option( 'title' );

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

                /**
                 * calculate_shipping function.
                 *
                 * @access public
                 * @param mixed $package
                 * @return void
                 */
                public function calculate_shipping( $package = array() ) {
                    $cost = $this->get_option( 'cost' );
                    if ( $this->get_option( 'free_from' ) > 0 && $package['contents_cost'] >= $this->get_option( 'free_from' ) ) {
                        $cost = 0;
                    }
                    $rate = array(
                        'id'        => $this->id,
                        'label'     => $this->title,
                        'cost'      => $cost,
                        'calc_tax'  => 'per_item'
                    );

                    // Register the rate
                    $this->add_rate( $rate );
                }

            }
        }
    }

    add_action( 'woocommerce_shipping_init', 'WC_wuunder_DPD_standard_method' );

    function wuunder_DPD_standard_shipping( $methods ) {
        $methods['wuunder_DPD_standard'] = 'WC_wuunder_DPD_standard';
        return $methods;
    }

    add_filter( 'woocommerce_shipping_methods', 'wuunder_DPD_standard_shipping' );
}
