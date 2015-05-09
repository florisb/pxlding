/**
 * index.js
 *
 * Module file for AMD-module "faq/index".
 *
 * @author [Auto-generated]
 * @date   01-08-2014 14:34:59
 */
define(function() {
	return new Class({
		
		/**
		 * initialize
		 *
		 * Constructor method for this module.
		 */
		initialize: function() {
  			var accordion = new Fx.Accordion($('accordion'), '#accordion h2', '#accordion .content', {
  				onActive: function(toggler, element){
        			toggler.addClass('active');
        			element.addClass('active');
    			},
    			onBackground: function(toggler, element){
        			toggler.removeClass('active');
        			element.removeClass('active');
   	 			}
  			});
		}
	});
});