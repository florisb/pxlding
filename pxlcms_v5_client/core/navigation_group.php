<?php
	foreach ($g as $group) {
		$has_modules = false;
		foreach ($group['sections'] as $section) {
			$modules = $CMS->getModules($section['id']);
			foreach ($modules as $module) {
				if ($module['admin_only'] && $CMS_SESSION['user']['id'] != 0) continue;
				if ($module['hide_from_menu'] && $CMS_SESSION['user']['id'] != 0) continue;
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $module['id'], 'read')) {
					$has_modules = true;
				}
			}
		}
		if ($has_modules) {
			echo "<a href='#' class='group_nav_select ".($group['id'] == $CMS_SESSION['group_id'] ? 'group_nav_selected' : '')."' id='group_nav_select_".$group['id']."' onclick=\"switch_navigation(".$group['id']."); return false;\">".$group['name']."</a>";
		}
	}