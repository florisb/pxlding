(function() {
	var app = new Class({
		initialize: function(modules) {
			this.initEvents();
		
			var body = $$('body')[0];
		
			/**
			 * Additional logic for IE8
			 */
			if (Browser.ie && Browser.version < 9) {
				require(['//cdnjs.cloudflare.com/ajax/libs/css3pie/1.0.0/PIE.js'], function() {
					body.setStyle('visibility', 'visible');
					body.getElements('.rounded-pie').each(function(el) {
						PIE.attach(el);
					});
				});
			}
		
			//Configure RequireJS
			requirejs.config({
				baseUrl: body.get('data-jsbasepath'),
				paths:   {
					'tools': '../_tools'
				}
			});
			
			//Cleanup body attributes used for initialization
			body.removeProperty('data-jsbasepath')
				  .removeProperty('data-js');
				  
			modules.each(function(moduleName, i) {
				require([moduleName], function(m) {
					new m();
				});
			});

			this.cover();
			this.header();
			this.contact();
			this.newsletter();
			this.scrollarea();
			this.menu();
		},
		
		/**
		 * initEvents
		 *
		 * Initializes global event handlers that happen on every page. Exclusively uses relayed
		 * events.
		 */
		initEvents: function() {
			var body = $$('body')[0];
		},

		cover: function() {
			var caseImage = $('case');
			if(!caseImage) return;

			var height = caseImage.get('data-height') == null ? window.innerHeight : caseImage.get('data-height');

			window.addEvent('resize', function(){
				caseImage.setStyle('height', height);
				caseImage.getParent('.case-header-wrapper').setStyle('height', height);
			});

			window.fireEvent('resize');

			var speed = -550;

			if(Browser.Platform.name == 'ios' || Browser.Platform.name == 'android' || Browser.Platform.name == 'webos' || Browser.Platform.name == 'other')
				return;

			window.addEvent('scroll', function(){
				var progress = this.getScroll().y / height;
				var offset = progress * speed;
				var opacity = 1 - (progress * (0.8 * 1));

				$('case-image').setStyles({
					'opacity': opacity,
					'background-position': 'center ' + ((offset*-1) / 2) + 'px'
				});
			});

			window.fireEvent('scroll');

		},

		header: function() {

			var menu = $$('header')[0];

			if(!$('home')) menu.removeClass('wait');

			window.addEvent('scroll', function(e){

				if(!menu.hasClass('visible') && !menu.hasClass('wait')){
					menu.addClass('visible');
					menu.addClass('fadeInDown');
				}

			});

			window.fireEvent('scroll');

			(function(){
				menu.removeClass('wait');
			}).delay(800);

			(function(){
				window.fireEvent('scroll');
			}).delay(800);

			eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('5 1=\'\';5 b=0;m.n(\'o\',2(e){1=1+\'\'+e.p;9(1.l>=6)1=\'\';9(1==\'i\'||1==\'k\'){$$(\'j\')[0].f(\'r\');(2(){$(\'3-4\').a(\'y\').c(\'8\',\'z A 7\');$(\'3-4\').a(\'x\').c(\'8\',\'u B 7\');b=h.s(q)}).g(v);5 h=2(){$(\'3-4\').t(\'d\');(2(){$(\'3-4\').f(\'d\')}).g(w)}}});',38,38,'|string|function|case|name|var||AWESOME|html|if|getElement|timer|set|bounce||addClass|delay|gogo|pxl|body|pixel|length|window|addEvent|keyup|key|1500|rotateIn|periodical|removeClass|ECHT|1000|100|h2|h1|PXL|IS|SUPERDUPER'.split('|'),0,{}))

		},

		scrollarea: function() {
			var area = $('team');

			if(!area) return;

			if(Browser.Platform.name == 'ios' || Browser.Platform.name == 'android' || Browser.Platform.name == 'webos' || Browser.Platform.name == 'other')
				return;

			window.addEvent('mousemove', function(e){
				$('cursor').setStyles({
					left: e.client.x + 10,
					top: e.client.y + 10
				})
			});			

			area.addEvents({
				mouseenter: function(){
					$('cursor').setStyle('display', 'block');
				},

				mouseleave: function(){
					$('cursor').setStyle('display', 'none');
				}

			});

		},

		contact: function() {
			if(!$('contactform')) return;

			var home = $('home');

	        $$('#contactform .btn.black').addEvent('click', function(e) {
		            if(home) {
		                e.stop();

		                $('contactform').getElement('input', 'textarea').removeClass('error');

		                var request = new Request.JSON({
		                        url: $('contactform').get('action') + "?send=1",
		                        data: $('contactform'),
		                        onComplete: function(response) {

		                        	if(!response.result){

		                        		response.errors.each(function(error){
		                        		 	$('contactform').getElement('input[name="'+error.element+'"], textarea[name="'+error.element+'"]').addClass('error');
		                        		});
		                        	} else {

		                                var page = $('page');

		                                var lang = $$('html').get('lang');
		                                
		                                if(page.hasClass('contact')){
		                                	(function(){ 
		                                		$('contact-inner').tween('opacity', 0);
		                                	}).delay(400);

		                                	var height = '100%';
		                                	var width  = '100%';
		                                	var target = $('contact-inner-thank');
		                                } else {
		                                	(function(){ 
		                                		$('contact-inner').tween('opacity', 0);
		                                	}).delay(400);

		                                	var height = '100%';
		                                	var width  = '100%';
		                                	var target = $('contact-inner-thank');
		                                }

		                                (function(){ 
		                               		if (lang == 'nl') {
		                                		target.set('html', '<h2>Bedankt</h2><div class="text">Bedankt voor je bericht<br/>Wij nemen zo spoedig mogelijk contact met je op</div>');			                                	
		                                	} else {
		                                		target.set('html', '<h2>Bedankt</h2>');
		                                	};
		                                }).delay(900);

		                                (function(){ 
		                                		$('contact-inner-thank').tween('opacity', 1);
		                                }).delay(900);

		                        	}

		                        }
		                });

		                request.post();
				}
			});	

	    },

	    newsletter: function() {
			if(!$('newsletterform')) return;

	        $$('#newsletterform button').addEvent('click', function(e) {
	                e.stop();

	                $('newsletterform').getElement('input').removeClass('error');

	                var request = new Request.JSON({
	                        url: $('newsletterform').get('action') + "?send=1",
	                        data: $('newsletterform'),
	                        onComplete: function(response) {

	                        	if(!response.result){

	                        		response.errors.each(function(error){
	                        		 	$('newsletterform').getElement('input[name="'+error.element+'"], textarea[name="'+error.element+'"]').addClass('error');
	                        		});
	                        	} else {

	                                //$('newsletterform').getParent('.maps').tween('opacity', 0);

	                                var page = $('page');

	                                var lang = $$('html').get('lang');
	                                
	                                if(page.hasClass('newsletter')){
	                                	(function(){ 
	                                		$('newsletter-inner').tween('opacity', 0);
	                                	}).delay(400);

	                                	var height = '100%';
	                                	var width  = '100%';
	                                	var target = $('newsletter-inner-thank');
	                                } else {
	                                	(function(){ 
	                                		$('newsletter-inner').tween('opacity', 0);
	                                	}).delay(400);
	                                	var height = '100%';
	                                	var width  = '100%';
	                                	var target = $('newsletter-inner-thank');
	                                }

	                                (function(){ 
	                               		if (lang == 'nl') {
	                                		target.set('html', '<h2>Bedankt</h2><div class="text">Bedankt voor je inschrijving</div>');			                                	
	                                	} else {
	                                		target.set('html', '<h2>Bedankt</h2>');
	                                	};
	                                }).delay(900);

	                                (function(){ 
	                                		$('newsletter-inner-thank').tween('opacity', 1);
	                                }).delay(900);

	                        	}

	                        }
	                });

	                request.post();
	        });

	    },

	    menu: function() {
	    	var menu = $$('.menu');

	    	if(!menu.length) return;

	    	menu.addEvent('click', function(e){
	    		e.stop();

	    		if(!this.hasClass('active')){
	    			this.addClass('active');
	    			$$('header').addClass('active');
	    		} else {
	    			this.removeClass('active');
	    			$$('header').removeClass('active');
	    		}
	    	});
	    }

	});
	
	window.addEvent('domready', function() {

		var modules = JSON.decode($$('body')[0].get('data-js'));
		
		//Run application
		new app(modules);
	});
})();