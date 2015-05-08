/*
	PXL.Datepicker v1.0
	-
	by johan
	1/6/2007
	-
	requires scriptaculous
*/

var pxl_datepicker;
var pxl_datepicker_calendar = 'includes/datepicker/datepicker.php';
var pxl_datepicker_loading = false;
var pxl_datepicker_inputs = new Array();

function pxl_datepicker_display(iframe) {
	if (pxl_datepicker == iframe || pxl_datepicker_loading) return;
	
	pxl_datepicker_hide();
	pxl_datepicker = iframe;
	pxl_datepicker_loading = true;
	Effect.Appear(iframe, { duration: 0.2, afterFinish: function() { pxl_datepicker_loading = false; }  } );
}

function pxl_datepicker_hide() {
	if ($(pxl_datepicker) && !pxl_datepicker_loading) {
		$(pxl_datepicker).style.display = 'none';
		pxl_datepicker = '';
	}
}

function pxl_datepicker_register_calendar(php) {
	pxl_datepicker_calendar = php;
}

function flz(n) {
	return n < 10 ? '0'+n : n;
}

function pxl_datepicker_render() {
	inputs = document.getElementsByTagName('input');
	for (ip = 0; ip < inputs.length; ip++) {
	
		if (/pxl_datepicker/.test(inputs[ip].className)) {
			p = 'pxl_datepicker_'+ip;
			var date    = new Date(inputs[ip].value * 1000)
			var year    = ((""+date.getYear()).length < 4 ? date.getYear() + 1900 : date.getYear());
			var day     = date.getDate();
			var month   = date.getMonth() + 1;
			var v       = day +' / '+ month +' / '+ year;
			if (inputs[ip].value == 0 || inputs[ip].value == '0') {
				v = '';
				year = day = month = 0;
			}
			var content = "<input style='"+inputs[ip].getAttribute('style')+"' type='text' onkeydown=\"if (event.keyCode == '8') { $('"+p+"_v').value = ''; this.value = ''; } else { return false; }\" onfocus=\"pxl_datepicker_display('"+p+"_picker');\" id='"+p+"_v_formatted' value='"+v+"' />";
			content    += "<input type='hidden' value='"+inputs[ip].value+"' id='"+p+"_v' name='"+inputs[ip].name+"' />";
			content    += "<iframe frameborder='0' scrolling='no' class='datepicker' id='"+p+"_picker' src='"+pxl_datepicker_calendar+"?day="+day+"&amp;month="+month+"&amp;year="+year+"&amp;target="+p+"_v' style='display: none;'></iframe>";
			
			new Insertion.After(inputs[ip], content);
			Element.remove(inputs[ip]);
			
		} else if (/pxl_datetimepicker/.test(inputs[ip].className)) {
		
			p = 'pxl_datepicker_'+ip;
			var date    = new Date(inputs[ip].value * 1000)
			var year    = ((""+date.getYear()).length < 4 ? date.getYear() + 1900 : date.getYear());
			var day     = date.getDate();
			var month   = date.getMonth() + 1;
			var hours   = flz(date.getHours());
			var minutes = flz(date.getMinutes());
			var v       = day +' / '+ month +' / '+ year + ' at ' + hours + ':' + minutes;
			var content = "<input style='"+inputs[ip].getAttribute('style')+"' type='text' onkeydown='return false;' onfocus=\"pxl_datepicker_display('"+p+"_picker');\" id='"+p+"_v_formatted' value='"+v+"' />";
			content    += "<input type='hidden' value='"+inputs[ip].value+"' id='"+p+"_v' name='"+inputs[ip].name+"' />";
			content    += "<iframe frameborder='0' scrolling='no' class='datetimepicker' id='"+p+"_picker' src='"+pxl_datepicker_calendar+"?day="+day+"&amp;hours="+hours+"&amp;minutes="+minutes+"&amp;month="+month+"&amp;year="+year+"&amp;target="+p+"_v' style='display: none;'></iframe>";
			
			new Insertion.After(inputs[ip], content);
			Element.remove(inputs[ip]);
		}
	}
}

function pxl_datepicker_init() {
	pxl_datepicker_render();
	document.onclick = function() {	pxl_datepicker_hide(); }
}

Event.observe(window, 'load', pxl_datepicker_init, false);