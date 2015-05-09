/**
 * details.js
 *
 * Module file for AMD-module "partners/details".
 *
 * @author [Auto-generated]
 * @date   09-12-2013 21:28:21
 */
define(['tools/projectSlideshow', 'tools/imagelazyloader'], function(projectSlideshow, imagelazyloader) {
	return new Class({

		events:         [],
		projectSlideshow:  null,

		
		/**
		 * initialize
		 *
		 * Constructor method for this module.
		 */
		initialize: function() {
			//Constructor body
			new imagelazyloader({
				injectPosition: 'top'
			}, $('page').getElements('figure'));

			this.readCase();
		},

		readCase: function() {
			
			var btn = $$('.btn.case');

			btn.addEvent('click', function(e){
				e.stop();
				new Fx.Scroll(window).toElement('case-content');				
			});
		}
	});
});