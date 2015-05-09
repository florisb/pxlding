/*
 * imagelazyloader.js
 *
 * Takes a set of domNodes, each
 * containing a data-img attribute and
 * injects the images into those domNodes
 * on-demand when they are scrolled
 * into view. Only supports vertical scrolling.
 * Adding a data-alt to the domNodes to supply
 * an alt tag to the injected img elements
 *
 * Set forceLoad to true if encountering problems
 * with loading on scroll (i.e. with fixed elements)
 *
 * Images are faded in using either CSS transitions (fast, preferred) or
 * Javascript animations (slower, fallback).
 *
 * @param		params	Hash of settings for this lazy loader instance
 * @param		nodes		Nodes to apply lazy loading to
 * @author	Max van der Stam
 */
define(function() {
	var params = {
		Implements: Events,
		nodesByPosition: {},
		scrollHandler: null,
		
		injectPosition: 'bottom',
		forceLoad: false,

		initialize: function(params, nodes)
		{
			this.initSettings(params);			
			this.groupNodesByPosition(nodes);
			this.attachScrollHandler();
		},
		
		destroy: function()
		{
			window.removeEvent('scroll', this.scrollHandler);
		},
		
		initSettings: function(settings)
		{
			var validKeys = ['injectPosition','forceLoad'];
			
			for (var n in settings) {
				if (validKeys.indexOf(n) === -1) {
					continue;
				}
				
				this[n] = settings[n];
			}
		},
		
		attachScrollHandler: function()
		{
			var scrollHandler = function() {
				var currY		= window.getScroll().y + window.getSize().y + 25;
						
				for (var n in this.nodesByPosition) {
					n = parseInt(n);
					if (true === isNaN(n)) {
						continue;
					}
					
					if (n <= currY || this.forceLoad) {
						this.nodesByPosition[n].each(this.loadImage.bind(this));
						
						//Unset since we've started loading this row
						delete this.nodesByPosition[n];
					}
				}
			};
			
			this.scrollHandler = scrollHandler.bind(this);
		
			window.addEvent('scroll', this.scrollHandler);
			
			//Fire the scroll event once to load images that are in the view already
			window.fireEvent('scroll');
		},
		
		/*
		 * groupNodesByPosition
		 *
		 * Groups the nodes by vertical position, since
		 * they will come into view simultaneously.
		 */
		groupNodesByPosition: function(nodes)
		{
			var that = this;
		
			nodes.each(function(node) {
				var yPos = node.getPosition().y;

				if (typeof that.nodesByPosition[yPos] === 'undefined') {
					that.nodesByPosition[yPos] = [node];
				} else {
					that.nodesByPosition[yPos].push(node);
				}
			});
		},
		
		/*
		 * loadImage
		 *
		 * Loads the image that corresponds to the node
		 * and injects it in the node with a fade-in
		 * effect afterwards.
		 */
		loadImage: function(node)
		{
			var imgUrl	= node.get('data-img'),
					that    = this,
					altTag	= node.get('data-alt');
					
			node.removeProperty('data-img');
			node.removeProperty('data-alt');
			
			if (typeof imgUrl !== 'string' || node.getElement('*[src={imgUrl}]'.substitute({imgUrl: imgUrl}))) {
				return;
			}
			
			if (imgUrl.length === 0) {
				return;
			}
			
			//Fire event to indicate we're about to load an image
			this.fireEvent('preLoad', imgUrl);
			
			Asset.image(imgUrl, {
				onLoad: function() {
					var el = new Element('img', {
						src:imgUrl,
						alt:altTag
					});
					
					el.setStyle('opacity', 0).inject(node, that.injectPosition);
					
					//Fire event to indicate loading is done, and we're about to fade it in
					that.fireEvent('postLoad', el);
					
					if (Browser.ie || true) {
						el.fade('in').get('tween');
					} else {
						setTimeout(el.setStyle.bind(el, 'opacity', 1), 100); //Wait 100ms for DOM to update, then remove the "hidden" class to trigger CSS transition
					}
				}
			});
		}
	};
	
	return new Class(params);
});