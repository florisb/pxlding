/**
 * slideshow.js
 *
 * A collection of slideshows, all implementing
 * a common interface.
 *
 * @author Max van der Stam <max@pixelindustries.com>
 */
define(function() {
	var slideshow = {
		/**
		 * slideshowInterface
		 *
		 * Common interface all slideshow classes should implement. This
		 * ensures common functionality across all slideshow-type classes
		 * while offering the same convenient interface.
		 *
		 * This class is not meant for stand-alone usage.
		 *
		 * @author     Max van der Stam <max@pixelindustries.com>
		 * @implements Options, Events
		 */
		interface: new Class({
			Implements: [Options, Events],
		
			options: {
				elementSelector:  null,
				activeClass:      'active',
				activeIeSelector: '.active',
				container:        null,
				picker:           null,
				startAtFirst:     true,
				periodical:       null
			},

			initPicker: function(slideshowObj) {
				if (!this.options.picker) {
					return;
				}
				this.picker = new slideshow.picker(this.options.picker, slideshowObj);
			},

			/**
			 * getCurrentElement
			 *
			 * Retrieves the current active element. An
			 * element is considered "active" when the element
			 * has a class called "active" attached to it. Only one
			 * element in the element collection should have this class.
			 *
			 * If no active element is found, the first element is returned instead.
			 */
			getCurrentElement: function() {
				var currElement = this.options.container.getElement('{selector}.{active}'.substitute({
					selector: this.options.elementSelector,
					active:   this.options.activeClass
				}));
				
				if(!currElement){
					currElement = this.getFirstElement();
				}
				
				return currElement;
			},
			
			/**
			 * getElementIndex
			 *
			 * Returns the index of a given element.
			 */
			getElementIndex: function(el) {
				return this.getAllElements().indexOf(el);
			},
			
			/**
			 * getCurrentElementIndex
			 *
			 * Retrieves the index of the current active element,
			 * where 1 is the first element.
			 */
			getCurrentElementIndex: function() {
				return this.getElementIndex(this.getCurrentElement()) + 1;
			},
			
			/**
			 * getNextElement
			 *
			 * Retrieves the next element in the element collection. If
			 * no valid element was found, the first element is returned
			 * instead.
			 */
			getNextElement: function() {
				var nextElement = this.getCurrentElement().getNext(this.options.elementSelector);
				
				if (!nextElement) {
					nextElement = this.getFirstElement();
				}
				
				return nextElement;
			},
			
			/**
			 * getPreviousElement
			 *
			 * Retrieves the previous element in the element collection. If
			 * no valid element was found, the last element is returned
			 * instead.
			 */
			getPreviousElement: function() {
				var previousElement = this.getCurrentElement().getPrevious(this.options.elementSelector);
				
				if (!previousElement) {
					previousElement = this.getLastElement();
				}
				
				return previousElement;
			},
			
			/**
			 * getFirstElement
			 *
			 * Retrieves the first element and returns it.
			 */
			getFirstElement: function() {
				return this.options.container.getElement('{selector}'.substitute({
					selector: this.options.elementSelector
				}));
			},
			
			/**
			 * getLastElement
			 *
			 * Retrieves the last element and returns it.
			 */
			getLastElement: function() {
				return this.options.container.getElement('{selector}:last-of-type'.substitute({
					selector: this.options.elementSelector
				}));
			},
			
			/**
			 * getElementByIndex
			 *
			 * Retrieves an element by index, starting at 0.
			 */
			getElementByIndex: function(index) {
				if (!index) {
					index = 1;
				}
			
				return this.options.container.getElement('{selector}:nth-child({idx})'.substitute({
					selector: this.options.elementSelector,
					idx:      index
				}));
			},
			
			/**
			 * getAllElements
			 *
			 * Retrieves all elements within the slideshow.
			 */
			getAllElements: function() {
				return this.options.container.getElements(this.options.elementSelector);
			},
			
			/**
			 * getElementLength
			 *
			 * Returns the length of all elements within the slideshow.
			 */
			getElementLength: function() {
				return this.getAllElements().length;
			},
			
			/**
			 * setElementActive
			 *
			 * Sets an element to being "active" by adding the activeClass as defined
			 * in the options object of the current object instance. This method
			 * also ensures that the element passed as the argument is indeed
			 * the only active element.
			 */
			setElementActive: function(element) {
				this.getAllElements().removeClass(this.options.activeClass);
				this.getAllElements().removeClass('next_active');
				if(Browser.ie && !this.options.container.hasClass('picker')){
					
					this.getAllElements().fade('out');
					element.fade('in').get('tween').chain((function(){
					 	element.addClass(this.options.activeClass);
					}).bind(this));
					
				
				}else{
					element.addClass(this.options.activeClass);
					var next = element.getNext('li');
					if(next) next.addClass('next_active');
				}
			},
			
			/**
			 * setPeriodical
			 *
			 * Runs the "scrollNext" method at the specified interval. Defaults to 5000ms.
			 */
			setPeriodical: function(interval) {
				if (interval) {
					this._interval = interval;
				}

				if (!this._interval) {
					this._interval = 5000;
				}
				
				this.resetPeriodical();
			},
			
			resetPeriodical: function() {
				this.stopPeriodical();
				
				if (this._interval) {
					this._periodical = this.scrollNext.bind(this).periodical(this._interval);
				}
			},
			
			stopPeriodical: function() {
				if (!this._interval) {
					return;
				}
				
				if (this._periodical) {
					clearInterval(this._periodical);
				}
			},
			
			synchronizePicker: function(element) {
				if (!this.picker) {
					return;
				}
				
				this.picker.synchronize.bind(this.picker, element)();
			},
			
		 /**
		  * Functions below are stubs and are implemented in classes that implement this interface.
			*/

		 /**
			* scrollNext
			*
			* Scrolls the next element in view.
			*/
			scrollNext:     function() { },
	
		 /**
			* scrollPrevious
			*
			* Scrolls the previous element into view.
			*/
			scrollPrevious: function() { },
	
		 /**
			* scrollIndex
			*
			* Scrolls to a certain element index, starting at 0.
			*/
			scrollIndex:    function() { },
			
			/**
			 * scrollFirst
			 *
			 * Scrolls to the first element.
			 */
			scrollFirst:    function() { },
			
			/**
			 * scrollLast
			 *
			 * Scrolls to the last element.
			 */
			scrollLast:     function() { },
		})
	};
	
	/**
	 * slideshow.picker
	 *
	 * Utility class that is used by other slideshow objects.
	 * This class provides an interface for an user-controllable
	 * slideshow.
	 *
	 * @author     Max van der Stam <max@pixelindustries.com>
	 * @implements slideshow.interface
	 */
	slideshow.picker = new Class({
		Implements: slideshow.interface,
		
		initialize: function(options, slideshow) {
			this.setOptions(options);
			this.setEventHandlers(slideshow);
		},
		
		setEventHandlers: function(slideshow) {
			var that      = this;
		
			this.options.container.addEvent('click:relay({selector})'.substitute({
				selector: this.options.elementSelector
			}), function() {
				if (slideshow.scrollFx) {
					if(slideshow.scrollFx.isRunning()) {
						return;
					}
				}
			
				slideshow.resetPeriodical();
			
				that.setElementActive(this);

				slideshow.scrollIndex.bind(slideshow, that.getCurrentElementIndex())();
			});
		},
		
		// synchronize: function(slideshow) {
		// 	var slideshowCurrIndex = slideshow.getCurrentElementIndex();
		// 	this.setElementActive(this.getElementByIndex(slideshowCurrIndex - 1));
		// }

		synchronize: function(element){
			this.setElementActive(this.getElementByIndex($(element).get('data-index').toInt()));
		}

	});
	
	/** 
	 * slideshow.crossfade
	 *
	 * A crossfading slideshow, where one
	 * element at a time is pulled into
	 * view.
	 *
	 * All elements should be absolutely positioned
	 * on top of each other, all having a negative z-index.
	 *
	 * @author     Max van der Stam <max@pixelindustries.com>
	 * @implements slideshow.interface
	 */
	slideshow.crossfade = new Class({
		Implements: slideshow.interface,
		
		initialize: function(options) {
			this.setOptions(options);
			this.initPicker(this);
			
			if (this.options.startAtFirst) {
				this.scrollFirst();
			}
			
			if (this.options.interval) {
				this.setPeriodical(this.options.interval);
			}
		},
		
		scrollNext: function() {
			var nextElement = this.getNextElement();
			
			this.setElementActive(nextElement);
			this.fireEvent('scrollNext');
			this.synchronizePicker(nextElement);
			this.resetPeriodical();
		},
		
		scrollPrevious: function() {
			var previousElement = this.getPreviousElement();
			
			this.setElementActive(previousElement);
			this.fireEvent('scrollPrevious');
			this.synchronizePicker(previousElement);
			this.resetPeriodical();
		},
		
		scrollIndex: function(idx) {
			var element = this.getElementByIndex(idx);
			
			this.setElementActive(element);
			this.fireEvent('scrollIndex');
			this.synchronizePicker(element);
			this.resetPeriodical();
		},
		
		scrollFirst: function() {
			var firstElement = this.getFirstElement();
			
			this.setElementActive(firstElement);
			this.fireEvent('scrollFirst');
			this.synchronizePicker(firstElement);
			this.resetPeriodical();
		},
		
		scrollLast: function() {
			var lastElement = this.getLastElement();
			
			this.setElementActive(lastElement);
			this.fireEvent('scrollLast');
			this.synchronizePicker(lastElement);
			this.resetPeriodical();
		}
	});
	
	/**
	 * slideshow.slider
	 *
	 * Classic slideshow, where elements are scrolled into
	 * to with the help of Fx.Scroll (included in MooTools Core).
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	slideshow.slider = new Class({
	
		Implements: slideshow.interface,
	
		options: {
			skipLastElement: 0,
			scrollOffset: {
				x: 0,
				y: 0
			},
			continuous: false
		},
		
		scrollFx: null,
		
		initialize: function(options) {
			this.setOptions(options);
			this.initPicker(this);
			
			this.scrollFx = new Fx.Scroll(this.options.container, {
				wheelStops: false,
				offset:     this.options.scrollOffset
			});
			
			if (this.options.startAtFirst) {
				this.scrollFirst();
			}
			
			if (this.options.interval) {
				this.setPeriodical(this.options.interval);
			}

			this.fireEvent('initialize');

		},
		
		scrollNext: function() {
			if (this.scrollFx.isRunning()) {
				return;
			}
			
			var nextElement = this.getNextElement();
			
			if (this.getElementIndex(nextElement) > (this.getElementLength() - this.options.skipLastElement - 1)) {
				nextElement = this.getFirstElement();
			}
			
			this.setElementActive(nextElement);
			this.scrollFx.toElement(nextElement).chain((function() {
				if (this.options.continuous) {
					this.getLastElement().grab(this.getPreviousElement(), 'after');
					this.scrollFx.element.scrollLeft = this.options.scrollOffset.x;
				}				
			}).bind(this));
			
			this.fireEvent('scrollNext');
			this.synchronizePicker(nextElement);
			this.resetPeriodical();

		},
		
		scrollPrevious: function() {
			if (this.scrollFx.isRunning()) {
				return;
			}
			
			if (this.options.continuous) {
				this.getCurrentElement().grab(this.getLastElement(), 'before');
				this.scrollFx.element.scrollLeft = this.getCurrentElement().getSize().x + this.getCurrentElement().getStyle('margin-left').toInt() + this.options.scrollOffset.x;
			}

			var previousElement = this.getPreviousElement();
			
			if (this.getElementIndex(previousElement) > (this.getElementLength() - this.options.skipLastElement - 1)) {
				previousElement = this.getElementByIndex(this.getElementLength() - this.options.skipLastElement - 1);
			}
			
			this.setElementActive(previousElement);
			this.scrollFx.toElement(previousElement);
			
			this.fireEvent('scrollPrevious');
			this.synchronizePicker(previousElement);
			this.resetPeriodical();
		},
		
		scrollFirst: function() {
			if (this.scrollFx.isRunning()) {
				return;
			}
			
			var firstElement = this.getFirstElement();

			this.setElementActive(firstElement);
			this.scrollFx.toElement(firstElement);
			
			this.fireEvent('scrollFirst');
			this.synchronizePicker(firstElement);
			this.resetPeriodical();
		},
		
		scrollLast: function() {
			if (this.scrollFx.isRunning()) {
				return;
			}
			
			var lastElement = this.getLastElement();
			
			if (this.getElementIndex(lastElement) > (this.getElementLength() - this.options.skipLastElement - 1)) {
				lastElement = this.getElementByIndex(this.getElementLength() - this.options.skipLastElement - 1);
			}
			
			this.setElementActive(lastElement);
			this.scrollFx.toElement(lastElement);
			
			this.fireEvent('scrollLast');
			this.synchronizePicker(lastElement);
			this.resetPeriodical();
		},
		
		scrollIndex: function(idx) {
			if (this.scrollFx.isRunning()) {
				return;
			}
			
			var element = this.getElementByIndex(idx);
			this.setElementActive(element);
			this.scrollFx.toElement(element);
			
			this.fireEvent('scrollIndex');
			this.synchronizePicker(element);
			this.resetPeriodical();
		}
	});
	
	return slideshow;
});