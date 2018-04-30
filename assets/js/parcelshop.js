// Get the modal
var modal = document.getElementById('myModal');

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

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

    /* ----- Pogin 1 ----- */
    // Make the request towards the backend
    // var xhttp = new XMLHttpRequest();
    // xhttp.onreadystatechange = function() {
    //   if (this.readyState == 4 && this.status == 200) {
    //       console.log("Request succesful");
    //       console.log(xhttp.responseText);
    //   } else {
    //       console.log("Request unsuccesful");
    //   }
    // }
    // xhttp.open("POST", '../wp-content/plugins/woocommerce-wuunder/includes/parcelshop', true);
    // xhttp.send(); // Hierin de factuurgegevens

    /* ----- Pogin 2 ----- */
    // jQuery.ajax({
    //       type:'POST',
    //       data:{action:'parcelshoplocator'},
    //       url: "../wp-admin/admin-ajax.php",
    //       success: function(value) {
    //         jQuery(this).html(value);
    //         console.log(value);
    //       }
    //     });

    /* ----- Pogin 3 ----- */
    var data = {
			'action': 'parcelshoplocator',
			'whatever': 1234
		};

		// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
		jQuery.post(ajaxurl, data, function(response) {
			alert('Got this from the server: ' + response);
		});
}
