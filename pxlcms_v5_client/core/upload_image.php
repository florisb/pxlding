<?php
	include "upload.php";
	ini_set('memory_limit', '64M');
	
	// retrieve some data to determine if we may add another field value
	
	$field = mysql_fetch_assoc(CMS_DB::mysql_query("SELECT * FROM `".$CMS_DB['prefix']."fields` WHERE `id` = '".$fid."'"));
	if ($CMS_SESSION['entry_id']) {
		if($field['multilingual']) {
			$now_values = mysql_fetch_row(CMS_DB::mysql_query("SELECT count(*) FROM `".$CMS_DB['prefix']."m_images` WHERE `field_id` = '".$fid."' AND `language_id` = " . $CMS_SESSION['language']['id'] . " AND `entry_id` = ".$CMS_SESSION['entry_id']));
		} else {
			$now_values = mysql_fetch_row(CMS_DB::mysql_query("SELECT count(*) FROM `".$CMS_DB['prefix']."m_images` WHERE `field_id` = '".$fid."' AND `entry_id` = ".$CMS_SESSION['entry_id']));
		}
	} else {
		// no entry existing yet
		$now_values = array(0);
	}
	
	imglog("allowed number of uploads: ".($field['value_count'] > 0 ? $field['value_count'] : 'unlimited').", current number of uploads: ".$now_values[0]);
	
	$allowed_extensions = array('jpg', 'jpeg', 'gif', 'png');
	
	if ($now_values[0] >= $field['value_count'] && $field['value_count'] > 0)
	{
		imglog("not processing request, max entry values reached");
	}
	else if (!in_array($fext, $allowed_extensions))
	{
		imglog("not moving file, extension (".$fext.") not allowed (".implode(',', $allowed_extensions).")");
	}
	else
	{
		// find not-existing target
		$target = find_file_target($target_path, $fname, $fext);
		imglog("uploaded filename: '".$fname.'.'.$fext."'");
		imglog("moving temporary upload '".$_FILES['Filedata']['tmp_name']."' to '".$target."'");
		
		if(move_uploaded_file($_FILES['Filedata']['tmp_name'], $target_path . $target))
		{
			
			Event::fire($field['module_id'], 'preUploadImage', array(
				"entry_id" 	=> $CMS_SESSION['entry_id'],
				"file" 		=> $target,
				"extension" => $fext
			));
			
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
			
			imglog("linking uploaded image to entry ".$CMS_SESSION['entry_id']);
			
			if($field['multilingual']) {
				$max_pos = mysql_fetch_row(CMS_DB::mysql_query("SELECT MAX(position) FROM `".$CMS_DB['prefix']."m_images` WHERE `entry_id` = '".$CMS_SESSION['entry_id']."' AND `field_id` = '".$fid."' AND `language_id` = '" . $CMS_SESSION['language']['id'] . "'"));
				CMS_DB::mysql_query("INSERT INTO `".$CMS_DB['prefix']."m_images` ( `entry_id` , `field_id` , `language_id`, `file` , `extension` , `uploaded` , `position` ) VALUES ( '".$CMS_SESSION['entry_id']."', '".$fid."', '" . $CMS_SESSION['language']['id'] . "', '".$target."', '".$fext."', '".time()."', '".($max_pos[0]+1)."')");
			}
			else {
				$max_pos = mysql_fetch_row(CMS_DB::mysql_query("SELECT MAX(position) FROM `".$CMS_DB['prefix']."m_images` WHERE `entry_id` = '".$CMS_SESSION['entry_id']."' AND `field_id` = '".$fid."'"));
				CMS_DB::mysql_query("INSERT INTO `".$CMS_DB['prefix']."m_images` ( `entry_id` , `field_id` , `file` , `extension` , `uploaded` , `position` ) VALUES ( '".$CMS_SESSION['entry_id']."', '".$fid."', '".$target."', '".$fext."', '".time()."', '".($max_pos[0]+1)."')");
			}
			$upload_id = mysql_insert_id();
			
			imglog("creating pxlcms_ thumbnail @ 80 x 80");
			$large = new clsImage();
			$large->loadfile($target);
			$large->resizeAspectCrop(80,80);
			$large->savefile("pxl80_" . $target);
			CMS_DB::mysql_query("INSERT INTO `".$CMS_DB['prefix']."m_thumbs` ( `image_id` , `resize_id`, `filename` ) VALUES ('".$upload_id."', NULL, 'pxl80_".$target."')");
			imglog("creating mini_ thumbnail @ 20 x 20");
			$large->resize(20,20);
			$large->savefile("pxl20_" . $target);
			CMS_DB::mysql_query("INSERT INTO `".$CMS_DB['prefix']."m_thumbs` ( `image_id` , `resize_id`, `filename` ) VALUES ('".$upload_id."', NULL, 'pxl20_".$target."')");
			
			$resizes = $CMS->getResizes($fid);
			foreach ($resizes as $resize) {
				imglog("creating ".$resize['prefix']." thumbnail @ ".$resize['width']." x ".$resize['height']);
				$large = new clsImage();
				$large->loadfile($target);
				
				if ($resize['no_cropping']) {
					if (strtolower($resize['background_color'][0]) == 't') {
						$rgb = false;
					} else {
						$rgb = str_split($resize['background_color'], 2);
						$rgb = array_map('hexdec', $rgb);
					}
					// imglog(var_dump($rgb));
					$large->resizeAspect($resize['width'], $resize['height'], $rgb); 
				} else {
					$large->resizeAspectCrop($resize['width'],$resize['height']);
				}
				
				if ($resize['watermark']) {
					$large->watermark('../watermarks/'.$resize['watermark_image'], array($resize['watermark_left'],$resize['watermark_top']));
				}
				
				if ($resize['make_grayscale']) {
					$large->make_grayscale();
				}
				
				if ($resize['corners']) {
					$large->corners('../corners/'.$resize['corners_name']);
				}
				
				if ($resize['trim']) {
					imglog("trimming transparency");
					$large->trim_trans();
				}
				
				$large->savefile($resize['prefix'] . $target);
				CMS_DB::mysql_query("INSERT INTO `".$CMS_DB['prefix']."m_thumbs` ( `image_id` , `resize_id`, `filename` ) VALUES ('".$upload_id."', ".$resize['resize_id'].", '".$resize['prefix'].$target."')");
			}
			
			imglog("Done");
			
			Event::fire($field['module_id'], 'postUploadImage', array(
				"entry_id" 	=> $CMS_SESSION['entry_id'],
				"file" 		=> $target,
				"extension" => $fext
			));
			
			//Delete original file?
			if ($CMS_EXTRA['delete_uploaded_img_originals']) {
				if(unlink($target_path.$target)) imglog("Deleted original file");
				else imglog("Failed to delete original file (".$target_path.$target.")");
			}
		}
		else
		{
			imglog("did not copy file, error moving from temp-location to target-location");
		}
	}
	
	writelog();
?>