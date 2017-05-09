jQuery(document).ready(function($) {

	function localize_address_fields (address_type) {

		$('#'+address_type+'_street_name_field').show();
		$('#'+address_type+'_house_number_field').show();
		$('#'+address_type+'_house_number_suffix_field').show();
		$('#'+address_type+'_address_1_field').hide();
		$('#'+address_type+'_address_2_field').hide();
		
	}

	localize_address_fields('billing');
	localize_address_fields('shipping');

});