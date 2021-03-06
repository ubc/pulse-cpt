
var Pulse_CPT_Form = {
	single_pulse_template: false,
	msg_t1: false,
	msg_t2: false,
	msg_id: 'pulse_msg_wrap',
	//track location for replies
	original_location: {
		type: null,
		ID: null,
	},
	
	onReady: function() {
		jQuery( '.autogrow' ).autogrow();
		jQuery('.pulse-form').submit( Pulse_CPT_Form.submitForm );
		
		if ( typeof Pulse_CPT_Form_local != 'undefined' ) {
			jQuery.each( Pulse_CPT_Form_local, function( index, value ) {
				Pulse_CPT_Form.init( index, value, jQuery('.pulse-form') );
			} ); 
		}
		
		Pulse_CPT_Form.single_pulse_template = doT.template( document.getElementById('pulse-cpt-single').text );
		
		jQuery('.pulse-shorten-url a').live( 'click', Pulse_CPT_Form.shorten_url_action );
		
		//assign location type and ids
		var location = { type: null, ID: null };
		location.type = jQuery('.postbox input[name="location[type]"]').val();
		location.ID = jQuery('.postbox input[name="location[ID]"]').val();
		Pulse_CPT_Form.original_location.type = ( typeof location.type == 'undefined' ) ? null : location.type;
		Pulse_CPT_Form.original_location.ID = ( typeof location.ID == 'undefined' ) ? null : location.ID;
		
		//reply form reset from placeholder hook
		jQuery('.postbox-placeholder').on( 'click', function(e) { Pulse_CPT_Form.reply(null); } );
	},
	
	/**
	 * takes the rains and run with them
	 */
	init: function( index, meta, parent ) {
		if ( meta.enable_character_count ) {
			var num_char = ( meta.num_char ? meta.num_char : 140 )
			var counter_shell = parent.find( '.pulse-form-counter' );
			
			counter_shell.data( 'num_char', num_char );
			parent.find('.pulse-form-input textarea').charCount( {
				allowed: num_char,
				counterElement: counter_shell,
			} );
			
			parent.find('.pulse-form-input textarea').keypress( function() {
				
			} );
		}
		
		parent.find( '.pulse-tabs' ).tabs( {
			selected: -1,
			collapsible: true,
		} );
		
		if ( meta.tabs != null ) {
			if ( meta.tabs.tagging ) {
				Pulse_CPT_Form.tag_it( parent, 'tags', Pulse_CPT_Form_global.tags);
			}
			
			if ( meta.tabs.co_authoring ) {
				Pulse_CPT_Form.tag_it( parent, 'author', Pulse_CPT_Form_global.authors );
			}
		}
	},
	
	/**
	 * Move form to desired pulse and change location data / reset location
	 */
	reply: function( parent_pulse ) {
		var orig_loc_null = Pulse_CPT_Form.original_location.type == null || Pulse_CPT_Form.original_location.ID == null;
		
		// If ID == null then reset form to original location
		if ( parent_pulse == null ) {
			if ( orig_loc_null ) {
				jQuery('input[name="location[type]"]').remove(); 
				jQuery('input[name="location[ID]"]').remove(); 
			} else {
				jQuery('input[name="location[type]"]').val(Pulse_CPT_Form.original_location.type);
				jQuery('input[name="location[ID]"]').val(Pulse_CPT_Form.original_location.ID);
			}
			
			// Hide placeholder and move the form back to its original location
			jQuery('.postbox-placeholder').hide();
			jQuery('.postbox').insertAfter( jQuery('.postbox-placeholder') );
			jQuery('.postbox textarea').focus();
			return;
		}
		
		if ( orig_loc_null ) {
			// Add new elements to track parent on the fly
			jQuery('<input>').attr( {
				name:  'location[type]',
				value: 'singular',
				type:  'hidden',
			} ).appendTo('.pulse-form');
			
			jQuery('<input>').attr( {
				name:  'location[ID]',
				value: parent_pulse.data('pulse-id'),
				type:  'hidden',
			} ).appendTo('.pulse-form');
		} else {
			// Change values of existing elements
			jQuery('input[name="location[type]"]').val('singular'); 
			jQuery('input[name="location[ID]"]').val( parent_pulse.data('pulse-id') ); 
		}
		
		// Show placeholder and insert the form inside the pivot element in the parent pulse
		jQuery('.postbox-placeholder').show();
		jQuery('.postbox').insertBefore( parent_pulse.find('.pulse-pivot:first') );
		jQuery('.pulse-form-input textarea').focus();
	},
	
	submitForm: function () {
		var form = jQuery(this);
		var form_data = form.serialize();
		
		var tags = form.find('.pulse-textarea-tags').text();
		if ( tags ) {
			form_data.replace( '&tags=', '' );
			form_data += '&tags='+tags;
		}
		
		jQuery( '.postbox .pulse-form-progress' ).show();
		
		jQuery.post( Pulse_CPT_Form_global.ajaxurl, form_data, function( response ) {
			
			jQuery( '.postbox .pulse-form-progress' ).hide();
			
			if ( response == -1 ) {
				Pulse_CPT_Form.display_msg( 'Your login has expired, please login again' );
			}
			
			if ( response.hasOwnProperty('error') ) {
				Pulse_CPT_Form.display_msg( response.error );
			} else {
				// counter goes back to what ever it used to be
				var num_char_wrap = form.find( '.pulse-form-counter' );
				var num_char = num_char_wrap.data( 'num_char' );
				num_char_wrap.text( num_char );
				
				// unselect tabs
				form.find( '.pulse-tabs' ).tabs( { selected: -1 });
				
				var html = Pulse_CPT_Form.single_pulse_template( response );
				var parent_pulse = jQuery('.postbox').closest('.pulse').find('.pulse-replies:first');
				
				if ( parent_pulse.length <= 0 ) {
					parent_pulse = jQuery('.pulse-list');
				}
				
				if ( typeof CTLT_Stream == 'undefined' ) {
					jQuery(html).prependTo(parent_pulse);
				}
			}
		}, "json" );
		
		// Clear the forms
		form.each( function() { this.reset(); } );
		jQuery('.tagbox').trigger('clear'); // A custom event defined in tagbox.js
		
		return false;
	},
	
	tag_it: function ( parent, el_class, source_autocomplete ) {
		var element = jQuery( parent ).find( ".pulse-textarea-"+el_class );
		var tr = element.tagBox( {
			single_input: el_class,
			update: function( tags ) {
				Pulse_CPT_Form.display_nicely( tags, this.options, parent );
			},
		} );
		
		var tags_input = element.parent().find('.input input');
		jQuery( parent ).find( '.pulse-tabs-'+el_class ).click( function() { Pulse_CPT_Form.focusInput( tags_input ); } );
		
		tags_input.autocomplete( { 
			source: source_autocomplete,
			minLength: 2,
			html: 'html',
			select: function( e, ui ) {
				e.preventDefault();
				jQuery(this).val( ui.item.value );
				jQuery(this).trigger("selectTag");
			},
			messages: {
				noResults: '',
				results: function() { }
			},
	    } );
	},
	
	focusInput: function(el){
		var element = jQuery(el);
		
		if ( element.is(":visible") ) {
			element.focus();
		}
	},
	
	display_nicely: function( tags, options, parent ) {
	    var i = 0;
	    var html_tags = new Array();
		var tags_array = tags.split(',');
		
		switch ( options.single_input ) {
		case "tags":
			if ( tags ) {
				while ( tags_array[i] ) {
					html_tags.push('<a href="?tag='+tags_array[i]+'">'+tags_array[i]+'</a>');
					i++;		   	  	
				}
				
				parent.find( '.pulse-tags-shell').html(html_tags.join(', '));
			} else {
				parent.find( '.pulse-tags-shell').html('');
			}
			break;
		case "author":
			if ( tags ) {
				while ( tags_array[i] ) {
					jQuery.ajax( Pulse_CPT_Form_global.ajaxurl, {
						async: false,
						dataType: 'html',
						type: 'post',
						data: {
							action: 'pulse_get_user_image',
							user: tags_array[i],
							size: 12,
						},
						success: function( response ) {
							html_tags.push(response+' <a href="?author='+tags_array[i]+'">'+tags_array[i]+'</a>');
						},
					} );
					i++;
				}
				
				parent.find( '.pulse-author-shell').html(html_tags.join(' '));
			} else {
				parent.find('.pulse-author-shell').html('');
			}
			break;
		}
	},
	
	display_msg: function ( msg ) {
		alert_box = jQuery( "#"+Pulse_CPT_Form.msg_id );
	    if ( alert_box.length > 0 ) {
	    	alert_box.html(msg);
	    } else {
	    	jQuery( '.postbox' ).prepend( '<div class="pulse-alert" id="'+Pulse_CPT_Form.msg_id+'">'+msg+'</div>' );
	    }
		
		// Watch for mouse & keyboard in .7s
		Pulse_CPT_Form.msg_t1 = setTimeout( "Pulse_CPT_Form.bind_msg_events()", 500 );
		// Remove message after 5s
		Pulse_CPT_Form.msg_t2 = setTimeout( "Pulse_CPT_Form.remove_msg()", 5000 );
	},

	bind_msg_events: function() {
		// Remove message if mouse is moved or key is pressed
		jQuery(window)
			.mousemove(Pulse_CPT_Form.remove_msg)
			.click(Pulse_CPT_Form.remove_msg)
			.keypress(Pulse_CPT_Form.remove_msg);
	},

	remove_msg: function() {
		// Unbind mouse & keyboard
		jQuery(window)
			.unbind('mousemove', Pulse_CPT_Form.remove_msg)
			.unbind('click', Pulse_CPT_Form.remove_msg)
			.unbind('keypress', Pulse_CPT_Form.remove_msg);
		
		// If message is fully transparent, fade it out
		jQuery('#'+Pulse_CPT_Form.msg_id).animate( { opacity: 0 }, 500, function() { jQuery(this).remove() } );
	},
	
	shorten_url_action: function(e) {
		e.preventDefault();
		var element = jQuery(this);
		var id = element.parents( '.widget_pulse_cpt' ).attr('id').substring(10);
		
		if ( Pulse_CPT_Form_local[id].enable_url_shortener ) {
			var textarea = element.closest('form').find('.pulse-form-input textarea');
			var value = textarea.val();
			
			if ( value != undefined ) {
				var words = value.split(" ");
				var num_of_words = words.length;
				
				for ( var i = 0; i < num_of_words; i++ ) {
					if ( words[i].substring(4, 0)  == "http" && 
						words[i].substring(13, 0) != "http://bit.ly" && // ignore bitly as well 
						words[i].substring(11, 0) != "http://j.mp" &&
						words[i].substring(13, 0) != "http://goo.gl" &&
						words[i].substring(14, 0) != "http://yhoo.it" ) {
						
						Pulse_CPT_Form.shorten_url( words[i], Pulse_CPT_Form_local[id].bitly_user, Pulse_CPT_Form_local[id].bitly_api_key, function( short_url, long_url ) {
							// How do I replace the string with the proper 
							var i = 0;
							for ( i = 0; i < num_of_words; i++ ) {
								if ( words[i] == long_url ) {
									words[i] = short_url;
								}
							}
							
							textarea.val( words.join(" ") ); // replace the right stuff back
						} ); // end of call back
					} // end of if statment 
				} // end of for loop
			}
		}
	},
	
	shortened_urls: new Array, 
	
	shorten_url: function( url, api_login, api_key, callback ) {
		// Build the URL to query
		var api_call = "http://api.bitly.com/v3/shorten?longUrl="+encodeURIComponent(url)+"&login="+api_login+"&apiKey="+api_key+"&callback=?";
		
		// See if we've shortened this url already
		var cached_result = Pulse_CPT_Form.shortened_urls[url];
		
		if ( cached_result !== undefined ) {
			// The timeout is to eliminate race conditions arising 
			// from the assumption that the callback will be
			// called after an ajax call
			window.setTimeout(function() {
				callback( cached_result, url );
			}, 1 );
		} else {
			// Utilize the bit.ly API
			jQuery.getJSON( api_call, function(result) {
				if ( "OK" == result.status_txt ) {
					Pulse_CPT_Form.shortened_urls[url] = result.data.url;
					callback( result.data.url, url);
				} else {
					alert( "Error with the URL Shortner:<br /> "+result.data.errorMessage);
				}
			} );
		}
	}
}

var Pulse_CPT_url_shortener_cache = {}

jQuery('document').ready(Pulse_CPT_Form.onReady);