<?php

	// TODO: comments!

	class Tools {
	
		public static function properties_html($properties) {
			$attributes = '';
			$escape = array('value');
			$javascript = array('onclick', 'onmousedown', 'onmouseup', 'onmouseover', 'onmouseout', 'onkeyup', 'onkeydown', 'onfocus', 'onblur', 'ondoubleclick');
			foreach ($properties as $attribute => $value) {
				if (in_array($attribute, $escape)) {
					$attributes .= " ".$attribute."=\"".str_replace('"', '&quot;', $value)."\"";
				} else {
					$attributes .= " ".$attribute."=\"".$value."\"";
				}
			}
			return $attributes;
		}
		
		// MySQL utility functions
		// -----------------------
		// makes from a key => value array a sql valid update:
		// "key1 = 'value1', key2 = 'value2'" (etc)
		public static function mysql_update($data) {
			$sql = array();
			foreach ($data as $key => $value) {
				$sql[] = "`".$key."` = '".pxl_db_safe($value)."'";
			}
			return implode(", ", $sql);
		}
		
		public static function keys_values($data) {
			$keys   = array_keys($data);
			$values = array_map('pxl_db_safe', array_values($data));
			return array("`".implode("`, `", $keys)."`", "'".implode("', '", $values)."'");
		}
		
		public static function microtime() { 
			list($usec, $sec) = explode(" ",microtime()); 
			return ((float)$usec + (float)$sec); 
		} 
		
		public static function string_between($str, $start, $end) {
			$pos_start = strpos($str, $start) + strlen($start);
			if ($pos_start === FALSE) return "";
			$str = substr($str, $pos_start);
			$pos_end = strpos($str, $end);
			if ($pos_end === FALSE) return "";
			$str = substr($str, 0, $pos_end);
			return $str;
		}
		
		public static function string_starts($str, $start) {
			return substr($str, 0, strlen($start)) == $start;
		}
		
		public static function clean_filename($f, $include_version_suffix = TRUE) {
			$f = str_replace("%20", " ", $f);
			
			if (!$include_version_suffix) { // if we don't want the _v# suffix
				$f = preg_replace('/(.*)_v[0-9]{1,4}$/','$1', $f);
			}
			return Tools::alphanumeric($f);
		}
		
		public static function alphanumeric($str, $spacereplace = '_') {
			$str = html_entity_decode($str);
			$str = mb_convert_encoding($str, "ISO-8859-1", 'UTF-8');
			$str = str_replace(' ', $spacereplace, strtolower($str));
			$str = Tools::replace_accents($str);
			$str = preg_replace('/[^0-9a-z'.$spacereplace.']/', '', $str);
			$str = preg_replace('/['.$spacereplace.']+/', $spacereplace, $str);
			$str = str_replace(' ', $spacereplace, trim(str_replace($spacereplace, ' ', $str)));
			return $str;
		} 
		
		public static function seo($str) {
			return Tools::alphanumeric($str, '-');
		}
		
		public static function replace_accents($str) {
			$str = htmlentities($str);
			$str = preg_replace('/&([a-zA-Z])(uml|acute|grave|circ|tilde|slash|cedil|ring|caron);/','$1',$str);
			$str = preg_replace('/&(ae|AE)lig;/','$1',$str); // æ to ae
			$str = preg_replace('/&(oe|OE)lig;/','$1',$str); // Œ to OE
			$str = preg_replace('/&szlig;/','ss',$str); // ß to ss
			$str = preg_replace('/&(eth|thorn);/','th',$str); // ð and þ to th
			$str = preg_replace('/&(ETH|THORN);/','Th',$str); // Ð and Þ to Th
			return html_entity_decode($str);
		}
		
		public static function file_extension($name) {
			return substr(strrchr($name, '.'), 1);
		}
		
		public static function file_name($name) {
			$ext = Tools::file_extension($name);
			return $ext !== false ? substr($name, 0, -(strlen($ext)+1)) : $name;
		}
		
		public static function shrink_text($s, $len = 20) {
			$l = strlen($s);
			if ($l <= $len) {
				return $s;
			} else {
				return substr($s, 0, $len - 12).' ... '.substr($s, -7);
			}
		}
		
		public static function shorten_text($txt, $amount = 150, $force = false) {
			// html text
			if (strlen(strip_tags($txt)) != strlen($txt)) {
				// we cant shorten this??
				return $txt;
			}
			// plain text
			else
			{
				if (strlen($txt) > $amount) {
					if ($force) {
						$txt = substr($txt, 0, $amount - 3).'...';
					} else {
						$pos = strpos($txt, ' ', $amount);
						if ($pos >= $amount) {
							$txt = substr($txt, 0, $pos).'...';
						}
					}
				}
				return $txt;
			}
		}
		
		public static function linkalize_plaintext($plaintext) {
			$plaintext = preg_replace("([a-zA-Z0-9]{1}[\S]+@[\S]+\.[a-zA-Z]+)", "<a href='mailto:$0'>$0</a>", $plaintext);
			$plaintext = preg_replace("#(?<=\s)((http://|www\.|https://)[a-zA-Z0-9~\.\-:/\?=_&]+[a-zA-Z/]+)#", "<a href='$1'>$1</a>", $plaintext);
			$plaintext = str_replace("<a href='www.", "<a href='http://www.", $plaintext);
			
			return $plaintext;
		}
		
	}
	
?>