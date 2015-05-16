/*
 * Home
 *
 * Javascript for controller
 * Browserify
 */

(function() {
	"use strict";

	var contactForm = require('../modules/ContactForm.js');


	$(function() {

		// swipe for slider
		// $('#home-slider').swipe({

		// 	allowPageScroll: 'vertical',

	 //        swipe: function(event, direction) {
	 //        	// only left + right for slider
	 //        	if (direction == 'left' || direction == 'up') {
	 //        		slider.slide(-1);
	 //        	} else if (direction == 'right' /*|| direction == 'down'*/) {
	 //        		slider.slide(1);
	 //        	}
	 //        }

		// });

		$('section.home-blog > div').owlCarousel({
			// stagePadding      : 40,
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


		/*
		 * Animate fadein for page elements while scrolling
		 */

		$('.appear-effect').addClass('appear-hide');
		$('.appear-effect').appear();

		$(document.body).on('appear', '.appear-effect', function(e, $affected) {
    		$(this).removeClass('appear-hide');
  		});

		// don't do anything after stuff has appeared
  		// $(document.body).on('disappear', '.appear-effect', function(e, $affected) {
		//
  		// });

		// force check, otherwise stuff stays hidden sometimes
		$.force_appear();

	});

})();