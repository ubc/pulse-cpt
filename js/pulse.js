/* js that is required by the pulse */


var Pulse_CPT = {
  clone: null,
  onReady: function(){
    Pulse_CPT.listen();
    //jQuery('.pulse-list .pulse').live( 'click', Pulse_CPT.expand );
    jQuery('.expand-action').on('click', Pulse_CPT.expand );
    jQuery('.reply-action').live('click', Pulse_CPT.reply);
//    clone = jQuery('.postbox').clone(false);
//    clone.appendTo(jQuery('body'));
  },
	
  listen: function(){
		
  // eather do a ajax pull or use socket to do more here
		
  },
	
  expand: function(event) { //we need event to check which element is actually clicked
    var el = jQuery(this).closest('.pulse');
    el.data('pulse');
    
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
  },
  
  reply: function(event) {
//    jQuery('#pulse_cpt-2 .pulse-form').appendTo(this.closest('.pulse-expand-content'));
  }	

}
jQuery('document').ready(Pulse_CPT.onReady);
