<?php
	include '../includes/read_config.php';
	include '../includes/pxl_library.php';
	include '../core/classes/Logger.php';
	include '../core/classes/Tools.php';
	include '../core/classes/Event.php';
	include '../core/classes/CMS.php';
	include "../core/classes/CMS_DB.php";
	include '../core/classes/CMS_Query.php';
	
	// check IP access
	if($CMS_IP['frontend_localhost_only'] && $_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']) {
		header('HTTP/1.1 404 Not Found');
		header('Status: 404 Not Found');
		exit;
	}
	
	// CREATE QUERY
	// set parameters & read entries
	$query = new CMS_Query();
	
	foreach($_GET as $key => $value) {
		$value = stripslashes($value);
		switch($key) {
			case 'entry_id':
			case 'references':
				$query->{$key} = explode('-', $value);
				break;
			
			case 'date_format':
			case 'number_format':
				$query->{$key} = ($value == 'null' ? null : $value);
				break;
			
			case 'load_passive_references':
				if($value == 1 || $value == 'true') {
					$query->{$key} = true;
				} else if($value == 0 || $value == 'false') {
					$query->{$key} = false;
				} else {
					$query->{$key} = explode('-', $value);
				}
				break;
			
			default:
				$query->{$key} = $value;
		}
	}
	
	// force these settings regardless of user
	$query->xml_safe = true;
	$query->format   = true;
	$query->debug    = false;
	
	function single($k) {
		$singles = array('entries' => 'entry', 'categories' => 'category', 'structure' => 'module', 'module' => 'fields', 'fields' => 'field');
		return isset($singles[$k]) ? $singles[$k] : 'child';
	}
	
	function headerXML($output = '') {
		$encoding = "<?xml version=\"1.0\" encoding=\"". (isset($_GET['encoding']) ? $_GET['encoding'] : 'UTF-8') ."\" ?>\n";
		
		header("Content-type: text/xml\n");
		header("Cache-Control: no-cache, must-revalidate"); //HTTP/1.1
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); //Date in the past
		if(!empty($output)) header('Content-Length: '. strlen($encoding.$output));
		
		echo $encoding;
	}
	
	function makeXML($a, $indent = '', $parent = '') {
		$output = '';
		
		if(!is_array($a)) return;
		foreach($a as $key => $value) {
			$i = $key;
			if($_GET['omit_system_data'] == 'true' && in_array((string) $key, array('e_active', 'e_position', 'e_category_id', 'e_user_id'))) {
				//omit
			} else {
				if(is_int($key)) {
					$key = single($parent);
				}
				if(is_array($value)) {
					if(count($value)) {
						$output .= $indent."<".$key." n='".count($value)."'".($parent == '_referenced' ? " mid='".$i."'" : '').">\n";
						$output .= makeXML($value, $indent."\t", $key);
						$output .= $indent."</".$key.">\n";
					} else {
						$output .= $indent."<".$key." />\n"; 
					}
				} else {
					if($value != '') {
						$output .= $indent."<".$key.">".$value."</".$key.">\n";
					} else {
						$output .= $indent."<".$key." />\n";
					}
				}
			}
		}
		
		return $output;
	}
	
	function fetch_all_structures($mid, $query, $structures = array()) {		
		if(isset($structures[$mid])) return $structures;
		
		$query->module_id = $mid;
		
		$structure = $query->fields(true);
		$structures[$mid] = array('id' => $mid, simplify_structure($structure));
		
		foreach($structure as $field) {
			if($field['refers_to_module'] > 0) {
				$structures = fetch_all_structures($field['refers_to_module'], $query, $structures);
			}
		}
		
		return $structures;
	}
	
	function fetch_passive_module_ids($entries, $module_ids = array()) {
		foreach($entries as $entry) {
			if(isset($entry['_referenced'])) {
				foreach($entry['_referenced'] as $key => $value) {
					$module_ids[] = $key;
					$module_ids = fetch_passive_module_ids($value, $module_ids);
				}
			}
		}
		
		return $module_ids;
	}
	
	function simplify_structure($data) {
		$new_data = array();
		
		foreach($data as $field) {
			$new_field = array(
				'name' => $field['cms_name'],
				'type' => $field['form_element']
			);
			
			if($field['refers_to_module'] > 0) $new_field['module_id'] = $field['refers_to_module'];
			
			$new_data[] = $new_field;
		}
		
		return $new_data;
	}