<?php
	
	class Form {
		
		var $id = null;
		var $field_prefix = 'pxl_f_';
		var $auto_enable = false;
		var $allow_auto_complete = false;
		var $warning = '';
		
		public function __construct() {
			$this->new_id();
		}
		
		public function start($properties = array()) {
			$this->new_id();
			if (!isset($properties['id'])) {
				$properties['id'] = $this->id;
			}
			if (!isset($properties['onsubmit'])) {
				$properties['onsubmit'] = "process_form(this); return false;";
			}
			if ($this->warning != '') {
				$properties['onsubmit'] = "if (confirm('".htmlentities($this->warning, ENT_QUOTES)."')) { ".$properties['onsubmit']." } else { return false; }";
			}
			if (!$this->allow_auto_complete) $properties['autocomplete'] = 'off';
			echo "<form ".Tools::properties_html($properties).">";
		}
		
		private function new_id() {
			$this->id = 'form_'.mt_rand();
		}
		
		public function stop() {
			echo "</form>";
		}
		
		public function text($name, $value, $properties = array()) {
			$properties['name']    = $this->field_prefix.$name;
			$properties['value']   = $value;
			$properties['type']    = 'text';
			if ($this->auto_enable) {
				if (!isset($properties['onkeyup'])) $properties['onkeyup'] = '';
				$properties['onkeyup'] .= "$('submit_".$this->id."').disabled = false;";
			}
			return "<input ".Tools::properties_html($properties)." />";
		}
		
		public function hidden($name, $value, $properties = array(), $invisible = false) {
			$properties['name']    = ($invisible ? '' : $this->field_prefix).$name;
			$properties['value']   = $value;
			$properties['type']    = 'hidden';
			return "<input ".Tools::properties_html($properties)." />";
		}
		
		public function textarea($name, $value, $properties = array()) {
			$properties['name']     = $this->field_prefix.$name;
			if ($this->auto_enable) {
				if (!isset($properties['onkeyup'])) $properties['onkeyup'] = '';
				$properties['onkeyup'] .= "$('submit_".$this->id."').disabled = false;";
			}
			return "<textarea ".Tools::properties_html($properties).">".$value."</textarea>";
		}
		
		/*
			CHECKBOX
			--------
			$checked = set to true to start this checkbox in a checked state
		*/
		public function checkbox($name, $value, $properties = array(), $checked = false) {
			$properties['name']     = $this->field_prefix.$name;
			$properties['type']     = 'checkbox';
			$properties['value']     = $value;
			if ($this->auto_enable) {
				if (!isset($properties['onclick'])) $properties['onclick'] = '';
				$properties['onclick'] .= "$('submit_".$this->id."').disabled = false;";
			}
			return "<input ".Tools::properties_html($properties)." ".($checked ? 'checked' : '')." />";
		}
		
		/*
			SELECT BOX / DROPDOWN
			---------------------
			$value        = current value (should be part of:)
			$set          = an associative array of the possible values (array-keys are values, array-values are textual descriptions)
			$emptyAllowed = when set to true, an empty option will be included (no value)
		*/
		public function select($name, $value, $set, $properties = array(), $emptyAllowed = false) {
			$properties['name']      = $this->field_prefix.$name;
			if ($this->auto_enable) {
				if (!isset($properties['onchange'])) $properties['onchange'] = '';
				$properties['onchange'] .= "$('submit_".$this->id."').disabled = false;";
			}
			$select = "<select ".Tools::properties_html($properties).">";
			if ($emptyAllowed) $select .= "<option value=''></option>";
			foreach ($set as $v => $d) {
				$select .= "<option ".($v == $value ? 'selected' : '')." ".Tools::properties_html(array('value' => $v)).">".$d."</option>";
			}
			$select .= "</select>";
			return $select;
		}
		
		public function submit($value, $properties = array()) {
			if (!isset($properties['id'])) $properties['id'] = 'submit_'.$this->id;
			$properties['value']    = $value;
			$properties['type']     = 'submit';
			if (!isset($properties['style'])) $properties['style'] = '';
			$properties['style']    .= 'display: inline;';
			if ($this->auto_enable) $properties['disabled'] = 'disabled';
			
			return "<input ".Tools::properties_html($properties)." />";
		}
		
		public function image($src, $properties = array()) {
			$properties['src']      = $src;
			$properties['type']     = 'image';
			return "<input ".Tools::properties_html($properties)." />";
		}
		
		public function key($k) {
			if (substr($k, 0, strlen($this->field_prefix)) == $this->field_prefix) {
				return substr($k, strlen($this->field_prefix));
			} else {
				return false;
			}
		}
		
		public function fetch_submit() {
			$submit = array();
			foreach ($_POST as $key => $value) {
				$key = $this->key($key);
				if ($key === false) continue;
				$submit[$key] = $value;
			}
			return $submit;
		}
	}
	
?>