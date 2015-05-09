/**
 * index.js
 *
 * Module file for AMD-module "team/index".
 *
 * @author [Auto-generated]
 * @date   08-12-2013 10:20:54
 */
define(['tools/scroll', 'tools/imagelazyloader'], function(Scroll, imagelazyloader) {
	return new Class({
				/**
		 * initialize
		 *
		 * Constructor method for this module.
		 */
		initialize: function() {
			//Constructor body
			this.team();
			this.resize();

		},

		resize: function() {
			//check if window is too small
			var block = $('page').getElement('.block.team .inner');

			var fix   = window.getHeight() <= 865 ? 150 : 0;

			var fix_size = function(){
				console.log($('team').getElement('img').getHeight());
				$('page').setStyles({
					'height': window.getHeight() - 49, 
					'min-height': block.getHeight() + $('team').getElement('img').getHeight() + fix - 49
				});
			}

			fix_size();

			window.addEvent('resize', function(){
				fix_size();
			});
		},

		team: function() {

			var area    = $('viewport');
			var figures = area.getElements('figure');
			
			if(window.getWidth() <= 568){
				var w = 126;
				var sn = false;
			} else {
				var w = 317;
				var sn = true;
			}

			$('scroller').setStyle('width', figures.length * w);

			(function(){
				var myScroll;

				myScroll = new IScroll('#wrapper', {
					scrollX: true,
					scrollY: false,
					momentum: false,
					snap: sn,
					snapSpeed: 400,
					keyBindings: true
				});
				

				// document.addEventListener('touchmove', function (e) { e.preventDefault(); }, false);
			}).delay(500);

			team_resize = function() {
				// $('team').getElement('.scrollarea').removeClass('anim');

				// if(window.getHeight < 712){
				// 	$('team').getElements('figure').setStyle('height', 350).setStyle('width', 293);
				// 	$('team').getElements('img').setStyle('height', 350).setStyle('width', 293);
				// 	area.getElement('div').setStyle('width', figures.length * 233);
				// } else {
				// 	$('team').getElements('figure').setStyle('height', 480).setStyle('width', 402);
				// 	$('team').getElements('img').setStyle('height', 480).setStyle('width', 402);
				// 	area.getElement('div').setStyle('width', figures.length * 317);
				// }

				// (function() {
				// 	$('team').getElement('.scrollarea').addClass('anim');
				// }).delay(50);

			};

			window.addEvent('resize', function(e){
				team_resize();
			});

			(function(){
				team_resize();
			}).delay(500);

		}
	});
});