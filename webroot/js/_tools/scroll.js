if(Browser.Platform.name == 'ios' || Browser.Platform.name == 'android' || Browser.Platform.name == 'webos' || Browser.Platform.name == 'other'){

  var Drag = new Class({

    Implements: [Events, Options],

    options: {
      snap: 6,
      unit: 'px',
      grid: false,
      style: true,
      limit: false,
      handle: false,
      invert: false,
      preventDefault: false,
      stopPropagation: false,
      modifiers: {x: 'left'}
    },

    initialize: function(){
      var params = Array.link(arguments, {
        'options': Type.isObject,
        'element': function(obj){
          return obj != null;
        }
      });

      this.element = document.id(params.element);
      this.document = this.element;
      this.setOptions(params.options || {});
      var htype = typeOf(this.options.handle);
      this.handles = ((htype == 'array' || htype == 'collection') ? $$(this.options.handle) : document.id(this.options.handle)) || this.element;
      this.mouse = {'now': {}, 'pos': {}};
      this.value = {'start': {}, 'now': {}};

      this.selection = (Browser.ie) ? 'selectstart' : 'mousedown';


      if (Browser.ie && !Drag.ondragstartFixed){
        document.ondragstart = Function.from(false);
        Drag.ondragstartFixed = true;
      }

      this.bound = {
        start: this.start.bind(this),
        check: this.check.bind(this),
        drag: this.drag.bind(this),
        stop: this.stop.bind(this),
        cancel: this.cancel.bind(this),
        eventStop: Function.from(false)
      };
      this.attach();
    },

    attach: function(){
      this.handles.addEvent('mousedown', this.bound.start);
      this.handles.addEvent('touchstart', this.bound.start);
      return this;
    },

    detach: function(){
      this.handles.removeEvent('mousedown', this.bound.start);
      this.handles.removeEvent('touchstart', this.bound.start);
      return this;
    },

    start: function(event){

      var options = this.options;

      if (event.rightClick) return;

      if (options.preventDefault) event.preventDefault();
      if (options.stopPropagation) event.stopPropagation();
      this.mouse.start = event.page;

      this.fireEvent('beforeStart', this.element);

      var limit = options.limit;
      this.limit = {x: []};

      var z, coordinates;
      for (z in options.modifiers){
        if (!options.modifiers[z]) continue;

        var style = this.element.getStyle(options.modifiers[z]);

        // Some browsers (IE and Opera) don't always return pixels.
        if (style && !style.match(/px$/)){
          if (!coordinates) coordinates = this.element.getCoordinates(this.element.getOffsetParent());
          style = coordinates[options.modifiers[z]];
        }

        if (options.style) this.value.now[z] = (style || 0).toInt();
        else this.value.now[z] = this.element[options.modifiers[z]];

        if (options.invert) this.value.now[z] *= -1;

        this.mouse.pos[z] = event.page[z] - this.value.now[z];

        if (limit && limit[z]){
          var i = 2;
          while (i--){
            var limitZI = limit[z][i];
            if (limitZI || limitZI === 0) this.limit[z][i] = (typeof limitZI == 'function') ? limitZI() : limitZI;
          }
        }
      }

      if (typeOf(this.options.grid) == 'number') this.options.grid = {
        x: this.options.grid
        //y: this.options.grid
      };

      var events = {
        mousemove: this.bound.check,
        mouseup: this.bound.cancel
      };
      events[this.selection] = this.bound.eventStop;
      this.document.addEvents(events);
      
      this.document.addEvent('touchmove', this.bound.check);
      this.document.addEvent('touchend', this.bound.check);
    },

    check: function(event){
      if (this.options.preventDefault) event.preventDefault();
      var distance = Math.round(Math.sqrt(Math.pow(event.page.x - this.mouse.start.x, 2) + Math.pow(event.page.y - this.mouse.start.y, 2)));
      if (distance > this.options.snap){
        this.cancel();
        this.document.addEvents({
          mousemove: this.bound.drag,
          mouseup: this.bound.stop
        });
        
        this.document.addEvent('touchmove', this.bound.drag);
        this.document.addEvent('touchend', this.bound.stop);
        
        this.fireEvent('start', [this.element, event]).fireEvent('snap', this.element);
        
        
      }
    },

    drag: function(event){
      var options = this.options;

      if (options.preventDefault) event.preventDefault();
      this.mouse.now = event.page;

      for (var z in options.modifiers){
        if (!options.modifiers[z]) continue;
        this.value.now[z] = this.mouse.now[z] - this.mouse.pos[z];

        if (options.invert) this.value.now[z] *= -1;

        if (options.limit && this.limit[z]){
          if ((this.limit[z][1] || this.limit[z][1] === 0) && (this.value.now[z] > this.limit[z][1])){
            this.value.now[z] = this.limit[z][1];
          } else if ((this.limit[z][0] || this.limit[z][0] === 0) && (this.value.now[z] < this.limit[z][0])){
            this.value.now[z] = this.limit[z][0];
          }
        }

        if (options.grid[z]) this.value.now[z] -= ((this.value.now[z] - (this.limit[z][0]||0)) % options.grid[z]);

        if (options.style) this.element.setStyle(options.modifiers[z], this.value.now[z] + options.unit);
        else this.element[options.modifiers[z]] = this.value.now[z];
      }

      this.fireEvent('drag', [this.element, event]);
    },

    cancel: function(event){
      this.document.removeEvents({
        mousemove: this.bound.check,
        mouseup: this.bound.cancel
      });
      if (event){
        this.document.removeEvent(this.selection, this.bound.eventStop);
        this.fireEvent('cancel', this.element);
      }
    },

    stop: function(event){
      var events = {
        mousemove: this.bound.drag,
        mouseup: this.bound.stop
      };
      events[this.selection] = this.bound.eventStop;
      this.element.removeEvents(events);
      if (event) this.fireEvent('complete', [this.element, event]);
    }

  });

}



(function(){

Drag.Scroll = new Class({

  // We'd like to use the Options Class Mixin
  Implements: [Options],

  // Default options
  options: {
    friction: 5,
    axis: {x: true, y: true}
  },

  initialize: function(element, options){
    element = this.element = document.id(element);
    this.content = element.getFirst();
    this.setOptions(options);

    // Drag speed
    var prevTime, prevScroll, speed, scroll, timer;
    var timerFn = function(){
      var now = Date.now();
      scroll = [element.scrollLeft, element.scrollTop];
      if (prevTime){
        var dt = now - prevTime + 1;
        speed = [
          1000 * (scroll[0] - prevScroll[0]) / dt,
          1000 * (scroll[1] - prevScroll[1]) / dt
        ];
      }
      prevScroll = scroll;
      prevTime = now;
    };

    // Use Fx.Scroll for scrolling to the right position after the dragging
    var fx = this.fx = new Fx.Scroll(element, {
      transition: Fx.Transitions.Expo.easeOut,
      duration: 1500
    });

    // Set initial scroll
    fx.set.apply(fx, this.limit(element.scrollLeft, element.scrollTop));

    var self = this;
      friction = this.options.friction,
      axis = this.options.axis;

    // Make the element draggable
    var drag = this.drag = new Drag(element, {
      style: false,
      invert: true,
      modifiers: {x: axis.x && 'scrollLeft', y: axis.y && 'scrollTop'},
      onStart: function(){
        // Start the speed measuring
        timerFn();
        timer = setInterval(timerFn, 1000 / 60);
        // cancel any fx if they are still running
        fx.cancel();
      },
      onComplete: function(){
        // Stop the speed measuring
        prevTime = false;
        clearInterval(timer);
        // Scroll to the new location
        fx.start.apply(fx, self.limit(
          scroll[0] + (speed[0] || 0) / friction,
          scroll[1] + (speed[1] || 0) / friction
        ));
      }
    });

  },

  // Calculate the limits
  getLimit: function(){
    var limit = [[0, 0], [0, 0]], element = this.element;
    var styles = Object.values(this.content.getStyles(
      'padding-left', 'border-left-width', 'margin-left',
      'padding-top', 'border-top-width', 'margin-top',
      'width', 'height'
    )).invoke('toInt');
    limit[0][0] = sum(styles.slice(0, 3));
    limit[0][1] = styles[6] + limit[0][0] - element.clientWidth;
    limit[1][0] = sum(styles.slice(3, 6));
    limit[1][1] = styles[7] + limit[1][0] - element.clientHeight;
    return limit;
  },

  // Apply the limits to the x and y values
  limit: function(x, y){
    var limit = this.getLimit();
    return [
      x.limit(limit[0][0], limit[0][1]),
      y.limit(limit[1][0], limit[1][1])
    ];
  }

});

var sum = function(array){
  var result = 0;
  for (var l = array.length; l--;) result += array[l];
  return result;
};

})();