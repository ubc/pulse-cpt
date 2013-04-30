var Pulse_CPT = {
    popover_args: null,
    
    onReady: function() {
        Pulse_CPT.listen();
        
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
        
        jQuery('.pulse-list-filter.show select').on( 'change', function() {
            var widget = jQuery(this).closest('.widget');
            var author_id = 
            Pulse_CPT.filter( widget, {
                filters: {
                    author_id: widget.find('.author-id').val(),
                    cat_id: widget.find('.cat-id').val(),
                    tag_id: widget.find('.tag-id').val(),
                    date: {
                        year: widget.find('.date-year').val(),
                        monthnum: widget.find('.date-monthnum').val(),
                        day: widget.find('.date-day').val(),
                    },
                },
            } );
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
                    console.debug(new_pulse_data);
                    
                    var all_visible_parents = jQuery('.pulse.expand');
                    var new_pulse = Pulse_CPT_Form.single_pulse_template(new_pulse_data);
                    var parent_found = false;
                    
                    jQuery.each( all_visible_parents, function( i, val ) { // Loop through and try to match the ids
                        parent_element = jQuery(val)
                        if ( parent_element.data('pulse-id') == new_pulse_data.parent ) { // If the ids match,
                            // Put the new pulse in it's reply section.
                            jQuery(new_pulse).prependTo( parent_element.find('.pulse-expand-content .pulse-replies') ).hide().slideDown('slow');
                            
                            var reply_count = parent_element.find(' > .pulse-wrap > .pulse-actions .reply-count');
                            reply_count.text( parseInt( reply_count.text() ) + 1 );
                            
                            parent_found = true;
                            return false; // break out of the loop.
                        } else {
                            return true;
                        }
                    } );
                    
                    // If no appropriate place is found for the new pulse, put it in the main pulse-list
                    if ( ! parent_found && new_pulse_data.content_type == Pulse_CPT_Form_global.content_type ) {
                        jQuery(new_pulse).prependTo('.pulse-list').hide().slideDown('slow');
                    }
                    
                    var split = Pulse_CPT_Form_global.content_type.split("/");
                    var type = split[0];
                    var value = split[1];
                    switch ( type ) {
                        case 'tag':
                            if ( new_pulse_data.tags != false ) {
                                jQuery.each( new_pulse_data.tags, function( index, tag ) {
                                    if ( tag.name == value ) {
                                        jQuery(new_pulse).prependTo('.pulse-list').hide().slideDown('slow');
                                        return false; // Break out of the loop.
                                    } else {
                                        return true;
                                    }
                                } );
                            }
                            break;
                        case 'author':
                            if ( new_pulse_data.author.user_login == value ) {
                                jQuery(new_pulse).prependTo('.pulse-list').hide().slideDown('slow');
                            }
                            break;
                        default:
                            break;
                    }
                }
            } );
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
    
    goToPage: function( id, element ) {
        if ( element == null ) {
            element = jQuery(this);
        } else {
            element = jQuery(element);
        }
        
        var list = element.closest('ul');
        switch ( id ) {
            case 'prev':
                page_id = parseInt(jQuery('input.pulse-list-page').val()) - 1;
                break;
            case 'next':
                page_id = parseInt(jQuery('input.pulse-list-page').val()) + 1;
                break;
            case 'first':
                page_id = 1;
                break;
            case 'last':
                list.children('.pulse-page-link').eq(-1).children().click();
                break;
            case 'select':
                page_id = prompt("What page do you want to go to?");
                max = parseInt( list.children('.pulse-page-link').eq(-1).children().text() );
                if ( page_id > max ) {
                    page_id = max;
                }
                break;
            default:
                page_id = id;
                break;
        }
        
        Pulse_CPT.filter( element.closest('.widget'), { 'page': page_id } );
    },
    
    filter: function( widget, overrides ) {
        var widget_id = widget.find('.widget-id').val();
        var show = jQuery('.pulse-list-filter.show select').val();
        var sort = jQuery('.pulse-list-filter.sort select').val();
        var page = jQuery('input.pulse-list-page').val();
        var list = widget.find('.pulse-list');
        var user;
        
        list.fadeTo( 200, 0.3 );
        list.css( 'pointer-events', 'none' );
        
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
                    'page'      : page,
                    'pagination': true,
                }, overrides ),
            },
            type: 'post',
            dataType: 'json',
            success: function( data ) {
                list.html("");
                Pulse_CPT.parsePulses( list, data );
                list.fadeTo( 200, 1 );
                list.css( 'pointer-events', 'auto' );
            }
        } );
    },
    
    parsePulses: function( holder, data ) {
        jQuery.each( data['pulses'], function( index, pulse_data ) {
            var new_pulse = Pulse_CPT_Form.single_pulse_template( pulse_data );
            jQuery(new_pulse).appendTo(holder);
        } );
        
        holder.prepend( data['prepend'] );
        holder.append( data['append'] );
    }
}

jQuery('document').ready(Pulse_CPT.onReady);