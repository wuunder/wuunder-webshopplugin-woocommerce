<?php

function wcwp_log($severity, $message ) {
    $logger = wc_get_logger();
    $context = array( 'source' => 'wuunder_connector' );
    if ( WP_DEBUG ) {
        $logger->log( $severity, $message, $context );
    }     

}