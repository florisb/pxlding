/**
 * index.js
 *
 * Module file for AMD-module "whatwedo/index".
 *
 * @author [Auto-generated]
 * @date   04-08-2014 15:28:30
 */
define(function() {
	return new Class({
		
		/**
		 * initialize
		 *
		 * Constructor method for this module.
		 */
		initialize: function() {
			this.slider();
			this.resize();
		},

		slider: function() {
            //slider variables for making things easier below
            var itemsHolder = $('slider');
            var myItems = $$(itemsHolder.getElements('.item'));
            //controls for slider
            var theControls = $('controls');
            var numNavHolder = $(theControls.getElement('ul'));
            var thePlayBtn = $(theControls.getElement('.play_btn'));
            var thePrevBtn = $(theControls.getElement('.prev_btn'));
            var theNextBtn = $(theControls.getElement('.next_btn'));
            //create instance of the slider, and start it up      
            var mySlider = new SL_Slider({
                    slideTimer: 6000,
                    orientation: 'horizontal',      //vertical, horizontal, or none: None will create a fading in/out transition.
                    fade: true,                    //if true will fade the outgoing slide - only used if orientation is != None
                    isPaused: true,
                    container: itemsHolder,
                    items: myItems,
                    numNavActive: true,
                    numNavHolder: numNavHolder,
                    playBtn: thePlayBtn,
                    prevBtn: thePrevBtn,
                    nextBtn: theNextBtn
            });
            mySlider.start();
		},

		resize: function() {
			var size = window.getHeight();

			var inner = size - 49;


			var fix_size = function() {
				$$('.block').setStyles({
					'height': inner,
					'min-height': inner,
				});
			}

			fix_size();

			window.addEvent('resize', function(){
				fix_size();
			});
		},
			
	});
});