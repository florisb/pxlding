/**
 * index.js
 *
 * Module file for AMD-module "cases/index".
 *
 * @author [Auto-generated]
 * @date   06-12-2013 21:25:23
 */
define(['tools/imagelazyloader'], function(imagelazyloader) {
	return new Class({
		
		/**
		 * initialize
		 *
		 * Constructor method for this module.
		 */
		initialize: function() {
			new imagelazyloader({
				injectPosition: 'top'
			}, $('page').getElements('figure'));

			$$('.offline')[0].getElements('a').addEvent('click', function(e){
				e.stop();
			});
		}
	});
});