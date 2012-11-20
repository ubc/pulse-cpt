/* js that is required by the pulse */


var Pulse_CPT = {
	onReady: function(){
		
		jQuery('.pulse').click( Pulse_CPT.expand);
	
	},
	
	expand: function(){
		
		jQuery(this).toggleClass('expand');
	}

}
jQuery('document').ready(Pulse_CPT.onReady);