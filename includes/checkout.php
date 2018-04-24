<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('woocommerce_before_order_notes', 'parcelshop_html');

function parcelshop_html(){
  $pluginPath = plugins_url();
  $pluginPath .= "/woocommerce-wuunder/assets/js/parcelshop.js";
  echo <<<EOT
  <script type="text/javascript">
  console.log("Dit is het path: ");
  console.log("$pluginPath");
  </script>
  <script type="text/javascript" data-cfasync="false" src="$pluginPath"></script>
EOT;
echo '<div id="parcelshopsSelectedContainer" onclick="showParcelshopPicker()"><a href="#" id="selectParcelshop">Klik hier om een parcelshop te kiezen</a></div>';
}

?>
