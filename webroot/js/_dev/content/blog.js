(function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);throw new Error("Cannot find module '"+o+"'")}var f=n[o]={exports:{}};t[o][0].call(f.exports,function(e){var n=t[o][1][e];return s(n?n:e)},f,f.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
/*
 * Blog
 *
 * Javascript for controller
 * Browserify
 *
 * Make sure masonry is included
 */


(function() {
	"use strict";

	var containerId     = '#blog-list-container';
	var loadingId       = '#blog-next-is-loading';
	var loadingButtonId = '#blog-next-page-load';
	// var mContainer, masonry;

	$(function() {

		// only do for index
		if ($(containerId).length) {

			// strip margin for correct calculation by masonry
			$(containerId + ' article.blog').css('margin-right', 0);

			// initialize masonry
			$(containerId).masonry({
				itemSelector    : 'article.blog',
				percentPosition : true,
				columnWidth     : 'article.blog',
				gutter          : 80	// cannot be variable or element based apparently, too bad
			});
		}

		// or always focus if set (for search pages)
		if ($('#blog-list-empty').css('display') == 'block' || $('#blog-search-form').attr('data-always-focus') > 0) {
			$('#blog-search-input').focus();
		}

	});


	// catch clicks on next page button
	$(loadingButtonId).click(function(e) {

		e.preventDefault();

		if ($(this).prop('disabled') || $(this).hasClass('disabled')) {
			return;
		}

		var nextPage  = parseInt( $(this).attr('data-current-page') , 10) + 1;
		var finalPage = parseInt( $(this).attr('data-final-page') , 10);

		// safeguard
		if (nextPage > finalPage) {
			_removeLoadingButton();
			return;
		}

		// show loading indicator
		$(loadingId).fadeIn('fast');
		$(loadingButtonId).prop('disabled', true).addClass('disabled');

		// hide loading indicator
		var removeLoadingIndicator = function(keepDisabled) {
			// hide loading indicator
			$(loadingId).fadeOut('fast');
			if (!keepDisabled) {
				$(loadingButtonId).prop('disabled', false).removeClass('disabled');
			}
		};

		// ajax call, load new content
        $.ajax({
            type : 'GET',
            'url': $('base').attr('href') + 'blog/' + nextPage,
            data : { ajax: 1 },

			// parse new content if succesful
            success: function(data) {

            	// actually show stuff
                _parseNewBlogContent(data);

                // update history for this page
				history.pushState({}, '', $(loadingButtonId).attr('data-url-base') + nextPage);

                if (nextPage < finalPage) {
					$(loadingButtonId).attr('data-current-page', nextPage);
					removeLoadingIndicator();

				} else {
					_removeLoadingButton();
					removeLoadingIndicator(true);
				}
            },

            error: function() {
            	// do nothing
				removeLoadingIndicator();
            }
        });

	});

	/**
	 * When max page reached, remove the button to load pages with
	 */
	var _removeLoadingButton = function() {

		$('#blog-next-page').slideUp('fast');
	};

	/**
	 * When new ajax content received, load it into the container
	 * and fix masonry
	 *
	 * @param  {string} content
	 */
	var _parseNewBlogContent = function(content) {

		// masonry needs elements to work with this
		// so we need to parse the HTML before handing it over
		var data = $.parseHTML(content);

		$(containerId).append(data);

		setTimeout(function() {

			$(containerId).masonry('appended', data, true);
			// $(containerId).masonry('reload');

		}, 0);
	};

})();
},{}]},{},[1])