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

		// strip margin for correct calculation by masonry
		$(containerId + ' article.blog').css('margin-right', 0);

		// initialize masonry
		$(containerId).masonry({
			itemSelector    : 'article.blog',
			percentPosition : true,
			columnWidth     : 'article.blog',
			gutter          : 80	// cannot be variable or element based apparently, too bad
		});

	});


	// catch clicks on next page button
	$(loadingButtonId).click(function() {

		if ($(this).prop('disabled') || $(this).hasClass('disabled')) {
			return;
		}

		// show loading indicator
		$(loadingId).fadeIn('fast');
		$(loadingButtonId).prop('disabled', true).addClass('disabled');

		var nextPage = parseInt( $(this).attr('data-current-page') , 10) + 1;

		// ajax call, load new content
		// parse new content if succesful

		// hide loading indicator
		$(loadingId).fadeOut('fast');
		$(loadingButtonId).prop('disabled', false).removeClass('disabled');
	});

	/**
	 * When new ajax content received, load it into the container
	 * and fix masonry
	 *
	 * @param  {string} content
	 */
	var _parseNewBlogContent = function(content) {

		// do something to load new html content as actual elements
		// otherwise masonry won't work

		var data = $('<article/>', {
		    'class': 'blog appear-effect',
		    style: '2px solid pink; height: 200px; width: 300px;'
		});
		data.html('TESTING!');


		$(containerId).append(data);

		setTimeout(function() {
			$(containerId).masonry('appended', data, true);
			// $(containerId).masonry('reload');
		}, 0);
	}

})();