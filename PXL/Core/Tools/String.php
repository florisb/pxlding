<?php
	namespace PXL\Core\Tools;
	
	abstract class String {
		
		public static function shorten_text($str, $length, $breakWords = true, $append = '…') {
			$strLength = mb_strlen($str);

			if ($strLength <= $length) {
				return $str;
			}

			if (!$breakWords) {
				while ($length < $strLength && preg_match('/^\pL$/', mb_substr($str, $length, 1))) {
					$length++;
				}
			}

			return mb_substr($str, 0, $length) . $append;
		}
		
		public static function toAscii($str, $replace = array(), $delimiter = '-') {
			setlocale(LC_ALL, 'en_US.UTF8');
			if(!empty($replace)) {
				$str = str_replace((array) $replace, ' ', $str);
			}

			$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
			$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
			$clean = strtolower(trim($clean, '-'));
			$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

			return $clean;
		}
		
		/**
		 * getLinkAnchors function.
		 * 
		 * Searches for URL's starting with the HTTP or HTTPS scheme and encloses
		 * them with HTML <a> tags with a variable target.
		 *
		 * @access public
		 * @static
		 * @param mixed $string
		 * @param string $target (default: "_blank")
		 * @return void
		 */
		public static function getLinkAnchors($string, $target = "_blank") {
			$replacement = "<a href=\"$1\" target=\"$target\">$1</a>";
			$regex       = '#(http[s]?://[^\s]+(?<!\.))#';
			
			return preg_replace($regex, $replacement, $string);
		}
	}