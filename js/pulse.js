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
    },
    
    listen: function() {
        if ( typeof CTLT_Stream != 'undefined' ) { // Check for stream activity
            CTLT_Stream.on( 'server-push', function (data) { // Catch server push
                if ( data.type == 'pulse' ) { // We are interested
                    var new_pulse_data = jQuery.parseJSON(data.data); // Extract pulse data from the event
                    
                    if ( new_pulse_data.parent == 0 ) { // No parent -> show on front page
                        var new_pulse = Pulse_CPT_Form.single_pulse_template(new_pulse_data);
                        
                        jQuery(new_pulse).prependTo('.pulse-list').hide().slideDown('slow');
                    } else { // Check to see if the parent of the pulse is visible to current user
                        var all_visible_parents = jQuery('.pulse.expand'); //all visible parents
                        var new_pulse = Pulse_CPT_Form.single_pulse_template(new_pulse_data);
                        var parent_found = false;
                        
                        jQuery.each( all_visible_parents, function( i, val ) { // Loop through and try to match the ids
                            parent_element = jQuery(val)
                            if ( parent_element.data('pulse-id') == new_pulse_data.parent ) { // If so, put it to its replies section
                                jQuery(new_pulse).prependTo( parent_element.find('.pulse-expand-content .pulse-replies') ).hide().slideDown('slow');
                                
                                var reply_count = parent_element.find(' > .pulse-wrap > .pulse-actions .reply-count');
                                reply_count.text( parseInt( reply_count.text() ) + 1 );
                                
                                parent_found = true;
                            }
                        } );
                        
                        // If no appropriate place is found for the new pulse, put it in the main pulse-list
                        if ( ! parent_found ) {
                            jQuery(new_pulse).prependTo('.pulse-list').hide().slideDown('slow');
                        }
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
                    'pulse_id' : element.data('pulse-id'),
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
    }	
}

jQuery('document').ready(Pulse_CPT.onReady);