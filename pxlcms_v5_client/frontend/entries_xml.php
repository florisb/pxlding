<?php
	/****************************
	  XML FRONTEND for PXLCMS v5
	 ****************************/

	include 'meta.php';
	
	// check if required parameter(s) present
	if(!isset($_GET['module_id'])) {
		echo 'Please specify module_id';
		exit;
	}
	if(!$query->xml_access($_GET['module_id'])) {
		echo 'No XML access allowed';
		exit;
	}
	
	$entries = $query->entries();
	
	if($query->hasError()) {
		headerXML();
		echo makeXML($query->error());
	} else {
		$xml = array('cms' => array('version' => $CMS_ENV['version'], 'entries' => $entries));
		
		if($_GET['show_structure'] == 'true') {
			$structures = fetch_all_structures((int) $_GET['module_id'], $query);
			
			//Fetch passive referenced structures
			$module_ids = fetch_passive_module_ids($entries);
			if(count($module_ids)) {
				foreach($module_ids as $module_id) {
					$structures = fetch_all_structures($module_id, $query, $structures);
				}
			}
			
			$xml['cms']['structure'] = $structures;
		}
		
		$output = makeXML($xml);
		headerXML($output);
		echo $output;
	}