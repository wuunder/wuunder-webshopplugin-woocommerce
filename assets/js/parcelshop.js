// Get the modal
var modal = document.getElementById('myModal');

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

// AJAX request for the parcelshops
// Add json.stringify data for address
function ajaxRequest(){
    jQuery.ajax({
          type:'POST',
          data:{action:'parcelshoplocator'},
          url: "../wp-admin/admin-ajax.php",
          success: function(value) {
            // console.log(value);
            loader.style.display = "none";
            var val = JSON.parse(value.substring(0, value.length - 1));
            addParcelshops(val);
          },
          error: function (xhr, ajaxOptions, thrownError) {
          console.log(xhr.status);
          console.log(thrownError);
          }
        });
}

function addParcelshops(data){
    console.log(data.parcelshops);
      data.parcelshops.forEach(function(shops){
        console.log(shops.provider);
        // document.getElementById('parcelshopList').innerHTML = shops.provider;
      });
}
