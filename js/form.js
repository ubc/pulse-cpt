var Pulse_CPT_Form = {
	single_pulse_template: false,
	onReady : function(){
		
		
		jQuery( '.autogrow' ).autogrow();
		jQuery( '.pulse-form-progress' ).addClass('hide');
		jQuery('.pulse-form').submit( Pulse_CPT_Form.submitForm );
		
		jQuery.each( Pulse_CPT_Form_local, function( i, v ) { Pulse_CPT_Form.init( i, v ) }); 
				
		Pulse_CPT_Form.single_pulse_template = doT.template( document.getElementById('pulse-cpt-single').text );

		
	},
	/**
	 * takes the rains and run with them
	 */
	init : function( index, meta ) {
		
		var parent = jQuery( "#"+meta.id );
		
		if( meta.enable_character_count ) {
			var num_char = (meta.num_char ? meta.num_char: 140 )
			
			var counter_shell =  parent.find( '.pulse-form-counter' );
			
			counter_shell.data( 'num_char', num_char );
			parent.find('.pulse-form-textarea').charCount({allowed: num_char, counterElement: counter_shell });
		}
		
		if( meta.enable_tabs ) {
			parent.find( '.pulse-tabs' ).tabs( {selected: -1, collapsible: true } );
			
			if( meta.enable_tagging ) {
				Pulse_CPT_Form.tag_it( parent, 'tags', Pulse_CPT_Form_global.tags);
			}
			if( meta.enable_co_authoring ) {
				Pulse_CPT_Form.tag_it( parent, 'author', Pulse_CPT_Form_global.authors );
			}
		}
	},
	
	submitForm : function () {
		var form = jQuery(this);
		var form_data = form.serialize();
		
		var tags = form.find('.pulse-textarea-tags').text();
		if( tags ){
			form_data.replace( '&tags=', '' );
			form_data += '&tags='+tags;
		}
		console.log( form_data );
		// console.log( form_data );
		// return false;
		jQuery( '.pulse-form-progress' ).removeClass( 'hide' );
	
		// todo: once the form is succesfuly submited clear the data entered from the form.
		jQuery.post( Pulse_CPT_Form_global.ajaxUrl , form_data ,  function( response ) {
			jQuery( '.pulse-form-progress' ).addClass('hide');
			if( response == -1){
				jQuery().sticky( 'Your login has expired, please login again' );
			}
				
			if( response.hasOwnProperty('error') ) {
				jQuery().sticky( response.error );
			} else {
			
				// clear the forms
				form.find('textarea,input[type=text]').val('');
				
				// counter goes back to what ever it used to be
				var num_char_wrap = form.find( '.pulse-form-counter' );
				var num_char = num_char_wrap.data( 'num_char' );
				num_char_wrap.text( num_char );
				
				// unselect tabs
				form.find( '.pulse-tabs' ).tabs( { selected: -1 });
				
				var html = Pulse_CPT_Form.single_pulse_template( response );
				// nicly tranition the addition of the new post
				jQuery(html).hide().prependTo('.pulse-list').slideDown("slow");
			
			}
			// jQuery().sticky('new post created!');
			
			
		}, "json" );
		return false;
	},
	tag_it : function ( parent, el_class, source_autocomplete ) {
	
		var el = jQuery( parent ).find( ".pulse-textarea-"+el_class );
		
		el.tagbox({single_input:el_class});

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