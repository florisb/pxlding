/**
 * index.js
 *
 * Module file for AMD-module "jobs/index".
 *
 * @author [Auto-generated]
 * @date   09-12-2013 21:59:27
 */
define(function() {
	return new Class({
		
		/**
		 * initialize
		 *
		 * Constructor method for this module.
		 */
		initialize: function() {
			//Constructor body

			this.pxl_jobs();
		},

		pxl_jobs: function() {
			var buttons = $$('.show');
			var closers = $$('.close');
			var content = $('jobs-content');

			buttons.each(function(button){
				button.addEvent('click', function(e){
					e.stop();

					var parent       = button.getParent('.job');
					var show_element = parent.getElement('.hidden');
					var close 		 = parent.getElement('.close');
					var get_height   = show_element.getScrollSize().y
						
						show_element.setStyle('height', get_height);
						button.fade('out');
						close.setStyle('display', 'block');
						button.setStyle('display', 'none');

						button.addClass('active');

						(function(){
							new Fx.Scroll(window, {
								offset: {
							        x: 0,
							        y: -40
							    }
							}).toElement(parent);
						}).delay(250);
				});
			});

			closers.each(function(closer){
				closer.addEvent('click', function(e){
					e.stop();
					
					var parent       = closer.getParent('.job');
					var show_element = parent.getElement('.hidden');
					var show_button	 = parent.getElement('.show');
					
					show_element.setStyle('height', '0');
					closer.setStyle('display', 'none');
					show_button.fade('in');
					show_button.setStyle('display', 'inline-block');

					closer.removeClass('active');

				})
			})
		}
	});
});