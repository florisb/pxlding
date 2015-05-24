/*
 * Home
 *
 * Javascript for controller
 * Browserify
 */

(function() {
	"use strict";

	var contactForm     = require('../modules/ContactForm.js'),
		homeVideo       = require('../modules/HomeExtraVideo.js');


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
         * Preroll
         *
         * Only if present (if it is, doPreroll was true)
         */
        if ($('#preroll-container').length) {

        	// prevent scrolling
			$('body').addClass('no-scroll-any');

        	// fill PXL
        	$('#preroll-container').addClass('animate');

        	// fade site in
	        setTimeout(function() {
	        	$('#preroll-container').fadeOut('slow');
	        }, 2900);

	        // start video
	        setTimeout(function() {
				$('#home-main-video').get(0).play();
	        	$('body').removeClass('no-scroll-any');
	        }, 3500);

		}

	});

})();