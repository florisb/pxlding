/**
 * Main Menu
 *
 * not a class; self-instantiating module
 */
module.exports = (function() {
    "use strict";

    // initialize everything
    $(function() {

        _initCloseWhenClickingOutside();

        // general
        _initSearchToggle();
        _initSearchSubmit();
        _initSubmenuToggle();
        // _initLanguageSelectorToggle();
        // _initLanguageSwitch();

        // mobile
        // _initMobileMenuToggle();
        // _initSubmenuToggleMobile();

        _initMobileMenuWithAnimation();
    });


    /*
     * PRIVATE
     */

    var _mobileMenuToggler         = '#mobile-menu-toggle';
    var _mobileMenu                = '#mobile-menu';



    // Search

    var _initSearchToggle = function() {

        $('#search-toggle, #search-container .closer').click(function(e) {
            e.preventDefault();

            if ($('#search-toggle').hasClass('open')) {
                _hideSearchMenu();
            } else {
                _hideLanguageSelector();
                _hideAllSubmenus();
                $('#search-toggle, #search-container').addClass('open');
                $('#search-container').find('input[type=search]').focus();
            }
        });

        // also small tweak for search input: escape closes the menu
        $('#search-input').keyup(function(e) {
            if (e.keyCode == 27 && $('#search-toggle, #search-container').hasClass('open')) {
                $('#search-toggle').click();
            }
        });
    };

    /**
     * Catch search form submit so that the casing of the input can be altered
     *
     * Note: must also catch separate form on search page
     */
    var _initSearchSubmit = function() {

        $('#search-container form, section.search-listing header form').submit(function(e) {
            e.preventDefault();

            // get the relevant input field, default is main header field
            var input = $(this).attr('data-input-id');
            if (!input) {
                input = 'search-input';
            }

            var term = $.trim( $('#' + input).val().toLowerCase() );

            // turn %20's into + to keep it the same as (older) google search terms
            var url  = $(this).attr('action') + '?search=' + encodeURIComponent(term).replace(/%20/g, '+');

            window.location = url;

            return false;
        });
    };


    // Submenu

    var _initSubmenuToggle = function() {

        $('nav.menu-primary').find('li').click(function(e) {
            if ($(this).attr('data-submenu')) {
                e.preventDefault();
                _toggleSubmenu(this);
            }
        });

    };


    var _hideSearchMenu = function() {
        $('#search-toggle, #search-container').removeClass('open');
    };


    var _toggleSubmenu = function(menuLink) {

        var menu = '#menu-sub-' + $(menuLink).attr('data-submenu');

        if ($(menu).hasClass('open')) {
            $(menuLink).removeClass('open');
            $(menu).removeClass('open');
            $(menu).removeAttr('style');

        } else {
            _hideSearchMenu();
            _hideAllSubmenus();
            $(menuLink).addClass('open');
            $(menu).addClass('open');
            $(menu).css('bottom', -1 * + $(menu).height() + 'px');
        }
    };

    var _hideAllSubmenus = function() {
        $('header.main').find('nav.menu-primary li, .menu-sub')
            .removeClass('open')
            .removeAttr('style');

    };


    // Submenu's for mobile
    var _initSubmenuToggleMobile = function() {
        $('#mobile-menu').find('li.expandable > a').click(function(e) {
            e.preventDefault();

            if ($(this).hasClass('open')) {
                $(this).removeClass('open');
                $(this).parent().children('.sub').removeClass('open');

            } else {
                _hideAllSubmenusMobile();
                $(this).addClass('open');
                $(this).parent().children('.sub').addClass('open');

                // scroll into view (then adjust for header fixed)
                $(this).scrollintoview({
                    complete: function() {
                        var y = $('#mobile-menu').scrollTop();
                        $('#mobile-menu').scrollTop( y - 100 );
                    },
                });
            }

        });
    };


    var _hideAllSubmenusMobile = function() {
        $('#mobile-menu').find('li.expandable').children('a, .sub').removeClass('open');
    };



    // Mobile menu
    var _initMobileMenuToggle = function() {

        $(_mobileMenuToggler).click(function(e) {
            e.preventDefault();

            if ($(this).hasClass('open')) {
                _hideMobileMenu();
            } else {
                _showMobileMenu();
            }
        });
    };

    var _hideMobileMenu = function() {
        $(_mobileMenuToggler).removeClass('open');
        $(_mobileMenu).removeClass('open');
        $('body').removeClass('no-scroll');
    };

    var _showMobileMenu = function() {
        $(_mobileMenuToggler).addClass('open');
        $(_mobileMenu).addClass('open');
        $('body').addClass('no-scroll');
    };


    // close stuff when clicking outside
    var _initCloseWhenClickingOutside = function() {

        $('html').click(function(event) {

            // check for submenu's
            if (    !$(event.target).closest('nav.menu-primary').length     &&
                    !$(event.target).closest('.menu-sub').length            &&
                    $('.menu-sub.open').length > 0
            ) {
                _hideAllSubmenus();
            }
        });
    };


    var _initMobileMenuWithAnimation = function() {

        var click = 'click';

        if ('ontouchstart' in window) {
            click = 'touchstart';
        }

        $('div.mobile-menu-burger').on(click, function () {
            if (!$(this).hasClass('open')) {
                openMenu();
            }  else {
                closeMenu();
            }
        });


        var openMenu = function () {

            $('div.mobile-menu').addClass('animate');
            $('div.mobile-menu-bg').addClass('animate');
            $('div.mobile-menu-header-overlay').addClass('animate');
            $('div.mobile-menu-burger').addClass('open');
            $('div.mobile-menu-burger div.x, div.mobile-menu-burger div.z').addClass('collapse');
            $('.mobile-menu li').addClass('animate');

            setTimeout(function() {
                $('div.mobile-menu-burger div.y').hide();
                $('div.mobile-menu-burger div.x').addClass('rotate30');
                $('div.mobile-menu-burger div.z').addClass('rotate150');
            }, 70);

            setTimeout(function() {
                $('div.mobile-menu-burger div.x').addClass('rotate45');
                $('div.mobile-menu-burger div.z').addClass('rotate135');
            }, 120);
        };

        var closeMenu = function () {

            $('.mobile-menu li').removeClass('animate');

            setTimeout(function(){
                $('div.mobile-menu-burger').removeClass('open');
                $('div.mobile-menu-burger div.x').removeClass('rotate45').addClass('rotate30');
                $('div.mobile-menu-burger div.z').removeClass('rotate135').addClass('rotate150');
                $('div.mobile-menu').removeClass('animate');
                $('div.mobile-menu-bg').removeClass('animate');
                $('div.mobile-menu-header-overlay').removeClass('animate');

                setTimeout(function() {
                    $('div.mobile-menu-burger div.x').removeClass('rotate30');
                    $('div.mobile-menu-burger div.z').removeClass('rotate150');
                }, 50);

                setTimeout(function() {
                    $('div.mobile-menu-burger div.y').show();
                    $('div.mobile-menu-burger div.x, div.mobile-menu-burger div.z').removeClass('collapse');
                }, 70);
            }, 100);
        };
    };

})();