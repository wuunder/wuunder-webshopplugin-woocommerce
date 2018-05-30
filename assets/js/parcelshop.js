// Get the modal
var modal = document.getElementById('parcelshopPopup');

// Get the <span> element that closes the modal
var span = document.getElementById("close_parcelshop_modal");

var loader = document.getElementById('wuunderLoading');

var previous = 'company_number0';

var searchBar = document.getElementById('submitParcelShopsSearchBar');

var map;

var parcelshop_address;

// If parcelshop is selected in shipment. Adds a button to choose a parcelshop
if(document.getElementById('shipping_method_0_wuunder_parcelshop').checked) {
    var node = document.createElement("div");
    node.className += "chooseParcelshop";
    node.innerHTML = '<div id="parcelshopsSelectedContainer" onclick="showParcelshopPicker()"><a href="#/" id="selectParcelshop">Klik hier om een parcelshop te kiezen</a></div>';
    window.parent.document.getElementsByClassName('shipping')[0].appendChild(node);

    if(parcelshop_address) {
        var node = document.createElement("div");
        node.className += "parcelshopInfo";
        node.innerHTML = "<strong>Huidige Parcelshop: </strong><br>" + parcelshop_address;
        window.parent.document.getElementsByClassName('chooseParcelshop')[0].appendChild(node);
    }
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
    document.getElementsByTagName("BODY")[0].style.overflow = "scroll";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
        document.getElementsByTagName("BODY")[0].style.overflow = "scroll";
    }
}

// To start a search from a new adres
searchBar.onclick = function() {
    // Removes the earlier list of parcelshops
    var paras = document.getElementsByClassName('parcelshopItem');
    while(paras[0]) {
      paras[0].parentNode.removeChild(paras[0]);
    }

    // The new request with address added
    ajaxRequest(document.getElementById('parcelShopsSearchBar').value)
}

// When parcelshop is chosen shows adres
function chooseParcelshopButton(adres, parcelshop_id, parcelshop_country) {
    modal.style.display = "none";
    document.getElementsByTagName("BODY")[0].style.overflow = "scroll";

    if(document.getElementsByClassName('parcelshopInfo')[0]){
        document.getElementsByClassName('parcelshopInfo')[0].remove();
    }

    // Add these to hidden fields in checkout
    document.getElementById('parcelshop_id').value = parcelshop_id;
    document.getElementById('parcelshop_country').value = parcelshop_country;

    var node = document.createElement("div");
    node.className += "parcelshopInfo";
    node.innerHTML = "<strong>Huidige Parcelshop: </strong><br>" + adres;
    window.parent.document.getElementsByClassName('chooseParcelshop')[0].appendChild(node);

    parcelshop_address = adres;
}

// Shows the popup and starts the request towards Wuunder
function showParcelshopPicker() {
    modal.style.display = "block";
    loader.style.display = "block";
    document.getElementById("wrapper").style.display = "none";
    document.getElementById("parcelShopsSearchBarContainer").style.display = "none";
    document.getElementsByTagName("BODY")[0].style.overflow = "hidden";

    ajaxRequest();
}

// Capitalizes first letter of every new word.
function capFirst(str) {
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

// Based on click of parcelshop info shows the opening hours which are normally hidden
function showHours(index, lat, lng) {
    var hourStylingPrevious = document.getElementsByClassName(previous)[0].style;
    hourStylingPrevious.display ='none';
    hourStylingPrevious.borderLeft ='hidden';
    hourStylingPrevious.borderBottom ='hidden';
    // document.getElementsByClassName(previous)[0].style.display = "none";

    document.getElementsByClassName('company_number'+index)[0].style.display = "block";

    var hourStyling = document.getElementsByClassName('com_num'+index)[0].style;
    hourStyling.borderLeft   = 'solid';
    hourStyling.borderBottom = 'solid';
    hourStyling.borderTopLeftRadius    = '0.5em';
    hourStyling.borderBottomLeftRadius = '0.5em';
    hourStyling.borderColor = '#94d600';

    var item = document.getElementsByClassName('com_num'+index)[0].parentNode;
    item.parentNode.scrollTop = item.offsetTop - item.parentNode.offsetTop;


    previous = 'company_number'+index;

    var center = new google.maps.LatLng(lat, lng);
    map.panTo(center);
}

// Parses the opening hours
function getHours(days_hours) {
    // var opening_hours = "";
    var opening_hours = Array();
    var parcelshop_day = "<div id=parcelshop_day>";
    var parcelshop_hour = "<div id=parcelshop_hour>";

    days_hours.forEach(function(days){
      if (days.open_morning != "00:00" && days.close_afternoon != "00:00") {
        parcelshop_day += days.weekday + "<br>";
        parcelshop_hour += days.open_morning + " - " + days.close_afternoon + "<br>";
      }
    });
    parcelshop_day += "</div>";
    parcelshop_hour += "</div>";

    opening_hours['days'] = parcelshop_day;
    opening_hours['hours'] = parcelshop_hour;
    return opening_hours;
}

// retrieves the correct logo name
function getLogo(carrier_name) {
  var logo = "";
    switch(carrier_name.toUpperCase()) {
        case "DHL_PARCEL":
          logo = "DHL-locator.png";
          break;
        case "DPD":
          logo = "DPD-locator.png";
          break;
        case "GLS":
          logo = "GLS-locator.png";
          break;
        case "POST_NL":
          logo = "POSTNL-locator.png";
          break;
        default:
          logo = "position-sender.png";
    }
  return logo;
}

// Adds a marker for the respective parcelshop
function addMarkerToMap(lat, lng, logo, index) {
  var markerImage = {
        url: "../../wp-content/plugins/woocommerce-wuunder/assets/images/parcelshop/" + logo,
        size: new google.maps.Size(81, 101),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(50, 50)
    };

  var marker = new google.maps.Marker({
    position: {
      lat: lat,
      lng: lng
    },
    icon: markerImage,
    map : map
  });

  marker.addListener('click', function () {
    map.setZoom(15);
    map.setCenter(marker.getPosition());

    // Shows the opening hours for the selected shop
    showHours(index, lat, lng);
  });
}

// Fills the address bar in the popup & Jouw Adres above the list with the current location
function setAddress(address) {
    var current_address = "";
    if(address.street_name){
        current_address += address.street_name + " ";
    }
    if(address.house_number){
        current_address += address.house_number + " ";
    }
    if(address.city){
        current_address += address.city + " ";
    }
    if(address.zip_code){
        current_address += address.zip_code;
    }
    document.getElementById("parcelShopsSearchBar").value = current_address;
    document.getElementById("ownAdres").innerHTML = current_address;
}

function sortParcelshops(parcelshops) {
    parcelshops.sort(function(a, b){
        if (a.distance === b.distance) {
            return 0;
        } else {
            return (a.distance < b.distance) ? -1 : 1;
        }
    });
    return(parcelshops);
}

function displayMap(location) {
  var pos = {lat: location.lat, lng: location.lng};
    var mapOptions = {
          zoom: 15,
          center: pos,
          mapTypeId: google.maps.MapTypeId.ROAsetParcelshopImageDMAP,
          mapTypeControl: false,
          scaleControl: true,
          streetViewControl: false,
          rotateControl: false,
          fullscreenControl: false
    }
map = new google.maps.Map(document.getElementById("parcelshopMap"), mapOptions);

addMarkerToMap(location.lat, location.lng, "position-sender.png");
}

function ajaxRequest(address = null) {
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function() {
      if (this.readyState == 4 && this.status == 200) {
        console.log(xhttp.responseText);
          var val = JSON.parse(xhttp.responseText.substring(0, xhttp.responseText.length - 1));
          displayMap(val.location);
          setAddress(val.address);
          addParcelshopList(sortParcelshops(val.parcelshops));
          loader.style.display = "none";
          document.getElementById("wrapper").style.display = "block";
          document.getElementById("parcelShopsSearchBarContainer").style.display = "block";
      } else if (this.status == 400) {
          alert("Something went wrong: " + xhttp.responseText);
      }
    }
    var data = new FormData();
    data.append('action', 'parcelshoplocator');
    (address ? data.append('address', address) : false);
    xhttp.open("POST", "../../wp-admin/admin-ajax.php");
    xhttp.send(data);
}

function addParcelshopList(data) {
    data.forEach(function(shops, i){
        var hours = getHours(shops.opening_hours);
        var logo  = getLogo(shops.carrier_name);
        addMarkerToMap(shops.latitude, shops.longitude, logo, i);

        var node = document.createElement("div");
        node.className += "parcelshopItem";
        node.innerHTML =    "<div class='companyList com_num"+i+"' id='parcelshopItem' onclick='showHours("+i+","+shops.latitude+","+shops.longitude+")'>" +
                            "<div id='parcelshopItem_logo'><img id='company_logo' src='../../wp-content/plugins/woocommerce-wuunder/assets/images/parcelshop/"+logo+"'></div>" +
                          	"<div id='parcelshopItem_text'><div id='company_info'><div id='company_name'><strong>" + capFirst(shops.company_name) + "</strong></div>" +
                            "<div id='street_name_and_number'>" + capFirst(shops[0].street_name) + " " + shops[0].house_number + "</div>" +
                            "<div id='zip_code_and_city'>" + shops[0].zip_code + " " + shops[0].city + "</div>" +
                            "<div id='distance'>" + Math.round(shops.distance*1000) + "m</div></div></div>" +
                            "<div class='company_number"+i+" opening_hours_list' id='opening_hours' style='display:none'><br><strong>Openingstijden</strong>" +
                            "<div>" + hours['days'] + hours['hours'] + "</div><br><div id='buttonContainer'><button class='parcelshopButton' onclick='chooseParcelshopButton(\"" + capFirst(shops.company_name) + "<br>" + capFirst(shops[0].street_name) +
                            " " + shops[0].house_number + "<br>" + shops[0].city + "\", \"" + shops.id + "\", \"" + shops[0].alpha2 + "\")' type='button'>Kies</button></div></div>";
        window.parent.document.getElementById('parcelshopList').appendChild(node);
    });
}
