var Pulse_CPT_Form = {
	onReady : function(){
		
		jQuery( '.pulse-form-textarea' ).focus( Pulse_CPT_Form.textareaFocus );
		jQuery( '.pulse-form-progress' ).addClass('hide');
		jQuery('.pulse-form').submit( Pulse_CPT_Form.submitForm );
		
		// tabs
		jQuery('.pulse-tabs').tabs({selected: -1, collapsible: true});	
	},
	
	textareaFocus : function (){
		jQuery(this).addClass('full');
	},
	
	submitForm : function () {
		console.log( 'submit' );
		return false;
	}
	,
	
	tabsForm: function() {
		
	
	}
	
	
	

}

jQuery('document').ready(Pulse_CPT_Form.onReady);