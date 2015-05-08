<?php
	
	if (isset($CMS_SESSION['special_page']))
	{
		switch ($CMS_SESSION['special_page'])
		{
			case 'users':
				$title    = 'User management';
				if ($CMS_SESSION['special_state'] == 'edit_user') {
					$contents = "core/user_edit.php";
				} else if ($CMS_SESSION['special_state'] == 'add_user') {
					$contents = "core/user_add.php";
				} else {
					$contents = "core/users.php";
				}
				break;
			
			case 'news':
				$contents = "core/news.php";
				$title    = 'Pixelindustries news';
				break;
			
			case 'logs':
				$contents = "core/logs.php";
				$title    = 'Log files';
				break;
				
			case 'resizing':
				$contents = "core/resizing.php";
				$title    = '(Re)Create thumbnails from originals';
				break;
			
			case 'cleanup':
				$contents = "core/cleanup.php";
				$title	 = 'Cleanup uploads folder';
				break;
			
			case 'database':
				$contents = "core/database.php";
				$title    = 'Run SQL';
				break;
			
			case 'publish':
				$contents = "core/publish.php";
				$title    = 'Publish content';
				break;

			case 'backups':
				$contents = "core/backups.php";
				$title    = 'Manage backups';
				break;
		}
	}
	else if ($CMS_SESSION['module_id'])
	{
		$CMS = new CMS($CMS_SESSION['module_id']);
		
		// if only 1 entry, and only 1 entry allowed, always show the edit page for this module!
		if (!$CMS->module_info['is_custom'] && isset($CMS->module_info['id'])) {
			$entry_id = $CMS->hasAndMayHaveOnlyOneEntry();
			if ($entry_id && $CMS_EXTRA['1_entry_immediate_edit']) {
				$CMS_SESSION['cms_state'] = 'edit';
				$CMS_SESSION['entry_id'] = $entry_id;
			}
		}
		// -
		
		if (is_array($CMS_SESSION['categories_simulator']) && count($CMS_SESSION['categories_simulator'])) {
			$dummy_cms = new CMS();
			$m = array();
			for ($i = 0; $i < count($CMS_SESSION['categories_simulator']); $i++) {
				$cat_sim = $CMS_SESSION['categories_simulator'][$i];
				$dummy_cms->setModule($cat_sim['from_module_id']);
				
				// load refering-field data for displaying in breadcrumb
				// depending on ML field or not, fetch it from the multilingual or flat table
				if($cat_sim['from_field_id']) { //do not use with custom modules
					$click_through_field = $dummy_cms->fieldData($cat_sim['from_field_id']);
					if ($click_through_field['multilingual']) {
						$rep = mysql_fetch_row(CMS_DB::mysql_query("SELECT `".$cat_sim['field']."` FROM `".$dummy_cms->table()."_ml` WHERE `language_id` = ".$CMS->language()." AND `entry_id` = ".$cat_sim['entry_id']));
					} else {
						$rep = mysql_fetch_row(CMS_DB::mysql_query("SELECT `".$cat_sim['field']."` FROM `".$dummy_cms->table()."` WHERE `id` = ".$cat_sim['entry_id']));
					}
				} else {
					$rep = null;
				}
				
				if ($i == count($CMS_SESSION['categories_simulator']) - 1) {
					$m[] = "<a href='#' onclick=\"process_setting({ filter_update: 'up' }); return false;\">".$dummy_cms->module_info['name'].''.(!empty($rep) ? ' "'.$rep[0].'"' : '').'</a>';
				} else {
					$m[] = $dummy_cms->module_info['name'].''.(!empty($rep) ? ' "'.$rep[0].'"' : '');
				}
			}
			$dummy_cms->setModule($cat_sim['module_id']);
			$m[] = "<a href='#' onclick=\"refresh(); return false;\">".$dummy_cms->module_info['name']."</a>";
			$m = implode('&nbsp;&nbsp;&raquo;&nbsp;&nbsp;', $m);
		} else {
			$m = "<a href='#' onclick=\"process_setting({'module_id': ".$CMS_SESSION['module_id'].", 'category_id': ''}); return false;\">".$CMS->module_info['name']."</a>";
		}
		
		// categories
		$path = '';
		$current_category_depth = 0;
		if (isset($CMS_SESSION['category_id'])) {
			$cid = $CMS_SESSION['category_id'];
			do
			{
				$c = $CMS->getCategory($cid);
				$path = " &raquo; <a href='#' onclick=\"process_setting({'category_id': ".$c['id']."}); return false;\">".$c['name']."</a>".$path;
				$cid = $c['parent_category_id'];
				$current_category_depth = max($c['depth'], $current_category_depth);
			}
			while ($c['depth'] > 1);
		}
		
		if (!isset($CMS_SESSION['cms_state'])) $CMS_SESSION['cms_state'] = 'overview';
		
		switch ($CMS_SESSION['cms_state'])
		{
			case 'add':
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'create')) {
					$contents = "core/entry_add.php";
					$title    = $m.$path.' &raquo; Add entry';
				}
				break;
				
			case 'edit':
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$contents = "core/entry_edit.php";
					$title    = $m.$path.' &raquo; Edit entry';
				}
				break;
				
			case 'category_edit':
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
					$contents = "core/category_edit.php";
					$title    = $m.$path.' &raquo; Edit folder';
				}
				break;
			
			case 'category_add':
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'create')) {
					$contents = "core/category_add.php";
					$title    = $m.$path.' &raquo; Add folder';
				}
				break;
				
			case 'overview':
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'read')) {
					if ($CMS->module_info['is_custom']) {
						// convert get parameters into real get variables
						$get_params = explode("?", $CMS->module_info['custom_path']);
						if (isset($get_params[1])) {
							$contents = "modules/".$get_params[0];
							$get_params = explode("&", $get_params[1]);
							foreach ($get_params as $get_param) {
								$get_param = explode("=", $get_param);
								$_GET[urldecode($get_param[0])] = urldecode($get_param[1]);
							}
						} else {
							$contents = "modules/".$CMS->module_info['custom_path'];
						}
						$title    = $m.$path;
					} else {
						$contents = "core/module_overview.php";
						$title    = $m.$path;
					}
				}
				break;
		}
	}
	
	
	if (!isset($contents) || $CMS_SESSION['cms_state'] == 'welcome') {
		if (file_exists("modules/welcome.php")) {
			$contents = "modules/welcome.php";
		} else {
			$contents = "core/welcome.php";
		}
		$title = $CMS_ENV['pxlcms_client'];
	}
	
	
	// rendering of the main body
	$widget = new Widget();
	$widget->height = '350px';
	$widget->title = $title;
	$widget->start();
	
	include $contents;
	
	$CMS_SESSION['last_state'] = $CMS_SESSION['cms_state'];
	
	unset($CMS_SESSION['cms_state']);
	
	$widget->stop();

?>