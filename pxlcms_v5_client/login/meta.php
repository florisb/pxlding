<?php
	session_start();
	$CMS_SESSION =& $_SESSION['cms_v5_session'];
	
	require_once "../includes/read_config.php";
	require_once "../includes/pxl_library.php";
	require_once "../includes/ip_check.php";
	require_once "../core/classes/Tools.php";
	require_once "../core/classes/Logger.php";
	require_once "../core/classes/CMS.php";
	require_once "../core/classes/CMS_DB.php";