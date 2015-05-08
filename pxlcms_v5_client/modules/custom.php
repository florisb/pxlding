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
	require_once "../../pxlcms_v5_client/core/classes/CMS.php";
	require_once "../../pxlcms_v5_client/core/classes/CMS_DB.php";
	require_once "../../pxlcms_v5_client/core/classes/CMS_Query.php";
	require_once "../../pxlcms_v5_client/core/classes/Logger.php";
	require_once "../../pxlcms_v5_client/core/classes/Tools.php";
	
	CMS_DB::connect();
	
	// continue
	include $_GET['p'];