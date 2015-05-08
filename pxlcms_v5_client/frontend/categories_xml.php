<?php
	/****************************
	  XML FRONTEND for PXLCMS v5
	 ****************************/
	
	include "meta.php";
	
	// check if required parameter(s) present
	if (!isset($_GET['module_id'])) {
		echo "Please specify module_id";
		exit;
	}
	if (!$query->xml_access($_GET['module_id'])) {
		echo "No XML access allowed";
		exit;
	}
	
	$categories = $query->categories();
	
	headerXML();
	
	if ($query->hasError()) {
		echo makeXML($query->error());
	} else {
		$categories = array('cms' => array(
									'version' => $CMS_ENV['version'],
									'categories' => $categories,
									)
						);
		echo makeXML($categories);
	}
?>