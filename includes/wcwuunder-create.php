<?php
//error_reporting(1);

if (!class_exists('WC_Wuunder_Create')) {

    class WC_Wuunder_Create
    {
        public $order_id;
        private $version_obj;

        public function __construct()
        {
            $this->version_obj = array(
                "product" => "Woocommerce extension",
                "version" => array(
                    "build" => "2.4.1",
                    "plugin" => "2.0"),
                "platform" => array(
                    "name" => "Woocommerce",
                    "build" => WC()->version
                ));
            add_action('load-edit.php', array(&$this, 'generateBookingUrl'));
            add_action('load-edit.php', array(&$this, 'test'));
            add_action('woocommerce_admin_order_actions_end', array(&$this, 'add_listing_actions'));
            add_action('add_meta_boxes_shop_order', array(&$this, 'add_meta_boxes'));
            add_action('admin_notices', array(&$this, 'sample_admin_notice__error'));
            wp_enqueue_style('wuunder-admin', (dirname(plugin_dir_url(__FILE__)) . '/assets/css/wuunder-admin.css'));
        }

        /**
         * Creates an error message for the admin order page
         */
        public function sample_admin_notice__error()
        {

            if (isset($_GET['notice']) && $_GET['notice'] == 'error') {

                $class = 'notice notice-error';
                $message = __('<b>Het aanmaken van het label voor #' . $_GET['id'] . ' is mislukt</b>', 'woocommerce-wuunder');
                $errors = $_GET['error_melding'];
                $message .= '<ul style="margin:0 0 0 20px; padding:0; list-style:inherit;">';
                foreach ($errors as $error) {
                    $message .= '<li>' . $error . '</li>';
                }
                $message .= '</ul>';

                printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);

            } else if (isset($_GET['notice']) && $_GET['notice'] == 'success') {

                $class = 'notice notice-success';
                $message = __('Het verzendlabel voor #' . $_GET['id'] . ' is aangemaakt', 'woocommerce-wuunder');
                printf('<div class="%1$s"><p>%2$s</p></div>', $class, $message);

            }

        }

        /**
         * Sets the address and package data for the booking request
         *
         * @param $orderId
         * @return $bookingConfig
         */
        private function setBookingConfig($orderId)
        {
            $logger = wc_get_logger();
            $context = array('source' => "wuunder_connector");
            $logger->log('info', "Filling the booking config", $context);

            $orderItems = $this->get_order_items($orderId);

            $order = new WC_Order($orderId);
            $orderPicture = null;
            foreach ($orderItems['images'] as $image) {
                if (!is_null($image)) {
                    $orderPicture = $this->get_base64_image($image);
                    break;
                }
            }

            // Get WooCommerce Wuunder Address from options page
            $company = $this->get_company_address();
            $customer = $this->get_customer_address($orderId);

            $totalWeight = 0;
            $dimensions = null;
            $description = "";

            foreach ($orderItems['products'] as $item) {
                $totalWeight += $item['total_weight'];
                if ($dimensions === null) {
                    $dimensions = explode(' x ', $item['dimensions']);
                }
                $description .= "- " . $item['quantity'] . "x " . $item['name'] . " \r\n";
            }

            if (count($dimensions) !== 3) {
                $dimensions = array(null, null, null);
            }

            $value = intval($order->get_subtotal() * 100);

            $bookingToken = uniqid();
            update_post_meta($orderId, '_wuunder_label_booking_token', $bookingToken);
            $redirectUrl = get_site_url(null, "/wp-admin/edit.php?post_type=shop_order");
            $webhookUrl = get_site_url(null, "index.php/wuunder/webhook?order=" . $orderId . '&token=' . $bookingToken);

            $bookingConfig = new Wuunder\Api\Config\BookingConfig();
            $bookingConfig->setWebhookUrl($webhookUrl);
            $bookingConfig->setRedirectUrl($redirectUrl);

            $bookingConfig->setDescription($description);
            $bookingConfig->setKind($totalWeight > 23000 ? "pallet" : "package");
            $bookingConfig->setValue($value ? $value : null);
            $bookingConfig->setLength($this->roundButNull($dimensions[0]));
            $bookingConfig->setWidth($this->roundButNull($dimensions[1]));
            $bookingConfig->setHeight($this->roundButNull($dimensions[2]));
            $bookingConfig->setWeight($totalWeight ? $totalWeight : null);
            $bookingConfig->setPreferredServiceLevel((count($order->get_items('shipping')) > 0) ? $this->get_filter_from_shippingmethod(reset($order->get_items('shipping'))->get_method_id()) : "");
            $bookingConfig->setSource($this->version_obj);

            $orderMeta = get_post_meta($orderId);
            if (isset($orderMeta['parcelshop_id']))
                $bookingConfig->setParcelshopId($orderMeta['parcelshop_id'][0]);

            $bookingConfig->setDeliveryAddress($customer);
            $bookingConfig->setPickupAddress($company);

            return $bookingConfig;
        }

        /**
         * Generates the booking url that takes the user to Wuunder.
         * Returns the user to the original order page with the redirect.
         */
        public function generateBookingUrl()
        {
            $logger = wc_get_logger();
            $context = array('source' => "wuunder_connector");

            if (isset($_REQUEST['order']) && $_REQUEST['action'] === "bookorder") {
                $logger->log('info', "Generating the booking url", $context);
                $order_id = $_REQUEST['order'];

                $status = get_option('wc_wuunder_api_status');
                $apiKey = ($status == 'productie' ? get_option('wc_wuunder_api') : get_option('wc_wuunder_test_api'));

                $connector = new Wuunder\Connector($apiKey, $status !== 'productie');
                $booking = $connector->createBooking();
                $bookingConfig = $this->setBookingConfig($order_id);

                if ($bookingConfig->validate()) {
                    $booking->setConfig($bookingConfig);
                    if ($booking->fire()) {
                        $url = $booking->getBookingResponse()->getBookingUrl();
                    } else {
                        $logger->log('error', $booking->getBookingResponse()->getError(), $context);
                    }
                } else {
                    $logger->log('error', "Bookingconfig not complete", $context);
                }

                if (isset($url)) {
                    update_post_meta($order_id, '_wuunder_label_booking_url', $url);
                    wp_redirect($url);
                } else {
                    wp_redirect(get_admin_url(null, "edit.php?post_type=shop_order"));
                }
                exit;
            }
        }

        public function test()
        {
        }

        /**
         * Returns rounded value, or null
         *
         * @param $val
         * @return float|null
         */
        private function roundButNull($val)
        {
            if (is_null($val)) {
                return null;
            }

            return round($val);
        }

        /**
         * Returns the filter (preferred service level) that is set in the Wuunder config
         *
         * @param $shipping_method
         * @return
         */
        private function get_filter_from_shippingmethod($shipping_method)
        {
            if (strpos($shipping_method, ':') !== false) {
                $shipping_method = explode(':', $shipping_method)[0];
            }
            if (get_option("wc_wuunder_mapping_method_1") === $shipping_method) {
                return get_option("wc_wuunder_mapping_filter_1");
            } else if (get_option("wc_wuunder_mapping_method_2") === $shipping_method) {
                return get_option("wc_wuunder_mapping_filter_2");
            } else if (get_option("wc_wuunder_mapping_method_3") === $shipping_method) {
                return get_option("wc_wuunder_mapping_filter_3");
            } else if (get_option("wc_wuunder_mapping_method_4") === $shipping_method) {
                return get_option("wc_wuunder_mapping_filter_4");
            } else {
                return "";
            }
        }

        /**
         * Gets the company address set in the Wuunder config
         *
         * @return $pickupAddress
         */
        public function get_company_address()
        {
            $logger = wc_get_logger();
            $context = array('source' => "wuunder_connector");

            $pickupAddress = new \Wuunder\Api\Config\AddressConfig();

            $pickupAddress->setEmailAddress(get_option('wc_wuunder_company_email'));
            $pickupAddress->setFamilyName(get_option('wc_wuunder_company_lastname'));
            $pickupAddress->setGivenName(get_option('wc_wuunder_company_firstname'));
            $pickupAddress->setLocality(get_option('wc_wuunder_company_city'));
            $pickupAddress->setStreetName(get_option('wc_wuunder_company_street'));
            $pickupAddress->setHouseNumber(get_option('wc_wuunder_company_housenumber'));
            $pickupAddress->setZipCode(get_option('wc_wuunder_company_postode'));
            $pickupAddress->setPhoneNumber(get_option('wc_wuunder_company_phone'));
            $pickupAddress->setCountry(get_option('wc_wuunder_company_country'));
            if ($pickupAddress->validate()) {
                return $pickupAddress;
            } else {
                $logger->log('error', "Invalid pickup address. There are mistakes or missing fields.", $context);
                return $pickupAddress;
            }
        }

        /**
         * Seperates the street name and number when both are set on the same line
         *
         * @param $addressLine
         * @return array containing 2 values: streetName and streetNumber
         */
        private function separateAddressLine($addressLine)
        {
            if (preg_match('/^([^\d]*[^\d\s]) *(\d.*)$/', $addressLine, $result)) {
                if (count($result) >= 2) {
                    $streetName = $result[1];
                    $streetNumber = $result[2];
                } else {
                    return array($addressLine, "");
                }

                return array($streetName, $streetNumber);
            }
            return array($addressLine, "");
        }

        /**
         * Retrieves part of the customer address
         *
         * @param $order_meta , $suffix
         * @return $order_meta
         */
        private function get_customer_address_part($order_meta, $suffix)
        {
            if (isset($order_meta['_shipping' . $suffix]) && !empty($order_meta['_shipping' . $suffix][0])) {
                return $order_meta['_shipping' . $suffix][0];
            } else if (isset($order_meta['_billing' . $suffix]) && !empty($order_meta['_billing' . $suffix][0])) {
                return $order_meta['_billing' . $suffix][0];
            } else {
                return "";
            }
        }

        /**
         * Retrieves the customer address puts in function separateAddressLine
         *
         * @param $order_meta
         * @return array containing 2 values: streetName and streetNumber
         */
        private function get_customer_address_from_address_line($order_meta)
        {
            if (isset($order_meta['_shipping_address_1']) && !empty($order_meta['_shipping_address_1'])) {
                return $this->separateAddressLine($order_meta['_shipping_address_1'][0]);
            } else if (isset($order_meta['_billing_address_1']) && !empty($order_meta['_billing_address_1'])) {
                return $this->separateAddressLine($order_meta['_billing_address_1'][0]);
            } else {
                return "";
            }
        }

        /**
         * Fills the delivery address with the customers data
         *
         * @param $orderid
         * @return $deliveryAddress
         */
        public function get_customer_address($orderid)
        {
            $logger = wc_get_logger();
            $context = array('source' => "wuunder_connector");

            // Get customer address from order
            $order_meta = get_post_meta($orderid);
            $deliveryAddress = new \Wuunder\Api\Config\AddressConfig();
            $street_name = $this->get_customer_address_part($order_meta, '_street_name');
            if (empty($street_name)) {
                $street_name = $this->get_customer_address_from_address_line($order_meta)[0];
            }
            $house_number = $this->get_customer_address_part($order_meta, '_house_number') . $this->get_customer_address_part($order_meta, '_house_number_suffix');
            if (empty($house_number)) {
                $house_number = $this->get_customer_address_from_address_line($order_meta)[1];
            }
            $deliveryAddress->setEmailAddress($this->get_customer_address_part($order_meta, '_email'));
            $deliveryAddress->setFamilyName($this->get_customer_address_part($order_meta, '_last_name'));
            $deliveryAddress->setGivenName($this->get_customer_address_part($order_meta, '_first_name'));
            $deliveryAddress->setLocality($this->get_customer_address_part($order_meta, '_city'));
            $deliveryAddress->setStreetName($street_name);
            $deliveryAddress->setHouseNumber($house_number);
            $deliveryAddress->setZipCode(str_replace(' ', '', $this->get_customer_address_part($order_meta, '_postcode')));
            $deliveryAddress->setPhoneNumber($order_meta['_billing_phone'][0]);
            $deliveryAddress->setCountry($this->get_customer_address_part($order_meta, '_country'));

            if ($deliveryAddress->validate()) {
                return $deliveryAddress;
            } else {
                $logger->log('error', "Invalid delivery address. There are mistakes or missing fields.", $context);
                return $deliveryAddress;
            }
        }

        /**
         * Checks if the image is smaller than 2MB and base 64 encodes if it is
         *
         * @param $imagepath
         * @return $image
         */
        public function get_base64_image($imagepath)
        {
            $logger = wc_get_logger();
            $context = array('source' => "wuunder_connector");
            try {
                $fileSize = (substr($imagepath, 0, 4) === "http") ? $this->remote_filesize($imagepath) : filesize($imagepath);
                if ($fileSize <= 2097152) { //smaller or equal to 2MB
                    $imagedata = file_get_contents($imagepath);
                    $image = base64_encode($imagedata);
                } else {
                    $image = "";
                }
                return $image;
            } catch (Exception $e) {
                $logger->log('error', $e, $context);
                return "";
            }
        }

        /**
         * Employed in get_base64_image to get the correct path
         *
         * @param $url
         * @return string
         */
        private function remote_filesize($url)
        {
            static $regex = '/^Content-Length: *+\K\d++$/im';
            if (!$fp = @fopen($url, 'rb')) {
                return false;
            }
            if (
                isset($http_response_header) &&
                preg_match($regex, implode("\n", $http_response_header), $matches)
            ) {
                return (int)$matches[0];
            }
            return strlen(stream_get_contents($fp));
        }

        /**
         *
         * @param $order
         */
        public function add_listing_actions($order)
        {
            // do not show buttons for trashed orders
            if ($order->get_status() == 'trash') {
                return;
            }

            if (!empty(get_post_meta($order->get_id(), '_wuunder_label_id', true))) {
                $listing_actions = array(
                    'shipping_label' => array(
                        'url' => get_post_meta($order->get_id(), '_wuunder_label_url', true),
                        'img' => Woocommerce_Wuunder::$plugin_url . 'assets/images/print-label.png',
                        'title' => __('Download label', 'woocommerce-wuunder'),
                    ),
                    'track_trace' => array(
                        'url' => get_post_meta($order->get_id(), '_wuunder_track_and_trace_url', true),
                        'img' => Woocommerce_Wuunder::$plugin_url . 'assets/images/in-transit.png',
                        'title' => __('Track & Trace', 'woocommerce-wuunder'),
                    )
                );

                echo '<div style="clear:both;">';
                foreach ($listing_actions as $action => $data) {
                    $target = ' target="_blank" ';
                    ?>
                    <a<?php echo $target; ?>href=" <?php echo $data['url']; ?>" class="<?php echo $data['action']; ?> button tips <?php echo $action; ?>" style="background:#8dcc00; height:2em; width:2em; padding:3px;" alt="<?php echo $data['title']; ?>" data-tip="<?php echo $data['title']; ?>">
                    <img src="<?php echo $data['img']; ?>" style="width:18px; margin: 4px 3px;"
                         alt="<?php echo $data['title']; ?>">
                    </a>
                    <?php
                }
                echo '</div>';
            } else {
                $listing_actions = array(
                    'create_label' => array(
                        'url' => (get_post_meta($order->get_id(), '_wuunder_label_booking_url', true) ? get_post_meta($order->get_id(), '_wuunder_label_booking_url', true) : wp_nonce_url(admin_url('edit.php?&action=bookorder&order=' . $order->get_id()), 'wcwuunder')),
                        'img' => Woocommerce_Wuunder::$plugin_url . 'assets/images/create-label.png',
                        'alt' => __('Verzendlabel aanmaken', 'woocommerce-wuunder'),
                    ),
                );

                foreach ($listing_actions as $action => $data) {
                    ?>
                    <a href="<?php echo $data['url']; ?>" class="button tips <?php echo $action; ?>"
                       style="background:#8dcc00; height:2em; width:2em; padding:3px;" alt="<?php echo $data['alt']; ?>"
                       data-tip="<?php echo $data['alt']; ?>">
                        <img src="<?php echo $data['img']; ?>" style="width:18px; margin: 4px 3px;"
                             alt="<?php echo $data['alt']; ?>">
                    </a>
                    <?php
                }
            }

        }

        /**
         * Add the meta box on the single order page
         */
        public function add_meta_boxes()
        {
            // create PDF buttons
            add_meta_box(
                'wpo_wcpdf-box',
                __('Wuunder', 'woocommerce-wuunder'),
                array($this, 'sidebar_box_content'),
                'shop_order',
                'side',
                'default'
            );
        }

        /**
         * Create the meta box content on the single order page
         */
        public function sidebar_box_content($post)
        {
            global $post_id;
            $order = new WC_Order($post_id);
            $this->add_listing_actions($order);
        }

        /**
         *
         *
         */
        public function get_order_items($order_id)
        {

            global $woocommerce;
            $order = new WC_Order($order_id);
            //global $_product;
            $items = $order->get_items();
            $data_list = array();
            $images = array();

            if (sizeof($items) > 0) {
                foreach ($items as $item) {
                    // Array with data for the printing template
                    $data = array();

                    $image = wp_get_attachment_image_src(get_post_thumbnail_id($item['product_id']), 'thumbnail');
                    $images[] = $image[0];

                    // Create the product
                    $product = $order->get_product_from_item($item);

                    // Set the variation
                    if (isset($item['variation_id']) && $item['variation_id'] > 0) {
                        $data['variation'] = woocommerce_get_formatted_variation($product->get_variation_attributes());
                    } else {
                        $data['variation'] = null;
                    }

                    // Set item name
                    $data['name'] = $item['name'];

                    // Set item quantity
                    $data['quantity'] = $item['qty'];

                    // Set item SKU
                    $data['sku'] = $product->get_sku();


                    // Set item weight
                    $weight = $product->get_weight();
                    $weight_unit = get_option('woocommerce_weight_unit');
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

                    $data['total_weight'] = $data['quantity'] * $data['weight'];

                    // Set item dimensions
                    $data['dimensions'] = wc_format_dimensions($product->get_dimensions(false));

                    $data_list['products'][] = $data;
                }
            }

            $data_list['images'] = $images;
            return $data_list;
        }

    }

}

new WC_Wuunder_Create();
