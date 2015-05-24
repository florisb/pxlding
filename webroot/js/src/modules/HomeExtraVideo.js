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
