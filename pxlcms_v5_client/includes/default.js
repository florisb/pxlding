/*
   SESSION LIFETIME MANAGER
   ========================
   the following timed ajax requests ensure the session stays alive
   and the requested page will check the allowed lifetime to only kill
   it when really necessary
*/
var cms_keep_alive_timer = 60000;
var pxlcms_fckeditors = new Array();
var dragresize = null; // for custom rendering edit
var custom_object = {};

function color_dragresize() {
	drs = $('custom_rendering_container').getElementsByClassName('drsElement');
	for (d = 0; d < drs.length; d++) {
		drs[d].style.backgroundColor = '#e1e3e8';
	}
}

function keep_alive_timer() {
	setTimeout('keep_alive()', cms_keep_alive_timer);
}

function load_ref_filter_entries(select, entries_container, user_id) {
	new Ajax.Updater(
		entries_container,
		'ajax.php',
		{
			method: 'post',
			parameters: 'page=user_module_entries.php&user_id='+user_id+'&module_id='+select.value,
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true
		});
}

function set_reference_filter(entries_container, user_id, module_id, entry_id) {
	new Ajax.Updater(
		entries_container,
		'ajax.php',
		{
			method: 'post',
			parameters: 'page=user_module_entries.php&user_id='+user_id+'&entry_id='+entry_id+'&module_id='+module_id,
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true
		});
}

function keep_alive() {
	new Ajax.Updater(
		'keep_alive',
		'ajax.php',
		{
			method: 'post',
			parameters: 'page=_keep_alive.php',
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true,
			onComplete: function() {
				keep_alive_timer();
			}
		});
}

function switch_navigation(group_id) {
	$('group_nav_title').innerHTML = $('group_nav_select_'+group_id).innerHTML;
	$$('.group_nav_select').each(function(elm) {
		elm.removeClassName('group_nav_selected');
	});
	$('group_nav_select_'+group_id).addClassName('group_nav_selected');
	new Ajax.Updater(
		'content_navigation',
		'ajax.php',
		{
			method: 'post',
			parameters: 'page=navigation.php&group_id='+group_id,
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true,
			onComplete: function() {
				open_first_section_nav();
			}
		});
}

function open_first_section_nav() {
	var visibles = false;
	var get_id = function(elm) {
		return elm.id.replace('navigation_submenu_', '');
	}
	
	$$('.nav_section').each(function(elm) {
		if (elm.getStyle('display') != 'none') {
			visibles = true;
			open_navigation = get_id(elm);
		}
	});
	if (!visibles) {
		navigation_open(get_id($$('.nav_section')[0]), true);
	}
}

function navigation_open(id, instantly) {
		if (parseInt(open_navigation) && $('navigation_submenu_'+open_navigation)) {
			Effect.BlindUp('navigation_submenu_'+open_navigation, { duration: 0.5 });
		}
		
		if (open_navigation == id) {
			open_navigation = '';
			return;
		}
		
		if (instantly) {
			$('navigation_submenu_'+id).style.display = '';
		} else {
			Effect.BlindDown('navigation_submenu_'+id, { duration: 0.8 });
		}
		open_navigation = id;
	}

/*
   action()
   ========
   general ajax wrapper
*/
function action(output_div, post) {
	loading_indicator(true);
	new Ajax.Updater(
		output_div,
		'ajax.php',
		{
			method: 'post',
			parameters: post,
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true,
			onComplete: function() {
				loading_indicator(false);
			}
		});
}

/* 
   ====
   HELP
   ====
*/
function hide_help() {
	// new Effect.Opacity('dark', { from: 0.85, to: 0, duration: 0.3, afterFinish: function() { $('dark').style.display = 'none'; } } );
	$('dark').style.display = 'none';
	$('help').style.display = 'none';
	$('html').style.overflow = 'hidden';
	$('html').style.overflowY = 'scroll';
}

function show_help() {
	$('dark').style.display = '';
	$('html').style.overflow = 'hidden';
	$('html').style.overflowY = 'hidden';
	$('help').style.display = '';
	// new Effect.Opacity('dark', { from: 0, to: 0.85, duration: 0.6, afterFinish: function() { $('help').style.display = ''; } } );
	
	new Ajax.Updater(
		'help',
		'ajax.php',
		{
			method: 'post',
			parameters: 'page=help.php',
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true
		});
}

/* 
   =================
   Image & File AJAX
   =================
*/
function image_caption_input(input, image_id) {
	action('', 'form_processing=save_image_caption&caption='+escape(input.value)+'&image_id='+image_id);
	input.style.color = '#000';
}
function sort_images(field_id, order) {
	action('multi_image_container'+field_id, 'page=image_overview.php&form_processing=sort_images&field_id='+field_id+'&'+order);
}
function sort_files(field_id, order) {
	action('multi_files_container'+field_id, 'page=files_overview.php&form_processing=sort_files&field_id='+field_id+'&'+order);
}
function delete_image(image_id, field_id, entry_id) {
	action('multi_image_container'+field_id, 'page=image_overview.php&field_id='+field_id+'&entry_id='+entry_id+'&form_processing=image_delete&image_id='+image_id);
}
function delete_file(file_id, field_id, entry_id) {
	action('multi_files_container'+field_id, 'page=files_overview.php&field_id='+field_id+'&entry_id='+entry_id+'&form_processing=file_delete&file_id='+file_id);
}
function thumbing() {
}
function showMultiUploadedImages(field_id) {
	renderImageMultiOverview(field_id, 'small');
}
function uploadComplete(field_id) {
	renderImageMultiOverview(field_id, 'large');
}
function fileUploadComplete(field_id) {
	action('multi_files_container'+field_id, 'page=files_overview.php&field_id='+field_id);
}
function renderImageMultiOverview(field_id, size) {
	action('multi_image_container'+field_id, 'page=image_overview.php&field_id='+field_id+'&size='+size);
}


/* Some other functions..... (great comment!) */
function save_order(row_type, order) {
	row_type = (row_type == 'entry') ? 'save_entry_order' : 'save_category_order';
	refresh('form_processing='+row_type+'&'+order);
}

function move_entry_to_category(row_id, category_id) {
	entry_id = row_id.replace(/row_/, '');
	refresh('form_processing=move_entry_to_category&entry_id='+entry_id+'&category_id='+category_id);
}

function move_category_to_category(row_id, category_id) {
	entry_id = row_id.replace(/row_folder_/, '');
	refresh('form_processing=move_category_to_category&entry_id='+entry_id+'&category_id='+category_id);
}

function move_simulated_to_category(row_id, category_id) {
	entry_id = row_id.replace(/simulated_/, '');
	refresh('form_processing=move_simulated_to_category&entry_id='+entry_id+'&category_id='+category_id);
}

function updateRTE(id, html) {
	document.getElementById(id).innerHTML = html.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;");
}

function process_setting(settings) {
	post = 'form_processing=process_setting&';
	$H(settings).each(
		function(pair) {
			if (typeof(pair.value) == 'object') {
				post += pair.key+'='+escape(Object.toJSON(pair.value))+'&';
			} else {
				post += 'settings[]='+escape(pair.key)+'&values[]='+escape(pair.value)+'&';
			}
		}); 
	refresh(post);
}

function process_form(form) {
	refresh('process_changes=1&'+Form.serialize(form));
}

function refresh(post, onComplete) {
	loading_indicator(true);
	new Ajax.Updater(
		'mainbody',
		'ajax.php',
		{
			method: 'post',
			parameters: 'page=body.php&'+post,
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true,
			onComplete: function() {
				pxl_datepicker_render();
				window.sliderpolyfill();
				if (PXLCMS && PXLCMS.LocationField) PXLCMS.LocationField.init();
				eval(onComplete);
				Element.scrollTo('topanchor');
				loading_indicator(false);
			}
		});
}

function saveEntry() {
	if (typeof Aloha === 'object') {
		while(Aloha.editables.length) {
			Aloha.editables.each(function(n) {
				n.destroy();
			});
		}
	}

	// update FCK editor textfields for Ajax processing
	for (fck = 0; fck < pxlcms_fckeditors.length; fck++) {
		FCKeditorAPI.GetInstance(pxlcms_fckeditors[fck]).UpdateLinkedField();
	}
	
	form_values = Form.serialize('cms_entry_form');
	// because of an ugly fix for IE, we might have had to split the form in multiple parts
	// if so, append the values from additional forms to the first
	// for more information, refer to the comments in the FormRenderer class
	for (i = 1; $('cms_entry_form'+i); i++) {
		form_values = form_values+'&'+Form.serialize('cms_entry_form' + i);
	}
	// ...bah...
	refresh('form_processing=entry_save&'+form_values);
}


/*
   LOADING INDICATOR
   =================
   show or hide the loading indicator;
   this is used on begin and end of ajax calls
*/

function loading_indicator(state) {
	if (state) {
		$('loading_indicator').innerHTML = 'Loading';
		$('loading_indicator').style.display = '';
	} else {
		setTimeout("$('loading_indicator').style.display = 'none';", 500);
	}
}

/* CUSTOM RENDERING SAVING */
function save_custom_rendering(elm, x, y, dx, dy) {
	field_id = parseInt(elm.id.replace('dragresize_field_', ''));
	
	loading_indicator(true);
	new Ajax.Updater(
		'',
		'ajax.php',
		{
			method: 'post',
			parameters: 'page=save_custom_rendering_position.php&field_id='+field_id+'&x='+x+'&y='+y+'&dx='+dx+'&dy='+dy,
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			onComplete: function() {
				loading_indicator(false);
			}
		});
}

function ajax_ref_search(searchterm, referer_module_id, output_div_id, field_id) {
	loading_indicator(true);
	new Ajax.Updater(
		output_div_id,
		'ajax.php',
		{
			method: 'post',
			parameters: 'page=ajax_references.php&action=search&searchterm='+escape(searchterm)+'&referer_module_id='+referer_module_id+'&output_div_id='+escape(output_div_id)+'&field_id='+field_id+'&existing_count='+($('ref_overview_'+field_id).childElements().length),
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true,
			onComplete: function() {
				loading_indicator(false);
			}
		});
}

function ajax_ref_select(existing_count, output_div_id, field_id, to_entry_id, referer_module_id) {
	if ($('ref_processing_'+field_id)) $('ref_processing_'+field_id).show();
	new Ajax.Updater(
		output_div_id,
		'ajax.php',
		{
			method: 'post',
			insertion: Insertion.Bottom,
			parameters: 'page=ajax_references.php&action=save&field_id='+field_id+'&to_entry_id='+to_entry_id+'&referer_module_id='+referer_module_id+'&existing_count='+existing_count,
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true,
			onComplete: function() {
				if ($('ref_processing_'+field_id)) $('ref_processing_'+field_id).hide();
			}
		});
}

function ajax_ref_remove(click_img, field_id) {
	if ($('ref_processing_'+field_id)) $('ref_processing_'+field_id).show();
	new Ajax.Updater(
		'ref_overview_'+field_id,
		'ajax.php',
		{
			method: 'post',
			insertion: Insertion.Bottom,
			parameters: 'page=ajax_references.php&field_id='+field_id+'&existing_count='+($('ref_overview_'+field_id).childElements().length - 1),
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true,
			onComplete: function() {
				if ($('ref_processing_'+field_id)) $('ref_processing_'+field_id).hide();
				$(click_img).up('div').remove();
			}
		});
}

function windowWidth() {
	if (typeof(window.innerWidth) != 'undefined') {
		//Non-IE
		return window.innerWidth;
	}
	else if (document.documentElement && document.documentElement.clientWidth) {
		//IE 6+ in 'standards compliant mode'
		return document.documentElement.clientWidth;
	}
	else if (document.body && document.body.clientWidth) {
		//IE 4 compatible
		return document.body.clientWidth;
	}
}

var screenwidth_manager_timeout;

function screenwidth_manager() {
	ww = windowWidth();
	clearTimeout(screenwidth_manager_timeout);
	screenwidth_manager_timeout = setTimeout("screenwidth_manager_saver("+ww+");", 500);
}

function screenwidth_manager_saver(ww) {
	new Ajax.Updater(
		'',
		'ajax.php',
		{
			method: 'post',
			parameters: 'page=_window_width.php&width='+ww,
			requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
			evalScripts: true,
			onComplete: function() {
				// refresh('', '');
			}
		});
}

function load_tab(tab) {
	$$('.tab').each(function(elm) {
		elm.hide();
	});
	$$('.tabs').each(function(elm) {
		elm.removeClassName('selected');
	});
	$('tabs'+tab).addClassName('selected');
	$('tab'+tab).show();
	if (PXLCMS && PXLCMS.LocationField) PXLCMS.LocationField.init(); //repaint location-fields in newly opened tab
	
	if($('custom_rendering_container')) {
		var new_height = 0;
		$$('#tab'+ tab +' .drsElement').each(function(elm) {
			var height = parseInt(elm.getStyle('top')) + parseInt(elm.getStyle('height')) + 100;
			if(height > new_height) new_height = height;
		});
		$('custom_rendering_container').setStyle({ 'height': new_height +'px' });
	}
	
	if(typeof(custom_object.switchtab) == "function") {
		custom_object.switchtab(tab);
	}
}

function expand(elm) {
	if($(elm).getStyle('overflow') == 'hidden') {
		$(elm).setStyle({
			overflow: "visible",
			height: "auto"
		});
	} else {
		$(elm).setStyle({
			overflow: "hidden",
			height: "17px"
		});
	}
}

Event.observe(window, 'load', keep_alive_timer, false);
Event.observe(window, 'load', screenwidth_manager, false);
Event.observe(window, 'resize', screenwidth_manager, false);