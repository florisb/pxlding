/**
 * Show distance to PXL, if possible
 */
module.exports = (function() {
    "use strict";

    var _location = false;
    var _pxlLocation = {
    	'latitude'  : 0,
    	'longitude' : 0
    };

    // initialize everything
    $(function() {
    	_pxlLocation.latitude  = parseFloat( $('body').attr('data-pxl-latitude') );
    	_pxlLocation.longitude = parseFloat( $('body').attr('data-pxl-longitude') );
    });

    /**
     * Real calculation
     *
     * @param  {[type]} lat1
     * @param  {[type]} lon1
     * @param  {[type]} lat2
     * @param  {[type]} lon2
     * @return in km
     */
    var _distanceInKilometers = function(lat1, lon1, lat2, lon2) {

		var radlat1  = Math.PI * lat1 / 180;
		var radlat2  = Math.PI * lat2 / 180;
		var radlon1  = Math.PI * lon1 / 180;
		var radlon2  = Math.PI * lon2 / 180;
		var theta    = lon1-lon2;
		var radtheta = Math.PI * theta / 180;

	    var dist = Math.sin(radlat1) * Math.sin(radlat2) + Math.cos(radlat1) * Math.cos(radlat2) * Math.cos(radtheta);

	    dist = Math.acos(dist);
	    dist = dist * 180 / Math.PI;
	    dist = dist * 60 * 1.1515;

	    dist = dist * 1.609344; // km

	    return dist;
    };


    return {

    	/**
    	 * Whether geo-location is available
    	 */
    	locationAvailable: function() {
    		return (navigator.geolocation);
    	},

    	/**
    	 * Get position and send it to a callback
    	 *
    	 * @param  {Function} callback
    	 */
    	getLocation: function(callback) {

			if (navigator.geolocation) {
				navigator.geolocation.getCurrentPosition(function(position) {

					if (!position || typeof position === 'undefined') {
						_location = false;
						return;
					}

					_location = {
						'latitude'  : position.coords.latitude,
						'longitude' : position.coords.longitude
					};

					if (typeof callback === 'function') {
						callback(_location);
					}
				});
			}
    	},

    	/**
    	 * Get the distance to PXL from a given lat/long combination
    	 * in km.
    	 *
    	 * @param  {float} latitude
    	 * @param  {float} longitude
    	 * @return {float}
    	 */
    	getDistanceFromPXL: function(latitude, longitude) {

    		return _distanceInKilometers(_pxlLocation.latitude, _pxlLocation.longitude, latitude, longitude);
    	}
    };

});
