// Get the modal
var parcelshopShippingMethodElem = document.getElementById('shipping_method_0_wuunder_parcelshop');

var shippingAddress;
var parcelshopAddress;
var rawParcelshopAddress;

var baseUrl;
var baseUrlApi;
var availableCarrierList;

function initParcelshopLocator(url, apiUrl, carrierList) {
    baseUrl = url;
    baseUrlApi = apiUrl;
    availableCarrierList = carrierList;

    if (parcelshopShippingMethodElem) {
        parcelshopShippingMethodElem.onchange = _onShippingMethodChange;
        _onShippingMethodChange();
    }
}

function _onShippingMethodChange() {
    if (parcelshopShippingMethodElem.checked) {
        var container = document.createElement('tr');
        container.className += "chooseParcelshop";
        container.innerHTML = '<td></td><td><div id="parcelshopsSelectedContainer" onclick="_showParcelshopLocator()"><a href="#/" id="selectParcelshop">Klik hier om een parcelshop te kiezen</a></div></td>';
        // window.parent.document.getElementsByClassName('shipping')[0].appendChild(container);
        window.parent.document.getElementsByClassName('woocommerce-shipping-totals')[0].parentNode.insertBefore(container, window.parent.document.getElementsByClassName('woocommerce-shipping-totals shipping')[0].nextSibling);

        _printParcelshopAddress();
    } else {
        var containerElems = window.parent.document.getElementsByClassName('chooseParcelshop');
        if (containerElems.length) {
            containerElems[0].remove();
        }
    }
}

function _printParcelshopAddress() {
    if (parcelshopAddress) {
        if (window.parent.document.getElementsByClassName("parcelshopInfo").length) {
            window.parent.document.getElementsByClassName("parcelshopInfo")[0].remove();
        }
        var currentParcelshop = document.createElement('div');
        currentParcelshop.className += 'parcelshopInfo';
        currentParcelshop.innerHTML = '<br/><strong>Ophalen in parcelshop:</strong><br/>' + parcelshopAddress;
        window.parent.document.getElementById('parcelshopsSelectedContainer').appendChild(currentParcelshop);
        window.parent.document.getElementById('parcelshop_country').value = rawParcelshopAddress.address.alpha2;
    }
}


function _showParcelshopLocator() {
    var address = "";

    jQuery.post(baseUrl + "admin-ajax.php", {
        action: 'wuunder_parcelshoplocator_get_address',
        address: address
    }, function (data) {
        shippingAddress = data;
        _openIframe();
    });
}


function _openIframe() {
    var iframeUrl = baseUrlApi + 'parcelshop_locator/iframe/?lang=nl&availableCarriers=' + availableCarrierList + '&address=' + encodeURI(shippingAddress);

    var iframeContainer = document.createElement('div');
    iframeContainer.className = "parcelshopPickerIframeContainer";
    var iframeDiv = document.createElement('div');
    iframeDiv.innerHTML = '<iframe src="' + iframeUrl + '" width="100%" height="100%">';
    iframeDiv.className = "parcelshopPickerIframe";
    iframeDiv.style.cssText = 'position: fixed; top: 0; left: 0; bottom: 0; right: 0; z-index: 2147483647';
    iframeContainer.appendChild(iframeDiv);
    window.parent.document.getElementsByClassName("chooseParcelshop")[0].appendChild(iframeContainer);

    function removeServicePointPicker() {
        removeElement(iframeContainer);
    }

    function onServicePointSelected(messageData) {
        window.parent.document.getElementById('parcelshop_id').value = messageData.parcelshopId;
        _loadSelectedParcelshopAddress(messageData.parcelshopId);
        removeServicePointPicker();
    }

    function onServicePointClose() {
        removeServicePointPicker();
    }

    function onWindowMessage(event) {
        var origin = event.origin,
            messageData = event.data;
        var messageHandlers = {
            'servicePointPickerSelected': onServicePointSelected,
            'servicePointPickerClose': onServicePointClose
        };
        if (!(messageData.type in messageHandlers)) {
            alert('Invalid event type');
            return;
        }
        var messageFn = messageHandlers[messageData.type];
        messageFn(messageData);
    }

    window.addEventListener('message', onWindowMessage, false);
}

function _loadSelectedParcelshopAddress(id) {
    jQuery.post(baseUrl + "admin-ajax.php", {
        action: 'wuunder_parcelshoplocator_get_parcelshop_address',
        parcelshop_id: id
    }, function (data) {
        data = JSON.parse(data);
        rawParcelshopAddress = data;
        var parcelshopInfoHtml = _capFirst(data.company_name) + "<br>" + _capFirst(data.address.street_name) +
            " " + data.address.house_number + "<br>" + data.address.city;
        parcelshopInfoHtml = parcelshopInfoHtml.replace(/"/g, '\\"').replace(/'/g, "\\'");
        parcelshopAddress = parcelshopInfoHtml;
        _printParcelshopAddress();
    });
}

// Capitalizes first letter of every new word.
function _capFirst(str) {
    if (str === undefined)
        return "";
    return str.replace(/\w\S*/g, function (txt) {
        return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
    });
}

function removeElement(element) {
    if (element.remove !== undefined) {
        element.remove();
    } else {
        element && element.parentNode && element.parentNode.removeChild(element);
    }
}