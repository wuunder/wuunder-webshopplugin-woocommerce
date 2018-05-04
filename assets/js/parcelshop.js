// Get the modal
var modal = document.getElementById('parcelshopPopup');

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

var loader = document.getElementById('wuunderLoading');

var previous = 'company_number0';

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
    modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
    if (event.target == modal) {
        modal.style.display = "none";
    }
}

function showParcelshopPicker() {
    // Show the popup window
    modal.style.display = "block";
    document.getElementById("wrapper").style.display = "none";
    document.getElementById("parcelShopsSearchBarContainer").style.display = "none";

    ajaxRequest();
}

// Capitalizes first letter of every new word.
function capFirst(str) {
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

function km2meter(km) {
    return Math.round(km*1000);
}

// Based on click of parcelshop info shows the opening hours which are normally hidden
function showHours(index) {
    document.getElementsByClassName(previous)[0].style.display = "none";
    document.getElementsByClassName('company_number'+index)[0].style.display = "block";
    previous = 'company_number'+index;
}

// Parses the opening hours
function getHours(days_hours) {
    var opening_hours = "";
    days_hours.forEach(function(days){
      if (days.open_morning != "00:00" && days.close_afternoon != "00:00") {
        opening_hours += "<div>" + days.weekday + " " + days.open_morning + " - " + days.close_afternoon + "</div>";
      }
    });
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
function addMarkerToMap() {

  var markerImage = {
        url: "../wp-content/plugins/woocommerce-wuunder/assets/images/parcelshop/position-sender.png",
        size: new google.maps.Size(81, 101),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(50, 50)
    };

  var marker = new google.maps.Marker({
    position: {
      lat: 51.1445517,
      lng: 5.89
    },
    icon: markerImage,
    map : map
  });

//   marker.addListener('click', function () {
//     map.setZoom(15);
//     map.setCenter(marker.getPosition());
//     openParcelshopItemDetails(window.parent.document.getElementsByClassName("parcelshopItem-" + i)[0]);
//   });

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
        current_address += address.city;
    }
    document.getElementById("parcelShopsSearchBar").value = current_address;
    document.getElementById("ownAdres").innerHTML = current_address;
}

// Scrapes the billing address to use for the locator
function getAddress() {
  var street_and_number = document.getElementById('billing_address_1').value;
  var city = document.getElementById('billing_city').value;
  if(street_and_number || city) {
      return street_and_number + " " + city;
  } else {
      return 'Utrecht';
  }
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
var map = new google.maps.Map(document.getElementById("parcelshopMap"), mapOptions);
}

// AJAX request for the parcelshops
function ajaxRequest() {
    jQuery.ajax({
          type:'POST',
          data:{ action:'parcelshoplocator',
                address: getAddress() },
          url: "../wp-admin/admin-ajax.php",
          success: function(value) {

// console.log(value);
            // Display the parcelshops
            var val = JSON.parse(value.substring(0, value.length - 1));
            displayMap(val.location);
            setAddress(val.address);
            addParcelshopList(sortParcelshops(val.parcelshops));
            loader.style.display = "none";
            document.getElementById("wrapper").style.display = "block";
            document.getElementById("parcelShopsSearchBarContainer").style.display = "block";
          },

          error: function (xhr, ajaxOptions, thrownError) {
          console.log(xhr.status);
          console.log(thrownError);
          }
        });
}

function addParcelshopList(data) {
      data.forEach(function(shops, i){
        var hours = getHours(shops.opening_hours);
        var logo  = getLogo(shops.carrier_name);
        // addMarkerToMap(shops.latitude, shops.longitude, logo);
// console.log(shops);
        var node = document.createElement("div");
        node.className += "parcelshopItem";
        // node.onclick = parcelshopItemCallbackClosure(data.lat, data.long);
        node.innerHTML =    "<div class='companyList' id='parcelshopItem' onclick='showHours("+i+")'>" +
                            "<div><img id='company_logo' src='../wp-content/plugins/woocommerce-wuunder/assets/images/parcelshop/"+logo+"'></div>" +
                          	"<div id='company_info'><div id='company_name'><strong>" + capFirst(shops.company_name) + "</strong></div>" +
                            "<div id='street_name_and_number'>" + capFirst(shops[0].street_name) + " " + shops[0].house_number + "</div>" +
                            "<div id='zip_code_and_city'>" + shops[0].zip_code + " " + shops[0].city + "</div>" +
                            "<div id='distance'>" + km2meter(shops.distance) + "m</div></div>" +
                            "<div class='company_number"+i+"' id='opening_hours' style='display:none'>" +
                            hours + "</div>";

        window.parent.document.getElementById('parcelshopList').appendChild(node);
      });
}
