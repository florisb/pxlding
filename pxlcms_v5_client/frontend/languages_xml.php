<?php
	/****************************
	  XML FRONTEND for PXLCMS v5
	 ****************************/
	
	include 'meta.php';
	
	$languages = $query->languages();
	
	if($query->hasError()) {
		headerXML();
		echo makeXML($query->error());
	} else {
		$languages = array('cms' => array('version' => $CMS_ENV['version'], 'languages' => $languages));
		
		$output = makeXML($languages);
		headerXML($output);
		echo $output;
	}