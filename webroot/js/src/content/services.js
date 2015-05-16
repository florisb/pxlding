/*
 * Services
 *
 * Javascript for controller
 * Browserify
 */

(function() {
	"use strict";


	$(function() {

		$('section.service-cases > div').owlCarousel({
			margin            : 80,
			loop              : false,
			items             : 3,

			responsive : {
				0 : {
					items  : 1,
					margin : 40
				},
				640: {
					items   : 2,
					margin  : 40
				},
				1050: {
				 	items : 3,
				 	margin : 80
				}
			}
		});

	});

})();