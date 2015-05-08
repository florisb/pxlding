<?php
	class FormRenderer {
		/*
			FORM-RENDERING
			--------------
			below are functions that manage the rendering of the CMS entry forms
			further below are the rendering functions for specific form elements
		*/

		var $format;
		var $values;
		var $CMS;
		var $entry_id;
		var $tabs = array();
		var $custom_rendering = false;

		function __construct(&$CMS) {
			global $CMS_SESSION;
			$this->entry_id = $CMS_SESSION['entry_id'];
			$this->CMS = $CMS;
		}

		public function setFormat($format) {
			$this->format = $format;
		}

		public function setValues($values) {
			$this->values = $values;
		}

		public function setTabs($values) {
			$this->tabs = $values;
		}

		public function getValues($field) {
			if (is_array($this->values)) {
				$values = $this->values[$field['cms_name']];

				if (!is_array($values))
				{
					return array($values);
				}
				else
				{
					switch ($field['form_element'])
					{
						case 'reference':
						case 'reference_multi':
							return array_keys($values);

						default:
							return $values;
					}
				}
			}
			return array(); // no value specified, empty form field
		}

		public function renderForm() {
			global $CMS_SESSION;

			$form = "<script type='text/javascript'>pxlcms_fckeditors = new Array()</script>";
			$form .= "<form id='cms_entry_form' action='' method='post' onsubmit='return false;'>";
			$height = 0;
			if(!empty($this->tabs)) {
				$first = true;
				$form .= "</form>";

				foreach($this->tabs as $tab) {
					if(isset($CMS_SESSION["tab_id"])) {
						$active = ($CMS_SESSION["tab_id"] == $tab["id"]) ;
					} else {
						$active = $first;
					}

					$form .= "<div style=' ".(!$active ? "display:none;":"")."' class='tab' id='tab".$tab["id"]."'>";
					$this->bugfix_formsplits++;
					$form .= "<form id='cms_entry_form".$this->bugfix_formsplits."' action='' method='post'>";
					foreach ($this->format as $field) {
						if($field['tab_id'] != $tab["id"]) continue;
						if($first) $height = max($height, $field['render_y'] + $field['render_dy']);
						$form .= $this->renderField($field);
					}
					$form .= "</form></div>";
					$first = false;
				}
				$this->bugfix_formsplits++;
				$form .= "<form id='cms_entry_form".$this->bugfix_formsplits."' action='' method='post'>";
			} else {
				foreach ($this->format as $field) {
					$height = max($height, $field['render_y'] + $field['render_dy']);
					$form .= $this->renderField($field);
				}
			}


			$form .= $this->renderSubmitButton();
			$form .= "</form>";

			if(!empty($this->tabs)) {
				echo "<ul class='tabs'>";
				$first = true;
				foreach($this->tabs as $tab) {
					if(isset($CMS_SESSION["tab_id"])) {
						$active = ($CMS_SESSION["tab_id"] == $tab["id"]) ;
					} else {
						$active = $first;
					}

					echo "<li class='tabs".($active ? " selected":"")."' id='tabs".$tab["id"]."'><div class='tl'></div><a href='#' onclick='load_tab(".$tab["id"].");return false;'>".$tab["name"]."</a><div class='tr'></div></li>";
					$first = false;
				}
				echo "</ul><div style='clear:both;'></div>";
			}

			if ($this->custom_rendering) {
				if ($CMS_SESSION['super_admin']) {
					echo "<div id='custom_render_popup' style='padding: 10px 10px 10px 71px; background: url(img/custom_rendering.jpg) 10px 6px no-repeat #f0f0f0; border: 1px dashed #58606e; margin: 0 0 20px 0;'>";
					echo "<b>This module is custom-rendered</b><br/><br/>Superadmins can move and resize fields to customize the layout of this form. To enable editing, please click below. Changes are automatically saved.<br/><br/>";
					echo "<input type='submit' value='Enable editing' onclick=\"color_dragresize(); dragresize.apply($('custom_rendering_container')); $('custom_render_popup').style.display = 'none'; return false;\" />";
					echo "</div>";
				}

				echo "<div id='custom_rendering_container' style='position: relative; height: ".($height + 100)."px;'>";
				echo $form;
				echo "</div>";

				if ($CMS_SESSION['super_admin']) {
					// custom rendering configuration
					echo "<script type='text/javascript'>

					//<![CDATA[

					// Using DragResize is simple!
					// You first declare a new DragResize() object, passing its own name and an object
					// whose keys constitute optional parameters/settings:

					dragresize = new DragResize('dragresize', { minWidth: 60, minHeight: 30, minLeft: 0, minTop: 0, gridSize: 15 } );

					// Optional settings/properties of the DragResize object are:
					//  enabled: Toggle whether the object is active.
					//  handles[]: An array of drag handles to use (see the .JS file).
					//  minWidth, minHeight: Minimum size to which elements are resized (in pixels).
					//  minLeft, maxLeft, minTop, maxTop: Bounding box (in pixels).

					// Next, you must define two functions, isElement and isHandle. These are passed
					// a given DOM element, and must 'return true' if the element in question is a
					// draggable element or draggable handle. Here, I'm checking for the CSS classname
					// of the elements, but you have have any combination of conditions you like:

					dragresize.isElement = function(elm)
					{
					 if (elm.className && elm.className.indexOf('drsElement') > -1) return true;
					};
					dragresize.isHandle = function(elm)
					{
					 if (elm.className && elm.className.indexOf('drsElement') > -1) return true;
					};

					// You can define optional functions that are called as elements are dragged/resized.
					// Some are passed true if the source event was a resize, or false if it's a drag.
					// The focus/blur events are called as handles are added/removed from an object,
					// and the others are called as users drag, move and release the object's handles.
					// You might use these to examine the properties of the DragResize object to sync
					// other page elements, etc.

					dragresize.ondragfocus = function() { };
					dragresize.ondragstart = function(isResize) { };
					dragresize.ondragmove = function(isResize) { };
					dragresize.ondragend = function(isResize) { save_custom_rendering(dragresize.element, dragresize.elmX, dragresize.elmY, dragresize.elmW, dragresize.elmH); };
					dragresize.ondragblur = function() { };

					// Finally, you must apply() your DragResize object to a DOM node; all children of this
					// node will then be made draggable. Here, I'm applying to the entire document.

					//]]>
					</script>";
				}
			} else {
				echo $form;
			}
		}

		private function wrap($f, $s) {
			if ($this->custom_rendering) {
				$properties = array(
									'id'    => 'dragresize_field_'.$f['id'],
									'class' => 'drsElement',
									'style' => "position: absolute; top: ".$f['render_y']."px; left: ".$f['render_x']."px; height: ".$f['render_dy']."px; width: ".$f['render_dx']."px;",
									);
				return "<div ".Tools::properties_html($properties)."><div style='width: 100%; height: 100%; overflow: hidden;'>".$s."</div></div>";
			} else {
				return $s;
			}
		}

		private function dechex2($n) {
			$h = dechex($n);
			if (strlen($h) == 1) return "0".$h;
			return $h;
		}

		private function isMac() {
			return (stristr($_SERVER['HTTP_USER_AGENT'], 'Macintosh') !== false);
		}

		private function renderField($field) {
			$values = $this->getValues($field);
			$mac = $this->isMac();

			if(isset($field['display_name']) && !empty($field['display_name'])) {
				$field['name'] = $field['display_name'];
			}

			switch ($field['form_element'])
			{
				case 'text': return $this->wrap($field, $this->renderTextField($field, $values));
					break;
				case 'color': return $this->wrap($field, $this->renderColorpicker($field, $values));
					break;
				case 'textarea': return $this->wrap($field, $this->renderTextArea($field, $values));
					break;
				case 'select': return $this->wrap($field, $this->renderSelect($field, $values));
					break;
				case 'checkbox': return $this->wrap($field, $this->renderCheckbox($field, $values));
					break;
				case 'htmltext': return $this->renderHTMLEditor($field, $values);
					break;
				case 'htmltext_fck': return $this->wrap($field, $this->renderFCKeditor($field, $values));
					break;
				case 'htmltext_aloha': return $this->wrap($field, $this->renderAlohaEditor($field, $values));
					break;	
				case 'htmlsource': return $this->wrap($field, $this->renderTextArea($field, $values));
					break;
				case 'image': return $mac ? $this->renderImage_HTML($field, $values) : $this->wrap($field, $this->renderImage($field, $values));
					break;
				case 'image_multi': return $this->renderImageMulti($field, $values);
					break;
				case 'file': return $this->wrap($field, $this->renderFile($field, $values));
					break;
				case 'time': return $this->wrap($field, $this->renderTime($field, $values));
					break;
				case 'date': return $this->wrap($field, $this->renderDate($field, $values));
					break;
				case 'label': return $this->wrap($field, $this->renderLabel($field, $values));
					break;
				case 'numeric': return $this->wrap($field, $this->renderNumeric($field, $values));
					break;
				case 'reference': return $this->wrap($field, $this->renderReference($field, $values));
					break;
				case 'reference_multi': return $this->wrap($field, $this->renderReferenceMulti($field, $values));
					break;
				case 'boolean': return $this->wrap($field, $this->renderBoolean($field, $values));
					break;
				case 'custom_text': return $this->wrap($field, $this->renderCustomHiddenInput($field, $values, $this->values));
					break;
				case 'custom_input': return $this->wrap($field, $this->renderCustomInput($field, $values));
					break;
				case 'range': return $this->wrap($field, $this->renderRange($field, $values));
					break;
				case 'slider': return $this->wrap($field, $this->renderSlider($field, $values));
					break;
				case 'location': return $this->wrap($field, $this->renderLocation($field, $values));
			}
		}

		/*
			FORM-ELEMENTS
			-------------
			below are functions that generate HTML for all form elements
		*/

		// HEADER (name + help-text)
		private function renderHeader($field) {
			global $CMS_SESSION;
			$f  = "<div style='".($this->custom_rendering ? "" : "margin-top: 18px;")." font-weight: bold; margin-bottom: 2px;'>".$field['name'];
			if ($field['multilingual']) $f .= " <span style='color: #aaa; font-style: italic; font-weight: normal;'>(".$CMS_SESSION['language']['language'].")</span> <img src='img/icons/multilingual.gif' style='vertical-align: middle; border: 1px solid #aaa;'/>";
			$f .= "</div>";
			if ($field['help_text'] != '') $f .= "<i>".nl2br(htmlspecialchars($field['help_text']))."</i><br/>";
			return $f;
		}

		// TEXTFIELD
		private function renderTextField($field, $values) {
			if(empty($values)) $values = array($field["default"]);

			$properties = array('type' => 'text',
								'style' => 'width: 95%;',
								'name'  => 'cmsform_field_'.$field['id'].'[]',
								'value' => htmlspecialchars($values[0]),
								);

			return $this->renderHeader($field)."<input ".Tools::properties_html($properties)." />";
		}

		// PLAIN HTML IMAGE UPLOAD
		private function renderImage_HTML($field, $values, $multi = false) {

			$f = '</form>';

			$s  = "<a href='#' style='float: right; margin-right: 15px;' onclick=\"this.innerHTML = this.innerHTML == 'Fit images' ? 'Standard size' : 'Fit images'; if ($('multi_image_container".$field['id']."').style.height == 'auto') { $('multi_image_container".$field['id']."').style.overflowY = 'scroll'; $('multi_image_container".$field['id']."').style.height = '120px'; } else { $('multi_image_container".$field['id']."').style.height = 'auto'; $('multi_image_container".$field['id']."').style.overflowY = 'hidden'; } return false;\">Fit images</a>";
			$s .= $this->renderHeader($field);
			$s .= "<div id='multi_image_container".$field['id']."' style='position: relative; padding: 1px; height: auto; width: 99%; margin-bottom: 15px; border: 1px solid #aaa; background-color: #f4f4f4; overflow: scroll; overflow-x: hidden;'>";
			list($overview, $javascript) = $this->renderImageMultiOverview($field['id'], $this->entry_id, 'large', $field['value_count']);
			$s .= $overview;
			$s .= "</div>";

			// may add another value?
			$may_add = sizeof($values) < $field['value_count'] || $field['value_count'] == 0;

			$s .= "<div id='image_uploader_".$field['id']."' style='margin-bottom: 30px; margin-top: 15px; display: ".(!$may_add ? 'none' : 'inline').";'>";

			$s .= "<form method='post' id='htmlupload_".$field['id']."' enctype='multipart/form-data' target='cms5_upload_iframe_".$field['id']."' action='core/upload_image.php?fid=".$field['id']."&sid=".session_id()."' >";
			if ($multi) {
				$s .= "<div style='font-style: italic; font-size: 10px; margin-bottom: 5px; color: #666;'>A multiple-files uploader is only available on a Microsoft Windows computer.</div>";
			}
			$s .= "<input id='cmsform_field_".$field['id']."' style='background: transparent; border: 0px;' type='file' onchange=\"$('htmlupload_".$field['id']."').submit(); $('uploading_indicator_".$field['id']."').style.display = '';\" name='Filedata' />";
			$s .= "<span id='uploading_indicator_".$field['id']."' style='display: none; margin-left: 20px;'><img src='img/loading_white.gif' style='position: relative; top: 1px; margin-right: 10px;' /> Uploading... </span>";
			$s .= "<iframe style='position: absolute; visibility: hidden;' onload=\"if ($('cmsform_field_".$field['id']."').value != '') { $('uploading_indicator_".$field['id']."').style.display = 'none'; uploadComplete(".$field['id']."); }\" name='cms5_upload_iframe_".$field['id']."' id='cms5_upload_iframe_".$field['id']."' src=''></iframe>";
			$s .= '</form>';

			$s .= "</div>";

			$s .= "<div id='image_uploader_warning_".$field['id']."' style='margin-bottom: 20px; font-style: italic; display: ".($may_add ? 'none' : 'inline').";'>";
			$s .= "No more images can be uploaded here (the maximum number of images is ".$field['value_count'].").";
			$s .= "</div>";

			$f .= $this->wrap($field, $s);

			$this->bugfix_formsplits++;
			$f .= "<form id='cms_entry_form".$this->bugfix_formsplits."' action='' method='post'>";

			return $f.$javascript;
		}

		// PLAIN HTML FILE UPLOAD
		private function renderFile_HTML($field, $values) {

			$f = '</form>';

			$s = "<a href='#' style='float: right; margin-right: 15px;' onclick=\"this.innerHTML = this.innerHTML == 'Fit files' ? 'Standard size' : 'Fit files'; if ($('multi_files_container".$field['id']."').style.height == 'auto') { $('multi_files_container".$field['id']."').style.overflowY = 'scroll'; $('multi_files_container".$field['id']."').style.height = '120px'; } else { $('multi_files_container".$field['id']."').style.height = 'auto'; $('multi_files_container".$field['id']."').style.overflowY = 'hidden'; } return false;\">Fit files</a>";
			$s .= $this->renderHeader($field);
			$s .= "<div id='multi_files_container".$field['id']."' style='position: relative; padding: 1px; height: auto; width: 99%; margin-bottom: 15px; border: 1px solid #aaa; background-color: #f4f4f4; overflow: scroll; overflow-x: hidden;'>";
			list($overview, $javascript) = $this->renderFilesOverview($field['id'], $this->entry_id, 'large', $field['value_count']);
			$s .= $overview;
			$s .= "</div>";

			// may add another value?
			$may_add = sizeof($values) < $field['value_count'] || $field['value_count'] == 0;

			$s .= "<div id='files_uploader_".$field['id']."' style='margin-bottom: 30px; margin-top: 15px; display: ".(!$may_add ? 'none' : 'inline').";'>";

			$s .= "<form method='post' id='htmlupload_".$field['id']."' enctype='multipart/form-data' target='cms5_upload_iframe_".$field['id']."' action='core/upload_file.php?fid=".$field['id']."&sid=".session_id()."' >";
			$s .= "<input id='cmsform_field_".$field['id']."' style='background: transparent; border: 0px;' type='file' onchange=\"$('htmlupload_".$field['id']."').submit(); $('uploading_indicator_".$field['id']."').style.display = '';\" name='Filedata' />";
			$s .= "<span id='uploading_indicator_".$field['id']."' style='display: none; margin-left: 20px;'><img src='img/loading_white.gif' style='position: relative; top: 1px; margin-right: 10px;' /> Uploading... </span>";
			$s .= "<iframe style='position: absolute; visibility: hidden;' onload=\"if ($('cmsform_field_".$field['id']."').value != '') { $('uploading_indicator_".$field['id']."').style.display = 'none'; fileUploadComplete(".$field['id']."); }\" name='cms5_upload_iframe_".$field['id']."' id='cms5_upload_iframe_".$field['id']."' src=''></iframe>";
			$s .= '</form>';

			$s .= "</div>";

			$s .= "<div id='file_uploader_warning_".$field['id']."' style='margin-bottom: 20px; font-style: italic; display: ".($may_add ? 'none' : 'inline').";'>";
			$s .= "No more files can be uploaded here (the maximum number of files is ".$field['value_count'].").";
			$s .= "</div>";

			$f .= $this->wrap($field, $s);

			$this->bugfix_formsplits++;
			$f .= "<form id='cms_entry_form".$this->bugfix_formsplits."' action='' method='post'>";

			return $f.$javascript;
		}

		// NUMERIC
		private function renderNumeric($field, $values) {
			if(empty($values)) $values = array($field["default"]);

			$properties = array('type' => 'text',
								'style' => 'width: 95%;',
								'name'  => 'cmsform_numeric_'.$field['id'].'[]',
								'value' => $values[0]
								);

			return $this->renderHeader($field)."<input ".Tools::properties_html($properties)." />";
		}

		// TEXTAREA
		private function renderTextArea($field, $values) {
			if(empty($values)) $values = array($field["default"]);

			$properties = array('rows' => '8',
								'style' => 'width: 95%;',
								'name'  => 'cmsform_field_'.$field['id'].'[]'
								);

			return $this->renderHeader($field)."<textarea ".Tools::properties_html($properties).">".htmlspecialchars($values[0])."</textarea>";
		}

		// SELECT
		private function renderSelect($field, $values) {
			if(empty($values)) $values = array($field["default"]);

			$f = $this->renderHeader($field);
			$f .= "<select name='cmsform_field_".$field['id']."[]'>";
			foreach ($field['allowed_values'] as $allowed_value) {
				$f .= "<option ".Tools::properties_html(array('value' => $allowed_value))." ".(in_array($allowed_value, $values) ? "selected" : "").">".$allowed_value."</option>";
			}
			$f .= "</select>";
			return $f;
		}

		// CHECKBOX
		private function renderCheckbox($field, $values) {
			$properties = array('type' => 'hidden',
								'value' => '1',
								'name'  => 'cmsform_checkbox_present_'.$field['id'].'[]');
			// header and a hidden inputfield (to communicate the presence of this checkbox range
			$f = $this->renderHeader($field)."<input ".Tools::properties_html($properties)." />";

			$properties = array('type' => 'checkbox',
								'style' => 'display: inline; margin-bottom: 0px;',
								'name'  => 'cmsform_checkbox_values_'.$field['id'].'[]');

			foreach ($field['allowed_values'] as $allowed_value) {
				$properties['value'] = $allowed_value;
				if (in_array($allowed_value, $values)) {
					$properties['checked'] = 'checked';
				} else {
					unset($properties['checked']);
				}
				$f .= "<div><input ".Tools::properties_html($properties)." />".$allowed_value."</div>";
			}
			return $f;
		}

		// HTML EDITOR
		private function renderHTMLEditor($field, $values) {
			global $CMS_ENV, $CMS_SESSION;

			$swf = $CMS_ENV['pxl_cms_url']."includes/richtext/RTE.swf";

			$validate_request = sha1($this->entry_id.$this->CMS->table().$field['cms_name']);
			$html = $CMS_ENV['pxl_cms_url']."core/renderHTML.php?language=".$CMS_SESSION['language']['id']."&entry_id=".$this->entry_id."&module=".$this->CMS->table()."&field=".$field['cms_name']."&validate=".$validate_request;

			$f = $this->renderHeader($field);

			$editor_object = pxl_activate_flash(
									373,
									332,
									$CMS_ENV['pxl_cms_url']."includes/richtext/RTE.swf",
									array(
										'id' => "cmsform_field_".$field['id'],
										'html' => $html
										));

			$f .= "<textarea id='cmsform_field_".$field['id']."' name='cmsform_field_".$field['id']."[]' style='visibility: hidden; position: absolute; width: 60%; height: 50px; font-size: 12px;'>".str_replace("<", "&lt;", str_replace(">", "&gt;", str_replace("&", "&amp;", file_get($html))))."</textarea>";

			// because of a STUPID bug in IE, which does not recognize the object by ID when placed inside a form,
			// we close the form before this upload object, and start a new one after...
			// there is no other way around, since ActiveX runs the problematic code :/
			$f .= '</form>';
			$f .= $this->wrap($field, $editor_object);
			$this->bugfix_formsplits++;
			$f .= "<form id='cms_entry_form".$this->bugfix_formsplits."' action='' method='post'>";
			// ...bah...

			return $f;
		}

		// FCK-Editor
		private function renderFCKeditor($field, $values) {
			global $CMS_SESSION, $CMS_EXTRA;
			
			$oFCKeditor = new FCKeditor('cmsform_field_'.$field['id'].'[]') ;
			$oFCKeditor->BasePath = 'includes/fckeditor/' ;
			if ($CMS_SESSION['super_admin']) {
				$oFCKeditor->ToolbarSet	= 'PXL_Admin' ;
			} else {
				$oFCKeditor->ToolbarSet	= 'PXL' ;
			}
			$oFCKeditor->Value = $values[0];
			$oFCKeditor->Height = isset($CMS_EXTRA['fck_width']) ? $CMS_EXTRA['fck_width'] : '300px';

			$register_fck_js = "<script type='text/javascript'>pxlcms_fckeditors.push('cmsform_field_".$field['id']."[]');</script>";

			return $register_fck_js.$this->renderHeader($field).$oFCKeditor->CreateHtml() ;
			
		}
		
		private function renderAlohaEditor($field, $values) {
			return $this->renderHeader($field) . "<textarea class=\"editable\" name=\"cmsform_field_{$field['id']}[]\" style=\"width: 750px; height: 300px; \">{$values[0]}</textarea><script type=\"text/javascript\">Aloha.jQuery('.editable').aloha();</script>";
		}

		// IMAGE
		private function renderImage($field, $values) {
			global $CMS_ENV, $CMS_EXTRA;

			if (!isset($CMS_EXTRA['max_image_size'])) {
				$CMS_EXTRA['max_image_size'] = 600*1024;
			}

			$f = $this->renderHeader($field);

			// since a limited # of images, always auto-height?
			// $f = "<a href='#' style='float: right; margin-right: 15px;' onclick=\"this.innerHTML = this.innerHTML == 'Fit pictures' ? 'Standard size' : 'Fit pictures'; if ($('multi_image_container".$field['id']."').style.height == 'auto') { $('multi_image_container".$field['id']."').style.overflowY = 'scroll'; $('multi_image_container".$field['id']."').style.height = '120px'; } else { $('multi_image_container".$field['id']."').style.height = 'auto'; $('multi_image_container".$field['id']."').style.overflowY = 'hidden'; } return false;\">Fit pictures</a>";

			$f .= "<div id='multi_image_container".$field['id']."' style='position: relative; padding: 1px; height: ".($field['value_count'] > 0 ? ($field['value_count'] * 21).'px' : 'auto')."; width: 95%; border: 1px solid #aaa; background-color: #f4f4f4; overflow: scroll; overflow-x: hidden;'>";
			list($overview, $javascript) = $this->renderImageMultiOverview($field['id'], $this->entry_id, 'large', $field['value_count']);
			$f .= $overview;
			$f .= "</div>";

			// may add another value?
			$may_add = sizeof($values) < $field['value_count'] || $field['value_count'] == 0;

			$f .= "<div id='image_uploader_".$field['id']."' style='margin-bottom: 20px; margin-top: 15px; display: ".(!$may_add ? 'none' : 'inline').";'>";

			// include flash file uploader

			$f .= pxl_activate_flash(
										100,
										45,
										$CMS_ENV['pxl_cms_url']."includes/uploading/upload_image.swf",
										array(
											'fid' => $field['id'],
											'postURL' => $CMS_ENV['pxl_cms_url']."core/upload_image.php",
											'finishJavascript' => 'uploadComplete',
											'sid' => session_id(),
											'maxFileSize' => $CMS_EXTRA['max_image_size']
											));

			$f .= "</div>";

			$f .= "<div id='image_uploader_warning_".$field['id']."' style='margin-bottom: 20px; font-style: italic; display: ".($may_add ? 'none' : 'inline').";'>";
			$f .= "No more images can be uploaded here (the maximum number of images is ".$field['value_count'].").";
			$f .= "</div>";
			$f .= $javascript;

			return $f;
		}

		// MULTI-IMAGE / GALLERY
		private function renderImageMulti($field, $values) {
			global $CMS_ENV, $CMS_EXTRA;

			if (!isset($CMS_EXTRA['max_image_size'])) {
				$CMS_EXTRA['max_image_size'] = 600*1024;
			}

			$suffix = array('', 'KB', 'MB', 'GB', 'TB');
			$s = 0;
			$size = $CMS_EXTRA['max_image_size'];
			while ($size > 1024) {
				$size /= 1024;
				$s++;
			}
			$mis = $size . $suffix[$s];

			$f = "<a href='#' style='float: right; margin-right: 15px;' onclick=\"this.innerHTML = this.innerHTML == 'Fit pictures' ? 'Standard size' : 'Fit pictures'; if ($('multi_image_container".$field['id']."').style.height == 'auto') { $('multi_image_container".$field['id']."').style.overflowY = 'scroll'; $('multi_image_container".$field['id']."').style.height = '120px'; } else { $('multi_image_container".$field['id']."').style.height = 'auto'; $('multi_image_container".$field['id']."').style.overflowY = 'hidden'; } return false;\">Fit pictures</a>";
			$f .= $this->renderHeader($field);
			$f .= "<div id='multi_image_container".$field['id']."' style='position: relative; padding: 1px; height: 120px; width: 99%; border: 1px solid #aaa; background-color: #f4f4f4; overflow: scroll; overflow-x: hidden; margin-bottom: 15px;'>";
			list($overview, $javascript) = $this->renderImageMultiOverview($field['id'], $this->entry_id);
			$f .= $overview;
			$f .= "</div>";

			// include flash file uploader
			$f .= '</form>';
			$f .= '<div class="upload_multiple">';
			$f .= '<div style="font-weight: bold; padding:5px 12px;background:#cacaca;">Upload new files</div>';
			$f .= "<ul id='image_progress_".$field["id"]."' class='image_uploads'>";
			$f .= "</ul><div style='clear:both;height:12px;'></div>";
			$f .= '<div style="padding:0 12px;">';
			$f .= "<div id='image_browse_button_".$field["id"]."'></div>";
			$f .= "<input type='submit' value='Upload' class='upload' onclick='swfu".$field["id"].".startUpload();return false;' style='display:none;' />";
			$f .= "<input type='submit' value='Cancel' class='cancel' onclick='swfu".$field["id"].".cancelQueue();return false;' style='display:none;' />";
			$f .= "<div style='clear:both;height:12px;'></div></div></div>";
			$f .= "<script type='text/javascript'>swfupload(".$field["id"].", '".session_id()."', '".$mis."');</script>";
			$this->bugfix_formsplits++;
			$f .= "<form id='cms_entry_form".$this->bugfix_formsplits."' action='' method='post'>";
			// ...bah...

			$f .= $javascript;

			return $f;
		}

		// CUSTOM HIDDEN INPUT
		private function renderCustomHiddenInput($field, $values, $entry) {
			$id = md5(rand(0, 99999));
			$properties = array('type' => 'hidden',
								'style' => '',
								'name'  => 'cmsform_field_'.$field['id'].'[]',
								'id' => $id,
								'value' => htmlspecialchars($values[0]),
								);
			ob_start();
			${'custom'} = array('id' => $id, 'properties' => $properties, 'field' => $field, 'values' => $values);
			if(!empty($field["custom_html"])) {
				include("fields/".$field["custom_html"]);
			}
			$custom = ob_get_clean();

			return $this->renderHeader($field)."<input ".Tools::properties_html($properties)." />".$custom;
		}

		// CUSTOM INPUT
		private function renderCustomInput($field, $values) {
			$id = md5(rand(0, 99999));
			$properties = array('type' => 'text',
								'style' => 'width: 95%',
								'name'  => 'cmsform_field_'.$field['id'].'[]',
								'id' => $id,
								'value' => htmlspecialchars($values[0]),
								);
			ob_start();
			${'custom'} = array('id' => $id, 'properties' => $properties, 'field' => $field, 'values' => $values);

			if(!empty($field["custom_html"])) {
				include("fields/".$field["custom_html"]);
			}
			$custom = ob_get_clean();
			return $this->renderHeader($field)."<input ".Tools::properties_html($properties)." />".$custom;
		}

		public function renderImageMultiOverview($field_id, $entry_id, $mode = 'small', $max_image_count = 0) {
			global $CMS_ENV;
			if ($entry_id == null) return array("<i>(no files uploaded yet)</i>", "<script type='text/javascript'>if ($('image_uploader_".$field_id."')) $('image_uploader_".$field_id."').style.display = ''; if ($('image_uploader_warning_".$field_id."')) $('image_uploader_warning_".$field_id."').style.display = 'none';</script>");

			$allowed_sizes = array('small' => array(), 'large' => array());
			if (!in_array($mode, array_keys($allowed_sizes))) $mode = 'small';


			$images = $this->CMS->getImages($field_id, $entry_id);
			$f = '';
			$s = '';

			if (sizeof($images)) {
				$f .= "<ul id='images_".$field_id."' class='image_list' style='width: 100%;'>";
				foreach ($images as $image) {
					$f .= "<li id='image_".$image['id']."' style='width: 100%; ".(count($images) > 1 ? "cursor: move;" : "")."'>";

					$f .= "<table border='0' cellspacing='0' cellpadding='0' style='width: 100%;'><tr>";
					$f .= "<td style='width: 230px;'>";
					$f .= "<img alt='[x]' title='Image: ".$image['file']."' src='uploads/pxl20_".$image['file']."' onclick=\"this.src = 'uploads/pxl80_".$image['file']."'; this.style.height = '80px'; this.style.width='80px';\" onmouseout=\"this.src = 'uploads/pxl20_".$image['file']."'; this.style.height = '15px'; this.style.width='15px';\" style='cursor: pointer; vertical-align: center; height: 15px; width: 15px; margin: 0 4px 0 2px;' />".Tools::shrink_text($image['file'], 35);
					$f .= "</td>";
					$f .= "<td width='240' style='padding: 0 15px 0 15px;'>";
					$f .= "<input type='text' name='image_caption_".$image['id']."' class='image_caption' onkeyup=\"this.style.color = '#666';\" onblur=\"return image_caption_input(this, ".$image['id'].");\" value=\"".str_replace('"', '&quot;', $image['caption'])."\" />";
					$f .= "</td>";
					$f .= "<td align='left'>";
					$f .= "<img style='margin-right: 5px; cursor: pointer;' alt='Delete image' title='Delete image' onclick=\"delete_image(".$image['id'].", ".$field_id.", ".$entry_id.");\" src='img/icons/delete.gif' >";
					$f .= "<a href='".$CMS_ENV['base_url_uploads'].$image['file']."' target='_blank'><img style='margin-right: 5px; cursor: pointer;' alt='Download file' title='Download file' src='img/icons/download.gif' ></a>";
					$f .= "</td>";
					$f .= "</tr></table>";

					$f .= "</li>";
				}
				$f .= "</ul>";
			}
			else {
				$f .= "<i>(no files uploaded yet)</i>";
			}

			$s .= "<script type='text/javascript'>";
			$s .= "Position.includeScrollOffsets = true;";
			// add parameter to create call to make container scrolls for the user at the edges: scroll: 'multi_image_container".$field_id."'
			if (count($images) > 1) $s .= "Sortable.create('images_".$field_id."', { tag: 'li', onUpdate: function() { sort_images(".$field_id.", Sortable.serialize('images_".$field_id."')); } } );";
			if ($max_image_count > 0 && $max_image_count <= count($images)) {
				$s .= "$('image_uploader_".$field_id."').style.display = 'none';";
				$s .= "$('image_uploader_warning_".$field_id."').style.display = '';";
			} else {
				$s .= "if ($('image_uploader_warning_".$field_id."')) $('image_uploader_warning_".$field_id."').style.display = 'none';";
				$s .= "if ($('image_uploader_".$field_id."')) $('image_uploader_".$field_id."').style.display = '';";
			}
			$s .= "if ($('cmsform_field_".$field_id."')) $('cmsform_field_".$field_id."').value = '';";
			$s .= "</script>";

			return array($f, $s);
		}

		public function renderFilesOverview($field_id, $entry_id, $max_file_count = 0) {
			global $CMS_ENV;

			if ($entry_id == null) return array("<i>(no files uploaded yet)</i>", "<script type='text/javascript'>if ($('file_uploader_warning_".$field_id."')) $('file_uploader_warning_".$field_id."').style.display = 'none'; if ($('file_uploader_".$field_id."')) $('file_uploader_".$field_id."').style.display = '';</script>");

			$files = $this->CMS->getFiles($field_id, $entry_id);
			$f = '';
			$s = '';

			if (sizeof($files)) {
				$f .= "<ul id='files_".$field_id."' class='files_list' style='width: 95%;'>";
				foreach ($files as $file) {
					$icon = file_exists('img/filetypes/'.$file['extension'].'.png') ? 'img/filetypes/'.$file['extension'].'.png' : 'img/icons/file.gif';
					$f .= "<li id='files_".$file['id']."' style='".(count($files) > 1 ? "cursor: move;" : "")."'>";

					$f .= "<table border='0' cellspacing='0' cellpadding='0'><tr><td style='width: 530px;'>";
					$f .= "<img alt='File: ".$file['file']."' src='".$icon."' style='position: relative; top: 2px; margin: 0 4px 0 2px;' />".$file['file'];
					$f .= "</td><td align='right'>";
					$f .= "<img style='margin-right: 5px; cursor: pointer;' alt='Delete file' title='Delete file' onclick=\"delete_file(".$file['id'].", ".$field_id.", ".$entry_id.");\" src='img/icons/delete.gif' >";
					$f .= "<a href='".$CMS_ENV['base_url_uploads'].$file['file']."' target='_blank'><img style='margin-right: 5px; cursor: pointer;' alt='Download file' title='Download file' src='img/icons/download.gif' ></a>";
					$f .= "</td></tr></table>";

					$f .= "</li>";
				}
				$f .= "</ul>";
			}
			else {
				$f .= "<i>(no files uploaded yet)</i>";
			}

			$s .= "<script type='text/javascript'>";
			$s .= "Position.includeScrollOffsets = true;";
			if (count($files) > 1) $s .= "Sortable.create('files_".$field_id."', { tag: 'li', onUpdate: function() { sort_files(".$field_id.", Sortable.serialize('files_".$field_id."')); } } );";
			if ($max_file_count > 0 && $max_file_count <= count($files)) {
				$s .= "$('file_uploader_".$field_id."').style.display = 'none';";
				$s .= "$('file_uploader_warning_".$field_id."').style.display = '';";
			} else {
				$s .= "if ($('file_uploader_warning_".$field_id."')) $('file_uploader_warning_".$field_id."').style.display = 'none';";
				$s .= "if ($('file_uploader_".$field_id."')) $('file_uploader_".$field_id."').style.display = '';";
			}
			$s .= "if ($('cmsform_field_".$field_id."')) $('cmsform_field_".$field_id."').value = '';";
			$s .= "</script>";

			return array($f, $s);
		}

		// FILE
		private function renderFile($field, $values) {
			global $CMS_ENV, $CMS_EXTRA;

			if (!isset($CMS_EXTRA['max_file_size'])) {
				$CMS_EXTRA['max_file_size'] = 600*1024;
			}

			$swf = $CMS_ENV['pxl_cms_url']."includes/uploading/upload_file.swf";
			$ids = 'fid='.$field['id'];
			$postURL = $CMS_ENV['pxl_cms_url']."core/upload_file.php";
			$finishJavascript = "fileUploadComplete";

			$src = $swf.'?'.$ids.'&amp;postURL='.$postURL.'&amp;finishJavascript='.$finishJavascript.'&amp;sid='.session_id();

			if ($field['value_count'] == 0) {
				$f = "<a href='#' style='float: right; margin-right: 15px;' onclick=\"this.innerHTML = this.innerHTML == 'Fit files' ? 'Standard size' : 'Fit files'; if ($('multi_files_container".$field['id']."').style.height == 'auto') { $('multi_files_container".$field['id']."').style.overflowY = 'scroll'; $('multi_files_container".$field['id']."').style.height = '120px'; } else { $('multi_files_container".$field['id']."').style.height = 'auto'; $('multi_files_container".$field['id']."').style.overflowY = 'hidden'; } return false;\">Fit files</a>";
			} else {
				$f = '';
			}
			$f .= $this->renderHeader($field);
			$f .= "<div id='multi_files_container".$field['id']."' style='position: relative; padding: 1px; height: ".($field['value_count'] > 0 ? ($field['value_count'] * 21).'px' : 'auto')."; width: 95%; border: 1px solid #aaa; background-color: #f4f4f4; overflow: scroll; overflow-x: hidden; margin-bottom: 15px;'>";

			list($overview, $javascript) = $this->renderFilesOverview($field['id'], $this->entry_id, $field['value_count']);
			$f .= $overview;
			$f .= "</div>";


			// may add another value?
			$may_add = sizeof($values) < $field['value_count'] || $field['value_count'] == 0;

			$f .= "<div id='file_uploader_".$field['id']."' style='margin-bottom: 20px; margin-top: 15px; display: ".(!$may_add ? 'none' : 'inline').";'>";

			// include flash file uploader

			$f .= pxl_activate_flash(
										100,
										45,
										$CMS_ENV['pxl_cms_url']."includes/uploading/upload_file.swf",
										array(
											'fid' => $field['id'],
											'postURL' => $CMS_ENV['pxl_cms_url']."core/upload_file.php",
											'finishJavascript' => 'fileUploadComplete',
											'sid' => session_id(),
											'maxFileSize' => $CMS_EXTRA['max_file_size']
											));

			$f .= "</div>";

			$f .= "<div id='file_uploader_warning_".$field['id']."' style='margin-bottom: 20px; font-style: italic; display: ".($may_add ? 'none' : 'inline').";'>";
			$f .= "No more files can be uploaded here (the maximum number of files is ".$field['value_count'].").";
			$f .= "</div>";
			$f .= $javascript;


			return $f;
		}

		// TIMESTAMP
		private function renderTime($field, $values) {
			if (sizeof($values) && $values[0] != 0) {
				$time = $values[0];
			}
			else {
				$time = time();
			}

			$f = "<input type='hidden' name='cmsform_field_".$field['id']."[]' value='".$time."'/>";
			return $f;
		}

		// COLORPICKER
		private function renderColorpicker($field, $values) {
			$f = $this->renderHeader($field);

			$size = 12;
			$rows = 0;
			$rows_gray = 44;
			$c_inc = 51;

			$f .= "<input type='text' value='".$values[0]."' id='colorcode".$field['id']."' onclick=\"new Effect.Appear('colorpicker_".$field['id']."', {from: 0.0, to: 1.0});\" name='cmsform_field_".$field['id']."[]' style='background-color: ".$values[0]."' />";
			$f .= "<div id='colorpicker_".$field['id']."' style='display: none;'>";
			$f .= "<table border='0' cellspacing='0' cellpadding='0'>";

			for ($r = 0; $r <= 255; $r += $c_inc) {

				$f .= "<tr height='".$size."'>";

				for ($g = 0; $g <= 255; $g += $c_inc) {
					for ($b = 0; $b <= 255; $b += $c_inc) {
						$color = $this->dechex2($r).$this->dechex2($g).$this->dechex2($b);
						$f .= "<td onclick=\"$('colorcode".$field['id']."').value = '#".$color."'; $('colorpicker_".$field['id']."').style.display = 'none';\" onmouseover=\"$('colorcode".$field['id']."').style.backgroundColor = '#".$color."';\" style='font-size: 1px; background-color: #".$color."' width='".$size."'>&nbsp;</td>";
					}
				}

				// grayscale
				for ($k = 0; $k < 6; $k++) {
					$gray = $this->dechex2($rows * $rows_gray + ($k * floor($rows_gray / 6)));
					$color = $gray.$gray.$gray;
					$f .= "<td onclick=\" $('colorcode".$field['id']."').value = '#".$color."'; $('colorpicker_".$field['id']."').style.display = 'none';\" onmouseover=\"$('colorcode".$field['id']."').style.backgroundColor = '#".$color."';\" style='font-size: 1px; background-color: #".$color."' width='".$size."'>&nbsp;</td>";
				}

				$f .= "</tr>";
				$rows++;
			}
			$f .= "</table>";
			$f .= "<a href='#' style='color: black;' onclick=\"$('colorcode".$field['id']."').style.backgroundColor = $('colorcode".$field['id']."').value; $('colorpicker_".$field['id']."').style.display = 'none'; return false;\">Cancel</a>";
			$f .= "</div>";

			return $f;
		}

		// DATE
		private function renderDate($field, $values) {

			$ret = $this->renderHeader($field);

			$default = strlen($field['default']) ? $field['default'] : $field['options']->default;
			$value = $values && $values[0] ? $values[0] : $default;

			//need autoupdating?
			if ($field['options']->auto_update == 1 || ($field['options']->auto_update == 'create' && !$values[0])) {
				$value = time();
			}

			if ($value == 'now') $value = time();
			if ($value == 'null') $value = null;

			if ($field['options']->editable) {
				$properties = array(
					'style'		=> 'width: 95%;',
					'class'		=> $field['options']->include_time ? 'pxl_datetimepicker' : 'pxl_datepicker',
					'name'		=> 'cmsform_field_'.$field['id'].'[]',
					'value'		=> $value
				);
				$ret .= '<input '.Tools::properties_html($properties).' />';
			} else {
				$properties = array(
					'name'		=> 'cmsform_field_'.$field['id'].'[]',
					'value'		=> $value,
					'type'		=> 'hidden'
				);
				$ret .= '<input '.Tools::properties_html($properties).' /><span>'.($value ? date(($field['options']->include_time ? 'd-m-Y H:i' : 'd-m-Y'), $value) : '').'</span>';
			}
			return $ret;
		}

		// SLIDER
		private function renderSlider($field, $values) {
			$ret = $this->renderHeader($field);
			$options = $field['options'];

			$min = $options ? $options->min : 0;
			$max = $options ? $options->max : 10;
			$step = $options ? $options->step : 1;
			$value = ($values && $values[0]) ? $values[0] : ($field['default'] ? $field['default'] : (($min+$max)/2));

			$properties = array(
				'type'			=> 'range',
				'style'			=> 'width: 400px; display: inline-block;',
				'class'			=> 'range',
				'id'				=> 'input_'.$field['id'].'[]',
				'name'			=> 'input_'.$field['id'].'[]',
				'value'			=> $value,
				'min'				=> $min,
				'max'				=> $max,
				'step'			=> $step,
				'onchange'		=> "$('cmsform_field_".$field['id']."[]').value = this.value;"
			);
			$ret .= '<span>'.$min.'</span>';
			$ret .= '<input'.Tools::properties_html($properties).' />';
			$ret .= '<span>'.$max.'</span>';
			$ret .= '<input
							type="text"
							id="cmsform_field_'.$field['id'].'[]"
							name="cmsform_field_'.$field['id'].'[]"
							value="'.$value.'"
							class="sliderhelper"
							style="
								display: inline;
								width: 40px;
								margin-left: 10px;
							"
							rel="input_'.$field['id'].'[]"
						/>';
			return $ret;
		}

		//RANGE
		private function renderRange($field, $values) {
			$ret = $this->renderHeader($field);
			$options = $field['options'];

			$min = $options ? $options->min : 0;
			$max = $options ? $options->max : 10;
			$step = $options ? $options->step : 1;
			$value = ($values && $values[0]) ? $values[0] : ($field['default'] ? $field['default'] : null);

			if ($value) {
				$value = json_decode($value);
				$vmin = $value->min;
				$vmax = $value->max;
			} else {
				$vmin = $min;
				$vmax = $max;
			}

			$ret .= '<span>'.$min.'</span>';
			$ret .= '<span class="faux-range" style="width: 400px;" data-slider-min="'.$min.'" data-slider-max="'.$max.'" data-slider-step="'.$step.'">';
			$ret .= '<span class="faux-slider-dot min" rel="cmsform_field_'.$field['id'].'_min[]"></span>';
			$ret .= '<span class="faux-range-filler"></span>';
			$ret .= '<span class="faux-slider-dot max" rel="cmsform_field_'.$field['id'].'_max[]"></span>';
			$ret .= '</span>';
			$ret .= '<span>'.$max.'</span>';
			$ret .= '<br />';
			$properties = array(
				'type'	=> 'text',
				'id'		=> 'cmsform_field_'.$field['id'].'_min[]',
				'name'	=> 'cmsform_field_'.$field['id'].'_min[]',
				'rel'		=> 'min_'.$field['id'].'',
				'value'	=> $vmin,
				'style'	=> 'display: inline-block; width: 40px; margin-left: 26px;',
				'class'	=> 'range_min'
			);
			$ret .= '<input '.Tools::properties_html($properties).' />';
			$properties = array(
				'type'	=> 'text',
				'id'		=> 'cmsform_field_'.$field['id'].'_max[]',
				'name'	=> 'cmsform_field_'.$field['id'].'_max[]',
				'rel'		=> 'max_'.$field['id'].'',
				'value'	=> $vmax,
				'style'	=> 'display: inline-block; width: 40px; margin-left: 316px;',
				'class'	=> 'range_max'
			);
			$ret .= '<input '.Tools::properties_html($properties).' />';
			$ret .= '<div style="clear: both;"></div>';

			return $ret;
		}

		private function renderLocation($field, $values) {
			global $CMS_ENV;

			$lat = 52.388251;
			$lng =  4.642679; //LatLng for PXL ;)

			$value = ($values && $values[0]) ? $values[0] : ($field['default'] ? $field['default'] : null);
			if ($value) {
				$value = json_decode($value);
				if ($value && $value->lat && $value->lng) {
					$lat = $value->lat;
					$lng = $value->lng;
				}
			}


			$ret = $this->renderHeader($field);
			$fid = $field['id'];
			$ret .= '<label for="loc_search_'.$fid.'">Zoek:</label><input class="location_search" type="search" name="loc_search_'.$fid.'" id="loc_search_'.$fid.'" data-map="map_'.$fid.'" />';
			$ret .= '<input type="button" value="Zoek" onclick="PXLCMS.LocationField.search($(\'loc_search_'.$fid.'\').value, $(\'map_'.$fid.'\').gmap);" class="loc_search_btn" />';

			$properties = array(
				'id'			=> 'map_'.$fid,
				'class'		=> 'location_map',
				'data-lat'	=> 'cmsform_field_'.$fid.'_lat[]',
				'data-lng'	=> 'cmsform_field_'.$fid.'_lng[]'
			);
			if ($field['options']->icon) {
				$properties['data-icon'] = $CMS_ENV['base_url'].$field['options']->icon;
			}
			$ret .= '<div '.Tools::properties_html($properties).'></div>';

			$ret .= '<label for="cmsform_field_'.$fid.'_lat[]">Lat:</label>';
			$properties = array(
				'class'		=> 'location_lat',
				'type'		=> 'text',
				'name'		=> 'cmsform_field_'.$fid.'_lat[]',
				'id'			=> 'cmsform_field_'.$fid.'_lat[]',
				'data-map'	=> 'map_'.$fid,
				'value'		=> $lat
			);
			$ret .= '<input  '.Tools::properties_html($properties).' />';
			$ret .= '<label for="cmsform_field_'.$fid.'_lng[]">Lng:</label>';
			$properties = array(
				'class'		=> 'location_lng',
				'type'		=> 'text',
				'name'		=> 'cmsform_field_'.$fid.'_lng[]',
				'id'			=> 'cmsform_field_'.$fid.'_lng[]',
				'data-map'	=> 'map_'.$fid,
				'value'		=> $lng
			);
			$ret .= '<input  '.Tools::properties_html($properties).' />';
			return $ret;
		}

		// BOOLEAN
		private function renderBoolean($field, $values) {
			global $CMS_EXTRA;
			if(empty($values)) $values = array($field["default"]);

			$f = $this->renderHeader($field);
			if(isset($CMS_EXTRA["boolean_as_checkbox"]) && $CMS_EXTRA["boolean_as_checkbox"]) {
				$f .= "<input type='checkbox' name='cmsform_field_".$field['id']."[]' ".(in_array(1, $values) ? "checked='checked'" : "")." value='1' />";
				$f .= "<input type='hidden' name='cmsform_field_".$field['id']."[]' value='0' />";
			} else {
				$f .= "<select name='cmsform_field_".$field['id']."[]'>";
				$f .= "<option value='0' ".(in_array(0, $values) ? "selected" : "").">No</option>";
				$f .= "<option value='1' ".(in_array(1, $values) ? "selected" : "").">Yes</option>";
				$f .= "</select>";
			}
			return $f;
		}

		// LABEL
		private function renderLabel($field, $values) {
			global $CMS_SESSION;
			if ($CMS_SESSION['super_admin']) {
				$field['name'] .= ' [label]';
				return $this->renderTextField($field, $values);
			} else {
				$f = "<b style='font-size: 200%;'>".$values[0]."</b>";
				if ($field['help_text'] != '') $f .= "<i>".$field['help_text']."</i><br/>";
				return $f;
			}
		}

		// REFERENCE
		private function renderReference($field, $values) {
			global $CMS_SESSION;

			$hidden_props = array('type' => 'hidden',
								'style' => 'width: 95%;',
								'name'  => 'cmsform_field_'.$field['id'].'[]',
								);

			if ($field['refers_to_module'] > 0 && $field['refers_to_module'] == $CMS_SESSION['user']['ref_filter_module_id']) {
				$hidden_props['value'] = $CMS_SESSION['user']['ref_filter_entry_id'];
				return "<input ".Tools::properties_html($hidden_props)." />";
			}

			if (is_array($CMS_SESSION['categories_simulator']) && count($CMS_SESSION['categories_simulator'])) {
				$filter = $CMS_SESSION['categories_simulator'][count($CMS_SESSION['categories_simulator'])-1];

				if ($CMS_SESSION['cms_state'] != 'edit' && $filter['field_id'] == $field['id']) {
					$hidden_props['value'] = $filter['entry_id'];
					return "<input ".Tools::properties_html($hidden_props)." />";
				}
			}

			$field['value_count'] = 1;
			return $this->renderReferenceMulti($field, $values);
		}

		// render function for overview
		public function renderReferenceOverview($entry, $field_id, $move = true) {
			$f  = "<div style='".($entry['e_active'] ? '' : 'color: #999;')." background: #fff; border: 1px solid #aaa; ".($move ? 'cursor: move;' : '')." overflow: hidden; padding: 2px; margin-bottom: 2px; height: 16px;'>";
			$f .= "<img src='img/icons/delete.gif' style='float: left; margin-right: 5px; cursor: pointer;' onclick=\"if (confirm('Are you sure you want to remove this relation?')) { ajax_ref_remove(this, ".$field_id."); }\" />";
			$f .= "<input type='hidden' name='cmsform_field_".$field_id."[]' value='".$entry['id']."' />";
			$f .= $entry['_identifier'];
			$f .= "</div>";
			return $f;
		}

		// REFERENCE 1:N
		private function renderReferenceMulti($field, $values) {
			global $CMS_SESSION;

			if (!$field['refers_to_module']) {
				return "<div style='margin-top: 20px; color: white; background: red; padding: 2px; width: 150px; text-align: center;'>[CMS configuration error]</div>&raquo; Multi-reference field '<span style='color: gray;'>".$field['name']."</span>' points to module <i>NULL</i><br/>&raquo; The field is therefore not rendered here<br/>&raquo; Please notify your Pixelindustries developer<br/><br/>";
			}

			$f = $this->renderHeader($field);

			$REF_CMS = new CMS();
			$REF_CMS->setModule($field['refers_to_module']);
			$REF_CMS->recursive = 0;
			$REF_CMS->generate_identifier = true;
			$REF_CMS->active_entries_only = false;

			$fields = $REF_CMS->fields();
			foreach ($fields as $fi) {
				if ($fi['refers_to_module'] > 0 && $fi['refers_to_module'] == $CMS_SESSION['user']['ref_filter_module_id'] && $fi['field_type_id'] == 16) {
					$usr_ref_filter = $fi;
				}
			}

			if (is_array($usr_ref_filter)) {
				$REF_CMS->conditions = "`".$usr_ref_filter['cms_name']."` = '".$CMS_SESSION['user']['ref_filter_entry_id']."'";
				$render_with_search = false;
			} else {
				$render_with_search = $REF_CMS->row_count() >= 30;
			}

			if($field["field_type_id"] == 27) {

				$references = $REF_CMS->getEntries();
				$f .= "<table id='source_reference_".$field['id']."'><tr>";
				$counter = 1;

				foreach ($references as $ref) {
					$f .= "<td><input name='cmsform_field_".$field['id']."[]' type='checkbox' value='".$ref['id']."' ".(in_array($ref['id'], $values) ? 'checked' : '')." style='display:inline;' />".$ref['_identifier']."</td>".($counter % 5 == 0 ? "</tr><tr>" : "");
					$counter++;
				}

				$f .= "</tr></table>";

			} else {

				if ($render_with_search)
				{
					$f .= "<div style='background: #f0f0f0; border: 1px solid #ccc; width: 95%; margin-bottom: 15px;'>";
						$f .= "<div style='padding: 10px;'>";
							$f .= "<input type='text'   name='ref_search_".$field['id']."' id='ref_search_".$field['id']."' style='width: 200px; margin-right: 10px; display: inline;' />";
							$f .= "<input type='submit' value='Search' onclick=\"ajax_ref_search($('ref_search_".$field['id']."').value, ".$field['refers_to_module'].", 'ref_search_results_".$field['id']."', ".$field['id']."); return false;\" style='display: inline; position: relative; top: 2px;' />";
							$f .= "<div id='ref_search_results_".$field['id']."'></div>";
							$f .= "<div id='ref_overview_".$field['id']."'>";
								if (count($values)) {
									$references = $REF_CMS->getEntries($values);
									foreach ($values as $value) {
										$f .= $this->renderReferenceOverview($references[$value], $field['id'], ($field["field_type_id"] != 26));
									}
								}
							$f .= "</div>";
							if($field["field_type_id"] != 26) {
								$f .= "<script type='text/javascript'>Sortable.create('ref_overview_".$field['id']."', { tag: 'div' } );</script>";
							}
							$f .= "<div style='display: none;' id='ref_processing_".$field['id']."'>Your action is being processed...</div>";
						$f .= "</div>";
					$f .= "</div>";
				}
				else
				{

					$references = $REF_CMS->getEntries();

					if ($field['value_count'] == 0) {

						// render "add references" part
						$f .= "<div id='source_reference_".$field['id']."' style='display: none;'>";
						$f .= "<select name='cmsform_field_".$field['id']."[]' style='width: 100%; overflow: hidden;'>";
						$f .= "<option value=''>-</option>";
						foreach ($references as $ref) {
							$f .= "<option style='".($ref['e_active'] ? '' : 'color: #999;')."' value='".$ref['id']."' ".($ref['id'] == $values[$i] ? 'selected' : '').">".$ref['_identifier']."</option>";
						}
						$f .= "</select>";
						$f .= "</div>";

						// render existing references
						for ($i = 0; $i < count($values); $i++) {
							$f .= "<select name='cmsform_field_".$field['id']."[]' style='width: 100%; overflow: hidden;'>";
							$f .= "<option value=''>-</option>";
							foreach ($references as $ref) {
								$f .= "<option style='".($ref['e_active'] ? '' : 'color: #999;')."' value='".$ref['id']."' ".($ref['id'] == $values[$i] ? 'selected' : '').">".$ref['_identifier']."</option>";
							}
							$f .= "</select>";
						}

						$f .= "<a href='#' onclick=\"new Insertion.Before(this, $('source_reference_".$field['id']."').innerHTML); return false;\">&raquo; add reference</a>";

					} else {

						for ($i = 0; $i < $field['value_count']; $i++) {
							$f .= "<select name='cmsform_field_".$field['id']."[]' style='width: 100%; overflow: hidden;'>";
							$f .= "<option value=''>-</option>";
							foreach ($references as $ref) {
								$f .= "<option style='".($ref['e_active'] ? '' : 'color: #999;')."' value='".$ref['id']."' ".($ref['id'] == $values[$i] ? 'selected' : '').">".$ref['_identifier']."</option>";
							}
							$f .= "</select>";
						}
					}
				}
			}

			$f .= "<input type='hidden' name='cmsform_field_".$field['id']."[]' />";

			return $f;
		}

		// SUBMIT
		private function renderSubmitButton() {
			global $CMS_SESSION;

			$properties = array('type' => 'submit',
								'onclick'  => 'saveEntry(); return false;',
								'value' => 'Save entry',
								'style' => 'float: left; margin-top: 20px;'
								);

			if ($this->custom_rendering) {
				$properties['style'] .= 'position: absolute; bottom: 30px;';
			}

			$save   = "<input ".Tools::properties_html($properties)." />";
			$cancel = '';

			$properties['onclick'] = 'refresh(); return false;';
			$properties['value']   = 'Cancel';
			$properties['style']   = 'float: left; margin-left: 20px; margin-top: 20px;';

			if ($this->custom_rendering) {
				$properties['style'] .= 'position: absolute; bottom: 30px; left: 110px;';
			}
			$cancel = "<input ".Tools::properties_html($properties)." />";

			$mid = '';
			if ($CMS_SESSION['super_admin']) $mid = "<span style='float: right; color: #fff;'>".$this->module_id."</span>";

			return "<div style='clear: both;'></div>".$save.$cancel.$mid;
		}
	}

?>
