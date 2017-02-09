jQuery(document).ready(function($) {
	jQuery('#billing_phone').val('+31');
	jQuery('#billing_phone').keyup(function () { 
		var voorbeeld1 = /[^0-9\+\-]/g;
      	this.value = this.value.replace(/[^0-9\+\-\.]/g, '');
    });
});	