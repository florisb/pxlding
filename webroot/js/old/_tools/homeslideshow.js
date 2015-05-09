define(['tools/max/slideshow'], function(slideshow) {
	return new Class({
		Extends: slideshow.crossfade,

		scrollNextHome: function() {
			var nextElement = this.getNextElement();

			$$('.blurred').removeClass('active');
			$('blurred-'+nextElement.get('data-id')).addClass('active');
			
			this.setElementActive(nextElement);
			this.fireEvent('scrollNext');
			this.synchronizePicker(nextElement);
			this.resetPeriodical();
		},
		
		scrollPreviousHome: function() {
			var previousElement = this.getPreviousElement();

			$$('.blurred').removeClass('active');
			$('blurred-'+previousElement.get('data-id')).addClass('active');
			
			this.setElementActive(previousElement);
			this.fireEvent('scrollPrevious');
			this.synchronizePicker(previousElement);
			this.resetPeriodical();
		},

	});
});