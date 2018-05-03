<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function parcelShopLocator()
{
    $status = get_option('wc_wuunder_api_status');
    $apiKey = ($status == 'productie' ? get_option('wc_wuunder_api') : get_option('wc_wuunder_test_api'));
    $address = $_POST['address'];

    $connector = new Wuunder\Connector($apiKey);
    $parcelshopsRequest = $connector->getParcelshopsByAddress();
    $parcelshopsConfig = new \Wuunder\Api\Config\ParcelshopsConfig();
    $parcelshopsConfig->setProviders(array("DHL_PARCEL", "DPD"));
    $parcelshopsConfig->setAddress($address);
    $parcelshopsConfig->setLimit(40);
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
