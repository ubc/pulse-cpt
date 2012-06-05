var Pulse_CPT_Form = {
	onReady : function(){
		
		
		jQuery( '.autogrow' ).autogrow();
		jQuery( '.pulse-form-progress' ).addClass('hide');
		jQuery('.pulse-form').submit( Pulse_CPT_Form.submitForm );
		
		jQuery.each( Pulse_CPT_Form_local, function( i, v ) { Pulse_CPT_Form.init( i, v ) }); 
		
		
		jQuery('.pulse-shorten-url').click( function(){ Pulse_CPT_Form.focusInput( '.pulse-form-textarea' ); } );
	
	},
	/**
	 * takes the 
	 */
	init : function( index, meta ) {
		
		
		var parent = jQuery( "#"+meta.id );
		
		if( meta.enable_character_count ) {
			var num_char = (meta.num_char ? meta.num_char: 140 )
			
			var counter_shell =  parent.find( '.pulse-form-counter' );
			parent.find('.pulse-form-textarea').charCount({allowed: num_char, counterElement: counter_shell });
		}
		
		if( meta.enable_tabs ) {
			parent.find( '.pulse-tabs' ).tabs({selected: -1, collapsible: true});
			
			if( meta.enable_tagging ) {
				Pulse_CPT_Form.tag_it( parent, 'tags', Pulse_CPT_Form_global.tags);
			}
			if( meta.enable_co_authoring ) {
				Pulse_CPT_Form.tag_it( parent, 'author', Pulse_CPT_Form_global.authors );
			}
		}
		
		
	},
	
	submitForm : function () {
		
		var form_data = jQuery(this).serialize();
		
		console.log(form_data, 'submit' );
		return false;
	},
	tag_it : function ( parent, el_class, source_autocomplete ) {
	
		var el = jQuery( parent ).find( ".pulse-textarea-"+el_class );
		
		el.tagbox();

		var tags_input = el.parent().find('.input input');
		
		jQuery( parent ).find( '.pulse-tabs-'+el_class ).click( function(){ Pulse_CPT_Form.focusInput( tags_input ); } );
		
		var arg = { 
			source: source_autocomplete,
    		minLength: 2,
    		select: function(e, ui) {
                e.preventDefault();
                jQuery(this).val( ui.item.value );
                jQuery(this).trigger("selectTag");
             }
       }
		
		tags_input.autocomplete( arg );
	}
	,
	focusInput: function(el){
		var element = jQuery(el);
		if( element.is(":visible") ) {
			element.focus();
		}
	}
	
	
	

}

jQuery('document').ready(Pulse_CPT_Form.onReady);