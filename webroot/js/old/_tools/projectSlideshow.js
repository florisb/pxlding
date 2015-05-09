define(['tools/slideshow'], function(slideshow) {
	return new Class({
		Extends: slideshow.slider,
		
		scrollFirst: function() {
			if (this.scrollFx.isRunning()) {
				return;
			}
			
			var firstElement = this.getFirstElement().getNext(this.options.elementSelector);

			this.setElementActive(firstElement);

			this.scrollFx.toElement(firstElement);
			
			this.fireEvent('scrollFirst');
			this.synchronizePicker(firstElement);
			this.resetPeriodical();
		},
		
		scrollNext: function() {
			if (this.scrollFx.isRunning()) {
				return;
			}

			this.fireEvent('scrollNext');
			
			var nextElement      = this.getNextElement(),
					previousElement  = this.getPreviousElement(),
					containerElement = nextElement.getParent(),
					widthDifference  = previousElement.getSize().x;
			
			containerElement.setStyle('width', containerElement.getStyle('width').toInt() + widthDifference);
			this.getLastElement().grab(previousElement.clone(), 'after');
			
			this.setElementActive(nextElement);
			this.scrollFx.toElement(nextElement).chain((function() {
				containerElement.setStyle('width', containerElement.getStyle('width').toInt() - widthDifference);
				previousElement.destroy();
				this.scrollFx.element.scrollLeft = this.getScrollOffSet().x + widthDifference;				
			}).bind(this));
			
			this.synchronizePicker(nextElement);
			this.resetPeriodical();
		},
		
		scrollPrevious: function() {
			if (this.scrollFx.isRunning()) {
				return;
			}
			
			this.fireEvent('scrollPrevious');
			
			var previousElement  = this.getPreviousElement(),
					nextElement      = this.getLastElement(),
					containerElement = previousElement.getParent(),
					widthDifference  = nextElement.getSize().x;
					
			containerElement.setStyle('width', containerElement.getStyle('width').toInt() + widthDifference);
			this.getFirstElement().grab(nextElement.clone(), 'before');
			this.scrollFx.element.scrollLeft += this.scrollFx.element.scrollLeft - this.options.scrollOffset.x;		

			var previousElement = this.getPreviousElement();
			
			this.setElementActive(previousElement);
			this.scrollFx.toElement(previousElement).chain((function() {
				containerElement.setStyle('width', containerElement.getStyle('width').toInt() - widthDifference);
				nextElement.destroy();
			}).bind(this));
			
			this.synchronizePicker(previousElement);
			this.resetPeriodical();
		},
		
		getScrollOffSet: function() {
			return this.options.scrollOffset;
		}
	});
});