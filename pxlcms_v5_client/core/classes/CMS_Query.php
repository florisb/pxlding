<?php
	
	class CMS_Query {
		
		// CONFIGURATION
		// ======================================
		// target
		public $module_id                = null;
		public $category_id              = -1;
		public $entry_id                 = null;
		// conditions
		public $conditions               = null;
		public $active_entries_only      = true;
		// pagination/subsetting
		public $start                    = null;
		public $limit                    = null;
		public $find_total_count         = false;
		public $total_count              = null;
		public $sorting                  = '';
		// references
		public $recursive                = 1;
		public $references               = array();
		public $load_passive_references  = false;
		public $load_files               = true;
		// formatting
		public $number_format            = '2,.';
		public $date_format              = 'd-m-Y @ H:i:s';
		public $encoding                 = 'UTF-8'; // not effective yet
		// value-formatting-settings
		public $format                   = true;  // format at all?
		public $xml_safe                 = false; // use CDATA tags to isolate html?
		public $no_br                    = false;
		public $debug                    = false;
		public $language                 = null;
		
		protected $processed             = false;

		
		// INTERNAL
		// =======================================
		protected $error                 = false;
		protected $errorMessage          = '';
		protected $cms                   = null;
		protected $fields                = array();
		
		// dynamically translate documented properties to obfuscated properties
		public function __set($key, $value) {
			global $obfuscator_variable_translation_table;
			$this->{$obfuscator_variable_translation_table[$key]} = $value;
		}
		public function __get($key) {
			global $obfuscator_variable_translation_table;
			return $this->{$obfuscator_variable_translation_table[$key]};
		}
		
		function xml_access($mid) {
			return $this->cms->xml_access($mid);
		}
		
		function processed() {
			return $this->processed;
		}
		
		function __construct(&$cms = null) {
			if ($cms === null) $cms = new CMS();
			$this->cms = $cms;
		}
		
		function error() {
			return array('error' => $this->error, 'message' => $this->errorMessage);
		}
		
		function hasError() {
			return $this->error;
		}
		
		function wrap_cdata($s) {
			return "<![CDATA[".$s."]]>";
		}
		
		function lifetime() {
			return $this->cms->lifetime();
		}
		
		function languages() {
			global $db_connection;
			$languages = array();
			$this->cms->debug = $this->debug;
			$q = "SELECT * FROM `".$this->cms->tables_prefix."languages` WHERE `available` = 1 ORDER BY `default` DESC, `common` DESC";
			
			$langs = CMS_DB::mysql_query($q);
			if ($this->cms->debug) {
				$this->cms->logger->log_query($q, $langs, 'CMS_Query.php line '.__LINE__);
			}
			while ($lang = mysql_fetch_assoc($langs)) {
				$languages[] = $lang;
			}
			return $languages;
		}
		
		function categories() {
			global $db_connection;
			$c = array();
			$pointers = array();
			$this->cms->debug = $this->debug;
			$q = "SELECT * FROM `".$this->cms->tables_prefix."categories` WHERE `module_id` = ".$this->module_id." ORDER BY `depth` ASC, `position` ASC";
			$categories = CMS_DB::mysql_query($q);
			
			if ($this->cms->debug) {
				$this->cms->logger->log_query($q, $categories, 'CMS_Query.php line '.__LINE__);
			}
			
			while ($category = mysql_fetch_assoc($categories)) {
				$category = array_map('htmlentities', $category);
				if ($this->xml_safe) {
					$category['description'] = $this->wrap_cdata($category['description']);
					$category['name'] = $this->wrap_cdata($category['name']);
				}
				if ($category['id'] == $category['parent_category_id']) {
					$pointers[$category['id']] =& $c[$category['id']];
					$c[$category['id']]['category'] = $category;
					$c[$category['id']]['children'] = array();
				} else {
					$pointers[$category['parent_category_id']]['children'][$category['id']] = array('category' => $category, 'children' => array());
					$pointers[$category['id']] =& $pointers[$category['parent_category_id']]['children'][$category['id']];
				}
			}
			
			if ($this->cms->debug) {
				echo $this->cms->showLog();
			}
			
			return $c;
		}
		
		function fields($extended = false) {
			$this->cms->debug = $this->debug;
			$this->cms->setModule($this->module_id);
			$fields = $this->cms->fields();
			if ($extended) {
				return $fields;
			} else {
				$fds = array();
				foreach ($fields as $f) {
					$fds[] = $f['cms_name'];
				}
				return $fds;
			}
		}

		function entries() {
			if (!$this->module_id) {
				$this->error = 1;
				$this->errorMessage = "Invalid module_id";
				return;
			}
			
			$this->cms->sorting                 = $this->sorting;
			$this->cms->recursive               = $this->recursive;
			$this->cms->active_entries_only     = $this->active_entries_only;
			$this->cms->load_passive_references = $this->load_passive_references;
			$this->cms->references              = $this->references;
			$this->cms->debug                   = $this->debug;
			$this->cms->conditions              = $this->conditions;
			$this->cms->category_id             = $this->category_id;
			$this->cms->find_total_count        = $this->find_total_count;
			$this->cms->language                = $this->language;
			$this->cms->load_files              = $this->load_files;
			$this->cms->generate_identifier     = false;
			
			$this->cms->setModule($this->module_id);
			
			if ($this->limit) {
				if ($this->start) {
					$this->cms->limit_sql = 'LIMIT '.$this->start.', '.$this->limit;
				} else {
					$this->cms->limit_sql = 'LIMIT '.$this->limit;
				}
			} else if ($this->start) {
				$this->cms->limit_sql = 'LIMIT '.$this->start.', 999999';
			}
			
			if ($this->entry_id == null || is_array($this->entry_id)) {
				$entries = $this->cms->getEntries($this->entry_id);
			} else if ($entry_id >= 0) {
				$entries = $this->cms->getEntry($this->entry_id);
			} 
			
			// return here if no formatting is desired...
			if (!$this->format) return $entries;
			
			// ... otherwise, value-formatting-preprocessing!
			$this->fields[$this->cms->module_id] = $this->cms->fields();
			$this->preformat($entries, $this->fields[$this->cms->module_id]);
			foreach ($entries as &$entry) {
				if (is_array($entry['_referenced'])) {
					foreach ($entry['_referenced'] as $module_id => &$values) {
						if (!isset($this->fields[$module_id])) {
							$this->cms->setModule($module_id);
							$this->fields[$module_id] = $this->cms->fields();
						}
						$this->preformat($values, $this->fields[$module_id]);
					}
				}
			}
			
			if ($this->cms->debug) {
				echo $this->cms->showLog();
			}
			
			$this->total_count = $this->cms->total_count;
			
			$this->processed = true;
			return $entries;
		}
		
		// preprocess entries for value-formatting
		function preformat(&$entries, $f) {
			foreach ($entries as &$entry) {
				foreach ($f as $field) {
					switch ($field['form_element'])
					{
						case 'textarea':
							if (!$this->no_br) {
								$entry[$field['cms_name']] = nl2br(htmlspecialchars($entry[$field['cms_name']]));
							} else {
								$entry[$field['cms_name']] = str_replace("\r", "", htmlspecialchars($entry[$field['cms_name']]));
							}
							if ($this->xml_safe) $entry[$field['cms_name']] = $this->wrap_cdata($entry[$field['cms_name']]);
							break;
							
						case 'htmltext':
							$out = nl2br(str_replace("&apos;", "'", str_replace("&amp;", "&", str_replace("&lt;", "<", str_replace("&gt;", ">", $entry[$field['cms_name']])))));
							$out = str_replace("<TEXTFORMAT LEADING=\"2\">", "", $out);
							$out = str_replace("</TEXTFORMAT>", "", $out);
							$out = str_replace("</FONT>", "", $out);
							$out = preg_replace("/<FONT[^>]+>/", "", $out);
							$out = preg_replace("/<P[^>]*><\/P>/", "<br/>", $out);
							$out = str_replace("<LI></LI>", "<br/>", $out);
							$out = preg_replace("@(<LI>.*</LI>)@", "<UL>$1</UL>", $out);
							
							if ($this->xml_safe) {
								$entry[$field['cms_name']] = $this->wrap_cdata($out);
							} else {
								$entry[$field['cms_name']] = $out;
							}
							break;
						
						case 'htmltext_fck':
							if ($this->xml_safe) {
								$entry[$field['cms_name']] = $this->wrap_cdata($entry[$field['cms_name']]);
							}
							break;
						
						case 'htmlsource':
							if ($this->xml_safe) $entry[$field['cms_name']] = $this->wrap_cdata($entry[$field['cms_name']]);
							break;
							
						case 'text':
						case 'label':
						case 'select':
							$entry[$field['cms_name']] = htmlspecialchars(str_replace("\n", "", $entry[$field['cms_name']]));
							break;
							
						case 'numeric':
							if ($this->number_format != null) {
								$entry[$field['cms_name']] = number_format($entry[$field['cms_name']], $this->number_format[0], $this->number_format[1], $this->number_format[2]);
							}
							break;
							
						case 'time':
						case 'date':
							$entry[$field['cms_name']] = $this->date_format != null ? date($this->date_format, $entry[$field['cms_name']]) : $entry[$field['cms_name']];
							break;
							
						case 'image':
						case 'image_multi':
							if (is_array($entry[$field['cms_name']])) {
								foreach ($entry[$field['cms_name']] as &$image) {
									$image['caption'] = htmlspecialchars(str_replace("\n", "", $image['caption']));
								}
							}
							break;
						
						case 'checkbox':
							foreach ($entry[$field['cms_name']] as &$value) {
								$value = htmlspecialchars(str_replace("\n", "", $value));
							}
							break;
						
						case 'reference':
						case 'reference_multi':
							if (is_array($entry[$field['cms_name']]) && is_array($entry[$field['cms_name']][array_shift(array_keys($entry[$field['cms_name']]))])) {
								if (!isset($this->fields[$field['refers_to_module']])) {
									$this->cms->setModule($field['refers_to_module']);
									$this->fields[$field['refers_to_module']] = $this->cms->fields();
								}
								$this->preformat($entry[$field['cms_name']], $this->fields[$field['refers_to_module']]);
							}
							break;
					}
				}
			}
		}
	}
?>