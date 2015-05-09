/**
 * details.js
 *
 * Module file for AMD-module "projects/details".
 *
 * @author [Auto-generated]
 * @date   03-04-2013 15:44:51
 */
define(['tools/projectSlideshow', 'tools/scroll', 'tools/imagelazyloader'], function(projectSlideshow, Scroll, imagelazyloader) {
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
			//this.googlemaps();
			this.team();
			this.counter();
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

		team: function() {

			new imagelazyloader({
				injectPosition: 'top'
			}, $('case-gallery').getElements('figure'));

			var area    = $('viewport');
			var figures = area.getElements('figure');

			if(window.getWidth() <= 568){
				var w = 126;
				var sn = false;
			} else {
				var w = 256;
				var sn = true;
			}

			$('scroller').setStyle('width', figures.length * w);

			var width = figures.length * w;

			// console.log($('scroller').getSize());


			(function(){
				var myScroll;

				myScroll = new IScroll('#wrapper', {
					scrollX: true,
					scrollY: false,
					momentum: false,
					snap: sn,
					snapSpeed: 400,
					keyBindings: true
				});

			}).delay(500);


		},

		googlemaps: function() {
			var mapOptions = {
	          center: new google.maps.LatLng(52.38775, 4.64159),
	          zoom: 17,
	          backgroundColor: '#fff',
	          disableDefaultUI: true,
	          disableDoubleClickZoom: true,
	          draggable: false,
	          scaleControl: false,
	          minZoom: 17,
	          maxZoom: 17
	        };

			var grey = [
			    {
			      featureType: "all",
			      elementType: "all",
			      stylers: [
			        { saturation: -100 } // <-- THIS
			      ]
			    }
			];

	        // var map = new google.maps.StyledMapType(document.getElementById("map-canvas"),
	        //     mapOptions);

			var map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

			var mapType = new google.maps.StyledMapType(grey, { name:"PXL" });    
			map.mapTypes.set('pxl', mapType);
			map.setMapTypeId('pxl');
		},

		initProjectSlider: function() {

			if(!($('project-detail-slideshow'))) {
				return this;
			}

			var slideshowEl = $('project-detail-slideshow'),
					sliderObj;

			var bg = function() {

				(function(){
					var active = slideshowEl.getElement('.project-detail-slideshow-container li.active');
					var id     = active.get('data-id');

					if($$('.blurred').length){
						$$('.blurred').removeClass('active');
						$('blurred_' + id).addClass('active');
					}
				}).delay(600);

			}
					
			sliderObj = new projectSlideshow({
				container:       slideshowEl.getElement('.project-detail-slideshow-container'),
				continuous:      true,
				elementSelector: 'li',
				scrollOffset:    {
					x: this.getProjectSliderScrollOffset()
				},
				elementOffset: {
					first: Math.round(slideshowEl.getElements('.project-detail-slideshow-container li').length / 2) - 1
				},
				onScrollFirst: function() {
					bg();

				},
				onScrollNext: function() {
					bg();

				},
				onScrollPrevious: function() {
					bg();

				}
				// },
				// onScrollFirst: function() {
				// 	fade();
				// },
				// onScrollNext: function() {
				// 	var elements = slideshowEl.getElements('.project-detail-slideshow-container li');
				// 	elements.each(function(el){
				// 		el.tween('opacity', 1);
				// 	});

				// 	(function(){
				// 		fade();
				// 	}).delay(750);				
				// },
				// onScrollPrevious: function() {
				// 	var elements = slideshowEl.getElements('.project-detail-slideshow-container li');
				// 	elements.each(function(el){
				// 		el.tween('opacity', 1);
				// 	});

				// 	(function(){
				// 		fade();
				// 	}).delay(750);				
				// }
			});
			
			slideshowEl.addEvents({
				'click:relay(#project-detail-slideshow .slide-prev)': sliderObj.scrollPrevious.bind(sliderObj),
				'click:relay(#project-detail-slideshow .slide-next)': sliderObj.scrollNext.bind(sliderObj)
			});
			
			this.projectSlideshow = sliderObj;
			
			return this;
		},

		getProjectSliderScrollOffset: function() {

			var offset = (Math.min( window.getSize().x , parseInt($$('.project-detail-slideshow-container').getStyle('width')) ) / 2 - 495) * -1;

			while (offset <= -990) {
				offset = offset + 990;
			}

			return offset;
		},

		resetProjectSlider: function() {
			if(!this.projectSlideshow) return;
			
			this.projectSlideshow.options.scrollOffset.x = this.getProjectSliderScrollOffset();

			this.projectSlideshow.initialize();
		},

		 elementInViewport: function(el) {
            var top = el.offsetTop;
            var left = el.offsetLeft;
            var width = el.offsetWidth;
            var height = el.offsetHeight;
          
            while(el.offsetParent) {
                    el = el.offsetParent;
                    top += el.offsetTop;
                    left += el.offsetLeft;
            }
          
            return (
                    top < (window.pageYOffset + window.innerHeight - 200) &&
                    left < (window.pageXOffset + window.innerWidth) &&
                    (top + height) > window.pageYOffset &&
                    (left + width) > window.pageXOffset
            );
        },

		counter: function() {

                //just set values for mobile, only animate for desktop
                if(Browser.Platform.ios || Browser.Platform.android || Browser.Platform.webos) {
                        $$('.counter').each(function(elm) {
                                elm.set('html', elm.get('data-number'));
                        });

                        var that = this;

                        window.addEvent('scroll', function(e){
                                if(that.elementInViewport($('speed-bar-top'))) {
                        $('speed-bar-top').setStyle('width', 302);
                        }
                });

                if(that.elementInViewport($$('.count')[0])) {
                window.fireEvent('scroll');
            }

                } else {
                        var that = this;

                        var flaps = $$('.counter');
                        if(!flaps.length) return;

                        window.addEvent('scroll', function(e){
                                flaps.each(function(el) {
                            if(el.retrieve('done')) return;
                            
                            var counter = parseInt(el.get('data-number'));
                            var id      = el.get('html');
                            var i       = 0;
                            var plus    = counter / 600;
                            
                            var count = function() {               
                                i += plus;
                                
                                if(i >= counter) {
                                    clearInterval(timer);

                                    el.set('html', el.get('data-number'));

                                } else {
                                        if(el.get('data-decimals') > 0) {
                                                el.set('html', i.toFixed(el.get('data-decimals')));
                                        } else {
                                                                el.set('html', parseInt(i));
                                                        }
                                                }
                            }
                            if(that.elementInViewport(el)) {
                                var timer = count.periodical(1);
                                el.store('done', true);
                            }
                        });   
                    });

                        if(that.elementInViewport($$('.count'))) {
                			window.fireEvent('scroll');
            			}
        	}
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