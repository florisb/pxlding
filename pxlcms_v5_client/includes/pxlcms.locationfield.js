if (!PXLCMS) var PXLCMS = {};

PXLCMS.LocationField = {
	init: function() {
		var maps = $$('.location_map');
		if (!maps.length) return;
		for (var i = 0, j = maps.length; i < j; i++) {
			var mapdiv = maps[i];
			if (mapdiv.gmap) {
				google.maps.event.trigger(mapdiv.gmap, 'resize');
				//for some reason the center gets displayed topleft instead of centered?
				var latLng = new google.maps.LatLng(
					$(mapdiv.getAttribute('data-lat')).value,
					$(mapdiv.getAttribute('data-lng')).value
				);
				//move center
				mapdiv.gmap.panTo(latLng);
				var temp = document.createElement('div');
				mapdiv.appendChild(temp);
				mapdiv.removeChild(temp);
				continue;
			}
			var center = new google.maps.LatLng(4.643046, 52.388304);
			var options = {
				center: center,
				mapTypeId: google.maps.MapTypeId.ROADMAP,
				zoom: 5
			};
			mapdiv.gmap = new google.maps.Map(mapdiv, options);
			mapdiv.gmap.panTo(center);
			
			google.maps.event.addListener(mapdiv.gmap, 'click', function(e) {
				PXLCMS.LocationField.updateLatLng(this, e.latLng);
			});
			
			Event.observe($(mapdiv.getAttribute('data-lat')), 'change', function() {PXLCMS.LocationField.updateMap($(this.getAttribute('data-map')).gmap);});
			Event.observe($(mapdiv.getAttribute('data-lng')), 'change', function() {PXLCMS.LocationField.updateMap($(this.getAttribute('data-map')).gmap);});
			
			PXLCMS.LocationField.updateMap(mapdiv.gmap);
		}
	},
	
	updateMap: function(map) {
		var latLng = new google.maps.LatLng(
				$(map.getDiv().getAttribute('data-lat')).value,
				$(map.getDiv().getAttribute('data-lng')).value
			);
		//move center
		map.panTo(latLng);
		//place new marker or move current
		if (map.getDiv().marker) {
			map.getDiv().marker.setPosition(latLng);
		} else {
			map.getDiv().marker = new google.maps.Marker({
				animation: google.maps.Animation.DROP,
				position: latLng,
				draggable: true,
				map: map
			});
			google.maps.event.addListener(map.getDiv().marker, 'dragend', function(e) {
				PXLCMS.LocationField.updateLatLng(this.getMap(), e.latLng);
			});
			if(map.getDiv().hasAttribute('data-icon')) {
			map.getDiv().marker.setIcon(new google.maps.MarkerImage(map.getDiv().getAttribute('data-icon')));
		}
		}
	},
	
	updateLatLng: function(map, latLng) {
		$(map.getDiv().getAttribute('data-lat')).value = latLng.lat().toFixed(6);
		$(map.getDiv().getAttribute('data-lng')).value = latLng.lng().toFixed(6);
		PXLCMS.LocationField.updateMap(map);
	},
	
	search: function(address, map) {
		var gc = new google.maps.Geocoder();
		var options = {
			address: address,
			bounds: map.getBounds()
		};
		gc.geocode(options, function(results, status) {
			if (status == google.maps.GeocoderStatus.OK) {
				PXLCMS.LocationField.updateLatLng(map, results[0].geometry.location);
			}
		});
	}
};

Event.observe(window, 'load', PXLCMS.LocationField.init, false);