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
	
  expand: function() {
		
    var el = jQuery(this);
    el.data('pulse');
    el.toggleClass('expand');
		
    if( el.hasClass('expand') ) {
      el.find('.expand-action').text( 'Collapse' );
      Pulse_CPT.expandPulse();
    } else {
      el.find('.expand-action').text( 'Expand' );		
    }
		
  },
	
  expandPulse: function() {
    var args = {};
    args['pulse_id'] = jQuery(element).data('pulse-id');
    jQuery.post(Pulse_CPT_Form_global.ajaxUrl,
    {
      action: 'pulse_cpt_get',
      data: args
    },
    function(data) {
      jQuery(element).find('.pulse-replies').html(data);
      console.dir(data);
    }
    );
  }
	

}
jQuery('document').ready(Pulse_CPT.onReady);
