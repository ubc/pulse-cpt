jQuery(document).ready(function() {
	var j = jQuery.noConflict();
	var breadcrumb = j('#pulse_breadcrumb');
	
	//change function for pulse breadcrumb checkbox
	breadcrumb.change(function() {
		var dropdown = j('#pulse_breadcrumb_length').parents('tr');
		if (j(this).is(':checked')) {
			dropdown.show();
		} else {
			dropdown.hide();
		}
	});
	
	//init hiding/showing
	breadcrumb.trigger('change');

});