/*
 * Home
 *
 * Javascript for controller
 * Browserify
 */

(function() {
	"use strict";

	var contactForm     = require('../modules/ContactForm.js'),
		DistanceFromPXL = require('../modules/DistanceFromPXL.js'),
		homeVideo       = require('../modules/HomeExtraVideo.js');

	// for distance shower
	var _distanceFromWhichToIgnoreDecimals = 3;


	$(function() {

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

        /*
         * Preroll
         *
         * Only if present (if it is, doPreroll was true)
         */
        if (('#preroll-container').length) {

        	// prevent scrolling
			$('body').addClass('no-scroll-any');

        	// fill PXL
        	$('#preroll-container').addClass('animate');

        	// fade site in
	        setTimeout(function() {
	        	$('#preroll-container').fadeOut('slow');
	        	$('body').removeClass('no-scroll-any');
	        }, 2900);

	        // start video
	        setTimeout(function() {
				$('#home-main-video').get(0).play();
	        }, 3500);

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