define(function() {
	return new Class({
		
		Implements: [Events, Options],
		
		initialize: function(options) {
			var self = this;
		
			this.setOptions(options);
			
			require(['//ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js'], function() {
				require(['//ajax.googleapis.com/ajax/libs/jqueryui/1.9.2/jquery-ui.min.js'], self.endInit.bind(self));
			});
		},
		
		endInit: function() {
			//Map global namespace for jQuery to prevent conflicts
			window.$j = jQuery.noConflict();
		
			var language       = $$('html')[0].get('lang'),
					i18nDatepicker = '../js/_tools/jquery.ui.datepicker-{lang}.js'.substitute({lang: language});
		
			//Retrieve datepicker i18n file and continue initializatinon afterwards
			require([i18nDatepicker], (function(m) {
				this.initDatePickers();
				this.fireEvent('ready');
			}).bind(this));
		},
		
		initDatePickers: function() {
			var body = $$('body')[0];
		
			/**
			 * Initialize datepickers
			 *
			 * Please note that the used datepicker is a (stable, well-working)
			 * jQuery-based object. Documentation can be found here:
			 *
			 * http://api.jqueryui.com/datepicker/
			 * http://jqueryui.com/datepicker/
			 */
			$$('*[data-date]').each(function(n) {
				$j(n).datepicker({
					showAnim:          'fadeIn',
					dateFormat:        'd MM, yy',
					showOtherMonths:   true,
					selectOtherMonths: true,
					minDate:           new Date(n.get('data-date-min').toInt() * 1000),
					onClose:     function() {
						var el        = $(this),
								timestamp = Math.round($j(n).datepicker('getDate').getTime() / 1000);
								
						body.fireEvent('change', {target: this, value: timestamp});
					}
				});
				
				//Set default date and minimal date
				$j(n).datepicker('setDate', new Date(n.get('data-date').toInt() * 1000));
				n.removeProperty('data-date');
			});
			
			return this;
		}
	});
});