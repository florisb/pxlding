define(function() {
	return new Class({
		Implements: [Options, Events],
	
		options: {
			mapEl:       $('googleMaps'),
			placeMarker: true,
			mapOptions:  {
				zoom:             16,
				disableDefaultUI: true,
				scrollwheel:      false
			},
			markerOptions: {
				visible:   true,
				draggable: true
			}
		},
		
		marker: null,
		mapObj: null,
	
		initialize: function(options) {
			this.setOptions(options);
		
			// Define callback for Google Maps
			window.initMaps = this.initMaps.bind(this);
			
			require(['//maps.googleapis.com/maps/api/js?sensor=false&callback=initMaps']);
		},
		
		initMaps: function() {
			var lat, lng, latLng, markerOptions;
					
			lat = parseFloat(this.options.mapEl.get('data-lat')),
			lng = parseFloat(this.options.mapEl.get('data-lng'));

			if (!isNaN(lat) && !isNaN(lng)) {
				latLng = new google.maps.LatLng(lat, lng);
			} else {
				latLng = new google.maps.LatLng(52.23, 4.55);
			}
		
			this.options.mapOptions.mapTypeId = google.maps.MapTypeId.ROADMAP
			this.options.mapOptions.center    = latLng;
			
			this.mapObj = new google.maps.Map(this.options.mapEl, this.options.mapOptions);
			
			if (this.options.placeMarker) {
				this.options.markerOptions.position = latLng;
				this.options.markerOptions.map      = this.mapObj;
			
				this.marker = new google.maps.Marker(this.options.markerOptions);
				
				this.initMarkerEvents();
			}
			
			window.initMaps = undefined; //Cleanup global namespace
		},
		
		geo: function(address, callbackFn) {
			var geocoder = new google.maps.Geocoder();
			
			geocoder.geocode({
				address: address
			}, callbackFn);
		},
		
		initMarkerEvents: function() {
			if (!this.marker) {
				return;
			}
			
			google.maps.event.addListener(this.marker, 'drag',    this.fireEvent.bind(this, 'markerDrag'));
			google.maps.event.addListener(this.marker, 'dragend', this.fireEvent.bind(this, 'markerDragend'));
			
			return this;
		}
	});
});