
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
		//jQuery( '.pulse-form-progress' ).addClass('hide');
		jQuery('.pulse-form').submit( Pulse_CPT_Form.submitForm );
		
		if ( typeof Pulse_CPT_Form_local != 'undefined' ) {
			jQuery.each( Pulse_CPT_Form_local, function( i, v ) { Pulse_CPT_Form.init( i, v, jQuery('.pulse-form') ) }); 
		}
		
		Pulse_CPT_Form.single_pulse_template = doT.template( document.getElementById('pulse-cpt-single').text );
		
		jQuery('.pulse-shorten-url').live('click', Pulse_CPT_Form.shorten_url_action );

		//assign location type and ids
		var location = { type: null, ID: null };
		location.type = jQuery('.postbox input[name="location[type]"]').val();
		location.ID = jQuery('.postbox input[name="location[ID]"]').val();
		Pulse_CPT_Form.original_location.type = (typeof location.type == 'undefined') ? null : location.type;
		Pulse_CPT_Form.original_location.ID = (typeof location.ID == 'undefined') ? null : location.ID;
		
		//reply form reset from placeholder hook
		jQuery('.postbox-placeholder').on('click', function(e) { Pulse_CPT_Form.reply(null); });
	},
	
	/**
	 * takes the rains and run with them
	 */
	init: function( index, meta, parent ) {
		if ( meta.enable_character_count ) {
			var num_char = (meta.num_char ? meta.num_char: 140 )
			
			var counter_shell = parent.find( '.pulse-form-counter' );
			
			counter_shell.data( 'num_char', num_char );
			parent.find('.pulse-form-textarea').charCount( {
				allowed: num_char,
				counterElement: counter_shell,
			} );
		}
		
		if ( meta.enable_tabs ) {
			parent.find( '.pulse-tabs' ).tabs( {
				selected: -1,
				collapsible: true,
			} );
			
			if ( meta.enable_tagging ) {
				Pulse_CPT_Form.tag_it( parent, 'tags', Pulse_CPT_Form_global.tags);
			}
			
			if ( meta.enable_co_authoring ) {
				Pulse_CPT_Form.tag_it( parent, 'author', Pulse_CPT_Form_global.authors );
				console.debug(Pulse_CPT_Form_global.authors)
			}
		}
	},
	
	/*
	 * move form to desired pulse and change location data / reset location
	 */
	reply: function(parent_pulse) {
		var orig_loc_null = Pulse_CPT_Form.original_location.type == null || Pulse_CPT_Form.original_location.ID == null;
		//if ID == null then reset form to original location
		if ( parent_pulse == null ) {
			if ( orig_loc_null ) {
				jQuery('input[name="location[type]"]').remove(); 
				jQuery('input[name="location[ID]"]').remove(); 
			} else {
				jQuery('input[name="location[type]"]').val(Pulse_CPT_Form.original_location.type);
				jQuery('input[name="location[ID]"]').val(Pulse_CPT_Form.original_location.ID);
			}
			//hide placeholder and move the form back to its original location
			jQuery('.postbox-placeholder').hide();
			jQuery('.postbox').insertAfter(jQuery('.postbox-placeholder'));
			jQuery('.pulse-form-textarea').focus();
			return;
		}
		if ( orig_loc_null ) {
			//add new elements to track parent on the fly
			jQuery('<input>').attr( {
				name: 'location[type]',
				value: 'singular',
				type: 'hidden'
			} ).appendTo('.pulse-form');
			
			jQuery('<input>').attr( {
				name: 'location[ID]',
				value: parent_pulse.data('pulse-id'),
				type: 'hidden'
			} ).appendTo('.pulse-form');
		} else {
			//change values of existing elements
			jQuery('input[name="location[type]"]').val('singular'); 
			jQuery('input[name="location[ID]"]').val(ID); 
		}
		
		//show placeholder and insert the form inside the pivot element in the parent pulse
		jQuery('.postbox-placeholder').show();
		jQuery('.postbox').insertBefore(parent_pulse.find('.pulse-pivot:first'));
		jQuery('.pulse-form-textarea').focus();
	},
	
	submitForm : function () {
		var form = jQuery(this);
		var form_data = form.serialize();
		
		var tags = form.find('.pulse-textarea-tags').text();
		if ( tags ) {
			form_data.replace( '&tags=', '' );
			form_data += '&tags='+tags;
		}
		
		// return false;
		jQuery( '.postbox .pulse-form-progress' ).removeClass( 'hide' );
	
		// todo: once the form is succesfuly submited clear the data entered from the form.
		jQuery.post( Pulse_CPT_Form_global.ajaxUrl, form_data, function( response ) {
			// we need to remove tags and authors
			jQuery( '.postbox .pulse-form-progress' ).addClass('hide');
			if ( response == -1 ) {
				Pulse_CPT_Form.display_msg( 'Your login has expired, please login again' );
			}
				
			if ( response.hasOwnProperty('error') ) {
				Pulse_CPT_Form.display_msg( response.error );
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
				var parent_pulse = jQuery('.postbox').closest('.pulse').find('.pulse-replies:first');
				if (parent_pulse.length > 0) {
					
				} else {
					parent_pulse = jQuery('.pulse-list');
				}
			}
		}, "json" );
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
		
		var arg = { 
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
	    }
        
		tags_input.autocomplete( arg );
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
		
		switch (options.single_input) {
		case "tags":
			if ( tags ){
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
					jQuery.ajax( Pulse_CPT_Form_global.ajaxUrl, {
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
					//html_tags.push('<a href="?author='+tags_array[i]+'">'+tags_array[i]+'</a>');
					i++;		   	  	
				}
				
				parent.find( '.pulse-author-shell').html(html_tags.join(' '));
				
				/*
				switch( tags_array.length ) {
				case 1:
					parent.find('.pulse-author-shell').html('posting with ' +tags_array[0]);
					break;
				case 2:
					parent.find('.pulse-author-shell').html('posting with ' +tags_array[0]+' and '+tags_array[1] );
					break;
				default:
					var last = tags_array.pop();
					parent.find('.pulse-author-shell').html('posting with ' +tags_array.join(', ') + ' and '+last );
					break;
				}
				*/
			} else {
				parent.find('.pulse-author-shell').html('');
			}
			
			break;
		}
		
	},
	
	display_msg: function ( msg ) {
	    if ( jQuery( "#"+Pulse_CPT_Form.msg_id ) ) {
	    	jQuery( "#"+Pulse_CPT_Form.msg_id ).html(msg);
	    } else {
	    	jQuery( 'body' ).append( '<div class="pulse-alert" id="'+Pulse_CPT_Form.msg_id+'">'+msg+'</div>' );
	    }
		
		// Watch for mouse & keyboard in .7s
		Pulse_CPT_Form.msg_t1 = setTimeout("Pulse_CPT_Form.bind_msg_events()", 700);
		// Remove message after 5s
		Pulse_CPT_Form.msg_t2 = setTimeout("Pulse_CPT_Form.remove_msg()", 5000);
	},

	bind_msg_events: function() {
	// Remove message if mouse is moved or key is pressed
		jQuery(window)
			.mousemove(Pulse_CPT_Form.remove_msg)
			.click(Pulse_CPT_Form.remove_msg)
			.keypress(Pulse_CPT_Form.remove_msg)
	},

	remove_msg: function() {
		// Unbind mouse & keyboard
		jQuery(window)
			.unbind('mousemove', Pulse_CPT_Form.remove_msg)
			.unbind('click', Pulse_CPT_Form.remove_msg)
			.unbind('keypress', Pulse_CPT_Form.remove_msg)
		
		// If message is fully transparent, fade it out
		jQuery('#'+Pulse_CPT_Form.msg_id).animate({ opacity: 0 }, 1000, function() { jQuery(this).hide() })
	},
	
	shorten_url_action: function(e){
		e.preventDefault();
		var el = jQuery(this);
		var parent = el.parents( '.widget_pulse_cpt' );
		var id = parent.attr('id').substring(10);
		
		// get the c
		if( !Pulse_CPT_Form_local[id].enable_url_shortener ){ return ; }
		
		var textarea = el.siblings('textarea');
		var words = textarea.val().split(" ");
		var i=0;
		var num_of_words =  words.length;
		// var new_words = new Array();
		
		if ( words[0] ) {
			for ( i = 0; i < num_of_words; i++ ) {
				if ( words[i].substring(4,0)  == "http" && 
				    words[i].substring(13,0) != "http://bit.ly" && // ignore bitly as well 
				    words[i].substring(11,0) != "http://j.mp" && // ignore bitly as well 
				    words[i].substring(13,0) != "http://goo.gl" &&
				    words[i].substring(14,0) != "http://yhoo.it") {
			    	
			    	Pulse_CPT_Form.shorten_url( words[i], Pulse_CPT_Form_local[id].bitly_user, Pulse_CPT_Form_local[id].bitly_api_key, function( short_url, long_url ) {
						// how do I replace the string with the proper 
						var i = 0;
						for ( i = 0; i < num_of_words; i++ ) {
							if ( words[i] == long_url ){
								words[i] = short_url;
							}
						}
						
						textarea.val( words.join(" ") ); // replace the right stuff back
			    	}); // end of call back
			    } // end of if statment 
			} // end of for loop
		} // end of if
	},
	
	shortened_urls: new Array, 
	
	shorten_url: function( url, api_login, api_key, callback ) {
		// Build the URL to query
		var api_call =  "http://api.bitly.com/v3/shorten?longUrl="+encodeURIComponent(url)+"&login="+api_login+"&apiKey="+api_key+"&callback=?";
		
		// See if we've shortened this url already
		var cached_result = Pulse_CPT_Form.shortened_urls[url];
		
		if ( cached_result !== undefined ) {
			// the timeout is to eliminate race conditions arising 
			// from the assumption that the callback will be
			// called after an ajax call
			window.setTimeout(function() {
				callback( cached_result, url );
			}, 1 );
		} else {
			// Utilize the bit.ly API
			jQuery.getJSON(api_call, function(result) {
				if ( "OK" == result.status_txt ) {
					Pulse_CPT_Form.shortened_urls[ url ] = result.data.url;
					callback( result.data.url, url);
				} else {
					alert( "Error with the URL Shortner:<br /> "+result.data.errorMessage);
				}
			}); // end of 
		}
	}
}

var Pulse_CPT_url_shortener_cache = {}
jQuery('document').ready(Pulse_CPT_Form.onReady);

