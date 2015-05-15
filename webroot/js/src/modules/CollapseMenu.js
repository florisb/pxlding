/**
 * Collapse Menu
 *
 * Shrinks/collapses menu when scrolled down on page
 *
 * not a class; self-instantiating module
 */
module.exports = (function() {
    "use strict";

    // initialize everything
    $(function() {

        _initCollapseMenu();

    });


    var _mainId = 'body';
    var _offset = 60;


    var _initCollapseMenu = function() {

        $(function() {

            $(_mainId).data('top-bar', 'big');

        });


        $(window).scroll(function() {

            if ($(document).scrollTop() > _offset) {

                console.log('test');

                if ($(_mainId).data('top-bar') == 'big') {
                    $(_mainId).data('top-bar', 'small');
                    $(_mainId).addClass('collapsed-top');
                }

            } else {

                if ($(_mainId).data('top-bar') == 'small') {
                    $(_mainId).data('top-bar', 'big');
                    $(_mainId).removeClass('collapsed-top');
                }
            }
        });
    };

})();