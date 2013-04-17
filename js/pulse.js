var Pulse_CPT = {
    onReady: function() {
        Pulse_CPT.listen();
        
        // These are delegated to .pulse-list to attach to dynamic elements too
        jQuery('.pulse-list').on( 'click', '.pulse', Pulse_CPT.expand );
        jQuery('.pulse-list').on( 'click', '.expand-action', Pulse_CPT.expand );
        
        // Prevent collapse if any of these within a pulse are clicked
        jQuery('.pulse-list').on( 'click', '.pulse-replies .pulse, .pulse a, .pulse .postbox', function(e) { e.stopPropagation(); } );
        
        // Reply
        jQuery('.pulse-list').on( 'click', '.reply-action', function(e) {
            // We want to know the caller
            Pulse_CPT.reply.apply(this);
            e.stopPropagation(); //stop so we dont collapse parent pulse
        } );
        
        jQuery('.pulse-list-filter.show select').on( 'change', function() {
            Pulse_CPT.filter( jQuery(this).closest('.widget') );
        } );
        
        jQuery('.pulse-list-filter.sort select').on( 'change', function() {
            Pulse_CPT.filter( jQuery(this).closest('.widget') );
        } );
        
        jQuery('input[name="pulse-list-page"]').on( 'change', function() {
            element = jQuery(this);
            Pulse_CPT.filter( jQuery(this).closest('.widget'), function() {
                var value = element.val();
                var list = element.closest('ul');
                list.children('.active').removeClass('active');
                element.closest('li').addClass('active');
                
                if ( value == 1 ) {
                    list.children(":first").addClass('disabled');
                } else {
                    list.children(":first").removeClass('disabled');
                }
                
                if ( value == list.children().length - 2 ) {
                    list.children(":last").addClass('disabled');
                } else {
                    list.children(":last").removeClass('disabled');
                }
            } );
        } );
        
        jQuery('.pulse-page-prev').on( 'click', function() {
            jQuery(this).closest('.widget').find('.pagination li.active').prev().children().click();
        } );
        
        jQuery('.pulse-page-next').on( 'click', function() {
            jQuery(this).closest('.widget').find('.pagination li.active').next().children().click();
        } );
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
            url: Pulse_CPT_Form_global.ajaxUrl,
            data: {
                action: 'pulse_cpt_replies',
                data: {
                    'parent_id': element.data('pulse-id'),
                    'widget_id': element.closest('.widget').find('.widget-id').val(),
                },
            },
            type: 'post',
            success: function( data ) {
                // Hide spinner again and show content
                jQuery(element).find('.pulse-replies').html(data);
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
    
    filter: function( widget, callback ) {
        var widget_id = widget.find('.widget-id').val();
        var show = jQuery('.pulse-list-filter.show select').val();
        var sort = jQuery('.pulse-list-filter.sort select').val();
        var page = jQuery('input[name="pulse-list-page"]:checked').val();
        var user;
        
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
            url: Pulse_CPT_Form_global.ajaxUrl,
            data: {
                action: 'pulse_cpt_replies',
                data: {
                    'parent_id': Pulse_CPT_Form_global.id,
                    'widget_id': widget_id,
                    'sort'     : sort,
                    'show'     : show,
                    'user'     : user,
                    'order'    : order,
                    'paged'    : page,
                },
            },
            type: 'post',
            success: function( data ) {
                jQuery('#pulse_cpt-'+widget_id+' .pulse-list').html(data);
                
                if ( callback != undefined ) {
                    callback();
                }
            }
        } );
    },
}

jQuery('document').ready(Pulse_CPT.onReady);