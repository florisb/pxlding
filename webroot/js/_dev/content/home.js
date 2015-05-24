(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/*
 * Home
 *
 * Javascript for controller
 * Browserify
 */

(function() {
	"use strict";

	var contactForm     = require('../modules/ContactForm.js'),
		DistanceFromPXL = require('../modules/DistanceFromPXL.js'),
		homeVideo       = require('../modules/HomeExtraVideo.js');

	// for distance shower
	var _distanceFromWhichToIgnoreDecimals = 3;


	$(function() {

		$('section.home-blog > div').owlCarousel({
			// stagePadding      : 40,
			margin            : 80,
			loop              : false,
			items             : 3,

			responsive : {
				0 : {
					items  : 1,
					margin : 40
				},
				640: {
					items   : 2,
					margin  : 40
				},
				1050: {
				 	items : 3,
				 	margin : 80
				}
			}
		});


		/*
         * Get location and set correct distance
         */
        var distancer = new DistanceFromPXL();

        if (distancer.locationAvailable()) {
        	distancer.getLocation(function(position) {

        		if (position === false) {
        			// console.log('no position available')
        			return;
        		}

        		var distance = distancer.getDistanceFromPXL(position.latitude, position.longitude);

        		// don't allow absurd distances to be listed
        		if (distance > 100) {
        			// console.log('distance to us too great')
        			return;
        		}

        		_showDistanceToUs(distance);
        	} );
        }

        /*
         * Preroll
         *
         * Only if present (if it is, doPreroll was true)
         */
        if (('#preroll-container').length) {

        	// prevent scrolling
			$('body').addClass('no-scroll-any');

        	// fill PXL
        	$('#preroll-container').addClass('animate');

        	// fade site in
	        setTimeout(function() {
	        	$('#preroll-container').fadeOut('slow');
	        	$('body').removeClass('no-scroll-any');
	        }, 2900);

	        // start video
	        setTimeout(function() {
				$('#home-main-video').get(0).play();
	        }, 3500);

		}

	});

	/**
	 * Display a distance with the placeholder helper
	 *
	 * @param  {float} distance 	in km
	 */
	var _showDistanceToUs = function(distance) {

		var title = $('#home-distance-from-us-title');

		var text = title.attr('data-with-location');

		if (distance > _distanceFromWhichToIgnoreDecimals) {
			distance = Math.round(distance);
		} else {
			distance = distance.toFixed(1);
		}

		text = text.replace('%DISTANCE%', distance + 'km');

		title.html(text);
	};

})();
},{"../modules/ContactForm.js":2,"../modules/DistanceFromPXL.js":3,"../modules/HomeExtraVideo.js":4}],2:[function(require,module,exports){
/**
 * Contact Form
 *
 * not a class; self-instantiating module
 */
module.exports = (function() {
    "use strict";

    // initialize everything
    $(function() {

        _initForm();
    });


    /*
     * PRIVATE
     */

    var _formId        = '#contact-form';
    var _formMessageId = '#contact-form-thanks';



    /**
     * Catch submission of form, do ajax call instead and show errors in form
     */
    var _initForm = function() {

        var that = this;
        var data = {};

        // make button do a submit
        $(_formId + ' .button-submit').click(function(e) {
            e.preventDefault();

            $(_formId).submit();
        });

        // catch form submit and send data
        $(_formId).submit(function(e) {
            //e.preventDefault();

            _clearErrors(this);

            data.contact = 1;
            data.name    = $(this).find('input[name=name]').val();
            data.email   = $(this).find('input[name=email]').val();
            data.website = $(this).find('input[name=website]').val();
            data.message = $(this).find('input[name=message]').val();

            _doFormSubmit(this, data);

            return false;
        });

        // clear error state on user input
        $(_formId).find('input[type=text], input[type=email], textarea').change(function() {
            _clearInvalidStateForField($(this));
        }).typing({
            stop: function (event, $elem) {
                _clearInvalidStateForField($elem);
            }
        });

    };


    /**
     * Simple removal of error class state
     */
    var _clearInvalidStateForField = function (element) {
        element.removeClass('error');
    };


    /**
     * Submit data from form through ajax
     *
     * @param  {string} formElement  element id name of the form container we're sending from
     * @param  {object} data        data to send
     */
    var _doFormSubmit = function(formElement, data) {
        var that = this;

        data.ajax = 1;

        $.ajax({
            type     : 'POST',
            'url'    : $('base').attr('href') + $('html').attr('lang') + '/' + 'home/contact',
            'data'   : data,
            dataType : 'json',
            // dataType : 'html',

            success: function(data, status) {

                // console.log(data);
                // console.log(status);

                // check result: show error or redirect to success page
                if (data.hasOwnProperty('result') && data.result) {
                    _showMessage(data.message);
                    return;
                }

                // should never happen, fallback
                if (!data.hasOwnProperty('errors')) {
                    return;
                }

                // show errors
                _showErrors(formElement, data.errors);
            },

            error: function() {

                // show generic error
                //window.location = $('base').attr('href') + 'newsletter/failed';
                _showErrors(formElement, {
                    'general': 'Error, could not sumbit. Please try again.'
                });
            }
        });

    };

    /**
     * Remove all the errors and error state from the form
     *
     * @param  {string} formElement     element
     */
    var _clearErrors = function(formElement) {

        $(formElement).find('input[type=text]').removeClass('error');
        $(formElement).find('input[type=email]').removeClass('error');
        $(formElement).find('.form-errors').text('').slideUp('fast');

    };

    /**
     * Show errors and set error state for input fields
     * @param  {string} formElement
     * @param  {object} errors
     */
    var _showErrors = function(formElement, errors) {

        var text = '<p>';

        $.each(errors, function(key, value) {
            // text += value + '<br>';
            // console.log(key);
            // console.log(value);
            // set error state for field if known
            var inputElem = $(formElement).find('input[name=' + key + ']');

            if (inputElem && inputElem.length) {
                $(inputElem).addClass('error');
            }
        });

        // just a single message for now

        text += 'Controleer de gemarkeerde velden,<br>er is iets loos.';

        text += '</p>';

        $(formElement).find('.form-errors').html(text).slideDown('fast');

    };

    /**
     * Show the thanks message
     */
    var _showMessage = function() {
        $(_formId).slideUp('fast');
        $(_formMessageId).slideDown('fast');
    };


})();
},{}],3:[function(require,module,exports){
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

},{}],4:[function(require,module,exports){
/**
 * Handle home video (the extra one)
 *
 * Not a class
 */
module.exports = (function() {
    "use strict";

    var _videoId       = '#home-extra-video';
    var _videoButtonId = '#home-extra-video-play';

    // initialize everything
    $(function() {

        // only do this for non-mobile,
        // since this breaks playback on iPhone
        if ( ! $(_videoId).attr('data-is-mobile')) {

            // reset after video is done
            $(_videoId).on('ended', function(e) {

                if (!e) {
                    e = window.event;
                }

                _showPlayButton();
            });

            $(document).on('click', _videoId + ', ' + _videoButtonId, function(e) {

                var video = $(_videoId).get(0);

                if (video.paused === false) {
                    _showPlayButton();
                    video.pause();
                } else {
                    _hidePlayButton();
                    video.play();
                }

                return false;
            });
        }
    });


    var _showPlayButton = function() {
        $(_videoButtonId).fadeIn('fast');
    };

    var _hidePlayButton = function() {
        $(_videoButtonId).fadeOut('fast');
    };


})();

},{}]},{},[1])