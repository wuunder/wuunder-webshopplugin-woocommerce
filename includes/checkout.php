<?php
if ( !defined('ABSPATH') ) {
    exit;
} // Exit if accessed directly

add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
add_action('woocommerce_review_order_after_submit', 'parcelshop_html');

function callback_for_setting_up_scripts() {
    if ( class_exists('WC_wuunder_parcelshop' ) ) {
        $style_file = dirname ( plugin_dir_url( __FILE__ ) ) . '/assets/css/parcelshop.css';
        $google_api_key = get_option( 'wc_wuunder_google_maps_api_key' );
        $script_file = '//maps.googleapis.com/maps/api/js?key=' . $google_api_key;
        wp_register_style( 'wuunderCSS', $style_file );
        wp_enqueue_style( 'wuunderCSS' );

        wp_register_script( 'googleMapsJS', $script_file );
        wp_enqueue_script( 'googleMapsJS' );
    }
}


function parcelshop_html()
{
    $pluginPath = dirname(plugin_dir_url(__FILE__));
    $pluginPathJS = $pluginPath . "/assets/js/parcelshop.js";

    $baseWebshopUrl = get_site_url(null, "/wp-admin/");
    $tmpEnvironment = new \Wuunder\Api\Environment(get_option('wc_wuunder_api_status') === 'staging' ? 'staging' : 'production');

    $baseApiUrl = substr($tmpEnvironment->getStageBaseUrl(), 0, -3);
    $carrierList = get_option('woocommerce_wuunder_parcelshop_settings')['select_carriers'];
    if ( 2 < strlen($carrierList)  ) {
        $availableCarriers = implode(',', $carrierList);
    }

    echo <<<EOT
        <script type="text/javascript" data-cfasync="false" src="$pluginPathJS"></script>
        <script type="text/javascript">
            initParcelshopLocator("$baseWebshopUrl", "$baseApiUrl", "$availableCarriers");
        </script>
EOT;
}

// Field added for the parcelshop_id, so that it can be requested from backend
add_action( 'woocommerce_after_order_notes', 'add_parcelshop_id_field' );
function add_parcelshop_id_field( $checkout ) {
    woocommerce_form_field('parcelshop_id', array(
        'type' => 'text',
        'class' => array(
            'my-field-class form-row-wide'
        ),
    ), $checkout->get_value( 'parcelshop_id' ) );

    woocommerce_form_field('parcelshop_country', array(
        'type' => 'text',
        'class' => array(
            'my-field-class form-row-wide'
        ),
    ), $checkout->get_value( 'parcelshop_country' ) );
}

// Save / Send the parcelshop id
add_action( 'woocommerce_checkout_update_order_meta', 'update_parcelshop_id' );
function update_parcelshop_id( $order_id ) {
    if ( ! empty( $_POST['parcelshop_id'] ) ) {
        update_post_meta( $order_id, 'parcelshop_id', sanitize_text_field( $_POST['parcelshop_id'] ) );
    }
}

// Check to see if a parcelshop is selected when parcel method is selected && Check if shipping country == parcelshop country
add_action( 'woocommerce_checkout_process', 'check_parcelshop_selection' );
function check_parcelshop_selection() {
    if ( 'wuunder_parcelshop' === $_POST['shipping_method'][0] ) {
        if ( !$_POST['parcelshop_id'] ) {
            wc_add_notice( __( 'Kies eerst een <strong>parcelshop</strong>' ), 'error' );
        }

        if ( $_POST['shipping_country'] != $_POST['parcelshop_country'] ) {
            wc_add_notice( __( 'Het <strong>land van de verzendgegevens</strong> moet overeenkomen met het <strong>land van de parcelshop</strong>' ), 'error' );
        }
    }
}
?>
