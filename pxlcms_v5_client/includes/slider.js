;(function() {
	var Slider = {
		options: {
			dotOffset: -9
		},
		
		dragStartX: null,
		sStartX: null,
		
		init: function() {
			var elems = Slick.search(document, 'input[type=range]');
			var elem = null;
			var min = 0;
			var max = 10;
			var step = 1;
			
			for (var i = 0, j = elems.length; i < j; i++) {
				elem = elems[i];
				min = elem.getAttribute('min') || min;
				max = elem.getAttribute('max') || max;
				step = elem.getAttribute('step') || step;
				style = elem.getAttribute('style') || '';
				
				elem.setAttribute('type', 'hidden');
				
				var s = document.createElement('span');
				if (style && style.length) {
					s.setAttribute('style', style);
				}
				s.setAttribute('class', 'faux-slider');
				s.setAttribute('data-slider-min', min);
				s.setAttribute('data-slider-max', max);
				s.setAttribute('data-slider-step', step);
				s.innerHTML = '&nbsp;<span class="faux-slider-dot">&nbsp;</span>';
				elem.parentNode.insertBefore(s, elem);
				
				Event.observe(elem, 'change', function() {
					Slider.update(s, this);
				});
				Event.observe(s, 'mousedown', function(e) {
					Slider.startDrag(e, s, elem)
				});
				
				this.update(s, elem);
			}
		},
		
		startDrag: function(e, s, elem) {
			var d = Slick.find(s, 'span');
			this.dragStartX = e.clientX;
			this.sStartX = parseInt(d.style['left']);
			Event.observe(document, 'mousemove', function(e) {
				Slider.onDrag(e, s, elem);
			});
			Event.observe(document, 'mouseup', function(e) {
				Slider.endDrag(e, s, elem);
			});
		},
		
		onDrag: function(e, s, elem) {
			var newLeft = this.sStartX + (e.clientX - this.dragStartX);
			var w = parseInt(s.style['width'])
			var min = s.getAttribute('data-slider-min');
			var max = s.getAttribute('data-slider-max');
			var step = s.getAttribute('data-slider-step');
			
			elem.value = this.getValueFromState(newLeft, w, min, max, step);
			this.update(s, elem);
			
			var out = Slick.find(document, 'input[rel='+elem.getAttribute('id')+']');
			out.value = elem.value;
		},
		
		endDrag: function(e, s, elem) {
			Event.stopObserving(document, 'mousemove');
			Event.stopObserving(document, 'mouseup');
		},
		
		update: function(s, e) {
			var d = Slick.find(s, 'span');
			var v = e.value;
			var w = parseInt(s.style['width'])
			var min = s.getAttribute('data-slider-min');
			var max = s.getAttribute('data-slider-max');
			var step = s.getAttribute('data-slider-step');
			
			d.style['left'] = this.getStateFromValue(v, w, min, max, step)+'px';
			e.value = this.fixValueStep(v, min, max, step);
		},
		
		getValueFromState: function(left, width, min, max, step) {
			value = ((left / width) * (max - min));
			return this.fixValueStep(value);
		},
		
		getStateFromValue: function(value, width, min, max, step) {
			value = this.fixValueStep(value, min, max, step);
			return Math.round(((value / (max - min)) * width + this.options.dotOffset));
		},
		
		fixValueStep: function(value, min, max, step) {
			min = parseFloat(min);
			max = parseFloat(max);
			step = parseFloat(step);
			value = parseFloat(value);
			
			value = (value < min ? min : value);
			value = (value > max ? max : value);
			
			var diff = (value - min) % step;
			switch (true) {
				case diff == 0:
					value = value;
					break;
				case diff >= step/2:
					value = value - diff + step;
					break;
				case diff < step/2:
					value = value - diff;
					break;
			}
			
			return value;
		}
	};
	
	var Range = {
		dragStartX: null,
		dStartX: null,
		
		init: function() {
			var elems = Slick.search(document, 'span.faux-range');
			
			for (var i = 0, j = elems.length; i < j; i++) {
				s = elems[i];
				d_min = Slick.find(s, 'span.min');
				d_max = Slick.find(s, 'span.max');
				elem_min = $(d_min.getAttribute('rel'));
				elem_max = $(d_max.getAttribute('rel'));
				
				Event.observe(elem_min, 'keyup', function(e) {
					switch (e.keyCode) {
						case Event.KEY_UP:
							this.value = parseFloat(this.value) + parseFloat(s.getAttribute('data-slider-step'));
							break;
						case Event.KEY_DOWN:
							this.value = parseFloat(this.value) - parseFloat(s.getAttribute('data-slider-step'));
							break;
					}
					//update slider
					Range.update(s);
				});
				Event.observe(elem_max, 'keyup', function(e) {
					switch (e.keyCode) {
						case Event.KEY_UP:
							this.value = parseFloat(this.value) + parseFloat(s.getAttribute('data-slider-step'));
							break;
						case Event.KEY_DOWN:
							this.value = parseFloat(this.value) - parseFloat(s.getAttribute('data-slider-step'));
							break;
					}
					//update slider
					Range.update(s);
				});
				Event.observe(d_min, 'mousedown', function(e) {
					Range.startDrag(e, this);
				});
				Event.observe(d_max, 'mousedown', function(e) {
					Range.startDrag(e, this);
				});
				
				Range.update(s);
			}
		},
		
		update: function(s) {
			var d_min = Slick.find(s, 'span.min');
			var d_max = Slick.find(s, 'span.max');
			var elem_min = $(d_min.getAttribute('rel'));
			var elem_max = $(d_max.getAttribute('rel'));
			var min = parseFloat(s.getAttribute('data-slider-min'));
			var max = parseFloat(s.getAttribute('data-slider-max'));
			var step = parseFloat(s.getAttribute('data-slider-step'));
			var w = parseInt(s.style['width']);
			var fill = Slick.find(s, 'span.faux-range-filler');
			
			var min_val = Slider.fixValueStep(elem_min.value, min, max, step);
			var max_val = Slider.fixValueStep(elem_max.value, min, max, step);
			
			if (min_val >= max_val) {
				if (min_val >= min + step) {
					min_val = max_val - step;
				} else {
					max_val = min_val + step;
				}
				min_val = Slider.fixValueStep(min_val, min, max, step);
				max_val = Slider.fixValueStep(max_val, min, max, step);
			}
			
			d_min.style['left'] = Slider.getStateFromValue(min_val, w, min, max, step)+'px';
			d_max.style['left'] = Slider.getStateFromValue(max_val, w, min, max, step)+'px';
			elem_min.value = min_val;
			elem_max.value = max_val;
			fill.style['left'] = (parseInt(d_min.style['left'])+9)+'px';
			fill.style['width'] = (parseInt(d_max.style['left']) - parseInt(d_min.style['left']))+'px';
		},
		
		startDrag: function(e, d) {
			this.dragStartX = e.clientX;
			this.dStartX = parseInt(d.style['left']);
			Event.observe(document, 'mousemove', function(e) {
				Range.onDrag(e, d);
			});
			Event.observe(document, 'mouseup', function(e) {
				Range.endDrag(e, d);
			});
		},
		onDrag: function(e, d) {
			var s = d.parentNode;
			var newLeft = this.dStartX + (e.clientX - this.dragStartX);
			var w = parseInt(s.style['width'])
			var min = s.getAttribute('data-slider-min');
			var max = s.getAttribute('data-slider-max');
			var step = s.getAttribute('data-slider-step');
			var elem = $(d.getAttribute('rel'));
			elem.value = Slider.getValueFromState(newLeft, w, min, max, step);
			this.update(s);
		},
		endDrag: function(e, d) {
			Event.stopObserving(document, 'mousemove');
			Event.stopObserving(document, 'mouseup');
		}
	};
	
	window.sliderpolyfill = function() {
		var needed =  false;
		var elems = Slick.search(document, 'input[type=range]');
		if (elems &&  elems.length) {
			elem = elems[0];
			if (elem.type != 'range') needed = true;
		}
		if (needed) {
			Slider.init();
		}
		var elems = Slick.search(document, 'input.sliderhelper');
		if (elems) {
			for (var i = 0, j = elems.length; i < j; i++) {
				elem = elems[i];
				Event.observe(elem, 'keyup', function(e) {
					switch (e.keyCode) {
						case Event.KEY_UP:
							this.value = parseFloat(this.value) + parseFloat($(this.getAttribute('rel')).getAttribute('step'));
							break;
						case Event.KEY_DOWN:
							this.value = parseFloat(this.value) - parseFloat($(this.getAttribute('rel')).getAttribute('step'));
							break;
					}
					$(this.getAttribute('rel')).value = this.value; //update slider
					if (needed) { //update faux-slider if it is used
						Slider.update(Slick.find($(this.getAttribute('rel')).parentNode, 'span.faux-slider'), $(this.getAttribute('rel')));
					}
					this.value = $(this.getAttribute('rel')).value; //read value back from slider, sanitizing and validation is done there
				});
			}
		}
		
		var elems = Slick.search(document, 'span.faux-range');
		if (elems && elems.length) {
			Range.init();
		}
	}
	
	Event.observe(window, 'load', window.sliderpolyfill, false);
})();