/**
 * details.js
 *
 * Module file for AMD-module "projects/details".
 *
 * @author [Auto-generated]
 * @date   03-04-2013 15:44:51
 */
define(['tools/projectSlideshow', 'tools/imagelazyloader'], function(projectSlideshow, imagelazyloader) {
	return new Class({

		events:         [],
		projectSlideshow:  null,

		
		/**
		 * initialize
		 *
		 * Constructor method for this module.
		 */
		initialize: function() {
			//Constructor body
			new imagelazyloader({
				injectPosition: 'top'
			}, $('page').getElements('figure'));

			this.readCase();
			this.initProjectSlider()
					.initEvents();
		},

		reinitialize: function() {
			this.initProjectSlider()
					.initEvents();
		},
		
		destroy: function() {

			//Remove events
			this.events.each(function(e) {
				window.removeEvent('resize', e);
			});
			
			delete this.projectSlideshow;
		},

		readCase: function() {
			if(window.getWidth() <= 320){
				var offset_y = -40;
			} else {
				var offset_y = -200;
			}

			new Fx.Scroll(window, {
			    offset: {
			        x: 0,
			        y: offset_y
			    }
			}).toElement('case-content');

			var btn = $$('.btn.case');

			btn.addEvent('click', function(e){
				e.stop();
				new Fx.Scroll(window).toElement('case-content');				
			});
		},

		initProjectSlider: function() {

			if(!($('project-detail-slideshow'))) {
				return this;
			}

			var slideshowEl = $('project-detail-slideshow'),
					sliderObj;
					
			sliderObj = new projectSlideshow({
				container:       slideshowEl.getElement('.project-detail-slideshow-container'),
				continuous:      true,
				elementSelector: 'li',
				scrollOffset:    {
					x: this.getProjectSliderScrollOffset()
				},
				elementOffset: {
					first: Math.round(slideshowEl.getElements('.project-detail-slideshow-container li').length / 2) - 1
				}
			});
			
			slideshowEl.addEvents({
				'click:relay(#project-detail-slideshow .slide-prev)': sliderObj.scrollPrevious.bind(sliderObj),
				'click:relay(#project-detail-slideshow .slide-next)': sliderObj.scrollNext.bind(sliderObj)
			});
			
			this.projectSlideshow = sliderObj;
			
			return this;
		},

		getProjectSliderScrollOffset: function() {

			if(window.getWidth() <= 768){
				var offset = (Math.min( window.getSize().x , parseInt($$('.project-detail-slideshow-container').getStyle('width')) ) / 1 - 660) * -1;
			} else {
				var offset = (Math.min( window.getSize().x , parseInt($$('.project-detail-slideshow-container').getStyle('width')) ) / 2 - 545) * -1;
			}

			while (offset <= -1090) {
				offset = offset + 1090;
			}

			return offset;
		},

		resetProjectSlider: function() {
			this.projectSlideshow.options.scrollOffset.x = this.getProjectSliderScrollOffset();

			this.projectSlideshow.initialize();
		},

		initEvents: function() {

			var timer    = null;

			window.addEvent('resize', (function() {

				if (timer) {
					window.clearTimeout(timer);
				}
				
				timer = this.resetProjectSlider.bind(this).delay(300);

			}).bind(this));
		}
	});
});