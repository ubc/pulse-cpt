/* js that is required by the pulse */


var Pulse_CPT = {
  onReady: function(){
    Pulse_CPT.listen();
    //these are delegated to .pulse-list to attach to dynamic elements too
    jQuery('.pulse-list').on('click', '.pulse', Pulse_CPT.expand);
    jQuery('.pulse-list').on('click', '.expand-action', Pulse_CPT.expand);
    //prevent collapse if any of these within a pulse are clicked
    jQuery('.pulse-list').on('click', '.pulse-replies .pulse, .pulse a, .pulse .postbox', function(e){e.stopPropagation();});
    //reply
    jQuery('.pulse-list').on('click', '.reply-action',  function(e) {
      //we want to know the caller
      Pulse_CPT.reply.apply(this);
      e.stopPropagation(); //stop so we dont collapse parent pulse
    });
  },
	
  listen: function(){
		
  // eather do a ajax pull or use socket to do more here
		
  },
	
  expand: function() {
    var el = jQuery(this).closest('.pulse');
    el.data('pulse');
    
    el.toggleClass('expand');
    el.find('.pulse-expand-content').toggle(); //toggle visibility of expand-content
		
    if( el.hasClass('expand') ) {
      el.find('.expand-action').text( 'Collapse' );
      Pulse_CPT.expandPulse(el); //ajax call to fetch replies
    } else {
      el.find('.expand-action').text( 'Expand' );
      Pulse_CPT_Form.reply(); //reset form location if collapsed
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
    }
    );
  },
  
  reply: function() {
    var parent_pulse = jQuery(this).closest('.pulse');
    if( !parent_pulse.hasClass('expand') ) { //expand first if not expanded
      Pulse_CPT.expand.apply(this);
    }
    //reply form, pass caller element along with parent pulse ID
    Pulse_CPT_Form.reply.apply(this,[parent_pulse]);
  }	

}
jQuery('document').ready(Pulse_CPT.onReady);
