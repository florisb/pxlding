<?php

	/*
		--------------------
		PXL.Function-Library
		--------------------
	*/
	
	
	/*
		pr($array)
		-
		recursively writes an array using PHP's print_r(), wrapping this output in <pre> tags
	*/
	function pr($array) {
		echo "<pre>";
		print_r($array);
		echo "</pre>";
	}
	
	function file_get($f) {
		// just open if not by url
		if (substr(strtolower($f), 0, 7) != 'http://') {
			return file_get_contents($f);
		} else {
			if (function_exists('curl_init')) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $f);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($ch, CURLOPT_HEADER, false);
				$result = curl_exec($ch);
				curl_close($ch);
				return $result;
			} else if (ini_get('allow_url_fopen')) {
				return file_get_contents($f);
			} else {
				die("Can't load external file over http: both PHP's cURL-module and 'allow_url_fopen'-setting are disabled.");
			}
		}
	}
	
	function array_peek(&$stack, $position = 0) {
		if(count($stack) == 0) return null;
		$keys = array_keys($stack);
		if ($position >= count($keys) || $position < 0) return null;
		return $stack[$keys[$position]];
	}
	
	function pxl_db_safe($value) {
		global $CMS_DB;
		
		if ($CMS_DB['host']) {
			CMS_DB::connect();
		}
		
		return mysql_real_escape_string(get_magic_quotes_gpc() || get_magic_quotes_runtime() ? stripslashes($value) : $value);
	}
	
	function pxl_activate_flash($width, $height, $file, $parameters = array(), $id = null) {
			$ajax = $_SERVER['AJAX_CALL'];
			
			if(is_null($id)) $id = 'flash'.rand(0, 10000);
			
			if ($parameters === false || $parameters === true) {
				die ("pxl_activate_flash no longer requires the \$ajax parameter!");
			}
			
			if (strtolower(substr($file, -4)) == '.swf') {
				$file = substr($file, 0, -4);
			}
			
			$pars = "";
			foreach ($parameters as $parameter_name => $parameter_value) {
				$pars .= ($pars == "" ? '?' : '&');
				$pars .= $parameter_name.'='.urlencode($parameter_value);
			}
		
			$r = "";
			if (!$ajax) $r .= "<script language=\"javascript\">
					if (AC_FL_RunContent == 0) {
						alert(\"This page requires AC_RunActiveContent.js.\");
					} else {
						AC_FL_RunContent(
							'codebase', 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0',
							'width', '".$width."',
							'height', '".$height."',
							'src', '".$file.$pars."',
							'quality', 'high',
							'pluginspage', 'http://www.macromedia.com/go/getflashplayer',
							'align', 'middle',
							'play', 'true',
							'loop', 'true',
							'scale', 'noscale',
							'wmode', 'transparent',
							'devicefont', 'false',
							'id', '".$id."',
							'bgcolor', '#000000',
							'name', '".$id."',
							'menu', 'false',
							'allowFullScreen', 'false',
							'allowScriptAccess','sameDomain',
							'movie', '".$file.$pars."',
							'salign', ''
							); //end AC code
					}
				</script>
				<noscript>";
					$r .= "<object id='".$id."' name='".$id."' width='".$width."' height='".$height."' align='middle' classid='clsid:d27cdb6e-ae6d-11cf-96b8-444553540000' codebase='http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0'>
					<param name='allowScriptAccess' value='sameDomain' />
					<param name='wmode' value='transparent'>
					<param name='allowFullScreen' value='false' />
					<param name='movie' value='".$file.".swf".$pars."' />
					<param name='quality' value='high' />
					<param name='scale' value='noscale' />
					<param name='menu' value='false' />
					<param name='bgcolor' value='#000000' />	
					<embed src='".$file.".swf".$pars."' quality='high' wmode='transparent' bgcolor='#000000' width='".$width."' height='".$height."' name='".$file."' align='middle' allowScriptAccess='sameDomain' allowFullScreen='false' type='application/x-shockwave-flash' pluginspage='http://www.macromedia.com/go/getflashplayer' />
					</object>";
			if (!$ajax) $r .= "</noscript>";
			
			return $r;
		}
	
?>