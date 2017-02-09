<?php
error_reporting(1);

if( !class_exists('WC_Wuunder_Create') ) {

	class WC_Wuunder_Create {
		public $order_id;

		public function __construct() {
			add_action( 'load-edit.php', array( &$this, 'generate_shipping_label_action' ) );
			//add_action( 'admin_init', array( &$this, 'generate_shipping_label_action' ) );
			add_action( 'woocommerce_admin_order_actions_end', array( &$this, 'add_listing_actions' ) );
			add_action( 'add_meta_boxes_shop_order', array(&$this, 'add_meta_boxes' ) );


			add_action( 'admin_notices', array(&$this, 'sample_admin_notice__error') );

			// AJAX function to create label and show errors
			//add_action( 'wp_ajax_generate_shipping_label_action', array($this, 'generate_shipping_label_action') );
            //add_action( 'wp_ajax_nopriv_generate_shipping_label_action', array($this, 'generate_shipping_label_action') );
		}


		public function sample_admin_notice__error() {

			if($_GET['notice'] == 'error'){

				$class = 'notice notice-error';
				$message = __( '<b>Het aanmaken van het label is mislukt</b>', 'woocommerce-wuunder' );
				//$errors = json_decode($_GET['content']);
				$errors = $_GET['error_melding'];
				$message .= '<ul style="margin:0 0 0 20px; padding:0; list-style:inherit;">';
				foreach($errors as $error){
					$message .= '<li>'.$error.'</li>';
				}
				$message .= '</ul>';
				
				printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message );

			}else if($_GET['notice'] == 'success'){

				$class = 'notice notice-success';
				$message = __( 'Het verzendlabel is aangemaakt', 'woocommerce-wuunder' );
				printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 

			}
			
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
							'huisnummertoevoeging'	=> isset($order_meta['_shipping_house_number_suffix'][0])?$order_meta['_shipping_house_number_suffix'][0]:'',
							'woonplaats'			=> $order_meta['_shipping_city'][0],
							'land'					=> $full_country->countries[$order_meta['_shipping_country'][0]],
							'email'					=> $order_meta['_billing_email'][0],
							'telefoon'				=> $order_meta['_billing_phone'][0],
							'huisnummer'			=> isset($order_meta['_shipping_house_number'][0])?$order_meta['_shipping_house_number'][0]:'',
							'straat'				=> isset($order_meta['_shipping_street_name'][0])?$order_meta['_shipping_street_name'][0]:'',
							'landcode'				=> $order_meta['_shipping_country'][0],
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

						$post_data 	= stripslashes_deep($_POST['data']);
						
						// Get image as base64_image
						$b64image 	= $this->get_base64_image($post_data['picture']);

						// Get WooCommerce Wuunder Address from options page
						$company 	= $this->get_company_address($post_data['customer_reference']);
						$customer 	= $this->get_customer_address($post_data['customer_reference']);
						
						$shippingArray = array(
							"description" 			=> $post_data['description'],
							"personal_message" 		=> $post_data['personal_message'],
							"customer_reference"	=> $post_data['customer_reference'],
							"picture" 				=> $b64image,
							"value" 				=> $post_data['value'],
							"kind" 					=> $post_data['kind'],
							"length" 				=> $post_data['length'],
							"width" 				=> $post_data['width'],
							"height" 				=> $post_data['height'],
							"weight" 				=> $post_data['weight'],
							"delivery_address" 		=> $customer,
							"pickup_address" 		=> $company,
						);
						
						$feedback =  $this->api_request_new($shippingArray, 'POST');

						if( !empty($feedback['errors']) ){

							$error_notices = $feedback['errors'];
							$errors = '';
							foreach ($error_notices as $error_notice) {
								$errors .= '&error_melding['.$error_notice['field'].']='.$error_notice['field'].': '.$error_notice['messages'][0];
								break;
							}
							header( "refresh:0;url=".admin_url('edit.php?post_type=shop_order&notice=error'.$errors) );

						}else{
							
							if( !empty($feedback['id']) || !empty($feedback['track_and_trace_url']) || !empty($feedback['label_url']) ){
								update_post_meta( $post_data['customer_reference'], '_wuunder_label_id', $feedback['id'] );
								update_post_meta( $post_data['customer_reference'], '_wuunder_track_and_trace_url', $feedback['track_and_trace_url'] );
								update_post_meta( $post_data['customer_reference'], '_wuunder_label_url', $feedback['label_url'] );
							}
							
							header( "refresh:0;url=".admin_url('edit.php?post_type=shop_order&notice=success') );
						}

					exit;

					case 'wcwuunder-retour':

						$post_data 	= stripslashes_deep($_POST['data']);
						
						// Get image as base64_image
						$b64image 	= $this->get_base64_image($post_data['picture']);

						// Get WooCommerce Wuunder Address from options page
						$company 	= $this->get_company_address($post_data['customer_reference']);
						$customer 	= $this->get_customer_address($post_data['customer_reference']);
						
						$shippingArray = array(
							"description" 			=> $post_data['description'],
							"personal_message" 		=> $post_data['personal_message'],
							"customer_reference"	=> $post_data['customer_reference'],
							"picture" 				=> $b64image,
							"value" 				=> $post_data['value'],
							"kind" 					=> $post_data['kind'],
							"length" 				=> $post_data['length'],
							"width" 				=> $post_data['width'],
							"height" 				=> $post_data['height'],
							"weight" 				=> $post_data['weight'],
							"delivery_address" 		=> $company,
							"pickup_address" 		=> $customer,
						);
						
						$feedback =  $this->api_request_new($shippingArray, 'POST');
						
						if( !empty($feedback['errors']) ){

							$error_notices = $feedback['errors'];
							$errors = '';
							foreach ($error_notices as $error_notice) {
								$errors .= '&error_melding['.$error_notice['field'].']='.$error_notice['field'].': '.$error_notice['messages'][0];
								break;
							}
							header( "refresh:0;url=".admin_url('edit.php?post_type=shop_order&notice=error'.$errors) );

						}else{
							if( !empty($feedback['id']) || !empty($feedback['track_and_trace_url']) || !empty($feedback['label_url']) ){

								update_post_meta( $post_data['customer_reference'], '_wuunder_retour_label_id', $feedback['id'] );
								update_post_meta( $post_data['customer_reference'], '_wuunder_retour_track_and_trace_url', $feedback['track_and_trace_url'] );
								update_post_meta( $post_data['customer_reference'], '_wuunder_retour_label_url', $feedback['label_url'] );

								echo get_post_meta( $post_data['customer_reference'], '_wuunder_retour_label_id', true );
							}
							
							header( "refresh:0;url=".admin_url('edit.php?post_type=shop_order&notice=success') );
						}
						
					exit;
					default: return;
				}

			}

		}

		public function api_request_new( $data, $method ){

			$json_data = json_encode($data);
			$access_key = get_option('wc_wuunder_api');
			$status = get_option('wc_wuunder_api_status');

			// Check Woocommerce Wuunder Setting
			if($status == 'productie'){
				// Productie api url
				$curl = curl_init( 'https://api.wuunder.co/api/shipments' );
			}else{
				// Staging api url
				$curl = curl_init( 'https://api-staging.wuunder.co/api/shipments' );
			}
			
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $access_key, 'Content-type: application/json' ) );
			
			switch ($method)
			{
				case "POST":
					curl_setopt($curl, CURLOPT_POST, 1);
		
					if ($json_data)
						curl_setopt($curl, CURLOPT_POSTFIELDS, $json_data);
					break;
				case "PUT":
					curl_setopt($curl, CURLOPT_PUT, 1);
					break;
				default:
					if ($json_data)
						$url = sprintf("%s?%s", $url, http_build_query($json_data));
			}
			
		    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		    $result = curl_exec($curl);
			curl_close($curl);
			$result = json_decode($result, true);
			
			return $result;

		}

		public function get_company_address($orderid){

			// Get Woocommerce Wuunder Settings
			$company_address = array(
				"business" 						=> get_option('wc_wuunder_company_name'),
				"chamber_of_commerce_number" 	=> $orderid,
				"email_address" 				=> get_option('wc_wuunder_company_email'),
				"family_name" 					=> get_option('wc_wuunder_company_lastname'),
				"given_name" 					=> get_option('wc_wuunder_company_firstname'),
				"locality" 						=> get_option('wc_wuunder_company_city'),
				"phone_number" 					=> get_option('wc_wuunder_company_phone'),
				"street_address" 				=> get_option('wc_wuunder_company_street'),
				"house_number" 					=> get_option('wc_wuunder_company_housenumber'),
				"zip_code" 						=> get_option('wc_wuunder_company_postode'),
				"country" 						=> "NL",
			);

			return $company_address;

		}

		public function get_customer_address($orderid){

			// Get customer address from order
			$order_meta = get_post_meta($orderid);
			$customer_address = array(
				"business" 						=> $order_meta['_shipping_company'][0],
				"chamber_of_commerce_number" 	=> $orderid,
				"email_address" 				=> $order_meta['_billing_email'][0],
				"family_name" 					=> $order_meta['_shipping_last_name'][0],
				"given_name" 					=> $order_meta['_shipping_first_name'][0],
				"locality" 						=> $order_meta['_shipping_city'][0],
				"phone_number" 					=> $order_meta['_billing_phone'][0],
				"street_address" 				=> isset($order_meta['_shipping_street_name'][0])?$order_meta['_shipping_street_name'][0]:'',
				"house_number" 					=> isset($order_meta['_shipping_house_number'][0])?$order_meta['_shipping_house_number'][0]:'', //isset($order_meta['_shipping_house_number_suffix'][0])?$order_meta['_shipping_house_number_suffix'][0]:'',
				"zip_code" 						=> str_replace(' ', '', $order_meta['_shipping_postcode'][0]),
				"country" 						=> $order_meta['_shipping_country'][0],//$full_country->countries[$order_meta['_shipping_country'][0]],
			);

			return $customer_address;

		}

		public function get_base64_image($picture){

			$imagepath 	= $picture;
			$imagetype 	= pathinfo($imagepath, PATHINFO_EXTENSION);
			$imagedata 	= file_get_contents($imagepath);
			$image 	= base64_encode($imagedata);

			return $image;

		}

		public function add_listing_actions( $order ) {

			// do not show buttons for trashed orders
			if ( $order->status == 'trash' ) {
				return;
			}

			if(!empty(get_post_meta( $order->id, '_wuunder_label_id', true)) && empty(get_post_meta( $order->id, '_wuunder_retour_label_id', true))){
				$listing_actions = array(
					'shipping_label'=> array (
						'url'		=> get_post_meta( $order->id, '_wuunder_label_url', true),
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/print-label.png',
						'title'		=> __( 'Download label', 'woocommerce-wuunder' ),
					),
					'track_trace'	=> array (
						'url'		=> get_post_meta( $order->id, '_wuunder_track_and_trace_url', true),
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/in-transit.png',
						'title'		=> __( 'Track & Trace', 'woocommerce-wuunder' ),
					),
					'retour'	=> array (
						'url'		=> wp_nonce_url( admin_url( 'edit.php?&action=wcwuunder&order_ids=' . $order->id.'&label=retour' ), 'wcwuunder' ),
						'action'	=> 'thickbox',
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/retour-icon.png',
						'title'		=> __( 'Retour label aanvragen', 'woocommerce-wuunder' ),
					)
				);

				echo '<div style="clear:both;">';
				foreach ($listing_actions as $action => $data) {
					$target = ' target="_blank" ';
					?>
					<a<?= $target; ?>href="<?php echo $data['url']; ?>" class="<?php echo $data['action']; ?> button tips <?php echo $action; ?>" style="background:#8dcc00; height:2em; width:2em;" alt="<?php echo $data['title']; ?>" data-tip="<?php echo $data['title']; ?>">
					<img src="<?php echo $data['img']; ?>" style="padding-top:2px;" alt="<?php echo $data['title']; ?>">
					</a>
					<?php
				}
				echo '</div>';
			}elseif(!empty(get_post_meta( $order->id, '_wuunder_retour_label_id', true))){
				$listing_actions = array(
					/*
					'retour_ontvangen'	=> array (
						'url'		=> get_post_meta( $order->id, '_wuunder_retour_label_url', true),
						'action'	=> 'thickbox',
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/retour-icon.png',
						'alt'		=> __( 'Retour ontvangen', 'woocommerce-wuunder' ),
						'title'		=> __( 'Ontvangen', 'woocommerce-wuunder' ),
					),
					*/
					'shipping_label'=> array (
						'url'		=> get_post_meta( $order->id, '_wuunder_label_url', true),
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/print-label.png',
						'alt'		=> __( 'Verzendlabel', 'woocommerce-wuunder' ),
						'title'		=> __( 'Download label', 'woocommerce-wuunder' ),
					),
					'track_trace'	=> array (
						'url'		=> get_post_meta( $order->id, '_wuunder_retour_track_and_trace_url', true),
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/barcode-icon.png',
						'alt'		=> __( 'Track & Trace retour', 'woocommerce-wuunder' ),
						'title'		=> __( 'Track & Trace retour', 'woocommerce-wuunder' ),
					),
					'retour_downloaden'	=> array (
						'url'		=> get_post_meta( $order->id, '_wuunder_retour_label_url', true),
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/download-icon.png',
						'alt'		=> __( 'Retour label downloaden', 'woocommerce-wuunder' ),
						'title'		=> __( 'Downloaden', 'woocommerce-wuunder' ),
					)
				);

				echo '<div style="clear:both;">';
				foreach ($listing_actions as $action => $data) {
					$target = ' target="_blank" ';
					?>
					<a<?= $target; ?>href="<?php echo $data['url']; ?>" class="<?php echo $data['action']; ?> button tips <?php echo $action; ?>" style="background:#8dcc00; height:2em; width:2em; color:#fff;" alt="<?php echo $data['title']; ?>" data-tip="<?php echo $data['alt']; ?>">
						<?php /*<?php echo $data['title']; ?>*/ ?>
						<img src="<?php echo $data['img']; ?>" style="padding-top:2px;" alt="<?php echo $data['title']; ?>">
					</a>
					<?php
				}
				echo '</div>';
				
			}else{
				$listing_actions = array(
					'create_label'		=> array (
						'url'		=> wp_nonce_url( admin_url( 'edit.php?&action=wcwuunder&order_ids=' . $order->id ), 'wcwuunder' ),
						'img'		=> Woocommerce_Wuunder::$plugin_url. 'assets/images/create-label.png',
						'alt'		=> __( 'Verzendlabel aanmaken', 'woocommerce-wuunder' ),
					),
				);

				foreach ($listing_actions as $action => $data) {
					?>
					<a href="<?php echo $data['url']; ?>" class="thickbox button tips <?php echo $action; ?>" style="background:#8dcc00; height:2em; width:2em;" alt="<?php echo $data['alt']; ?>" data-tip="<?php echo $data['alt']; ?>">
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