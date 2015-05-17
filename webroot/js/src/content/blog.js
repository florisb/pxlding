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

	$(function() {

		// strip margin
		$('#blog-list-container article.blog').css('margin-right', 0);

		var container = document.querySelector('#blog-list-container');
		var msnry = new Masonry(container, {
			// options
			// columnWidth: 200,
			itemSelector: 'article.blog',
			percentPosition: true,
			columnWidth: 'article.blog',
			// function( containerWidth ) {
			// 	return containerWidth / 5;
			// }
			gutter: 80,
			// columnWidth: container.querySelector('article.blog')
		});


	});

})();