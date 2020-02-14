<?php

function wcwp_parcelShopLocator()
{
    include_once( 'wcwuunder-shipping-method.php' );
    $status = get_option( 'wc_wuunder_api_status' );
    $apiKey = ( 'productie' == $status ? get_option( 'wc_wuunder_api' ) : get_option( 'wc_wuunder_test_api' ) );

    if( ! empty( $_POST['address'] ) ) {
        $shipping_address = sanitize_text_field($_POST['address']);
    } else {

        $dest_address = get_option( 'woocommerce_ship_to_destination' );
        if ($dest_address === 'shipping') {
            $shipping_address =  '';
            $shipping_address .= ( ! empty(WC()->customer->get_shipping_address() )  ? WC()->customer->get_shipping_address()  . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_shipping_city() )     ? WC()->customer->get_shipping_city()     . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_shipping_postcode() ) ? WC()->customer->get_shipping_postcode() . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_shipping_country() )  ? WC()->customer->get_shipping_country()  . ' ' : '' );
        } else if ($dest_address === 'billing') {
            $shipping_address =  '';
            $shipping_address .= ( ! empty(WC()->customer->get_billing_address() )  ? WC()->customer->get_billing_address()  . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_billing_city() )     ? WC()->customer->get_billing_city()     . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_billing_postcode() ) ? WC()->customer->get_billing_postcode() . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_billing_country() )  ? WC()->customer->get_billing_country()  . ' ' : '' );
        }
    }

    $connector = new Wuunder\Connector( $apiKey, $status == 'productie' ? false : true);
    $connector->setLanguage( 'NL' );
    $parcelshopsRequest = $connector->getParcelshopsByAddress();
    $parcelshopsConfig = new \Wuunder\Api\Config\ParcelshopsConfig();

    $parcelshopsConfig->setProviders( get_option( 'woocommerce_wuunder_parcelshop_settings' )['select_carriers'] );
    $parcelshopsConfig->setAddress( $shipping_address );

    if ( $parcelshopsConfig->validate() ) {
        $parcelshopsRequest->setConfig($parcelshopsConfig);
        if ( $parcelshopsRequest->fire() ) {
            $parcelshops = $parcelshopsRequest->getParcelshopsResponse()->getParcelshopsData();
        } else {
            var_dump( $parcelshopsRequest->getParcelshopsResponse()->getError() );
        }
    } else {
        $parcelshops = 'ParcelshopsConfig not complete';
    }
    echo json_encode( $parcelshops );
    exit;
}


function wcwp_getAddress() {
    $shipping_address = null;

    if(!empty($_POST['address'])) {
        $shipping_address = sanitize_text_field($_POST['address']);
    } else {
        $dest_address = get_option( 'woocommerce_ship_to_destination' );
        if ($dest_address === 'shipping') {
            $shipping_address =  '';
            $shipping_address .= ( ! empty(WC()->customer->get_shipping_address() )  ? WC()->customer->get_shipping_address()  . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_shipping_city() )     ? WC()->customer->get_shipping_city()     . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_shipping_postcode() ) ? WC()->customer->get_shipping_postcode() . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_shipping_country() )  ? WC()->customer->get_shipping_country()  . ' ' : '' );
        } else if ($dest_address === 'billing') {
            $shipping_address =  '';
            $shipping_address .= ( ! empty(WC()->customer->get_billing_address() )  ? WC()->customer->get_billing_address()  . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_billing_city() )     ? WC()->customer->get_billing_city()     . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_billing_postcode() ) ? WC()->customer->get_billing_postcode() . ' ' : '' );
            $shipping_address .= ( ! empty(WC()->customer->get_billing_country() )  ? WC()->customer->get_billing_country()  . ' ' : '' );
        }
    }

    echo json_encode($shipping_address);
    exit;
}

function wcwp_getParcelshopAddress() {
    $shipping_address = null;
    if(empty($_POST['parcelshop_id'])) {
        echo null;
    } else {
        $status = get_option('wc_wuunder_api_status');
        $apiKey = ($status == 'productie' ? get_option('wc_wuunder_api') : get_option('wc_wuunder_test_api'));

        $connector = new Wuunder\Connector($apiKey, $status == 'productie' ? false : true);
        $connector->setLanguage("NL");
        $parcelshopRequest = $connector->getParcelshopById();
        $parcelshopConfig = new \Wuunder\Api\Config\ParcelshopConfig();

        $parcelshopConfig->setId(sanitize_text_field($_POST['parcelshop_id']));

        if ($parcelshopConfig->validate()) {
            $parcelshopRequest->setConfig($parcelshopConfig);
            if ($parcelshopRequest->fire()) {
                $parcelshop = $parcelshopRequest->getParcelshopResponse()->getParcelshopData();
            } else {
                var_dump($parcelshopRequest->getParcelshopResponse()->getError());
            }
        } else {
            $parcelshop = "ParcelshopsConfig not complete";
        }
        echo json_encode($parcelshop);
    }

    exit;
}