<?php
	include "upload.php";
	ini_set('memory_limit', '64M');
	
	// retrieve some data to determine if we may add another field value
	
	$field = mysql_fetch_assoc(CMS_DB::mysql_query("SELECT * FROM `".$CMS_DB['prefix']."fields` WHERE `id` = '".$fid."'"));
	if ($CMS_SESSION['entry_id']) {
		if ($field['multilingual']) {
			$now_values = mysql_fetch_row(CMS_DB::mysql_query("SELECT count(*) FROM `".$CMS_DB['prefix']."m_files` WHERE `field_id` = '".$fid."' AND `language_id` = ".$CMS_SESSION['language']['id']." AND `entry_id` = ".$CMS_SESSION['entry_id']));
		} else {
			$now_values = mysql_fetch_row(CMS_DB::mysql_query("SELECT count(*) FROM `".$CMS_DB['prefix']."m_files` WHERE `field_id` = '".$fid."' AND `entry_id` = ".$CMS_SESSION['entry_id']));
		}
	} else {
		// no entry existing yet
		$now_values = array(0);
	}
	
	imglog("allowed number of uploads: ".($field['value_count'] > 0 ? $field['value_count'] : 'unlimited').", current number of uploads: ".$now_values[0]);
		
	$disallowed_extensions = array('php');
	
	if ($now_values[0] >= $field['value_count'] && $field['value_count'] > 0)
	{
		imglog("not processing request, max entry values reached");
	}
	else if (in_array($fext, $disallowed_extensions))
	{
		imglog("not moving file, extension (".$fext.") is disallowed (".implode(',', $disallowed_extensions).")");
	}
	else
	{
		// find not-existing target
		$target = find_file_target($target_path, $fname, $fext);		
		imglog("uploaded filename: '".$fname.'.'.$fext."'");
		imglog("moving temporary upload '".$_FILES['Filedata']['tmp_name']."' to '".$target."'");
		
		if(move_uploaded_file($_FILES['Filedata']['tmp_name'], $target_path . $target))
		{
			imglog("instantiating CMS");
			$CMS = new CMS();
			imglog("setting module ".$CMS_SESSION['module_id']);
			$CMS->setModule($CMS_SESSION['module_id']);
			
			// ensure we have an entry to link to
			if (!$CMS_SESSION['entry_id']) {
				imglog("no entry... creating ");
				imglog("cms table: ".$CMS->table());
				$CMS_SESSION['entry_id'] = $CMS->addEntry($CMS_SESSION['category_id']);
				imglog("created new entry: ".$CMS_SESSION['entry_id']);
			}
			
			imglog("linking uploaded file to entry ".$CMS_SESSION['entry_id']);
			
			if ($field['multilingual']) {
				$max_pos = mysql_fetch_row(CMS_DB::mysql_query("SELECT MAX(position) FROM `".$CMS_DB['prefix']."m_files` WHERE `entry_id` = '".$CMS_SESSION['entry_id']."' AND `field_id` = '".$fid."' AND `language_id` = ".$CMS_SESSION['language']['id']));
				CMS_DB::mysql_query("INSERT INTO `".$CMS_DB['prefix']."m_files` ( `entry_id` , `field_id` , `language_id` , `file` , `extension` , `uploaded` , `position` ) VALUES ( '".$CMS_SESSION['entry_id']."', '".$fid."', '".$CMS_SESSION['language']['id']."', '".$target."', '".$fext."', '".time()."', '".($max_pos[0]+1)."')");
			} else {
				$max_pos = mysql_fetch_row(CMS_DB::mysql_query("SELECT MAX(position) FROM `".$CMS_DB['prefix']."m_files` WHERE `entry_id` = '".$CMS_SESSION['entry_id']."' AND `field_id` = '".$fid."'"));
				CMS_DB::mysql_query("INSERT INTO `".$CMS_DB['prefix']."m_files` ( `entry_id` , `field_id` , `file` , `extension` , `uploaded` , `position` ) VALUES ( '".$CMS_SESSION['entry_id']."', '".$fid."', '".$target."', '".$fext."', '".time()."', '".($max_pos[0]+1)."')");
			}
			
			$upload_id = mysql_insert_id();
			
			imglog("Done");
		}
		else
		{
			imglog("did not copy file, error moving from temp-location to target-location");
		}
	}
	
	writelog();
?>