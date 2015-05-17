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
// window.pxl._jquery._scrollintoview = require('../lib/scrollintoview/jquery.scrollintoview.min.js');
// window.pxl._jquery._touchswipe     = require('../lib/touchswipe/touchswipe.min.js');
// window.pxl._jquery._popupoverlay   = require('../lib/popupoverlay/popupoverlay.js');
// window.pxl._jquery._featherlight   = require('../lib/featherlight/featherlight.min.js');


// import other general plugins
window.pxl._plugin._hammer         = require('../lib/hammer/hammer.min.js');
// pxl._plugin._modernizr             = require('../lib/modernizr/2.8.2/modernizr.min.js');
// pxl._plugin._plugin_imagesloaded   = require('../lib/imagesloaded/imagesloaded.pkgd.min.js');


// set global scoped re-used app modules
window.pxl.settings     = require('./settings.js');
// window.pxl.loadingState = require('../_tools/LoadingState.js');



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

		// don't do anything after stuff has appeared
  		// $(document.body).on('disappear', '.appear-effect', function(e, $affected) {
		//
  		// });

		// force check, otherwise stuff stays hidden sometimes
		$.force_appear();

	});
})();
