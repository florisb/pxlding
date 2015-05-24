/*
 * Home
 *
 * Javascript for controller
 * Browserify
 */

(function() {
	"use strict";

	var contactForm     = require('../modules/ContactForm.js'),
		DistanceFromPXL = require('../modules/DistanceFromPXL.js');

	// for distance shower
	var _distanceFromWhichToIgnoreDecimals = 3;


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
         * Get location and set correct distance
         */
        var distancer = new DistanceFromPXL();

        if (distancer.locationAvailable()) {
        	distancer.getLocation(function(position) {

        		if (position === false) {
        			// console.log('no position available')
        			return;
        		}

        		var distance = distancer.getDistanceFromPXL(position.latitude, position.longitude);

        		// don't allow absurd distances to be listed
        		if (distance > 100) {
        			// console.log('distance to us too great')
        			return;
        		}

        		_showDistanceToUs(distance);
        	} );
        }

	});

	/**
	 * Display a distance with the placeholder helper
	 *
	 * @param  {float} distance 	in km
	 */
	var _showDistanceToUs = function(distance) {

		var title = $('#home-distance-from-us-title');

		var text = title.attr('data-with-location');

		if (distance > _distanceFromWhichToIgnoreDecimals) {
			distance = Math.round(distance);
		} else {
			distance = distance.toFixed(1);
		}

		text = text.replace('%DISTANCE%', distance + 'km');

		title.html(text);
	};

})();