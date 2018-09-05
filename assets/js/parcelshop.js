// Get the modal
var modal = document.getElementById('parcelshopPopup');

// Get the <span> element that closes the modal
var span = document.getElementById("close_parcelshop_modal");

var loader = document.getElementById('wuunderLoading');

var searchBar = document.getElementById('submitParcelShopsSearchBar');

var map;

var parcelshop_address;

// If parcelshop is selected in shipment. Adds a button to choose a parcelshop
if (document.getElementById('shipping_method_0_wuunder_parcelshop').checked) {
    var node = document.createElement("div");
    node.className += "chooseParcelshop";
    node.innerHTML = '<div id="parcelshopsSelectedContainer" onclick="showParcelshopPicker()"><a href="#/" id="selectParcelshop">Klik hier om een parcelshop te kiezen</a></div>';
    window.parent.document.getElementsByClassName('shipping')[0].appendChild(node);

    if (parcelshop_address) {
        var node = document.createElement("div");
        node.className += "parcelshopInfo";
        node.innerHTML = "<strong>Huidige Parcelshop: </strong><br>" + parcelshop_address;
        window.parent.document.getElementsByClassName('chooseParcelshop')[0].appendChild(node);
    }
}

// When the user clicks on <span> (x), close the modal
span.onclick = function () {
    modal.style.display = "none";
    document.getElementsByTagName("BODY")[0].style.overflow = "scroll";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function (event) {
    if (event.target == modal) {
        modal.style.display = "none";
        document.getElementsByTagName("BODY")[0].style.overflow = "scroll";
    }
}

// To start a search from a new adres
searchBar.onclick = function () {
    // Removes the earlier list of parcelshops
    var paras = document.getElementsByClassName('parcelshopItem');
    while (paras[0]) {
        paras[0].parentNode.removeChild(paras[0]);
    }

    // The new request with address added
    ajaxRequest(document.getElementById('parcelShopsSearchBar').value)
}

// When parcelshop is chosen shows adres
function chooseParcelshopButton(adres, parcelshop_id, parcelshop_country) {
    modal.style.display = "none";
    document.getElementsByTagName("BODY")[0].style.overflow = "scroll";

    if (document.getElementsByClassName('parcelshopInfo')[0]) {
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
    return str.replace(/\w\S*/g, function (txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}

function selectParcelshopItemByIndex(index, lat, long) {
    var clickedElement = window.document.getElementsByClassName("parcelshopItem" + index)[0];
    if (!clickedElement.classList.contains('parcelshopItem')) {
        clickedElement = closest(clickedElement, '.parcelshopItem');
    }
    openParcelshopItemDetails(clickedElement);
    map.setCenter(new google.maps.LatLng(lat, long));
}

function parcelshopItemCallbackClosure(lat, long) {
    return function (e) {
        var clickedElement = e.target;
        if (!clickedElement.classList.contains('parcelshopItem')) {
            clickedElement = closest(clickedElement, '.parcelshopItem');
        }
        openParcelshopItemDetails(clickedElement);
        map.setCenter(new google.maps.LatLng(lat, long));
    }
}

function openParcelshopItemDetails(parcelshopItem) {
    if (parcelshopItem.classList.contains("selected")) {
        parcelshopItem.classList.remove("selected");
    } else {
        closeAllParcelshopItemDetails();
        parcelshopItem.classList.add("selected");
        parcelshopItem.parentNode.scrollTop = parcelshopItem.offsetTop - parcelshopItem.parentNode.offsetTop;
    }
}

function closeAllParcelshopItemDetails() {
    var elements = window.parent.document.getElementsByClassName('parcelshopItem');

    for (var i = 0; i < elements.length; i++) {
        elements[i].classList.remove("selected");
    }
}

// Parses the opening hours
function getHours(days_hours) {
    var opening_hours_list = "<div><table>";

    days_hours.forEach(function (days) {
        if (days.open_morning !== "00:00" && days.open_morning !== null && days.close_afternoon !== "00:00" && days.close_afternoon !== null) {
            opening_hours_list += "<tr><td>" + days.weekday + "</td><td>" + days.open_morning + " - " + days.close_afternoon + "</td></tr>";
        }
    });
    opening_hours_list += "</table></div>";

    return opening_hours_list;
}

// retrieves the correct logo name
function getLogo(carrier_name) {
    var logo = "";
    switch (carrier_name.toUpperCase()) {
        case "DHL_PARCEL":
            logo = "DHL-locator.png";
            break;
        case "DPD":
            logo = "DPD-locator.png";
            break;
        case "GLS":
            logo = "GLS-locator.png";
            break;
        case "POSTNL":
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
        url: pluginPath + "/assets/images/parcelshop/" + logo,
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
        map: map
    });

    marker.addListener('click', function () {
        map.setZoom(15);
        map.setCenter(marker.getPosition());

        // Shows the opening hours for the selected shop
        selectParcelshopItemByIndex(index, lat, lng);
    });
}

// Fills the address bar in the popup & Jouw Adres above the list with the current location
function setAddress(address) {
    var current_address = "";
    if (address.street_name) {
        current_address += address.street_name + " ";
    }
    if (address.house_number) {
        current_address += address.house_number + " ";
    }
    if (address.city) {
        current_address += address.city + " ";
    }
    if (address.zip_code) {
        current_address += address.zip_code;
    }
    document.getElementById("parcelShopsSearchBar").value = current_address;
    document.getElementById("ownAdres").innerHTML = current_address;
}

function sortParcelshops(parcelshops) {
    parcelshops.sort(function (a, b) {
        if (a.distance === b.distance) {
            return 0;
        } else {
            return (a.distance < b.distance) ? -1 : 1;
        }
    });
    return (parcelshops);
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

function ajaxRequest(address) {
    if (address === undefined) {
        address = null;
    }
    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {
            var val = JSON.parse(xhttp.responseText);
            displayMap(val.location);
            setAddress(val.address);
            addParcelshopList(sortParcelshops(val.parcelshops));
            loader.style.display = "none";
            document.getElementById("wrapper").style.display = "block";
            document.getElementById("parcelShopsSearchBarContainer").style.display = "block";
        } else if (this.status === 400) {
            alert("Something went wrong: " + xhttp.responseText);
        }
    };
    var data = new FormData();
    data.append('action', 'parcelshoplocator');
    (address ? data.append('address', address) : false);
    xhttp.open("POST", "../../wp-admin/admin-ajax.php");
    xhttp.send(data);
}

function addParcelshopList(data) {
    data.forEach(function (shops, i) {
        var hours = getHours(shops.opening_hours);
        var logo = getLogo(shops.carrier_name);
        addMarkerToMap(shops.latitude, shops.longitude, logo, i);

        var node = document.createElement("div");
        node.className += "parcelshopItem parcelshopItem" + i;
        node.onclick = parcelshopItemCallbackClosure(shops.latitude, shops.longitude);
        node.innerHTML = "<div id='parcelshopItem_logo'><img id='company_logo' src='" + pluginPath + "/assets/images/parcelshop/" + logo + "'></div>" +
            "<div id='parcelshopItem_text'><div id='company_info'><div id='company_name'><strong>" + capFirst(shops.company_name) + "</strong></div>" +
            "<div id='street_name_and_number'>" + capFirst(shops.address.street_name) + " " + shops.address.house_number + "</div>" +
            "<div id='zip_code_and_city'>" + shops.address.zip_code + " " + shops.address.city + "</div>" +
            "<div id='distance'>" + Math.round(shops.distance * 1000) + "m</div></div></div>" +
            "<div class='opening_hours_list'><br><strong>Openingstijden</strong>" +
            "<div>" + hours + "</div><br><div id='buttonContainer'><button class='parcelshopButton' onclick='chooseParcelshopButton(\"" + capFirst(shops.company_name) + "<br>" + capFirst(shops.address.street_name) +
            " " + shops.address.house_number + "<br>" + shops.address.city + "\", \"" + shops.id + "\", \"" + shops.address.alpha2 + "\")' type='button'>Kies</button></div>";
        window.parent.document.getElementById('parcelshopList').appendChild(node);
    });
}

function closest(el, selector) {
    var matchesFn;

    // find vendor prefix
    ['matches', 'webkitMatchesSelector', 'mozMatchesSelector', 'msMatchesSelector', 'oMatchesSelector'].some(function (fn) {
        if (typeof document.body[fn] === 'function') {
            matchesFn = fn;
            return true;
        }
        return false;
    });

    var parent;

    // traverse parents
    while (el) {
        parent = el.parentElement;
        if (parent && parent[matchesFn](selector)) {
            return parent;
        }
        el = parent;
    }

    return null;
}