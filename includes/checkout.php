<?php
if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

add_action('wp_enqueue_scripts', 'wcwp_callback_for_setting_up_scripts');
// add_action('woocommerce_review_order_before_submit', 'parcelshop_html');
add_action('woocommerce_review_order_after_submit', 'wcwp_parcelshop_html');

function wcwp_callback_for_setting_up_scripts()
{
    if (class_exists('WC_wuunder_parcelshop')) {
        $style_file_parcelshop_locator = dirname(plugin_dir_url(__FILE__)) . '/assets/css/parcelshop.css';
        $style_file_checkout_fields = dirname(plugin_dir_url(__FILE__)) . '/assets/css/wuunder-checkout.css';
        $google_api_key = get_option('wc_wuunder_google_maps_api_key');
        $script_file = '//maps.googleapis.com/maps/api/js?key=' . $google_api_key;
        wp_register_style('wuunderCSSParcelshopLocator', $style_file_parcelshop_locator);
        wp_enqueue_style('wuunderCSSParcelshopLocator');
        wp_register_style('wuunderCSSCheckout', $style_file_checkout_fields);
        wp_enqueue_style('wuunderCSSCheckout');

        wp_register_script('googleMapsJS', $script_file);
        wp_enqueue_script('googleMapsJS');
    }
}


function wcwp_parcelshop_html()
{
    $pluginPath = dirname(plugin_dir_url(__FILE__));
    $pluginPathJS = $pluginPath . "/assets/js/parcelshop.js";

    $baseWebshopUrl = get_site_url(null, "/wp-admin/");
    $tmpEnvironment = new \Wuunder\Api\Environment(get_option('wc_wuunder_api_status') === 'staging' ? 'staging' : 'production');

    $baseApiUrl = substr($tmpEnvironment->getStageBaseUrl(), 0, -3);
    $carrierConfigList = get_option('woocommerce_wuunder_parcelshop_settings')['select_carriers'] ?? [];
    $carrierList = implode(',', $carrierConfigList);
    if (0 !== strlen($carrierList)) {
        $availableCarriers = $carrierList;
    } else {
        $defaultCarrierConfig = get_option('default_carrier_list') ? get_option('default_carrier_list') : [];
        $availableCarriers = implode(',', array_keys($defaultCarrierConfig));
    }

    $chooseParcelshopText = __('Click here to select a parcelshop', 'woocommerce-wuunder');
    $chosenParcelshopText = __('Pickup in parcelshop', 'woocommerce-wuunder');

    echo <<<EOT
        <script type="text/javascript" data-cfasync="false" src="$pluginPathJS"></script>
        <script type="text/javascript">
            initParcelshopLocator("$baseWebshopUrl", "$baseApiUrl", "$availableCarriers", "$chooseParcelshopText", "$chosenParcelshopText");
        </script>
EOT;
}

/**
 * Returns the filter (preferred service level) that is set in the Wuunder config
 *
 * @param $shipping_method
 * @return
 */
function wcwp_get_filter_from_shippingmethod($shipping_method)
{
    if (false !== strpos($shipping_method, ':')) {
        $shipping_method = explode(':', $shipping_method)[0];
    }
    if ($shipping_method === get_option('wc_wuunder_mapping_method_1')) {
        return get_option('wc_wuunder_mapping_filter_1');
    } elseif ($shipping_method === get_option('wc_wuunder_mapping_method_2')) {
        return get_option('wc_wuunder_mapping_filter_2');
    } elseif ($shipping_method === get_option('wc_wuunder_mapping_method_3')) {
        return get_option('wc_wuunder_mapping_filter_3');
    } elseif ($shipping_method === get_option('wc_wuunder_mapping_method_4')) {
        return get_option('wc_wuunder_mapping_filter_4');
    } else {
        return '';
    }
}

// Field added for the parcelshop_id, so that it can be requested from backend
add_action('woocommerce_after_order_notes', 'wcwp_add_parcelshop_id_field');
function wcwp_add_parcelshop_id_field($checkout)
{
    woocommerce_form_field('parcelshop_id', array(
        'type' => 'text',
        'class' => array(
            'wuunder-hidden-checkout-field form-row-wide'
        ),
        'autocomplete' => "new-password"
    ), $checkout->get_value('parcelshop_id'));

    woocommerce_form_field('parcelshop_country', array(
        'type' => 'text',
        'class' => array(
            'wuunder-hidden-checkout-field form-row-wide'
        ),
        'autocomplete' => "new-password"
    ), $checkout->get_value('parcelshop_country'));
}

/*
 * Add a referanse field to the Order API response.
*/
function prefix_wc_rest_prepare_order_object($response, $object, $request)
{
    $shipping_method_id = null;
    try {
        $order = new WC_Order($object->get_id());
        $shipping_object = $order->get_items('shipping');
        if (count($shipping_object) > 0) {
            $shipping_method_id = wcwp_get_filter_from_shippingmethod(reset($shipping_object)->get_method_id());
        }
    } catch (Exception $e) {
    }
    $response->data['wuunder_preferred_service_level'] = $shipping_method_id;

    $bookingTokenData = get_post_meta($object->get_id(), '_wuunder_label_booking_token');

    if (count($bookingTokenData)) {
        $bookingToken = $bookingTokenData[0];
    } else {
        $bookingToken = uniqid();
        update_post_meta($object->get_id(), '_wuunder_label_booking_token', $bookingToken);
    }

    $response->data['wuunder_booking_token'] = $bookingToken;


    $total_order_weight = wcwp_get_order_weight($object->get_id());
    $response->data['wuunder_total_order_weight'] = $total_order_weight;


    return $response;
}

add_filter('woocommerce_rest_prepare_shop_order_object', 'prefix_wc_rest_prepare_order_object', 10, 3);

function wcwp_get_order_weight($order_id)
{
    global $woocommerce;
    $order = new WC_Order($order_id);
    //global $_product;
    $items = $order->get_items();
    $total_weight = 0;

    if (sizeof($items) > 0) {
        foreach ($items as $item) {
            // Create the product
            $product = $item->get_product();
            // Set item weight
            $weight = $product->get_weight();
            $weight = empty($weight) ? 0 : $weight;
            $weight_unit = get_option('woocommerce_weight_unit');
            $quantity = $item['qty'];
            try {
                switch ($weight_unit) {
                    case 'kg':
                        $data['weight'] = $weight * 1000;
                        break;
                    case 'g':
                        $data['weight'] = $weight;
                        break;
                    case 'lbs':
                        $data['weight'] = $weight * 0.45359237;
                        break;
                    case 'oz':
                        $data['weight'] = $weight * 0.0283495231;
                        break;
                    default:
                        $data['weight'] = $weight;
                        break;
                }
            } catch (\Throwable $e) {
                $data['weight'] = 0;
                wcwp_log('error', 'Invalid weight value: ' . $weight);
            }

            $total_product_weight = $quantity * $data['weight'];
            $total_weight += $total_product_weight;
        }
    }

    return $total_weight;
}


// Save / Send the parcelshop id
add_action('woocommerce_checkout_update_order_meta', 'wcwp_update_parcelshop_id');
function wcwp_update_parcelshop_id($order_id)
{
    if (!empty($_POST['parcelshop_id']) && isset($_POST['shipping_method']) && isset($_POST['shipping_method'][0]) && 'wuunder_parcelshop' === sanitize_text_field($_POST['shipping_method'][0])) {
        update_post_meta($order_id, 'parcelshop_id', sanitize_text_field($_POST['parcelshop_id']));
        WC()->session->__unset('WCWP_SELECTED_PARCELSHOP_ID');
    }
}

// Check to see if a parcelshop is selected when parcel method is selected && Check if shipping country == parcelshop country
add_action('woocommerce_checkout_process', 'wcwp_check_parcelshop_selection');
function wcwp_check_parcelshop_selection()
{
    if ('wuunder_parcelshop' === sanitize_text_field($_POST['shipping_method'][0])) {
        if (!$_POST['parcelshop_id']) {
            wc_add_notice(__('First choose a <strong>parcelshop</strong>'), 'error');
        }

        if ($_POST['parcelshop_id'] && (!isset($_POST['shipping_country']) || $_POST['shipping_country'] != $_POST['parcelshop_country'])) {
            wc_add_notice(__('The <strong>shipping country</strong> must match with <strong>the parcelshop country</strong>'), 'error');
        }
    }
}
