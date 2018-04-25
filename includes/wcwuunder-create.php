<?php
//error_reporting(1);

if (!class_exists('WC_Wuunder_Create')) {

    class WC_Wuunder_Create
    {
        public $order_id;
        private $version_obj = array("product" => "Woocommerce extension", "version" => array("build" => "2.3.1", "plugin" => "2.0"));

        public function __construct()
        {
            add_action('load-edit.php', array(&$this, 'generateBookingUrl'));
            add_action('load-edit.php', array(&$this, 'test'));
            add_action('woocommerce_admin_order_actions_end', array(&$this, 'add_listing_actions'));
            add_action('add_meta_boxes_shop_order', array(&$this, 'add_meta_boxes'));
            add_action('admin_notices', array(&$this, 'sample_admin_notice__error'));
            wp_enqueue_style('wuunder-admin', (dirname(plugin_dir_url(__FILE__)) . '/assets/css/wuunder-admin.css'));
        }

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

        private function buildWuunderData($orderId, $redirectUrl, $webhookUrl)
        {
            $orderItems = $this->get_order_items($orderId);
            $orderMeta = get_post_meta($orderId);
            $order = new WC_Order($orderId);
            $orderPicture = null;
            foreach ($orderItems['images'] as $image) {
                if (!is_null($image)) {
                    $orderPicture = $this->get_base64_image($image);
                    break;
                }
            }

            $defLength = 80;
            $defWidth = 50;
            $defHeight = 35;
            $defWeight = 5000;
            $defValue = 25 * 100;

            // Get WooCommerce Wuunder Address from options page
            $company = $this->get_company_address($orderId, 0);
            $customer = $this->get_customer_address($orderId, $orderMeta['_billing_phone'][0]);

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

            if ($totalWeight === 0) {
                $totalWeight = $defWeight;
            }
            if (count($dimensions) !== 3) {
                $dimensions = array($defLength, $defWidth, $defHeight);
            }

            $value = intval($order->get_subtotal() * 100);

            $bookingConfig = new Wuunder\Api\Config\BookingConfig();
            $bookingConfig->setWebhookUrl($webhookUrl);
            $bookingConfig->setRedirectUrl($redirectUrl);

            $bookingConfig->setDescription($description);
            $bookingConfig->setKind($totalWeight > 23000 ? "pallet" : "package");
            $bookingConfig->setValue($value ? $value : $defValue);
            $bookingConfig->setLength(round($dimensions[0]));
            $bookingConfig->setWidth(round($dimensions[1]));
            $bookingConfig->setHeight(round($dimensions[2]));
            $bookingConfig->setWeight($totalWeight ? $totalWeight : $defWeight);
            $bookingConfig->setPreferredServiceLevel((count($order->get_items('shipping')) > 0) ? $this->get_filter_from_shippingmethod(reset($order->get_items('shipping'))->get_method_id()) : "");

            $bookingConfig->setDeliveryAddress($customer);
            $bookingConfig->setPickupAddress($company);

            return $bookingConfig;
        }

        public function generateBookingUrl()
        {
            if (isset($_REQUEST['order']) && $_REQUEST['action'] === "bookorder") {
                $order_id = $_REQUEST['order'];
                if (true) {
                    $postData = array();
                    if (isset($_POST['data']))
                        $postData = stripslashes_deep($_POST['data']);

                    $bookingToken = uniqid();
                    update_post_meta($order_id, '_wuunder_label_booking_token', $bookingToken);

                    $redirectUrl = get_site_url(null, "/wp-admin/edit.php?post_type=shop_order");
                    $webhookUrl = get_site_url(null, "/wuunder/webhook?order=" . $order_id . '&token=' . $bookingToken);

                    $status = get_option('wc_wuunder_api_status');
                    if ($status == 'productie') {
                        $apiUrl = 'https://api.wearewuunder.com/api/bookings?redirect_url=' . $redirectUrl . '&webhook_url=' . $webhookUrl;
                        $apiKey = get_option('wc_wuunder_api');
                    } else {
                        $apiUrl = 'https://api-staging.wearewuunder.com/api/bookings?redirect_url=' . $redirectUrl . '&webhook_url=' . $webhookUrl;
                        $apiKey = get_option('wc_wuunder_test_api');
                    }

                    $connector = new Wuunder\Connector($apiKey);
                    $booking = $connector->createBooking();
                    $bookingConfig = $this->buildWuunderData($order_id, $redirectUrl, $webhookUrl);

                    if ($bookingConfig->validate()) {
                        $booking->setConfig($bookingConfig);
                        if ($booking->fire()) {
                            $url = $booking->getBookingResponse()->getBookingUrl();
                        } else {
                            var_dump($booking->getBookingResponse()->getError());
                        }
                    } else {
                        print("Bookingconfig not complete");
                    }

                    update_post_meta($order_id, '_wuunder_label_booking_url', $url);
                    if (!(substr($url, 0, 5) === "http:" || substr($url, 0, 6) === "https:")) {
                        if ($status == 'productie') {
                            $url = 'https://api.wearewuunder.com' . $url;
                        } else {
                            $url = 'https://api-staging.wearewuunder.com' . $url;
                        }
                    }
                    wp_redirect($url);
                    exit;
                } else {
//                    wp_redirect(get_site_url(null, " / wp - admin / edit . php ? post_type = shop_order"));
//                    exit;
                }
            } else {
//                wp_redirect(get_site_url(null, " / wp - admin / edit . php ? post_type = shop_order"));
//                exit;
            }
        }

        public function test()
        {
//            $order_meta = get_post_meta(73);
////            var_dump($this->get_customer_address_part($order_meta, '_first_name'));
//            $statuses = wc_get_order_statuses();
//            echo "<pre>";
//            var_dump($statuses);
//            echo " </pre>";
        }

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

        public function check_company_address()
        {

            if (get_option('wc_wuunder_company_name') && get_option('wc_wuunder_company_firstname') && get_option('wc_wuunder_company_lastname') && get_option('wc_wuunder_company_street') && get_option('wc_wuunder_company_housenumber') && get_option('wc_wuunder_company_postode') && get_option('wc_wuunder_company_city') && get_option('wc_wuunder_company_country') && get_option('wc_wuunder_company_email') && get_option('wc_wuunder_company_phone')) {
                $check = true;
            } else {
                $check = false;
            }

            return $check;

        }

        public function get_company_address($orderid, $pickup_address)
        {
          $pickupAddress = new \Wuunder\Api\Config\AddressConfig();
            if ($pickup_address == 0) {
                // Get Woocommerce Wuunder Settings
                  $pickupAddress->setEmailAddress(get_option('wc_wuunder_company_email'));
                  $pickupAddress->setFamilyName(get_option('wc_wuunder_company_lastname'));
                  $pickupAddress->setGivenName(get_option('wc_wuunder_company_firstname'));
                  $pickupAddress->setLocality(get_option('wc_wuunder_company_city'));
                  $pickupAddress->setStreetName(get_option('wc_wuunder_company_street'));
                  $pickupAddress->setHouseNumber(get_option('wc_wuunder_company_housenumber'));
                  $pickupAddress->setZipCode(get_option('wc_wuunder_company_postode'));
                  $pickupAddress->setPhoneNumber(get_option('wc_wuunder_company_phone'));
                  $pickupAddress->setCountry(get_option('wc_wuunder_company_country'));
            } else {
                // Get Woocommerce Wuunder Settings
                  $pickupAddress->setEmailAddress(get_option('wc_wuunder_company_email_' . $pickup_address));
                  $pickupAddress->setFamilyName(get_option('wc_wuunder_company_lastname_' . $pickup_address));
                  $pickupAddress->setGivenName(get_option('wc_wuunder_company_firstname_' . $pickup_address));
                  $pickupAddress->setLocality(get_option('wc_wuunder_company_city_' . $pickup_address));
                  $pickupAddress->setStreetName(get_option('wc_wuunder_company_street_' . $pickup_address));
                  $pickupAddress->setHouseNumber(get_option('wc_wuunder_company_housenumber_' . $pickup_address));
                  $pickupAddress->setZipCode(get_option('wc_wuunder_company_postode_' . $pickup_address));
                  $pickupAddress->setPhoneNumber(get_option('wc_wuunder_company_phone_' . $pickup_address));
                  $pickupAddress->setCountry(get_option('wc_wuunder_company_country_' . $pickup_address));
            }
            return $pickupAddress;
        }

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

        public function get_customer_address($orderid, $phone)
        {
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
            $deliveryAddress->setPhoneNumber("$phone");
            $deliveryAddress->setCountry($this->get_customer_address_part($order_meta, '_country'));

            return $deliveryAddress;
        }

        public function get_base64_image($imagepath)
        {
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
                return "";
            }
        }

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
                    <img src="<?php echo $data['img']; ?>" style="width:16px;" alt="<?php echo $data['title']; ?>">
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
                        <img src="<?php echo $data['img']; ?>" style="width:16px;" alt="<?php echo $data['alt']; ?>">
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
