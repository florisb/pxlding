<?php
	if (isset($_POST['group_id'])) {
		$CMS_SESSION['group_id'] = (int) $_POST['group_id'];
	}
	
	if (!isset($groups)) {
		$CMS = new CMS();
		$groups = $CMS->getStructure();
	}

	$opened = false;
	
	$admin_only = "<img src='img/icons/lock.png' style='margin-left: 5px; title='Superadmin only' alt='Superadmin only' />";
	
	foreach ($groups as $group) {
		if ($group['id'] != $CMS_SESSION['group_id']) {
			continue;
		}
		foreach ($group['sections'] as $section) {
			$modules = $CMS->getModules($section['id']);
			$simulated_modules = array();
			$simulated_modules_rs = CMS_DB::mysql_query("SELECT `module_id` FROM `".$CMS_DB['prefix']."fields` WHERE `id` IN (SELECT `simulate_categories_for` FROM `".$CMS_DB['prefix']."modules`, `".$CMS_DB['prefix']."fields` WHERE `".$CMS_DB['prefix']."modules`.`simulate_categories_for` = `".$CMS_DB['prefix']."fields`.`id` AND `".$CMS_DB['prefix']."modules`.`id` != `".$CMS_DB['prefix']."fields`.`module_id`)");
			while ($simulated_modules_r = mysql_fetch_assoc($simulated_modules_rs)) {
				$simulated_modules[] = $simulated_modules_r['module_id'];
			}
			$m = array();
			foreach ($modules as $module) {
				if ($module['admin_only'] && $CMS_SESSION['user']['id'] != 0) continue;
				if ($module['hide_from_menu'] == 1 && $CMS_SESSION['user']['id'] != 0) continue;
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $module['id'], 'read')) {
					$icon = $module['icon_image'] != '' ? $module['icon_image'] : '_default.gif';
					if ($module['id'] == $CMS_SESSION['module_id']) $opened = $section['id'];
					if (in_array($module['id'], $simulated_modules) && $module['hide_from_menu'] != -1) {
						if ($CMS_SESSION['user']['id'] == 0) {
							$m[] = "<div class='item' style='color: #aaa; padding-bottom: 1px;' onclick=\"process_setting({'module_id': ".$module['id'].", 'category_id': ''});\">".$module['name'].$admin_only."</div>";
						}
					} else if ($module['hide_from_menu'] == 1) {
						$m[] = "<div class='item' style='color: #aaa; padding-bottom: 1px;' onclick=\"process_setting({'module_id': ".$module['id'].", 'category_id': ''});\">".$module['name'].$admin_only."</div>";
					} else {
						$m[] = "<div class='item' style='background-image: url(img/modules/".$icon."); padding-bottom: 1px;' onclick=\"process_setting({'module_id': ".$module['id'].", 'category_id': ''});\">".$module['name'].($module['admin_only'] ? $admin_only : '')."</div>";
					}
				}
			}
			if  (count($m)) {
				echo "<h3 style='margin-top: 0px; margin-bottom: 15px; cursor: pointer;' onclick='navigation_open(".$section['id'].");'>".$section['name']."</h3>";
				if (!$opened && !isset($CMS_SESSION['module_id'])) {
					$opened = $section['id'];
				}
				echo "<div class='nav_section' id='navigation_submenu_".$section['id']."' style='".($opened == $section['id'] ? '' : 'display: none').";'>";
				echo implode('', $m);
				echo "<br/>&nbsp;";
				echo "</div>";
			}
		}
	}
?>
<script type='text/javascript'>
	var open_navigation = '<?= $opened ?>';
</script>