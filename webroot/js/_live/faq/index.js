define(function(){return new Class({initialize:function(){var accordion=new Fx.Accordion($('accordion'),'#accordion h2','#accordion .content',{onActive:function(toggler,element){toggler.addClass('active');element.addClass('active')},onBackground:function(toggler,element){toggler.removeClass('active');element.removeClass('active')}})}})})