
var Pulse_CPT = {
    popover_args: null,
    
    onReady: function() {
        Pulse_CPT.listen();
        
        //jQuery(".pulse-co-authors a").tooltip();
        
        // These are delegated to .pulse-list to attach to dynamic elements too
        jQuery('.pulse-list').on( 'click', '.pulse', Pulse_CPT.expand );
        jQuery('.pulse-list').on( 'click', '.expand-action', Pulse_CPT.expand );
        
        // Prevent collapse if any of these within a pulse are clicked
        jQuery('.pulse-list').on( 'click', '.pulse-replies .pulse, .pulse a, .pulse .postbox', function(e) { e.stopPropagation(); } );
        
        // Reply
        jQuery('.pulse-list').on( 'click', '.reply-action', function( event ) {
            // We want to know the caller
            Pulse_CPT.reply.apply(this);
            event.stopPropagation(); //stop so we dont collapse parent pulse
        } );
        
        jQuery('.pulse-pagination').on( 'click', '.pulse-pagination-btn:not(.disabled)', function( event ) {
            Pulse_CPT.loadMore(this);
        } );
        
        jQuery('.pulse-list-filter.show select').on( 'change', function() {
            Pulse_CPT.filter( jQuery(this).closest('.widget') );
        } );
        
        jQuery('.pulse-list-filter.sort select').on( 'change', function() {
            Pulse_CPT.filter( jQuery(this).closest('.widget') );
        } );
        
        // This code is commented out because it is incomplete. The PHP code that supports it is also incomplete.
        // What follows is code to enable the ability of users to attach comments to their ratings.
        /*
        jQuery('body').append('<div id="popover-wrapper"></div>')
        
        jQuery('.pulse-list').on( 'click', '.rate', function( event ) {
            var element = jQuery(event.target);
            var wrapper = element.closest('.evaluate-wrapper');
            
            if ( ! element.hasClass('selected') ) {
                wrapper.addClass('has-popover');
                Pulse_CPT.popover_args = Evaluate.parseUrl( element.attr('href') );
                wrapper.popover( {
                    trigger: 'manual',
                    html: true,
                    content: '<div class="pulse-popover">Add a comment to your rating.<br /><textarea rows="2" cols="50" maxlength="40"></textarea><br /><button class="submit">Submit</button><button>No Thanks</button></div>',
                    placement: 'left',
                    container: '#popover-wrapper',
                } );
                wrapper.popover('show');
                jQuery('#popover-wrapper textarea').focus();
            } else {
                wrapper.popover('destroy');
                wrapper.removeClass('has-popover');
            }
        } );
        
        jQuery('#popover-wrapper').on( 'click', '.submit', function( event ) {
            Pulse_CPT.popover_args['comment'] = jQuery('#popover-wrapper textarea').val();
            Pulse_CPT.popover_args['vote'] = null;
            
            var data = {
                action: 'evaluate-vote',
                data: Pulse_CPT.popover_args,
            }
            
            //jQuery.post( evaluate_ajax.ajaxurl, data );
        } );
        
        jQuery('#popover-wrapper').on( 'click', 'button', function( event ) {
            var element = jQuery('.pulse-list .evaluate-wrapper.has-popover');
            element.popover('destroy');
            element.removeClass('has-popover');
        } );
        */
    },
    
    listen: function() {
        if ( typeof CTLT_Stream != 'undefined' ) { // Check for stream activity
            CTLT_Stream.on( 'server-push', function ( data ) { // Catch server push
                if ( data.type == 'pulse' ) { // We are interested
                    var new_pulse_data = jQuery.parseJSON(data.data); // Extract pulse data from the event
                    var new_pulse = Pulse_CPT_Form.single_pulse_template(new_pulse_data);
                    var parent_found = false;
                    
                    jQuery.each( jQuery('.pulse'), function( i, val ) { // Loop through and try to match the ids
                        parent_element = jQuery(val)
                        if ( parent_element.data('pulse-id') == new_pulse_data.parent ) { // If the ids match,
                            var reply_count = parent_element.find(' > .pulse-wrap > .pulse-actions .reply-count');
                            reply_count.text( parseInt( reply_count.text() ) + 1 );
                            
                            if ( parent_element.hasClass('expand') ) {
                                // Put the new pulse in it's reply section.
                                jQuery(new_pulse).prependTo( parent_element.find('.pulse-expand-content .pulse-replies') ).hide().slideDown('slow');
                                parent_found = true;
                            } else {
                                parent_element.find('.pulse-reply-counter').addClass('new');
                            }
                            
                            return false; // break out of the loop.
                        } else {
                            return true;
                        }
                    } );
                    
                    // If no appropriate place is found for the new pulse, put it in the main pulse-list
                    if ( ! parent_found && new_pulse_data.content_type == Pulse_CPT_Form_global.content_type ) {
                        Pulse_CPT.addPulse(new_pulse);
                    }
                    
                    var split = Pulse_CPT_Form_global.content_type.split("/");
                    var type = split[0];
                    var value = split[1];
                    switch ( type ) {
                        case 'tag':
                            if ( new_pulse_data.tags != false ) {
                                jQuery.each( new_pulse_data.tags, function( index, tag ) {
                                    if ( tag.name == value ) {
                                        Pulse_CPT.addPulse(new_pulse);
                                        return false; // Break out of the loop.
                                    } else {
                                        return true;
                                    }
                                } );
                            }
                            break;
                        case 'author':
                            if ( new_pulse_data.author.user_login == value ) {
                                Pulse_CPT.addPulse(new_pulse);
                            }
                            break;
                        default:
                            break;
                    }
                    
                    var content_rating_metric = new_pulse_data.content_rating;
                    if ( content_rating_metric != undefined ) {
                        var content_rating = Evaluate.template[content_rating_metric.type](content_rating_metric);
                        jQuery('.pulse-'+new_pulse_data.ID+' .content-rating .evaluate-wrapper').html(content_rating);
                    }
                }
            } );
        }
    },
    
    addPulse: function( new_pulse ) {
        jQuery(new_pulse).prependTo('.pulse-list').hide().slideDown('slow');
        
        if ( jQuery('.pulse-list > .pulse').length > 10 ) {
            jQuery('.pulse-list').children('.pulse').last().slideUp('slow');
            jQuery('.pulse-pagination-btn').removeClass('.disabled');
        }
    },
    
    expand: function() {
        var element = jQuery(this).closest('.pulse');
        element.data('pulse');
        
        element.toggleClass('expand');
        
        if ( element.hasClass('expand') ) {
            element.find('.expand-action').first().text( 'Collapse' );
            Pulse_CPT.expandPulse(element); //ajax call to fetch replies
        } else {
            element.find('.expand-action').first().text( 'Expand' );
            Pulse_CPT_Form.reply(); //reset form location if collapsed
        }
    },
    
    expandPulse: function( element ) {
        // Show loading spinner
        element.find( '.pulse-form-progress' ).first().show();
        jQuery.ajax( {
            url: Pulse_CPT_Form_global.ajaxurl,
            data: {
                action: 'pulse_cpt_replies',
                data: {
                    'parent_id': element.data('pulse-id'),
                    'widget_id': element.closest('.widget').find('.widget-id').val(),
                },
            },
            type: 'post',
            dataType: 'json',
            success: function( data ) {
                // Hide spinner again and show content
                var list = jQuery(element).find('.pulse-replies');
                list.html("");
                Pulse_CPT.parsePulses( list, data );
                element.find( '.pulse-form-progress' ).first().hide();
                element.find('.pulse-reply-counter').first().removeClass( 'new' );
            }
        } );
    },
    
    reply: function() {
        var parent_pulse = jQuery(this).closest('.pulse');
        if ( ! parent_pulse.hasClass('expand') ) { //expand first if not expanded
            Pulse_CPT.expand.apply(this);
        }
        
        //reply form, pass caller element along with parent pulse ID
        Pulse_CPT_Form.reply.apply( this, [ parent_pulse ] );
    },
    
    loadMore: function( element ) {
        element = jQuery(element);
        var widget = element.closest('.widget');
        
        Pulse_CPT.filter( widget, {
            'offset': widget.find('.pulse-list .pulse').length,
        }, false );
    },
    
    filter: function( widget, overrides, clear ) {
        var widget_id = widget.find('.widget-id').val();
        var show = jQuery('.pulse-list-filter.show select').val();
        var sort = jQuery('.pulse-list-filter.sort select').val();
        var page = jQuery('input.pulse-list-page').val();
        var list = widget.find('.pulse-list');
        var user;
        
        clear = ( clear == undefined ? true : clear );
        
        jQuery('.pulse-list-actions .pulse-form-progress').show();
        if ( clear == true ) {
            list.fadeTo( 200, 0.3 );
            list.css( 'pointer-events', 'none' );
        }
        
        var prefix = "user_";
        if ( show.slice(0, prefix.length) == prefix ) {
            user = show.slice(prefix.length);
            show = 'user';
        }
        
        split = sort.split('/')
        sort = split[0]
        if ( split[1] == 'DESC' ) {
            order = 'DESC';
        } else {
            order = 'ASC';
        }
        
        jQuery.ajax( {
            url: Pulse_CPT_Form_global.ajaxurl,
            data: {
                action: 'pulse_cpt_replies',
                data: jQuery.extend( {
                    'parent_id' : Pulse_CPT_Form_global.id,
                    'widget_id' : widget_id,
                    'sort'      : sort,
                    'show'      : show,
                    'user'      : user,
                    'order'     : order,
                    'pagination': true,
                    'filters': {
                        'author_id': widget.find('.author-id').val(),
                        'cat_id': widget.find('.cat-id').val(),
                        'tag_id': widget.find('.tag-id').val(),
                        'date': {
                            'year': widget.find('.date-year').val(),
                            'monthnum': widget.find('.date-monthnum').val(),
                            'day': widget.find('.date-day').val(),
                        },
                    },
                }, overrides ),
            },
            type: 'post',
            dataType: 'json',
            success: function( data ) {
                if ( clear == true ) {
                    list.html("");
                }
                
                Pulse_CPT.parsePulses( list, data, ! clear );
                
                if ( clear == true ) {
                    list.fadeTo( 200, 1 );
                    list.css( 'pointer-events', 'auto' );
                }
                
                jQuery('.pulse-list-actions .pulse-form-progress').hide();
                
                if ( data['pagination'] ) {
                    jQuery('.pulse-pagination .pulse-pagination-btn').removeClass('disabled');
                } else {
                    jQuery('.pulse-pagination .pulse-pagination-btn').addClass('disabled');
                }
            }
        } );
    },
    
    parsePulses: function( holder, data, slide ) {
        jQuery.each( data['pulses'], function( index, pulse_data ) {
            var new_pulse = Pulse_CPT_Form.single_pulse_template( pulse_data );
            new_pulse = jQuery(new_pulse).appendTo(holder);
            
            if ( slide == true ) {
                new_pulse.hide().slideDown();
            }
            
            if ( pulse_data.content_rating != undefined ) {
                var content_rating = Evaluate.template[pulse_data.content_rating.type](pulse_data.content_rating);
                jQuery('.pulse-'+pulse_data.ID+' .content-rating .evaluate-wrapper').html(content_rating);
            }
        } );
        
        holder.prepend( data['prepend'] );
        holder.append( data['append'] );
    }
}

jQuery(document).ready(Pulse_CPT.onReady);
