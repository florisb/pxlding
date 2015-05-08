<?php
	
	/**
	 * library.php
	 *
	 * Various utility functions that are accessible
	 * in a global context.
	 */
	
	/**
	 * path function.
	 * 
	 * Creates a platform-independent URI. Custom delimiters
	 * may be used and will be considered case-sensitive.
	 *
	 * @access public
	 * @param mixed $sPath
	 * @param string $sDelimiter (default: '/')
	 * @param mixed $sReplacementDelimiter (default: DIRECTORY_SEPARATOR)
	 * @return void
	 */
	function path($sPath, $sDelimiter = '/', $sReplacementDelimiter = DIRECTORY_SEPARATOR) {
		return str_replace($sDelimiter, $sReplacementDelimiter, $sPath);
	}
	
	function pr($v) {
		if (isCli()) {
			var_dump($v);
		} else {
			echo '<pre>';
			var_dump($v);
			echo '</pre>';
		}
	}

	function dd($v) {
		pr($v);
		exit;
	}

	function isCli() {
		return (php_sapi_name() === 'cli');
	}
	
	function objectToArray($obj) {
        if(is_object($obj)) $obj = (array) $obj;
        if(is_array($obj)) {
            $new = array();
            foreach($obj as $key => $val) {
                $new[$key] = objectToArray($val);
            }
        }
        else $new = $obj;
        return $new;
    }
	
	function array_peek($array, $i = 0) {
		if ($array instanceof \PXL\Core\Collection\Collection || $array instanceof \PXL\Core\Collection\Map) {

			// If this is an empty collection, return null
			if (!count($array)) {
				return null;
			}

			$iterator = $array->getIterator();
			$iterator->rewind();
		  
			for ($j = 0; $j < $i; $j++) {
			  $iterator->next();
		  }
		  
		  $ret = $iterator->current();
	  } elseif (is_array($array)) {
			$e = array_values($array);
			$ret = $e[$i];
		} else {
			throw new \InvalidArgumentException('Unable to peek on this!');
		}
		
		return $ret;
	}
	
	/**
	 * is_assoc function.
	 * 
	 * Checks whether an array is associative or not
	 *
	 * @access public
	 * @param mixed $array
	 * @return void
	 */
	function is_assoc($array) {
  	return (bool)count(array_filter(array_keys($array), 'is_string'));
  }
  
  /**
   * pxl_db_safe function.
   * 
   * Runs a real_escape_string using mysqli and immediately
   * returns the result.
   *
   * @access public
   * @param mixed $str
   * @return void
   */
  function pxl_db_safe($str) {
	  return \PXL\Core\Db\Db::getInstance()->getConnection()->real_escape_string($str);
  }
  
  /**
   * detectLanguage function.
   * 
   * Detects which languages are accepted
   * by the client, and returns them as an
   * array in order of importance.
   *
   * The implementation has been slightly altered
   * in order to fit the functionality in a single function,
   * rather than to be wrapped in a class. All credits
   * go to the original author: Ronald Mansveld (ronald@pixelindustries.com)
   
   * @access public
   * @return array
   */
  function detectLanguage() {
  	if (!array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER) || empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
	  	return array();
  	}
  
	  $parts = array_map('trim', explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']));
		foreach ($parts as &$part) {
			$part = explode(';', $part);
			$weight = (array_key_exists(1, $part) ? (float) substr($part[1], 2) : 1);
			preg_match_all(call_user_func(function() {
				$regular          = 'art-lojban|cel-gaulish|no-bok|no-nyn|zh-guoyu|zh-hakka|zh-min|zh-min-nan|zh-xiang';
				$irregular        = 'en-GB-oed|i-ami|i-bnn|i-default|i-enochian|i-hak|i-klingon|i-lux|i-mingo|i-navajo|i-pwn|i-tao|i-tay|i-tsu|sgn-BE-FR|sgn-BE-NL|sgn-CH-DE';
				$grandfathered    = $irregular.'|'.$regular;
				$privateuse       = 'x(?:-[a-z0-9]{1,8})+';
				$singleton        = '[a-wy-z0-9]';
				$extension        = $singleton.'(?:-[a-z0-9]{2,8})+';
				$variant          = '[a-z0-9]{5,8}|\d[a-z0-9]{3}';
				$region           = '[a-z]{2}|\d{3}';
				$script           = '[a-z]{4}';
				$extlang          = '[a-z]{3}(?:-[a-z]{3}){,2}';
				$language         = '[a-z]{2,3}(?P<extlang>'.$extlang.')?|[a-z]{4}|[a-z]{5,8}';
				$langtag          = '(?P<language>'.$language.')'.'(?:-(?P<script>'.$script.'))?'.'(?:-(?P<region>'.$region.'))?'.'(?:-(?P<variant>'.$variant.'))*'.'(?:-(?P<extension>'.$extension.'))*'.'(?:-(?P<privuse>'.$privateuse.'))?';
				$languagetag      = '(?P<langtag>'.$langtag.')|'.'(?P<privateuse>'.$privateuse.')|'.'(?P<grandfathered>'.$grandfathered.')';

				return '#'.$languagetag.'#i';
			}), $part[0], $part);
			foreach (array_keys($part) as $k) {
				if(is_int($k)) unset($part[$k]);
				else $part[$k] = $part[$k][0];
			}
			
			$part['weight'] = $weight;
			
		}
		
		uasort($parts, function($a, $b) {
			return ($a['weight'] == $b['weight'] ? 0 : ($a['weight'] < $b['weight'] ? 1 : -1)); // -1 and 1 reversed, since we want to sort DESC, not ASC
		});
		
		$result = array();
		foreach($parts as $part) {
			$result[$part['language']] = $part;
		}
		
		return $result;
  }
  
  /*
		===============
		== chkiplist ==
		===============
		originally by: palitoy-ga
		commented and signature change by johan kohne
		=> signature change: instead of reading $lines from file, it's included as parameter
	
		description
		---------
		ultra-uitgebreide ip check functie, met alle range and wildcard support die maar kan wensen
		zie ook: http://answers.google.com/answers/threadview?id=379235
	
		usage
		---------
		chkiplist($ip, $lines)
	
		$ip    - the ip that will be matched against:
		$lines - an array of ip masks. masks may contain wildcards/ranges, using: "*", "?", "-"
	
		eg:
	
		chkiplist("24.25.26.27", array("24.25.255.*", "24.25.25-29.1-100))   -> matches (on 2nd mask)
		chkiplist("24.25.255.7", array("24.25.255.*", "24.25.25-29.1-100))   -> matches (on 1st mask)
		chkiplist("24.25.26.119", array("24.25.255.*", "24.25.25-29.1-100))   -> no match
		chkiplist("24.25.26.119", array("24.25.255.*", "24.25.25-29.1-100))   -> no match

		it also supports masks of the format:
	
		10.125.1.1 - 10.125.1.255
		192.168.* - 192.169.*

	*/
  function chkiplist($ip, $lines) {
		# set a variable as false
		$found = false;
		# convert ip address into a number
		$split_it = explode(".",$ip);
		$ip = "1" . sprintf("%03d",$split_it[0]) . sprintf("%03d",$split_it[1]) . sprintf("%03d",$split_it[2]) . sprintf("%03d",$split_it[3]);
		# loop through the ip address file
		foreach ($lines as $line) {
			# set a maximum and minimum value
			$max = $line;
			$min = $line;
			# replace * with a 3 digit number
			if ( strpos($line,"*",0) <> "" ) {
				$max = str_replace("*","999",$line);
				$min = str_replace("*","000",$line);
			}
			# replace ? with a single digit
			if ( strpos($line,"?",0) <> "" ) {
				$max = str_replace("?","9",$line);
				$min = str_replace("?","0",$line);
			}
			# if the line is invalid go to the next line
			if ( $max == "" ) { continue; };
			# check for a range
			if ( strpos($max," - ",0) <> "" ) {
				$split_it = explode(" - ",$max);
				# if the second part does not match an ip address
				if ( !preg_match("|\d{1,3}\.|",$split_it[1]) ) {
					$max = $split_it[0];
				} else { 
					$max = $split_it[1];
				};
			}
			if ( strpos($min," - ",0) <> "" ) {
				$split_it = explode(" - ",$min);
				$min = $split_it[0];
			}
			# make $max into a number
			$split_it = explode(".",$max);
			for ( $i=0;$i<4;$i++ ) {
				if ( $i == 0 ) { $max = 1; };
				if ( strpos($split_it[$i],"-",0) <> "" ) {
					$another_split = explode("-",$split_it[$i]);
					$split_it[$i] = $another_split[1];
				} 
				$max .= sprintf("%03d",$split_it[$i]);
			}
			# make $min into a number
			$split_it = explode(".",$min);
			for ( $i=0;$i<4;$i++ ) {
				if ( $i == 0 ) { $min = 1; };
				if ( strpos($split_it[$i],"-",0) <> "" ) {
					$another_split = explode("-",$split_it[$i]);
					$split_it[$i] = $another_split[0];
				}
				$min .= sprintf("%03d",$split_it[$i]);
			}
			# check for a match
			if ( ($ip <= $max) && ($ip >= $min) ) {
				$found = true;
				break;
			}
		}
	
		return $found;
	}
	
	function sanitizeFilename($fileName, $defaultIfEmpty = 'default', $separator = '_', $lowerCase = true) {
    // Gather file informations and store its extension
    $fileInfos = pathinfo($fileName);
    $fileExt   = array_key_exists('extension', $fileInfos) ? '.'. strtolower($fileInfos['extension']) : '';

    // Removes accents
    $fileName = @iconv('UTF-8', 'us-ascii//TRANSLIT', $fileInfos['filename']);

    // Removes all characters that are not separators, letters, numbers, dots or whitespaces
    $fileName = preg_replace("/[^ a-zA-Z". preg_quote($separator). "\d\.\s]/", '', $lowerCase ? strtolower($fileName) : $fileName);

    // Replaces all successive separators into a single one
    $fileName = preg_replace('!['. preg_quote($separator).'\s]+!u', $separator, $fileName);

    // Trim beginning and ending seperators
    $fileName = trim($fileName, $separator);

    // If empty use the default string
    if (empty($fileName)) {
        $fileName = $defaultIfEmpty;
    }

    return $fileName. $fileExt;
	}

	if (!function_exists('http_response_code')) {
		function http_response_code($code = NULL) {
			if ($code !== NULL) {
				switch ($code) {
					case 100: $text = 'Continue'; break;
					case 101: $text = 'Switching Protocols'; break;
					case 200: $text = 'OK'; break;
					case 201: $text = 'Created'; break;
					case 202: $text = 'Accepted'; break;
					case 203: $text = 'Non-Authoritative Information'; break;
					case 204: $text = 'No Content'; break;
					case 205: $text = 'Reset Content'; break;
					case 206: $text = 'Partial Content'; break;
					case 300: $text = 'Multiple Choices'; break;
					case 301: $text = 'Moved Permanently'; break;
					case 302: $text = 'Moved Temporarily'; break;
					case 303: $text = 'See Other'; break;
					case 304: $text = 'Not Modified'; break;
					case 305: $text = 'Use Proxy'; break;
					case 400: $text = 'Bad Request'; break;
					case 401: $text = 'Unauthorized'; break;
					case 402: $text = 'Payment Required'; break;
					case 403: $text = 'Forbidden'; break;
					case 404: $text = 'Not Found'; break;
					case 405: $text = 'Method Not Allowed'; break;
					case 406: $text = 'Not Acceptable'; break;
					case 407: $text = 'Proxy Authentication Required'; break;
					case 408: $text = 'Request Time-out'; break;
					case 409: $text = 'Conflict'; break;
					case 410: $text = 'Gone'; break;
					case 411: $text = 'Length Required'; break;
					case 412: $text = 'Precondition Failed'; break;
					case 413: $text = 'Request Entity Too Large'; break;
					case 414: $text = 'Request-URI Too Large'; break;
					case 415: $text = 'Unsupported Media Type'; break;
					case 500: $text = 'Internal Server Error'; break;
					case 501: $text = 'Not Implemented'; break;
					case 502: $text = 'Bad Gateway'; break;
					case 503: $text = 'Service Unavailable'; break;
					case 504: $text = 'Gateway Time-out'; break;
					case 505: $text = 'HTTP Version not supported'; break;
					default:
						exit('Unknown http status code "' . htmlentities($code) . '"');
						break;
				}

				$protocol = (isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0');

				header($protocol . ' ' . $code . ' ' . $text);

				$GLOBALS['http_response_code'] = $code;
			} else {
				$code = (isset($GLOBALS['http_response_code']) ? $GLOBALS['http_response_code'] : 200);
			}

			return $code;

		}
	}