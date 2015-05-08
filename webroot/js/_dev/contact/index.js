/**
 * contact.js
 *
 * Module file for AMD-module "home/contact".
 *
 * @author [Auto-generated]
 * @date   11-12-2013 07:55:41
 */
define(function() {
	return new Class({
		
		/**
		 * initialize
		 *
		 * Constructor method for this module.
		 */
		initialize: function() {
			this.contact();
		},

		 contact: function() {

            if(!$('contactform')) return;

            $('contactform').addEvent('submit', function(e) {
                    e.stop();

                    $('contactform').getElement('input, textarea').removeClass('error');

                    var request = new Request.JSON({
                            url: this.get('action') + "?send=1",
                            data: this,
                            onComplete: function(response) {

                                    if(!response.result){

                                            response.errors.each(function(error){
                                                    $('contactform').getElement('input[name="'+error.element+'"], textarea[name="'+error.element+'"]').addClass('error');
                                            });
                                    } else {

                                    $('contactform').getParent('.maps').tween('opacity', 0);

                                    var page = $('page');

                                    if(page.hasClass('contact')){
                                            (function(){ 
                                                    $('contact-bg').tween('opacity', 0);
                                            }).delay(400);

                                            var height = '100%';
                                            var width  = '100%';
                                            var target = $('page');
                                            target.setStyles({
                                                    'position': 'fixed',
                                                    'width' : '100%',
                                                    'height' : '100%'
                                            });
                                    } else {
                                            var height = '100%';
                                            var width  = '100%';
                                            var target = $('contactform').getParent('.block').getElement('.thankyou');
                                    }

                                    var url    = 'http://www.youtube.com/embed/g7gu5FiyY1c';

                                    (function(){ 
                                            target.set('html', '<div id="intro-video" data-video="g7gu5FiyY1c" style="position: absolute; background: #000000; width: 100%; height: 100%; overflow: hidden;"><iframe class="embed" id="yt574457296" frameborder="0" allowfullscreen="1" title="" width="'+width+'" height="'+height+'" src="'+url+'?controls=0&amp;showinfo=0&amp;modestbranding=1&amp;autoplay=1&amp;rel=0&amp;wmode=transparent&amp;enablejsapi=1&amp;"></iframe></div>');
                                    }).delay(800);

                                    (function(){
                                            $('contactform').getParent('.maps').tween('opacity', 1);
                                    }).delay(1600);

                                    (function(){
                                            $('contactform').getParent('.inner').destroy();
                                    }).delay(1800);

                                    }


                            }
                    });

                    request.post();
            });

        }

	});
});