/*
 * Main javascript file
 *
 * loaded for all pages.
 *
 * Browserify
 */


// slightly neater global scope container
// yes, this is pretty terrible, but no time or desire
// to rewrite the whole approach for this
window.pxl = {
	_jquery: {},
	_plugin: {}
};

// import commonly used jquery plugins
window.pxl._jquery._typing         = require('../lib/typing/jquery.typing-0.2.0.min.js');
window.pxl._jquery._dotdotdot      = require('../lib/dotdotdot/jquery.dotdotdot.min.js');
window.pxl._jquery._owlcarousel    = require('../lib/owl-carousel/owl.carousel.min.js');
window.pxl._jquery._appear         = require('../lib/appear/jquery.appear.js');
window.pxl._jquery._hoverdir       = require('../lib/hoverdir/jquery.hoverdir.js');

// import other general plugins
// window.pxl._plugin._hammer         = require('../lib/hammer/hammer.min.js');
window.pxl._plugin._skrollr        = require('../lib/skrollr/skrollr.min.js');
// pxl._plugin._modernizr             = require('../lib/modernizr/2.8.2/modernizr.min.js');
// pxl._plugin._plugin_imagesloaded   = require('../lib/imagesloaded/imagesloaded.pkgd.min.js');


// set global scoped re-used app modules
// window.pxl.settings     = require('./settings.js');



(function () {
	"use strict";

	// import local main app modules
	var _mainMenu     = require('./modules/MainMenu.js'),
		_collapseMenu = require('./modules/CollapseMenu.js');


	// init
	$(function () {

		/*
		 * Animate fadein for page elements while scrolling
		 */

		$('.appear-effect').addClass('appear-hide');
		$('.appear-effect').appear();

		$(document.body).on('appear', '.appear-effect', function(e, $affected) {
    		$(this).removeClass('appear-hide');
  		});


		// force check, otherwise stuff stays hidden sometimes
		$.force_appear();


		/*
		 * Handle parallax and other scroll effects
		 */

		// init skrollr for parallax (if necessary)
		if ($('body').hasClass('has-parallax')) {

			var s = window.pxl._plugin._skrollr.init({
				// render: function(data) {
				//		console.log(data.curTop);
				//	}
			});
		}

		/*
		 * Handle rocket
		 */
		var rocketOffset = parseInt( $('body').attr('data-rocket-offset') , 10);
		var rocketBottom = parseInt( $('body').attr('data-rocket-bottom') , 10);

		$('#pxl-rocket').addClass('shake').addClass('shake-little');

		if (rocketOffset) {

			$('#pxl-rocket').click(function() {

				// rumbles
				$('#pxl-rocket').addClass('shake-constant').removeClass('shake-little');

				// shoots up
				setTimeout(function() {
					$('#pxl-rocket').css('bottom', '1500px');
				}, 350);

				// scroll up and reset the rocket to bottom
				setTimeout(function() {
	            	$("html, body").animate({ scrollTop: 0 }, { duration: 500 });

	            }, 750);

				// reset rocket
	            setTimeout(function() {
	            	$('#pxl-rocket').css('bottom', rocketBottom + 'px');
	            	$('#pxl-rocket').removeClass('shake-constant').addClass('shake-little');
	            }, 900);

			});

			$(document).scroll(function() {
				var pos = $(window).scrollTop();

				if (pos >= rocketOffset){
					$('#pxl-rocket').fadeIn('fast');
				} else {
					$('#pxl-rocket').fadeOut('fast');
				}
			});
		}

	});
})();
