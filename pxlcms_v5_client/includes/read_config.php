<?php

	// READ CONFIGURATION FILES
	// AND PREPROCESS CONFIGURATION
	if (!isset($CMS_ENV) || !is_array($CMS_ENV))
	{
		$path = explode("/", str_replace("\\", "/", $_SERVER['SCRIPT_FILENAME']));
		switch ($path[count($path) - 2])
		{
			case 'login':
			case 'core':
			case 'frontend':
				include "../config/config.php";
				break;
			default:
				include "config/config.php";
				break;
		}
	}

	// select WORKING or PUBLISHED database
	if (isset($CMS_DB_PUBLISHED) && is_array($CMS_DB_PUBLISHED) && stristr($_SERVER['SCRIPT_FILENAME'], $CMS_ENV['pxlcms_folder']) === false) {
		$CMS_DB = $CMS_DB_PUBLISHED;
	} else {
		// a published database was configured, but we're connecting through the CMS itself,
		// so just use CMS_DB to connect to the working database
	}

	/* VERSION
	   ------- */
	$CMS_ENV['version']         = '5.59.13';
	// NB to developers: database changes? -> do not forget to change the default layout in "new.sql"
	$CMS_ENV['version_number']  = 45; // incremental numbering to track database changes
	$CMS_ENV['version_release'] = '29-10-2013';


	/*  IP
		------------------------------------
		execute the ip filter if configured
		------------------------------------ */
	if ($CMS_IP['filter_ips'])
	{
		require_once("ip_check.php");
		if (!chkiplist($_SERVER['REMOTE_ADDR'], $CMS_IP['allowed_ips'])) {
			header("HTTP/1.1 404 Not Found");
			header("Status: 404 Not Found");
			exit;
		}
	}


	/* ENVIRONMENT
	   ---------------------------------
	   deduced variables
	   --------------------------------- */
	$script = str_replace("\\", "/", $_SERVER['SCRIPT_FILENAME']);

	$CMS_ENV['root_path']         = substr($script, 0, strpos($script, $CMS_ENV['pxlcms_folder']));
	if ($CMS_ENV['root_path'] == '') $CMS_ENV['root_path'] = str_replace(strrchr($_SERVER['SCRIPT_FILENAME'], '/'), '/', $_SERVER['SCRIPT_FILENAME']);
	$CMS_ENV['root_path']        .= $CMS_ENV['pxlcms_folder'];

	$CMS_ENV['pxl_cms_url']       = $CMS_ENV['base_url'].$CMS_ENV['pxlcms_folder'];
	$CMS_ENV['base_url_uploads']  = $CMS_ENV['pxl_cms_url']."uploads/";
	$CMS_ENV['root_path_uploads'] = $CMS_ENV['root_path']."uploads/";
	$CMS_ENV['pxlcms_version'] 	  = $CMS_ENV['version'].' / '.$CMS_ENV['version_number'];


	/*  ERROR HANDLING
		------------------------------------
		configure and set up error handling
		------------------------------------ */
	error_reporting(E_ALL & (~E_STRICT & ~E_NOTICE));

	if ($CMS_EXTRA['errors_2_file']) {
		ini_set ('display_errors', 0);
		ini_set ('log_errors', 1);
		ini_set ('html_errors', 0);
		ini_set ('error_log', $CMS_ENV['root_path'].'logs/errors_'.date('Ymd').'.log');
	} else {
		ini_set ('error_prepend_string', "<div style='background: #fff; color: #000; margin: 0px; padding: 10px; border: 3px solid red;'>");
		ini_set ('error_append_string', "</div>");
		ini_set ('html_errors', 0);
		ini_set ('log_errors', 0);
		ini_set ('display_errors', 1);
	}


	// the variable below is only used when the source of the CMS is obfuscated
	// it should be left exactly as it is; changing it will disable the CMS
	$obfuscator_variable_translation_table = "[%%OBFUSCATOR_VARIABLE_TRANSLATION_TABLE%%]";
