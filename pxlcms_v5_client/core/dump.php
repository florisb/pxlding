<?php

	

	
	// check if session started, if not, start it
	if (!isset($_SESSION)) {
		header("Content-type:text/html; charset=utf-8");
		if (isset($_GET['session_id'])) session_id($_GET['session_id']);
		session_start();
		$CMS_SESSION =& $_SESSION['cms_v5_session'];
	}
	
	// check if logged in
	if (!isset($CMS_SESSION['logged_in']) || !$CMS_SESSION['logged_in']) exit;
	
	
	// includes
	require_once "../../pxlcms_v5_client/config/config.php";
	require_once "../../pxlcms_v5_client/includes/read_config.php";
	require_once "../../pxlcms_v5_client/includes/pxl_library.php";
	require_once "../../pxlcms_v5_client/core/classes/Event.php";
	require_once "../../pxlcms_v5_client/core/classes/CMS.php";
	require_once "../../pxlcms_v5_client/core/classes/CMS_DB.php";
	require_once "../../pxlcms_v5_client/core/classes/CMS_Query.php";
	require_once "../../pxlcms_v5_client/core/classes/Logger.php";
	require_once "../../pxlcms_v5_client/core/classes/Tools.php";
	
	
	$CMS = new CMS($CMS_SESSION['module_id']);
	$CMS->generate_identifier = true;
	$CMS->active_entries_only = false;
	
	header("Content-type: application/octet-stream");
	header("Content-Disposition: attachment; filename=\"".$CMS->table().".csv\"");
	
	$fields = $CMS->fields();
	$header = array();
	$field_types = array();
	foreach($fields as $field) {
		$header[] = '"'.$field["name"].'"';
		$field_types[$field['cms_name']] = $field['form_element'];
	}
	$entries = $CMS->getEntries();
	$lines = "";
	foreach($entries as $entry) {	
		$line = array();
		foreach($fields as $field) {
			if (is_array($entry[$field['cms_name']])) {
				switch ($field_types[$field['cms_name']])
				{
					case 'reference':
					case 'reference_multi':
						$values = array();
						foreach ($entry[$field['cms_name']] as $reference) {
							$values[] = $reference['_identifier'];
						}
						$values = (count($values) > 1 ? count($values).' references: ' : '').implode(", ", $values);
						break;
					
					case 'image':
					case 'image_multi':
						$values = array();
						foreach ($entry[$field['cms_name']] as $image) {
							$values[] = $image['file']." ";
						}
						$values = implode(" ", $values);
						break;
						
					case 'file':
						if (count($entry[$field['cms_name']])) {
							foreach ($entry[$field['cms_name']] as &$file) {
								$values .= Tools::file_name($file).'.'.Tools::file_extension($file).", ";
							}
						} else {
							$values = 'none';
						}
						break;
					
					case 'checkbox':
						if (count($entry[$field['cms_name']])) {
							$values = implode(', ', $entry[$field['cms_name']]);
						} else {
							$values = 'none';
						}
						break;
				
				}
				$line[] = $values;
			} else {
				switch ($field_types[$field['cms_name']])
				{
					case 'date':
						$line[] = date('d-m-Y', $entry[$field['cms_name']]);
						break;
					
					case 'color':
						$line[] = "#".$entry[$field['cms_name']];
						break;
						
					case 'time':
						$line[] = date('d-m-Y / H:i:s', $entry[$field['cms_name']]);
						break;
					
					case 'boolean':
						$line[] = $entry[$field['cms_name']] ? 'Yes' : 'No';
						break;
						
					case 'htmlsource':
						$line[] = htmlspecialchars($entry[$field['cms_name']]);
                        break;
						
					case 'htmltext':
					case 'htmltext_fck':
						$line[] = strip_tags($entry[$field['cms_name']]);
						break;
						
					default:
						$line[] = htmlspecialchars($entry[$field['cms_name']]);
						break;
				}
			}
		}
		foreach($line as $key => $l) {
			$line[$key] = '"'.$l.'"';
		}
		$lines .= implode(";", $line)."\n";
	}
	echo implode(";", $header)."\n";
	echo $lines;