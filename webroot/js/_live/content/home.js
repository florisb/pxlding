!function t(e,n,o){function i(a,u){if(!n[a]){if(!e[a]){var s="function"==typeof require&&require;if(!u&&s)return s(a,!0);if(r)return r(a,!0);throw new Error("Cannot find module '"+a+"'")}var c=n[a]={exports:{}};e[a][0].call(c.exports,function(t){var n=e[a][1][t];return i(n?n:t)},c,c.exports,t,e,n,o)}return n[a].exports}for(var r="function"==typeof require&&require,a=0;a<o.length;a++)i(o[a]);return i}({1:[function(t,e,n){!function(){"use strict";var e=(t("../modules/ContactForm.js"),t("../modules/DistanceFromPXL.js")),n=(t("../modules/HomeExtraVideo.js"),3);$(function(){$("section.home-blog > div").owlCarousel({margin:80,loop:!1,items:3,responsive:{0:{items:1,margin:40},640:{items:2,margin:40},1050:{items:3,margin:80}}});var t=new e;t.locationAvailable()&&t.getLocation(function(e){if(e!==!1){var n=t.getDistanceFromPXL(e.latitude,e.longitude);n>100||o(n)}})});var o=function(t){var e=$("#home-distance-from-us-title"),o=e.attr("data-with-location");t=t>n?Math.round(t):t.toFixed(1),o=o.replace("%DISTANCE%",t+"km"),e.html(o)}}()},{"../modules/ContactForm.js":2,"../modules/DistanceFromPXL.js":3,"../modules/HomeExtraVideo.js":4}],2:[function(t,e,n){e.exports=function(){"use strict";$(function(){n()});var t="#contact-form",e="#contact-form-thanks",n=function(){var e={};$(t+" .button-submit").click(function(e){e.preventDefault(),$(t).submit()}),$(t).submit(function(t){return r(this),e.contact=1,e.name=$(this).find("input[name=name]").val(),e.email=$(this).find("input[name=email]").val(),e.website=$(this).find("input[name=website]").val(),e.message=$(this).find("input[name=message]").val(),i(this,e),!1}),$(t).find("input[type=text], input[type=email], textarea").change(function(){o($(this))}).typing({stop:function(t,e){o(e)}})},o=function(t){t.removeClass("error")},i=function(t,e){e.ajax=1,$.ajax({type:"POST",url:$("base").attr("href")+$("html").attr("lang")+"/home/contact",data:e,dataType:"json",success:function(e,n){return e.hasOwnProperty("result")&&e.result?void u(e.message):void(e.hasOwnProperty("errors")&&a(t,e.errors))},error:function(){a(t,{general:"Error, could not sumbit. Please try again."})}})},r=function(t){$(t).find("input[type=text]").removeClass("error"),$(t).find("input[type=email]").removeClass("error"),$(t).find(".form-errors").text("").slideUp("fast")},a=function(t,e){var n="<p>";$.each(e,function(e,n){var o=$(t).find("input[name="+e+"]");o&&o.length&&$(o).addClass("error")}),n+="Controleer de gemarkeerde velden,<br>er is iets loos.",n+="</p>",$(t).find(".form-errors").html(n).slideDown("fast")},u=function(){$(t).slideUp("fast"),$(e).slideDown("fast")}}()},{}],3:[function(t,e,n){e.exports=function(){"use strict";var t=!1,e={latitude:0,longitude:0};$(function(){e.latitude=parseFloat($("body").attr("data-pxl-latitude")),e.longitude=parseFloat($("body").attr("data-pxl-longitude"))});var n=function(t,e,n,o){var i=Math.PI*t/180,r=Math.PI*n/180,a=(Math.PI*e/180,Math.PI*o/180,e-o),u=Math.PI*a/180,s=Math.sin(i)*Math.sin(r)+Math.cos(i)*Math.cos(r)*Math.cos(u);return s=Math.acos(s),s=180*s/Math.PI,s=60*s*1.1515,s=1.609344*s};return{locationAvailable:function(){return navigator.geolocation},getLocation:function(e){navigator.geolocation&&navigator.geolocation.getCurrentPosition(function(n){return n&&"undefined"!=typeof n?(t={latitude:n.coords.latitude,longitude:n.coords.longitude},void("function"==typeof e&&e(t))):void(t=!1)})},getDistanceFromPXL:function(t,o){return n(e.latitude,e.longitude,t,o)}}}},{}],4:[function(t,e,n){e.exports=function(){"use strict";var t="#home-extra-video",e="#home-extra-video-play";$(function(){$(t).attr("data-is-mobile")||($(t).on("ended",function(t){t||(t=window.event),n()}),$(document).on("click",t+", "+e,function(e){var i=$(t).get(0);return i.paused===!1?(n(),i.pause()):(o(),i.play()),!1}))});var n=function(){$(e).fadeIn("fast")},o=function(){$(e).fadeOut("fast")}}()},{}]},{},[1]);