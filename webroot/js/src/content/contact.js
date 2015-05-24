/*
 * Contact
 *
 * Javascript for controller
 * Browserify
 */

(function() {
	"use strict";

	var contactForm     = require('../modules/ContactForm.js'),
		DistanceFromPXL = require('../modules/DistanceFromPXL.js');

	$(function() {
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

	// for distance shower
	var _distanceFromWhichToIgnoreDecimals = 3;

	/**
	 * Display a distance with the placeholder helper
	 *
	 * @param  {float} distance 	in km
	 */
	var _showDistanceToUs = function(distance) {

		var title = $('#distance-from-us-title');

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