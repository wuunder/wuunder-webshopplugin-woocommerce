<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function parcelShopLocator()
{
    $status = get_option('wc_wuunder_api_status');
    $apiKey = ($status == 'productie' ? get_option('wc_wuunder_api') : get_option('wc_wuunder_test_api'));

    if(!empty($_POST['address'])) {
        $shipping_address = $_POST['address'];
    } else {
        $shipping_address = "";
        $shipping_address .= (!empty(WC()->customer->get_shipping_address())  ? WC()->customer->get_shipping_address()  . " " : "");
        $shipping_address .= (!empty(WC()->customer->get_shipping_city())     ? WC()->customer->get_shipping_city()     . " " : "");
        $shipping_address .= (!empty(WC()->customer->get_shipping_postcode()) ? WC()->customer->get_shipping_postcode() . " " : "");
        $shipping_address .= (!empty(WC()->customer->get_shipping_country())  ? WC()->customer->get_shipping_country()  . " " : "");
    }

    $connector = new Wuunder\Connector($apiKey);
    $connector->setLanguage("NL");
    $parcelshopsRequest = $connector->getParcelshopsByAddress();
    $parcelshopsConfig = new \Wuunder\Api\Config\ParcelshopsConfig();
    $parcelshopsConfig->setProviders(array("DHL_PARCEL", "DPD", "POST_NL"));
    $parcelshopsConfig->setAddress($shipping_address);

    if ($parcelshopsConfig->validate()) {
        $parcelshopsRequest->setConfig($parcelshopsConfig);
        if ($parcelshopsRequest->fire()) {
            $parcelshops = $parcelshopsRequest->getParcelshopsResponse()->getParcelshopsData();
        } else {
            var_dump($parcelshopsRequest->getParcelshopsResponse()->getError());
        }
    } else {
        $parcelshops = "ParcelshopsConfig not complete";
    }
    echo json_encode($parcelshops);
}
