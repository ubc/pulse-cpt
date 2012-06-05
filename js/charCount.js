/*
 * 	Character Count Plugin - jQuery plugin
 * 	Dynamic character count for text areas and input fields
 *	written by Alen Grakalic	
 *	http://cssglobe.com/
 *
 *	Copyright (c) 2009 Alen Grakalic (http://cssglobe.com)
 *	Dual licensed under the MIT (MIT-LICENSE.txt)
 *	and GPL (GPL-LICENSE.txt) licenses.
 *
 *	Built for jQuery library
 *	http://jquery.com
 *
 */
 
(function($) {

	$.fn.charCount = function(options){
	  
		// default configuration properties
		var defaults = {	
			allowed: 140,		
			warning: 10,
			counterElement: '.pulse-form-counter',
			cssWarning: 'warning',
			cssExceeded: 'exceeded',
			counterText: ''
		}; 
			
		var options = $.extend(defaults, options); 
		
		function calculate(obj){
			var count = $(obj).val().length;
			var available = options.allowed - count;
			if(available <= options.warning && available >= 0){
				$(options.counterElement).addClass(options.cssWarning);
			} else {
				$(options.counterElement).removeClass(options.cssWarning);
			}
			if( available < 0){
				/* todo: disable the form if elements are exceded */
				$(options.counterElement).addClass(options.cssExceeded);
				
			} else {
				$(options.counterElement).removeClass(options.cssExceeded);
				
			}
			$(options.counterElement).html(options.counterText + available);
		};
				
		this.each(function() {  			
			
			calculate(this);
			$(this).keyup(function(){calculate(this)});
			$(this).change(function(){calculate(this)});
		});
	  
	};

})(jQuery);
