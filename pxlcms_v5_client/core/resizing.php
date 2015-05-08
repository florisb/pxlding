<?php
	if (isset($_POST['gogogo']))
	{
		include "classes/image/class.image.php5.php";
		ini_set('memory_limit', '64M');
		session_start();
		set_time_limit(0);
		
		$resize = mysql_fetch_assoc(CMS_DB::mysql_query("SELECT * FROM `".$CMS_DB['prefix']."field_options_resizes` WHERE `id` = ".$_POST['resize_id']));
		$images = CMS_DB::mysql_query("SELECT * FROM `".$CMS_DB['prefix']."m_images` WHERE `field_id` = ".$resize['field_id']." ORDER BY `id` LIMIT ".((int) $_POST['start']).",".((int) $_POST['batch_size']));
		
		$resize_count = 0;
		
		CMS_DB::mysql_query("DELETE FROM `".$CMS_DB['prefix']."m_thumbs` WHERE `resize_id` = ".((int)$resize['id']));
		while ($image = mysql_fetch_assoc($images)) {
			$resize_count++;
			$target = $image['file'];
			
			if (!file_exists('uploads/'.$image['file'])) {
				echo "<script type='text/javascript'>$('missing_files').innerHTML = $('missing_files').innerHTML + 'MISSING: ".htmlentities($image['file'], ENT_QUOTES)." (entry_id: ".$image['entry_id'].")<br/>';</script>";
			} else {
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
					$large->watermark('watermarks/'.$resize['watermark_image'], array($resize['watermark_left'],$resize['watermark_top']));
				}
				
				if ($resize['make_grayscale']) {
					$large->make_grayscale();
				}
				
				if ($resize['corners']) {
					$large->corners('corners/'.$resize['corners_name']);
				}
				
				if ($resize['trim']) {
					$large->trim_trans();
				}
				
				$large->savefile($resize['prefix'] . $target);
				CMS_DB::mysql_query("INSERT INTO `".$CMS_DB['prefix']."m_thumbs` ( `image_id` , `resize_id`, `filename` ) VALUES ('".$image['id']."', ".$resize['id'].", '".$resize['prefix'].$target."')");
			}
		}
			
		echo min(round((($_POST['start'] + $_POST['batch_size']) / $_POST['total_count']) * 100, 2), 100).' %';
		exit;
	}
		
	echo "<h2>Resize generator tool</h2>";
	
	if ($CMS_EXTRA['delete_uploaded_img_originals'])
	{
		echo "Unfortunately, the setting 'delete_uploaded_img_originals' is turned on. The system can therefore not generate new thumbnails, because the original images have already been deleted.";
	}
	else
	{
		if (isset($_POST['resize_id']) && $_POST['resize_id'] != '')
		{
			$resize = mysql_fetch_assoc(CMS_DB::mysql_query("SELECT * FROM `".$CMS_DB['prefix']."field_options_resizes` WHERE `id` = ".$_POST['resize_id']));
			
			$images = mysql_fetch_row(CMS_DB::mysql_query("SELECT count(*) FROM `".$CMS_DB['prefix']."m_images` WHERE `field_id` = ".$resize['field_id']));
			
			echo "Image count <span style='background: #f2f2f2; border: 1px solid #aaa; padding: 2px 5px;'>".$images[0]."</span> , batch size <select id='resize_batch_size_select' style='display: inline; width: 60px;'><option value='20'>20</option><option value='50'>50</option><option value='100'>100</option></select>";
			
			echo "<div style='margin: 20px 0; padding: 20px; width: 400px; font-size: 16px; background: #eee; text-align: center; border: 1px solid #aaa;' id='resize_batch_progress'>0 %</div>";
			echo "<div id='missing_files'></div>";
			
			echo "<input type='submit' id='gogogo_resize' value='Run resizes' onclick=\"$('resize_batch_progress').innerHTML = 'Working first batch...'; do_resize_now('".$_POST['resize_id']."', $('resize_batch_size_select').value, 0, ".$images[0].");\"/>";
			
		}
		else
		{	
			$image_fields = CMS_DB::mysql_query("SELECT `s`.`name` AS section_name, `g`.`name` AS group_name, `m`.`name` AS module_name, `f`.`name` AS field_name, `f`.`id` FROM `".$CMS_DB['prefix']."fields` f, `".$CMS_DB['prefix']."modules` m, `".$CMS_DB['prefix']."groups` g, `".$CMS_DB['prefix']."sections` s WHERE `f`.`module_id` = `m`.`id` AND `g`.`id` = `s`.`group_id` AND `s`.`id` = `m`.`section_id`  AND `f`.`field_type_id`  IN ( SELECT `id` FROM `".$CMS_DB['prefix']."field_types` WHERE `form_element` IN ('image', 'image_multi')) ORDER BY `g`.`position`, `s`.`position`, `m`.`position`, `f`.`position`");
			
			$last_section = '';
			echo "<form action='.' method='post' onsubmit=\"$('work_in_progress_resizing').style.display = ''; $('create_thumbs_go').style.display = 'none';\">";
				echo "Select image:<br/><select id='resize_id' name='resize_id'>";
				echo "<option value=''>-- choose your resize --</option>";
				while ($image_field = mysql_fetch_assoc($image_fields)) {
					if ($last_section != $image_field['section_name']) {
						if ($last_section != '') {
							echo "</optgroup>";
						}
						echo "<optgroup label='".htmlentities($image_field['group_name']).' &gt; '.htmlentities($image_field['section_name'])."'>";
					}
					$resizes = CMS_DB::mysql_query("SELECT * FROM `".$CMS_DB['prefix']."field_options_resizes` WHERE `field_id` = ".$image_field['id']);
					while ($resize = mysql_fetch_assoc($resizes)) {
						echo "<option value='".$resize['id']."'>".$image_field['module_name']." &rarr; ".$image_field['field_name']." &rarr; ".$resize['prefix']." (".$resize['width']."x".$resize['height'].")</option>";
					}
					$last_section = $image_field['section_name'];
				}
				echo "</select><br/>";
				
				echo "<input type='submit' value='Select batch size' id='create_thumbs_go' />";
				echo "<br /><br />OR<br /><br /><input type='button' value='Cleanup' id='cleanup' onclick=\"process_setting({special_page: 'cleanup'});\" />";
				echo '<span style="font-size: 80%;">Warning! The cleanup-process can be pretty server-intensive, depending on the number of (stray) files. Please be patient while loading and executing the page.</span>';
			echo "</form>";
		}
	}
?>
<script type='text/javascript'>
	var do_resize_now = function(resize_id, batch_size, start, total_count) {
		
		start       = start || 0;
		batch_size  = parseInt(batch_size);
		total_count = parseInt(total_count);
		resize_id   = parseInt(resize_id);
		
		if (start == 0) {
			$('work_in_progress_resizing').style.display = '';
		}
		
		$('resize_batch_size_select').disabled = true;
		$('gogogo_resize').setStyle({ display: 'none' });
		
		new Ajax.Updater(
			'resize_batch_progress',
			'ajax.php',
			{
				method: 'post',
				parameters: 'page=resizing.php&gogogo=1&total_count=' + total_count + '&batch_size=' + batch_size + '&start=' + start + '&resize_id=' + resize_id,
				requestHeaders: ['Expires', 'Thu, 16 May 2001 10:10:10 GMT', 'Cache-Control', 'no-cache, must-revalidate', 'Pragma', 'no-cache'],
				evalScripts: true,
				onComplete: function(result) {
					if (start < total_count) {
						do_resize_now(resize_id, batch_size, start + batch_size, total_count);
						$('work_in_progress_resizing').style.display = '';
					} else {
						$('work_in_progress_resizing').style.display = 'none';
					}
				}
			});
	}
</script>

<?php
	echo "<div id='work_in_progress_resizing' style='display: none; width: 160px; padding: 7px 17px 10px 14px; border: 1px dashed #888; background: #fff; margin-bottom: 20px;'>";
	echo "<img style='position: relative; top: 3px;' src='img/loading_white.gif' /> &nbsp; &nbsp; &nbsp; Creating thumbnails";
	echo "</div>";
?>