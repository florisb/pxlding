<?php
	
	$CMS = new CMS($CMS_SESSION['module_id']);

	// CHECK RIGHTS
	if (!$CMS_SESSION['entry_id'] && !$CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'create')) {
		return;
	} else if (!$CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
		return;
	}
	// end of rights checking
	
	global $run_insert_events; //ugly, I know, but we've got to make it work somehow :(
	$run_insert_events = false;
	if (!$CMS_SESSION['entry_id']) {
		$run_insert_events = true;
		$CMS_SESSION['entry_id'] = $CMS->addEntry($CMS_SESSION['category_id']);
	}
	
	function num($n) {
		return str_replace(',', '.', $n);
	}
	
	// read posted variables from the html-form
	$form_prefix    = "cmsform_field_";
	$check_prefix   = "cmsform_checkbox_";
	$numeric_prefix = "cmsform_numeric_";
	$entry          = array('user_id' => $CMS_SESSION['user']['id']);

	foreach ($_POST as $key => $values)
	{
		if (substr($key, 0, strlen($form_prefix)) == $form_prefix)
		{
			$field_id = (int) str_replace($form_prefix, '', $key);
			//allow for multivalues
			$testprefix = $form_prefix.$field_id.'_';
			if (strlen($key) > strlen($testprefix) && substr($key, 0, strlen($testprefix)) == $testprefix) {
				$prop = str_replace($testprefix, '', $key);
				if (strlen($prop)) {
					$v = new stdClass();
					if (isset($entry[$field_id]) && strlen($entry[$field_id][0])) {
						$v = json_decode($entry[$field_id][0]);
					}
					$v->$prop = $values[0];
					$values = array(json_encode($v)); //wrap in array because CMS::saveEntry is gay and expects values wrapped in arrays....
				}
			}
			$entry[$field_id] = $values;
		}
		else
		{
			if (substr($key, 0, strlen($check_prefix)) == $check_prefix)
			{
				if (substr($key, 0, strlen($check_prefix."present_")) == $check_prefix."present_")
				{
					$field_id = (int) str_replace($check_prefix."present_", '', $key);
					$values = isset($_POST[$check_prefix."values_".$field_id]) ? $_POST[$check_prefix."values_".$field_id] : array();
					$entry[$field_id] = $values;
				}
			}
			else if (substr($key, 0, strlen($numeric_prefix)) == $numeric_prefix)
			{
				$field_id = (int) str_replace($numeric_prefix, '', $key);
				$entry[$field_id] = array_map('num', $values);
			}
		}
	}
	
	$CMS->saveEntry($CMS_SESSION['entry_id'], $entry);

	// debug log output?
	$show_log = 0;
	if ($show_log) {
		echo "<div style='background: #fff; padding: 15px; border: 1px solid #000;'>";
		echo $CMS->showLog();
		echo "</div>";
	}
	
?>