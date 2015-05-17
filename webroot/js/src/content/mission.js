/*
 * Mission
 *
 * Javascript for controller
 * Browserify
 */

(function() {
	"use strict";


	$(function() {

		// hover direction assisted effect
		$('section.people-matrix .person').each(function() {
			$(this).hoverdir({
				speed: 275
			});
		});

	});


})();
