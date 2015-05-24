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
	var searchLoadingId = '#blog-search-is-loading';

	var currentlySearchingFor = '';


	$(function() {

		// only do for index
		if ($(containerId).length) {

			// strip margin for correct calculation by masonry
			$(containerId + ' article.blog').css('margin-right', 0);

			_initMasonry();
		}

		// or always focus if set (for search pages)
		if ($('#blog-list-empty').css('display') == 'block' || $('#blog-search-form').attr('data-always-focus') > 0) {
			$('#blog-search-input').focus();
		}

		// store what we're currently looking for, if anything
		currentlySearchingFor = $.trim( $('#blog-search-input').val() );

    	// initialize automatic searching
        $('#blog-search-input').change(function(e) {
        	// allow reloading page if empty
            _doSearch(true);
        });

        $('#blog-search-input').typing({
            stop: function (event, $elem) {
                _doSearch();
            },
        });

	});

	/**
	 * Initializes (or re-initializes) masonry
	 */
	var _initMasonry = function() {
		$(containerId).masonry({
			itemSelector    : 'article.blog',
			percentPosition : true,
			columnWidth     : 'article.blog',
			gutter          : 80	// cannot be variable or element based apparently, too bad
		});

		$(containerId).masonry('on', 'layoutComplete', function() {
			_masonryLaidOut();
		});
	};

	/**
	 * Call when masonry is done changing its layout
	 */
	var _masonryLaidOut = function() {
		$(containerId).css('opacity', 1);
	};


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
            'method' : 'GET',
            'url'    : $('base').attr('href') + $('html').attr('lang') + '/blog/' + nextPage,
            'data'   : { ajax: 1 },

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
	 * @param  {string}  content
	 * @param  {boolean} replace	if true, does not add but replace incoming stuff
	 */
	var _parseNewBlogContent = function(content, replace) {

		// masonry needs elements to work with this
		// so we need to parse the HTML before handing it over
		var data = $.parseHTML(content);

		if (replace) {
			// hide container because replacing stuff is ugly
			$(containerId).css('opacity', 0);
			$(containerId).empty();

		} else {
			// safeguard, force opacity if we might need it
			$(containerId).css('opacity', 1);
		}

		$(containerId).append(data);

		setTimeout(function() {

			if (replace)  {
				$(containerId).masonry('reloadItems');
				$(containerId + ' article.blog').css('margin-right', 0);
				_initMasonry();
			} else {
				$(containerId).masonry('appended', data, true);
			}

		}, 0);
	};


	/**
	 * Performs search operation for blog search input
	 */
	var _doSearch = function(allowEmpty) {

		var term = $.trim( $('#blog-search-input').val() );

		// only start looking if different
		if (term == currentlySearchingFor) {
			return;
		}

		// don't load for empty search unless allowed
		if (!allowEmpty && !term) {
			return;
		}

		// not searching for anything? reload the page as normal index
		if (!term) {
			window.location.href = $('base').attr('href') + 'blog';
		}

		// console.log('searching for: ' + term);

		// show loading indicator
		$(searchLoadingId).fadeIn('fast');

		// hide loading indicator
		var removeLoadingIndicator = function() {
			// hide loading indicator
			$(searchLoadingId).fadeOut('fast');
		};

		// ajax call, load new content
        $.ajax({
            'method'   : 'POST',
            'url'      : $('base').attr('href') + $('html').attr('lang') + '/blog/search',
            'dataType' : 'html',
            'data'     : {
            	'ajax'   : 1,
            	'search' : term
           	},

			// parse new content if succesful
            success: function(data) {

            	// console.log(data);
				currentlySearchingFor = term;

            	// actually show stuff
                _parseNewBlogContent(data, true);

                // update history for this page
				history.pushState({}, '', $('base').attr('href') + 'blog/search');

				_removeLoadingButton();
				removeLoadingIndicator();

				// check if we got any items, if none, show empty state
				if ( $(containerId).find('article.blog').length ) {
					$('#blog-list-empty').fadeOut('fast');
					$(containerId).fadeIn('fast');
				} else {
					$(containerId).fadeOut('fast');
					$('#blog-list-empty').fadeIn('fast');
				}
            },

            error: function() {
            	// console.log('error');
            	// do nothing
				removeLoadingIndicator();
            }
        });
	};

})();