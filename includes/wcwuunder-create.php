<?php
//error_reporting(1);

if (!class_exists('WC_Wuunder_Create')) {

    class WC_Wuunder_Create
    {
        public $order_id;
        private $version_obj = array("product" => "Woocommerce extension", "version" => array("build" => "2.1.2", "plugin" => "2.0"));

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

        private function buildWuunderData($orderId)
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
//                if ($description === null) {
                    $description .= "- " . $item['name'] . "\r\n";
//                }
            }

            if ($totalWeight === 0) {
                $totalWeight = $defWeight;
            }
            if (count($dimensions) !== 3) {
                $dimensions = array($defLength, $defWidth, $defHeight);
            }

            $value = intval($order->get_subtotal() * 100);

            return array(
                "description" => $description,
                "personal_message" => "",
                "customer_reference" => $orderId,
                "picture" => $orderPicture,
                "value" => ($value ? $value : $defValue),
                "kind" => ($totalWeight > 23000 ? "pallet" : "package"),
                "length" => round($dimensions[0]),
                "width" => round($dimensions[1]),
                "height" => round($dimensions[2]),
                "weight" => ($totalWeight ? $totalWeight : $defWeight),
                "delivery_address" => $customer,
                "pickup_address" => $company,
                "preferred_service_level" => (count($order->get_items('shipping')) > 0) ? $this->get_filter_from_shippingmethod(reset($order->get_items('shipping'))->get_method_id()) : "",
                "source" => $this->version_obj
            );
        }

        public function generateBookingUrl()
        {
            if (isset($_REQUEST['order']) && $_REQUEST['action'] === "bookorder") {
                $order_id = $_REQUEST['order'];
                if (true) {
                    $postData = stripslashes_deep($_POST['data']);
                    $bookingToken = uniqid();
                    update_post_meta($order_id, '_wuunder_label_booking_token', $bookingToken);

                    $redirectUrl = urlencode(get_site_url(null, "/wp-admin/edit.php?post_type=shop_order"));
                    $webhookUrl = urlencode(get_site_url(null, "/wuunder/webhook?order=" . $order_id . '&token=' . $bookingToken));

                    $status = get_option('wc_wuunder_api_status');
                    if ($status == 'productie') {
                        $apiUrl = 'https://api.wuunder.co/api/bookings?redirect_url=' . $redirectUrl . '&webhook_url=' . $webhookUrl;
                        $apiKey = get_option('wc_wuunder_api');
                    } else {
                        $apiUrl = 'https://api-staging.wuunder.co/api/bookings?redirect_url=' . $redirectUrl . '&webhook_url=' . $webhookUrl;
                        $apiKey = get_option('wc_wuunder_test_api');
                    }

                    $wuunderData = $this->buildWuunderData($order_id, $postData);

                    // Encode variables
                    $json = json_encode($wuunderData);

                    // Setup API connection
                    $cc = curl_init($apiUrl);

                    curl_setopt($cc, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $apiKey, 'Content-type: application/json'));
                    curl_setopt($cc, CURLOPT_POST, 1);
                    curl_setopt($cc, CURLOPT_POSTFIELDS, $json);
                    curl_setopt($cc, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($cc, CURLOPT_VERBOSE, 1);
                    curl_setopt($cc, CURLOPT_HEADER, 1);

                    // Don't log base64 image string
                    $wuunderData['picture'] = 'base64 string removed';

                    // Execute the cURL, fetch the XML
                    $result = curl_exec($cc);
                    $header_size = curl_getinfo($cc, CURLINFO_HEADER_SIZE);
                    $header = substr($result, 0, $header_size);
                    preg_match("!\r\n(?:Location|URI): *(.*?) *\r\n!i", $header, $matches);
                    $url = $matches[1];

//                    echo "<pre>";
//                    var_dump($wuunderData);
                    // Close connection
                    curl_close($cc);

                    update_post_meta($order_id, '_wuunder_label_booking_url', $url);
                    echo $url;
                    if (!(substr($url, 0, 5) === "http:" || substr($url, 0, 6) === "https:")) {
                        if ($status == 'productie') {
                            $url = 'https://api.wuunder.co' . $url;
                        } else {
                            $url = 'https://api-staging.wuunder.co' . $url;
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

            if ($pickup_address == 0) {
                // Get Woocommerce Wuunder Settings
                $company_address = array(
                    "business" => get_option('wc_wuunder_company_name'),
                    "chamber_of_commerce_number" => $orderid,
                    "email_address" => get_option('wc_wuunder_company_email'),
                    "family_name" => get_option('wc_wuunder_company_lastname'),
                    "given_name" => get_option('wc_wuunder_company_firstname'),
                    "locality" => get_option('wc_wuunder_company_city'),
                    "phone_number" => get_option('wc_wuunder_company_phone'),
                    "street_name" => get_option('wc_wuunder_company_street'),
                    "house_number" => get_option('wc_wuunder_company_housenumber'),
                    "zip_code" => get_option('wc_wuunder_company_postode'),
                    "country" => get_option('wc_wuunder_company_country'),
                );
            } else {
                // Get Woocommerce Wuunder Settings
                $company_address = array(
                    "business" => get_option('wc_wuunder_company_name_' . $pickup_address),
                    "chamber_of_commerce_number" => $orderid,
                    "email_address" => get_option('wc_wuunder_company_email_' . $pickup_address),
                    "family_name" => get_option('wc_wuunder_company_lastname_' . $pickup_address),
                    "given_name" => get_option('wc_wuunder_company_firstname_' . $pickup_address),
                    "locality" => get_option('wc_wuunder_company_city_' . $pickup_address),
                    "phone_number" => get_option('wc_wuunder_company_phone_' . $pickup_address),
                    "street_name" => get_option('wc_wuunder_company_street_' . $pickup_address),
                    "house_number" => get_option('wc_wuunder_company_housenumber_' . $pickup_address),
                    "zip_code" => get_option('wc_wuunder_company_postode_' . $pickup_address),
                    "country" => get_option('wc_wuunder_company_country_' . $pickup_address),
                );
            }

            return $company_address;

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
            $street_name = $this->get_customer_address_part($order_meta, '_street_name');
            if (empty($street_name)) {
                $street_name = $this->get_customer_address_from_address_line($order_meta)[0];
            }
            $house_number = $this->get_customer_address_part($order_meta, '_house_number') . $this->get_customer_address_part($order_meta, '_house_number_suffix');
            if (empty($house_number)) {
                $house_number = $this->get_customer_address_from_address_line($order_meta)[1];
            }
            $customer_address = array(
                "business" => $this->get_customer_address_part($order_meta, '_company'),
                "chamber_of_commerce_number" => $orderid,
                "email_address" => $this->get_customer_address_part($order_meta, '_email'),
                "family_name" => $this->get_customer_address_part($order_meta, '_last_name'),
                "given_name" => $this->get_customer_address_part($order_meta, '_first_name'),
                "locality" => $this->get_customer_address_part($order_meta, '_city'),
                "phone_number" => "$phone",
                "street_name" => $street_name,
                "house_number" => $house_number,
                "zip_code" => str_replace(' ', '', $this->get_customer_address_part($order_meta, '_postcode')),
                "country" => $this->get_customer_address_part($order_meta, '_country'),
            );

            return $customer_address;

        }

        public function get_base64_image($picture)
        {

            $imagepath = $picture;
            try {
                if (filesize($imagepath) <= 2097152) { //smaller or equal to 2MB
                    $imagedata = file_get_contents($imagepath);
                    $image = base64_encode($imagedata);
                } else {
                    $image = "";
                }
                return $image;
            }catch(Exception $e) {
                return "";
            }
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
                    $data['dimensions'] = $product->get_dimensions();

                    $data_list['products'][] = $data;
                }
            }

            $data_list['images'] = $images;
            return $data_list;
        }

    }

}

new WC_Wuunder_Create();