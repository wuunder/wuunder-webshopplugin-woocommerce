// Get the modal
var modal = document.getElementById('parcelshopPopup');

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

var loader = document.getElementById('wuunderLoading');

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

function showParcelshopPicker(){
    // Show the popup window
    modal.style.display = "block";

    ajaxRequest();
}

function capFirst(str)
{
    return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

// AJAX request for the parcelshops
// Add json.stringify data for address
function ajaxRequest(){
    jQuery.ajax({
          type:'POST',
          data:{action:'parcelshoplocator'},
          url: "../wp-admin/admin-ajax.php",
          success: function(value) {
            // Turn of the loading icon
            loader.style.display = "none";

            // Display the map
            displayMap();

            // Display the parcelshops
            var val = JSON.parse(value.substring(0, value.length - 1));
            addParcelshopList(val);
          },

          error: function (xhr, ajaxOptions, thrownError) {
          console.log(xhr.status);
          console.log(thrownError);
          }
        });
}

function displayMap() {
  var pos = {lat: 51, lng: 5.83};
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


function addParcelshopList(data){
      data.parcelshops.forEach(function(shops){
        // console.log(shops);
        var node = document.createElement("div");
        node.className += "parcelshopItem";
        // node.onclick = parcelshopItemCallbackClosure(data.lat, data.long);
        node.innerHTML =   "<div class='companyList' id='parcelshopItem'>" +
                          	"<div id='company_name'>" + capFirst(shops.company_name) + "</div>" +
                            "<div id='street_name_and_number'>" + shops[0].street_name + " " + shops[0].house_number + "</div>" +
                            "<div id='zip_code_and_city'>" + shops[0].zip_code + " " + shops[0].city + "</div>" +
                            "<div id='distance'>" + shops.distance + "km</div>" +
                          "</div>" +
                          "<div class='opening_hours'>" +
                          "<div id='day'>Maandag</div><div id='day'>Dinsdag</div></div>";
        window.parent.document.getElementById('parcelshopList').appendChild(node);
      });
}
