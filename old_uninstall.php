<?php

// if uninstall.php is not called by WordPress, die
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    die;
}

$options = array(
    'section_title',
    'api',
    'test_api',
    'api_status',
    'section_end',
    'section_title_1',
    'company',
    'firstname',
    'lastname',
    'email',
    'phone',
    'street',
    'housenumber',
    'postcode',
    'city',
    'country',
    'section_end',
    'company_1',
    'firstname_1',
    'lastname_1',
    'email_1',
    'phone_1',
    'street_extra_1',
    'housenumber_extra_1',
    'postcode_extra_1',
    'city_extra_1',
    'country_1',
    'section_end_1',
    'section_title_2',
    'company_2',
    'firstname_2',
    'lastname_2',
    'email_2',
    'phone_2',
    'street_extra_2',
    'housenumber_extra_2',
    'postcode_extra_2',
    'city_extra_2',
    'country_2',
    'section_end_2'
);

foreach ( $options as $option ) {
    if ( get_option($option ) ) {
        delete_option( $option );
    }
}