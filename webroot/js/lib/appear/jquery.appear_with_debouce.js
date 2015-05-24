/*
 * jQuery appear plugin
 *
 * Copyright (c) 2012 Andrey Sidorov
 * licensed under MIT license.
 *
 * https://github.com/morr/jquery.appear/
 *
 * Version: 0.3.3
 */
(function($) {
  var selectors = [];
  var check_binded = false;
  var defaults = {
    interval: 100,
    force_process: false
  }
  var $window = $(window);

  var $prior_appeared;

  function process() {
    for (var index = 0; index < selectors.length; index++) {
      var $appeared = $(selectors[index]).filter(function() {
        return $(this).is(':appeared');
      });
      $appeared.trigger('appear', [$appeared]);
      if ($prior_appeared) {
        var $disappeared = $prior_appeared.not($appeared);
        $disappeared.trigger('disappear', [$disappeared]);
      }
      $prior_appeared = $appeared;
    }
  }

  // "appeared" custom filter
  $.expr[':']['appeared'] = function(element) {
    var $element = $(element);
    if (!$element.is(':visible')) {
      return false;
    }
    var window_left = $window.scrollLeft();
    var window_top = $window.scrollTop();
    var offset = $element.offset();
    var left = offset.left;
    var top = offset.top;
    if (top + $element.height() >= window_top &&
        top - ($element.data('appear-top-offset') || 0) <= window_top + $window.height() &&
        left + $element.width() >= window_left &&
        left - ($element.data('appear-left-offset') || 0) <= window_left + $window.width()) {
      return true;
    } else {
      return false;
    }
  }

  $.fn.extend({
    // watching for element's appearance in browser viewport
    appear: function(options) {
      var opts = $.extend({}, defaults, options || {});
      var selector = this;
      if (!check_binded) {
        var debounced = $.debounce(process, opts.interval);
        $(window)
        .scroll(debounced)
        .resize(debounced);
        check_binded = true;
      }
      if (opts.force_process) {
        process();
      }
      selectors.push(selector);
      return $(selector);
    },
    disappear: function() {
      var selector = this;
      selectors = selectors.filter(function (item) {
        return item !== selector;
      });
      return $(selector);
    }
  });

})(jQuery);