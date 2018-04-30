<?php

add_action( 'wp_ajax_parcelshoplocator', 'parcelShopLocator');

function parcelShopLocator()
{
//     $status = get_option('wc_wuunder_api_status');
//     $apiKey = ($status == 'productie' ? get_option('wc_wuunder_api') : get_option('wc_wuunder_test_api'));
// echo $apiKey;
//
    // $connector = new Wuunder\Connector("YVc7rKdM6e6Q_HQK81NCt7SM0LT0TtQB");
    // $parcelshopsRequest = $connector->getParcelshopsByAddress();
    // $parcelshopsConfig = new \Wuunder\Api\Config\ParcelshopsConfig();
    // $parcelshopsConfig->setProviders(array("DHL_PARCEL", "DPD"));
    // $parcelshopsConfig->setAddress("Wilgenlaan 8 maasbracht");
    // $parcelshopsConfig->setLimit(40);
    // if ($parcelshopsConfig->validate()) {
    //     $parcelshopsRequest->setConfig($parcelshopsConfig);
    //     if ($parcelshopsRequest->fire()) {
    //         $parcelshops = $parcelshopsRequest->getParcelshopsResponse()->getParcelshopsData();
    //     } else {
    //         var_dump($parcelshopsRequest->getParcelshopsResponse()->getError());
    //     }
    // } else {
    //     // print("ParcelshopsConfig not complete");
    //     $parcelshops = "ParcelshopsConfig not complete";
    // }
    // // echo $parcelshops;
    echo "Dit is een test";
}

parcelShopLocator();
