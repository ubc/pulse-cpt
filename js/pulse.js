/* js that is required by the pulse */


var Pulse_CPT = {
  onReady: function(){
    Pulse_CPT.listen();
		
    jQuery('.pulse-list .pulse').live( 'click', Pulse_CPT.expand );
    jQuery('.expand-action').on('click', function(e) {
      e.preventDefault();
    } );
	
  },
	
  listen: function(){
		
  // eather do a ajax pull or use socket to do more here
		
  },
	
  expand: function(event) { //we need event to check which element is actually clicked
		
    var el = jQuery(this);
    el.data('pulse');
    el.toggleClass('expand');
    
    //prevent collapse if clicked on a reply
    if( jQuery(event.target).parents('.pulse-replies').length ) {
      return;
    }
    
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
    }
    );
  }
	

}
jQuery('document').ready(Pulse_CPT.onReady);
