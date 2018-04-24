<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
add_action('woocommerce_before_order_notes', 'parcelshop_html');

function callback_for_setting_up_scripts() {
    $pluginPath = dirname(plugin_dir_url(__FILE__));
    $pluginPath .= "/assets/css/parcelshop.css";
    wp_register_style( 'wuunderCSS', $pluginPath);
    wp_enqueue_style( 'wuunderCSS' );
}

function parcelshop_html(){
    $pluginPath = dirname(plugin_dir_url(__FILE__));
    $pluginPathJS = $pluginPath . "/assets/js/parcelshop.js";
    $pluginPathImg = $pluginPath . "/assets/images/parcelshop/bring-to-parcelshop.png";
    echo <<<EOT
    <div id="myModal" class="modal">
      <div class="modal-content">

        <div>
          <img id=bring-to-parcelshop src="$pluginPathImg" alt="parcelshop">
          <span id=parcelShopsTitleLogoChatbox>Kies een parcelshop</span>
          <span class="close">&times;</span>
        </div>

        <td>
          <span id="parcelShopsSearchBarContainer">
          <input id="parcelShopsSearchBar" type="text" placeholder="Search for address">
          <span id="submitParcelShopsSearchBar">OK</span>
          </span>
        </td>

      </div>
    </div>
    <script type="text/javascript" data-cfasync="false" src="$pluginPathJS"></script>
EOT;
echo '<div id="parcelshopsSelectedContainer" onclick="showParcelshopPicker()"><a href="#" id="selectParcelshop">Klik hier om een parcelshop te kiezen</a></div>';
}

?>