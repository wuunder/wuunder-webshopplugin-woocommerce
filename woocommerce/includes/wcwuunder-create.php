<?php
if( !class_exists('WC_Wuunder_Create') ) {

	class WC_Wuunder_Create {
		public $order_id;

		public function __construct() {
			add_action( 'load-edit.php', array( &$this, 'generate_shipping_label_action' ) );
			add_action( 'woocommerce_admin_order_actions_end', array( &$this, 'add_listing_actions' ) );
			add_action( 'add_meta_boxes_shop_order', array(&$this, 'add_meta_boxes' ) );
		}

		/**
		 * Export selected orders
		 *
		 * @access public
		 * @return void
		 */
		public function generate_shipping_label_action() {
			
			if ( isset($_REQUEST['action']) ) {
				$action = $_REQUEST['action'];

				switch($action) {
					case 'wcwuunder':
						$order_ids = $_GET['order_ids'];
						$order_meta = get_post_meta( $order_ids );
						$order = new WC_Order( $order_ids );
						$order_number = $order->get_order_number();
						$formatted_address = $order->get_formatted_shipping_address();
						$full_country = new WC_Countries;
						$bestelling = $this->get_order_items( $order_ids );

						$data[] = array(
							'firstname'				=> $order_meta['_shipping_first_name'][0],
							'lastname'				=> $order_meta['_shipping_last_name'][0],
							'bedrijfsnaam'			=> $order_meta['_shipping_company'][0],
							'postcode'				=> str_replace(' ', '', $order_meta['_shipping_postcode'][0]),
							'adres1'				=> isset($order_meta['_shipping_address_1'][0])?$order_meta['_shipping_address_1'][0]:'',
							'adres2'				=> isset($order_meta['_shipping_address_2'][0])?$order_meta['_shipping_address_2'][0]:'',
							'huisnummer'			=> isset($order_meta['_shipping_house_number'][0])?$order_meta['_shipping_house_number'][0]:'',
							'huisnummertoevoeging'	=> isset($order_meta['_shipping_house_number_suffix'][0])?$order_meta['_shipping_house_number_suffix'][0]:'',
							'straat'				=> isset($order_meta['_shipping_street_name'][0])?$order_meta['_shipping_street_name'][0]:'',
							'woonplaats'			=> $order_meta['_shipping_city'][0],
							'landcode'				=> $order_meta['_shipping_country'][0],
							'land'					=> $full_country->countries[$order_meta['_shipping_country'][0]],
							'email'					=> $order_meta['_billing_email'][0],
							'telefoon'				=> $order_meta['_billing_phone'][0],
							'orderid'				=> $order_ids,							
							'ordernr'				=> $order_number,
							'picture'				=> $bestelling['images'][0],
							'bestelling'			=> $bestelling['products'],
							'formatted_address'		=> $formatted_address,
							'waarde'				=> $order->get_subtotal()
						);

						include('wcwuunder-export-html.php');
						die();
					break;

					case 'wcwuunder-export':
						$company = get_option('wc_wuunder_company_name');
						$street = get_option('wc_wuunder_company_street');
						$number = get_option('wc_wuunder_company_housenumber');
						$postcode = get_option('wc_wuunder_company_postode');
						$city = get_option('wc_wuunder_company_city');
						$firstname = get_option('wc_wuunder_company_firstname');
						$lastname = get_option('wc_wuunder_company_lastname');
						$email = get_option('wc_wuunder_company_email');
						$phone = get_option('wc_wuunder_company_phone');

						$post_data = stripslashes_deep($_POST['data']);
						$b64image = base64_encode($post_data['picture']);
						$shippingArray = array(
							"description" => $post_data['description'],
							"personal_message" => $post_data['personal_message'],
							"picture" => null,//$b64image,
							"value" => $post_data['value'],
							"kind" => $post_data['kind'],
							"length" => $post_data['length'],
							"width" => $post_data['width'],
							"height" => $post_data['height'],
							"weight" => $post_data['weight'],
							"delivery_address" => array(
								"business" => $post_data['business'],
								"chamber_of_commerce_number" => $post_data['chamber_of_commerce_number'],
								"email_address" => $post_data['email_address'],
								"family_name" => $post_data['family_name'],
								"given_name" => $post_data['given_name'],
								"locality" => $post_data['locality'],
								"phone_number" => $post_data['phone_number'],
								"street_address" => $post_data['street_address'],
								"house_number" => $post_data['house_number'],
								"zip_code" => $post_data['zip_code'],
								"country" => $post_data['country'],
							),
							"pickup_address" => array(
								"business" => $company,
								"chamber_of_commerce_number" => $post_data['chamber_of_commerce_number'],
								"email_address" => $email,
								"family_name" => $firstname,
								"given_name" => $lastname,
								"locality" => $city,
								"phone_number" => $phone,
								"street_address" => $street,
								"house_number" => $number,
								"zip_code" => $postcode,
								"country" => "NL",
							)
						);
						
						$feedback = $this->api_request($shippingArray);
						//echo '<pre>';
						//print_r($feedback);
						//echo '</pre>';
						if( !empty($feedback->id) || !empty($feedback->track_and_trace_url) || !empty($feedback->label_url) ){
							update_post_meta( $post_data['chamber_of_commerce_number'], '_wuunder_label_id', $feedback->id );
							update_post_meta( $post_data['chamber_of_commerce_number'], '_wuunder_track_and_trace_url', $feedback->track_and_trace_url );
							update_post_meta( $post_data['chamber_of_commerce_number'], '_wuunder_label_url', $feedback->label_url );
						}
						
						header( "refresh:0;url=".admin_url('edit.php?post_type=shop_order') );
					exit;
					default: return;
				}
			}			
		}


		public function add_listing_actions( $order ) {
			// do not show buttons for trashed orders
			if ( $order->status == 'trash' ) {
				return;
			}

			if(!empty(get_post_meta( $order->id, '_wuunder_label_id', true))){
				$listing_actions = array(
					'shipping_label'		=> array (
						'url'		=> get_post_meta( $order->id, '_wuunder_label_url', true),
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/shipping-icon.png',
						'title'		=> __( 'Download label', 'woocommerce-wuunder' ),
					),
					'track_trace'	=> array (
						'url'		=> get_post_meta( $order->id, '_wuunder_track_and_trace_url', true),
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/barcode-icon.png',
						'title'		=> __( 'Track & Trace', 'woocommerce-wuunder' ),
					),
					/*
					'retour'	=> array (
						'url'		=> '#',//get_post_meta( $order->id, '_wuunder_label_url', true),
						'action'	=> 'thickbox',
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/retour-icon.png',
						'title'		=> __( 'Retour label', 'woocommerce-wuunder' ),
					),
					*/
				);
				echo '<div style="clear:both;">';
				foreach ($listing_actions as $action => $data) {
					$target = ' target="_blank" ';
					?>
					<a<?= $target; ?>href="<?php echo $data['url']; ?>" class="button tips <?php echo $action; ?>" style="background:#8dcc00; height:2em; width:2em;" alt="<?php echo $data['title']; ?>" data-tip="<?php echo $data['title']; ?>">
					<img src="<?php echo $data['img']; ?>" style="padding-top:2px;" alt="<?php echo $data['title']; ?>">
					</a>
					<?php
				}
				echo '</div>';
			}else{
				$listing_actions = array(
					'create_label'		=> array (
						'url'		=> wp_nonce_url( admin_url( 'edit.php?&action=wcwuunder&order_ids=' . $order->id ), 'wcwuunder' ),
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/create-icon.png',
						'alt'		=> __( 'Verzendlabel aanmaken', 'woocommerce-wuunder' ),
					),
				);

				foreach ($listing_actions as $action => $data) {
					?>
					<a href="<?php echo $data['url']; ?>" class="thickbox button tips <?php echo $action; ?>" style="height:2em; width:2em;" alt="<?php echo $data['alt']; ?>" data-tip="<?php echo $data['alt']; ?>">
						<img src="<?php echo $data['img']; ?>" style="padding-top:2px;" alt="<?php echo $data['alt']; ?>">
					</a>
					<?php
				}
			}
		}

		/**
		 * Add the meta box on the single order page
		 */
		public function add_meta_boxes() {
			// create PDF buttons
			add_meta_box(
				'wpo_wcpdf-box',
				__( 'Wuunder', 'woocommerce-wuunder' ),
				array( $this, 'sidebar_box_content' ),
				'shop_order',
				'side',
				'default'
			);
		}

		/**
		 * Create the meta box content on the single order page
		 */
		public function sidebar_box_content( $post ) {
			global $post_id;

			$meta_actions = array(
				'shipping_label'=> array (
					'url'		=> wp_nonce_url( admin_url( 'edit.php?&action=wcwuunder&order_ids=' . $post_id ), 'wcwuunder' ),
					'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/shipping-icon.png',
					'alt'		=> __( 'Shipping label', 'woocommerce-wuunder' ),
					'title'		=> __( 'Shipping label', 'wpo_wcpdf' ),
				),
			);

			?>
			<ul class="ww-actions">
				<?php
				foreach ($meta_actions as $action => $data) {
					printf('<li><a href="%1$s" class="thickbox button" alt="%2$s">%3$s</a></li>', $data['url'], $data['alt'],$data['title']);
				}
				?>
			</ul>
			<?php
		}

		public function api_request( $data, $method = 'POST' ) {
			$json = json_encode($data);
			$access_key = get_option('wc_wuunder_api');
			$curl = curl_init( 'https://api-staging.wuunder.co/api/shipments' );
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_key, 'Content-type: application/json' ) );
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		    curl_setopt($curl, CURLOPT_POSTFIELDS, $json);
		    $result = curl_exec( $curl );
			curl_close($curl);
			$result = json_decode($result);

			return $result;
		}

		public function get_order_items( $order_id ) {
			global $woocommerce;
			$order = new WC_Order( $order_id );
			//global $_product;
			$items = $order->get_items();
			$data_list = array();
			$images = array();
		
			if( sizeof( $items ) > 0 ) {
				foreach ( $items as $item ) {
					// Array with data for the printing template
					$data = array();
					
					$image = wp_get_attachment_image_src( get_post_thumbnail_id( $item['product_id'] ), 'thumbnail' );
					$images[] = $image[0];
					
					// Create the product
					$product = $order->get_product_from_item( $item );

					// Set the variation
					if( isset( $item['variation_id'] ) && $item['variation_id'] > 0 ) {
						$data['variation'] = woocommerce_get_formatted_variation( $product->get_variation_attributes() );
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
					$weight_unit = get_option( 'woocommerce_weight_unit' );
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
					
					$data['total_weight'] = $data['quantity']*$data['weight'];
					
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