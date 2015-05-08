<?php
	// use output buffering for logfile
	ob_start();

	include "../includes/read_config.php";
	include "../includes/pxl_library.php";
	
	include "classes/Tools.php";
	include "classes/Logger.php";
	include "classes/Event.php";
	include "classes/CMS.php";
	include "classes/CMS_DB.php";
	include "classes/FormRenderer.php";
	include "classes/image/class.image.php5.php";
	
	include "../includes/upload_functions.php";

	//read hooks
	foreach (new DirectoryIterator('../hooks') as $file) {
		if ($file->isFile() && $file->getExtension() == 'php' && $file->getFilename() != 'example.php') {
			include $file->getPathname();
		}
	}
	
	// logging
	imglog("Starting upload");
	imglog("posted session id (GET): ".$_GET['sid']);
	
	// field ID
	$fid = (int) $_GET['fid'];
	$CMS_SESSION['field_id'] = $fid;
	
	// read and instantiate session
	session_id($_GET['sid']);
	session_start();
	
	// verify session
	if (!isset($_SESSION['cms_v5_session'])) {
		imglog("[ERROR] no session present");
		exit;
	} else {
		$CMS_SESSION =& $_SESSION['cms_v5_session'];
	}
	
	if (!isset($CMS_SESSION['logged_in']) || !$CMS_SESSION['logged_in']) {
		imglog("[ERROR] illegal session: not logged in");
		exit;
	}
	
	imglog("originated from module_id ".$CMSForm->module_id.", field_id: ".$fid." - linked to entry_id ".$CMS_SESSION['entry_id']);
	
	$target_path = $CMS_ENV['root_path_uploads'];
	$fname       = "_".strtolower(Tools::file_name($_FILES['Filedata']['name']));
	$fext        = strtolower(Tools::file_extension($_FILES['Filedata']['name']));
?>