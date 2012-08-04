/* Copyright (c) 2010 Christian Pfeiffer (christian.pfeiffer.k@gmail.com || http://www.fancydesign.de)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php)
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Project: jQuery Pageswitch Plugin
 * Last Change: 2010-11-22
 * Version: 1.00
 * 
 */
 
 
(function($){
	$.fn.pageswitch = function(options) {
		var defaults = {
			url:		'default',												// 'default' for <a>-href, use e.g. 'http://www.example.com' to overwrite href with another url
			event: 		'click',												// e.g. 'click', 'mouseover', 'mouseenter', 'mouseleave', ..
			target: 	'body',													// name of one or more animation selectors
   			properties: { opacity: 0 },											// standard jquery animation properties, e.g. { opacity: 0 }
			options: 	{ duration: 400, easing: "linear", queue: true }		// standard jquery animation options, e.g. { duration: 750 }
   		};
		
   		var options = $.extend(defaults, options);
		
   		return this.each(function() {
			if(options.url == 'default') var target = $(this).attr('href');
			else var target = options.url;			
						
			$(this).bind(options.event, function() {	
				options.options.complete = function() { window.location.replace(target) }; 
				$(options.target).animate(options.properties, options.options);	   	
				return false;		
			});
  		});
 	};
})(jQuery);