<?php
	
	header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	
	
	// VERIFY SESSION
	// !!!! DISABLED SINCE NOT ALWAYS WORKING :( !!!!
	// ==========================================================
	/*
	if (session_id() != $_GET['session_id']) {
		die ("[ERROR LOADING CONTENT / INVALID SESSION (1)]");
	}
	
	if (!isset($_SESSION['cms_v5_session'])) {
		die ("[ERROR LOADING CONTENT / INVALID SESSION (2)]");
	} else {
		$CMS_SESSION =& $_SESSION['cms_v5_session'];
	}
	
	if (!isset($CMS_SESSION['logged_in']) || !$CMS_SESSION['logged_in']) {
		die ("[ERROR LOADING CONTENT / INVALID SESSION (3)]");
	}
	*/
	// ==========================================================
	
	
	include "classes/CMS_DB.php";
	include "../includes/read_config.php";
	
	// preprocess for (db)safety
	$field    = preg_replace('/[^0-9a-z_]/', '', $_GET['field']);
	$module   = preg_replace('/[^0-9a-z_]/', '', $_GET['module']);
	$language = preg_replace('/[^0-9a-z_]/', '', $_GET['language']);
	$entry_id = (int) $_GET['entry_id'];
	
	$validate_request = sha1($entry_id.$module.$field);
	
	// new entry? clean slate!
	if (!$entry_id) {
		die("");
	}
	
	// is this feature required at all?
	$flexeditors = CMS_DB::mysql_query("SELECT * FROM `".$CMS_DB['prefix']."fields` WHERE field_type_id = 5");

	if (mysql_num_rows($flexeditors) > 0)
	{
		if ($validate_request == $_GET['validate']) {
			$f = array();
			$existing_fields = CMS_DB::mysql_query("SHOW COLUMNS FROM `".$module."`");
			while ($existing_field = mysql_fetch_assoc($existing_fields)) {
				$f[] = $existing_field['Field'];
			}
			
			if (in_array($field, $f)) {
				$html = mysql_fetch_row(CMS_DB::mysql_query("SELECT `".$field."` FROM `".$module."` WHERE `id` = ".$entry_id));
			} else {
				$html = mysql_fetch_row(CMS_DB::mysql_query("SELECT `".$field."` FROM `".$module."_ml` WHERE `language_id` = '".$language."' AND `entry_id` = ".$entry_id));
			}
			
			echo str_replace("&amp;", "&", str_replace("&lt;", "<", str_replace("&gt;", ">", $html[0])));
		} else {
			die ("[ERROR LOADING CONTENT / INVALID VALIDATOR (1)]");
		}
	} else {
		die ("[CONNECTOR UNAVAILABLE]");
	}
?>