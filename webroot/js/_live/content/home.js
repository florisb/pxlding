!function t(n,e,o){function i(a,s){if(!e[a]){if(!n[a]){var u="function"==typeof require&&require;if(!s&&u)return u(a,!0);if(r)return r(a,!0);throw new Error("Cannot find module '"+a+"'")}var c=e[a]={exports:{}};n[a][0].call(c.exports,function(t){var e=n[a][1][t];return i(e?e:t)},c,c.exports,t,n,e,o)}return e[a].exports}for(var r="function"==typeof require&&require,a=0;a<o.length;a++)i(o[a]);return i}({1:[function(t,n,e){!function(){"use strict";var n=(t("../modules/ContactForm.js"),t("../modules/DistanceFromPXL.js")),e=3;$(function(){$("section.home-blog > div").owlCarousel({margin:80,loop:!1,items:3,responsive:{0:{items:1,margin:40},640:{items:2,margin:40},1050:{items:3,margin:80}}});var t=new n;t.locationAvailable()&&t.getLocation(function(n){if(n!==!1){var e=t.getDistanceFromPXL(n.latitude,n.longitude);e>100||o(e)}})});var o=function(t){var n=$("#home-distance-from-us-title"),o=n.attr("data-with-location");t=t>e?Math.round(t):t.toFixed(1),o=o.replace("%DISTANCE%",t+"km"),n.html(o)}}()},{"../modules/ContactForm.js":2,"../modules/DistanceFromPXL.js":3}],2:[function(t,n,e){n.exports=function(){"use strict";$(function(){e()});var t="#contact-form",n="#contact-form-thanks",e=function(){var n={};$(t+" .button-submit").click(function(n){n.preventDefault(),$(t).submit()}),$(t).submit(function(t){return r(this),n.contact=1,n.name=$(this).find("input[name=name]").val(),n.email=$(this).find("input[name=email]").val(),n.website=$(this).find("input[name=website]").val(),n.message=$(this).find("input[name=message]").val(),i(this,n),!1}),$(t).find("input[type=text], input[type=email], textarea").change(function(){o($(this))}).typing({stop:function(t,n){o(n)}})},o=function(t){t.removeClass("error")},i=function(t,n){n.ajax=1,$.ajax({type:"POST",url:$("base").attr("href")+$("html").attr("lang")+"/home/contact",data:n,dataType:"json",success:function(n,e){return n.hasOwnProperty("result")&&n.result?void s(n.message):void(n.hasOwnProperty("errors")&&a(t,n.errors))},error:function(){a(t,{general:"Error, could not sumbit. Please try again."})}})},r=function(t){$(t).find("input[type=text]").removeClass("error"),$(t).find("input[type=email]").removeClass("error"),$(t).find(".form-errors").text("").slideUp("fast")},a=function(t,n){var e="<p>";$.each(n,function(n,e){var o=$(t).find("input[name="+n+"]");o&&o.length&&$(o).addClass("error")}),e+="Controleer de gemarkeerde velden,<br>er is iets loos.",e+="</p>",$(t).find(".form-errors").html(e).slideDown("fast")},s=function(){$(t).slideUp("fast"),$(n).slideDown("fast")}}()},{}],3:[function(t,n,e){n.exports=function(){"use strict";var t=!1,n={latitude:0,longitude:0};$(function(){n.latitude=parseFloat($("body").attr("data-pxl-latitude")),n.longitude=parseFloat($("body").attr("data-pxl-longitude"))});var e=function(t,n,e,o){var i=Math.PI*t/180,r=Math.PI*e/180,a=(Math.PI*n/180,Math.PI*o/180,n-o),s=Math.PI*a/180,u=Math.sin(i)*Math.sin(r)+Math.cos(i)*Math.cos(r)*Math.cos(s);return u=Math.acos(u),u=180*u/Math.PI,u=60*u*1.1515,u=1.609344*u};return{locationAvailable:function(){return navigator.geolocation},getLocation:function(n){navigator.geolocation&&navigator.geolocation.getCurrentPosition(function(e){return e&&"undefined"!=typeof e?(t={latitude:e.coords.latitude,longitude:e.coords.longitude},void("function"==typeof n&&n(t))):void(t=!1)})},getDistanceFromPXL:function(t,o){return e(n.latitude,n.longitude,t,o)}}}},{}]},{},[1]);