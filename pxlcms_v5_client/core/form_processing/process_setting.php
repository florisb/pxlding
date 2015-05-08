<?php

	// 1. reading
	$posted_settings = array();
	for ($s = 0; $s < count($_POST['settings']); $s++) {
		$posted_settings[$_POST['settings'][$s]] = $_POST['values'][$s] != '' ? $_POST['values'][$s] : null;
	}
	
	if(!isset($posted_settings["tab_id"])) unset($CMS_SESSION["tab_id"]);
	
	// 2. processing
	foreach ($posted_settings as $key => $value) {
		switch ($key)
		{
			case 'items_per_page':
				$CMS_SESSION['page'] = 0;
				$CMS_SESSION[$key] = $value;
				break;
				
			case 'special_page':
				$CMS_SESSION[$key] = $value;
				unset($CMS_SESSION['special_state']);
				break;
			
			case 'menu_id':
				$CMS_SESSION['menu_id'] = $value;
				unset($CMS_SESSION['module_id']);
				unset($CMS_SESSION['section_id']);
				unset($CMS_SESSION['cms_state']);
				unset($CMS_SESSION['last_state']);
				$CMS = new CMS();
				$groups = $CMS->getStructure();
				foreach ($groups as $group) {
					if ($group['menu_id'] != $CMS_SESSION['menu_id']) continue;
					$CMS_SESSION['group_id'] = $group['id'];
					break;
				}
				echo "<script type='text/javascript'>window.location.reload();</script>";
				break;
				
			// when switching module,
			// reset the filtering
			// reset pagination
			case 'module_id':
				$CMS_SESSION['page'] = 0;
				$CMS_SESSION[$key] = $value;
				unset($CMS_SESSION['special_page']);
				unset($CMS_SESSION['categories_simulator']);
				break;
			
			case 'filter_update':
				if (!isset($CMS_SESSION['categories_simulator'])) $CMS_SESSION['categories_simulator'] = array();
				if ($value == 'up') {
					$old_filter = array_pop($CMS_SESSION['categories_simulator']);
					$CMS_SESSION['module_id'] = $old_filter['from_module_id'];
				} else if ($value == 'down') {
					$CMS_SESSION['categories_simulator'][] = $filter = (array) json_decode(stripslashes($_POST['filter']));
					$CMS_SESSION['module_id'] = $filter['module_id'];
				}
				$CMS_SESSION['page'] = 0;
				unset($CMS_SESSION['special_page']);
				break;
		
			default:
				$CMS_SESSION[$key] = $value;
		}
	}
	
?>