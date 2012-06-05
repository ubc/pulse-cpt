var Pulse_CPT_Form = {
	onReady : function(){
		
		jQuery( '.pulse-form-textarea' ).charCount(); // .focus( Pulse_CPT_Form.textareaFocus )
		jQuery( '.autogrow' ).autogrow();
		jQuery( '.pulse-form-progress' ).addClass('hide');
		jQuery('.pulse-shorten-url').click( function(){ Pulse_CPT_Form.focusInput( '.pulse-form-textarea' ); } );
		jQuery('.pulse-form').submit( Pulse_CPT_Form.submitForm );
		
		// tabs
		jQuery('.pulse-tabs').tabs({selected: -1, collapsible: true});
		
		Pulse_CPT_Form.tag_it('tags', Pulse_CPT_Form_local.tags);
		
		Pulse_CPT_Form.tag_it('author', Pulse_CPT_Form_local.authors );
		
		
		// tags 
		// .tagBox();
	},
	
	textareaFocus : function (){
		jQuery(this).addClass('full');
	},
	
	submitForm : function () {
		
		var form_data = jQuery(this).serialize();
		
		console.log(form_data, 'submit' );
		return false;
	},
	tag_it : function ( el_class, source_autocomplete ) {
		var el = jQuery(".pulse-textarea-"+el_class);
		
		jQuery(".pulse-textarea-"+el_class).tagbox();
	
		var tags_input = el.parent().find('.input input');
		jQuery('.pulse-tabs-'+el_class).click( function(){ Pulse_CPT_Form.focusInput( tags_input ); } );
						
		tags_input.autocomplete({
    		source: source_autocomplete,
    		minLength: 2,
    		 select: function(e, ui) {
                e.preventDefault();
                
                jQuery(this).val( ui.item.value );
                jQuery(this).trigger("selectTag");
               
                 
             }
       		});
	}
	,
	enableTags: function(){
		
		
	}
	,
	tabsForm: function() {
		
	
	},
	focusInput: function(el){
		var element = jQuery(el);
		if( element.is(":visible") ) {
			element.focus();
		}
	}
	
	
	

}

jQuery('document').ready(Pulse_CPT_Form.onReady);