/* js that is required by the pulse */


var Pulse_CPT = {
  onReady: function(){
    Pulse_CPT.listen();
		
    //jQuery('.pulse-list .pulse').live( 'click', Pulse_CPT.expand );
    jQuery('.expand-action').on('click', Pulse_CPT.expand );
    jQuery('.reply-action').live('click', function(e) {
	parent_id = jQuery(this).closest('.pulse').data('pulse-id');
      //look for location[type] and location[ID] fields, if not add them for the reply
      if(jQuery('input[name="location[type]"]').length > 0 && jQuery('input[name="location[ID]"]').length > 0) {
	jQuery('input[name="location[type]"]').val('singular'); //we are guaranteed to always reply to a pulse
	jQuery('input[name="location[ID]"]').val(parent_id);
      } else {
	jQuery('<input>').attr({
	  name: 'location[ID]',
	  value: parent_id,
	  type: 'hidden'
	}).appendTo('.pulse-form');
	jQuery('<input>').attr({
	  name: 'location[type]',
	  value: 'singular',
	  type: 'hidden'
	}).appendTo('.pulse-form');
      }
	jQuery('.postbox').append('replying to: ' + parent_id);
	jQuery(window).scrollTop(jQuery('.widget_pulse_cpt').offset().top);
    });
	
  },
	
  listen: function(){
		
  // eather do a ajax pull or use socket to do more here
		
  },
	
  expand: function(event) { //we need event to check which element is actually clicked
		
    var el = jQuery(this).closest('.pulse');
    el.data('pulse');
    
    //prevent collapse if clicked on a reply
    if( jQuery(event.target).parents('.pulse-replies').length ) {
      return;
    }
    
    el.toggleClass('expand');
    
    el.find('.pulse-expand-content').toggle(); //toggle visibility of expand-content
		
    if( el.hasClass('expand') ) {
      el.find('.expand-action').text( 'Collapse' );
      Pulse_CPT.expandPulse(el); //ajax call to fetch replies
    } else {
      el.find('.expand-action').text( 'Expand' );		
    }
		
  },
	
  expandPulse: function(element) {
    var args = {};
    args['pulse_id'] = element.data('pulse-id');
    
    //ajax post request, returns html data
    jQuery.post(Pulse_CPT_Form_global.ajaxUrl,
    {
      action: 'pulse_cpt_replies',
      data: args
    },
    function(data) { //put replies to its div .pulse-replies
      jQuery(element).find('.pulse-replies').html(data);
      jQuery(element).find('.pulse-replies .pulse').addClass('expand'); //dont show hover css
    }
    );
  }
	

}
jQuery('document').ready(Pulse_CPT.onReady);
