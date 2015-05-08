<?php

	$CMS = new CMS();
	
	$users = $CMS->getUsers($CMS_SESSION['user']['id']);
	// pr($users);
	
	$groups = $CMS->getStructure();
	foreach ($groups as &$group) {
		if (count($group['sections'])) {
			foreach ($group['sections'] as &$section) {
				$section['modules'] = $CMS->getModules($section['id']);
			}
		}
	}
	unset($section);
	unset($group);
	
	if (count($users) == 0) {
		echo "No users were created yet.";
		echo "<input type='submit' value='Create new user' style='margin-top: 30px;' onclick=\"process_setting({special_state: 'add_user'});\" />";
		return;
	}
	
	$actions = array('create', 'read', 'update', 'delete');
	
	
	echo "<table border='0' cellspacing='0' cellpadding='0' id='user_rights'>";
	
	echo "<tr>";
	echo "<td class='nopad' style='height: 17px; width: 17px; background: url(img/blue/tl.gif);'></td>";
	echo "<td class='nopad' style='background: #d5d7da' colspan='".(1 + count($users))."'>&nbsp;</td>";
	echo "<td class='nopad' style='height: 17px; width: 17px; background: url(img/blue/tr.gif);'></td>";
	echo "</tr>";
	
	echo "<tr style='background: #d5d7da'>";
	echo "<td style='border-right: 1px solid #fff;' colspan='2'>&nbsp;</td>";
	foreach ($users as $u) {
		echo "<td style='border-right: 1px solid #fff; border-top: 1px solid #fff;'>".$u['fullname']."<br/>";
		echo "<img alt='Give all rights' title='Give all rights' src='img/icons/activate.gif' onclick=\"if (confirm('Really grant all rights to this user?')) { refresh('form_processing=all_rights&user_id=".$u['id']."'); } return false;\" style='float: right; cursor: pointer;' />";
		echo "<img alt='Edit user' title='Edit user' src='img/icons/edit.gif' onclick=\"process_setting({special_state: 'edit_user', edit_user_id: ".$u['id']."}); return false;\" style='cursor: pointer;' />";
		echo "<img alt='Delete user' title='Delete user' src='img/icons/delete.gif' onclick=\"if (confirm('Really delete this user?')) { refresh('form_processing=delete_user&user_id=".$u['id']."'); } return false;\" style='cursor: pointer;' />";
		echo "</td>";
	}
	echo "<td>&nbsp;</td>";
	echo "</tr>";
	
	// languages (header)
	echo "<tr style='background: #6e7278'>";
		echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
		echo "<td style='border-right: 1px solid #fff; color: #fff; font-weight: bold; border-top: 1px solid #fff;'><img src='img/icons/multilingual.gif' alt='' /> Languages</td>";
		foreach ($users as $u) {
			echo "<td style='border-right: 1px solid #fff; border-top: 1px solid #fff;'>&nbsp;</td>";
		}
		echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
	echo "</tr>";
	
	// languages (items)
	$languages = $CMS->languages();
	foreach ($languages as $language) {
		echo "<tr style='background: #d5d7da'>";
			echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
			echo "<td style='border-top: 1px solid #fff; padding-left: 20px; padding-right: 15px; border-right: 1px solid #fff;'>".$language['language']."</td>";
			foreach ($users as $u) {
				$checked = $CMS->checkLanguageAllowed($u['id'], $language['id']);
				echo "<td style='min-width: 110px; border-right: 1px solid #fff; border-top: 1px solid #fff; text-align: center;'>";
					echo "<input ".($checked ? 'checked' : '')." onclick=\"action('', 'form_processing=toggle_user_language&user_id=".$u['id']."&language_id=".$language['id']."');\" type='checkbox' style='display: inline; margin: 0px; padding: 0px;'/>";
				echo "</td>";
			}
			echo "<td>&nbsp;</td>";
		echo "</tr>";
	}
	
	// reference filter (header)
	if ($CMS_SESSION['user']['id'] == 0)
	{
		echo "<tr class='header'>";
			echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
			echo "<td style='border-right: 1px solid #fff; border-top: 1px solid #fff;'>Reference Restriction</td>";
			$selected_ref_filter = false;
			foreach ($users as $u) {
				echo "<td style='min-width: 110px; border-right: 1px solid #fff; border-top: 1px solid #fff; text-align: center;'>";
					echo "<select onchange=\"load_ref_filter_entries(this, 'entries_".$u['id']."', ".$u['id'].");\">";
					echo "<option value=''></option>";
					foreach ($groups as $group) {
						echo "<optgroup label='".$group['name']."'>";
						if (count($group['sections'])) {
							foreach ($group['sections'] as $section) {
								foreach ($section['modules'] as $module) {
									if (!$selected_ref_filter) {
										$selected_ref_filter = ($u['ref_filter_module_id'] == $module['id']) ? $module['id'] : false;
									}
									echo "<option ".($u['ref_filter_module_id'] == $module['id'] ? 'selected' : '')." value='".$module['id']."'>".$section['name'].': '.$module['name']."</option>";
								}
							}
						}
						echo "</optgroup>";
					}
					echo "</select>";
					echo "<div id='entries_".$u['id']."'>";	
						if ($selected_ref_filter) {
							$_POST['user_id']   = $u['id'];
							$_POST['module_id'] = $selected_ref_filter;
							$_POST['selected_ref_filter'] = $u['ref_filter_entry_id'];
							include "user_module_entries.php";
						}
					echo "</div>";
				echo "</td>";
			}
			echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
		echo "</tr>";
	}
	

	
	foreach ($groups as $group) {
		if (count($group['sections']))
		{
			echo "<tr style='background: #6e7278'>";
				echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
				echo "<td style='border-right: 1px solid #fff; color: #fff; font-weight: bold; border-top: 1px solid #fff;'>".$group['name']."</td>";
				foreach ($users as $u) {
					echo "<td style='border-right: 1px solid #fff; border-top: 1px solid #fff;'>&nbsp;</td>";
				}
				echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
			echo "</tr>";
		}
		
		foreach ($group['sections'] as $section) {
			$modules = $section['modules'];
			$m = array();
			
			foreach ($modules as $module) {
				if ($module['admin_only']) continue;
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $module['id'], 'read')) {
					$m[] = $module;
				}
			}
			
			if  (count($m)) {
				echo "<tr class='header'>";
				echo "<td></td>";
				echo "<td style='border-right: 1px solid #fff; padding-right: 15px;'>".$section['name']."</td>";
				foreach ($users as $u) {
					echo "<td style='border-right: 1px solid #fff; border-top: 1px solid #fff;'>";
					echo "<span style='font-style: italic; color: #8f9297;'>".$u['fullname']."</span>";
					echo "</td>";
				}
				echo "<td></td>";
				echo "</tr>";
				for ($mc = 0; $mc < count($m); $mc++) {
					$module = $m[$mc];
					$last   = $mc == (count($m) - 1);
					echo "<tr style='background: #d5d7da'>";
					echo "<td>&nbsp;</td><td style='padding-left: 20px; padding-right: 15px; border-right: 1px solid #fff;'>".$module['name']."</td>";
					foreach ($users as $u) {
						echo "<td style='min-width: 110px; border-right: 1px solid #fff; ".(!$last ? "border-bottom: 1px solid #fff;" : "")."'>";
						foreach ($actions as $action) {
							if ($CMS->checkRights($CMS_SESSION['user']['id'], $module['id'], $action)) {
								echo "<div style='float: left; text-align: center; margin-right: 10px;'>";
								echo "<img src='img/rights/".$action.".gif' alt='".$action."' title='".$action."' />";
								echo "<br/>";
								echo "<input ".($CMS->module_rights[$module['id']][$action] == 0 ? 'disabled' :'')." onclick=\"action('', 'form_processing=toggle_user_right&user_id=".$u['id']."&module_id=".$module['id']."&action=".$action."');\" type='checkbox' ".($CMS->checkRights($u['id'], $module['id'], $action) ? 'checked' : '')." style='display: inline; margin: 0px; padding: 0px;'/>";
								echo "</div>";
							}
						}
						echo "</td>";
					}
					echo "<td>&nbsp;</td>";
					echo "</tr>";
				}
			}
		}
	}
	
	echo "<tr>";
	echo "<td class='nopad' style='height: 17px; width: 17px; background: url(img/blue/bl.gif);'></td>";
	echo "<td class='nopad' style='background: #d5d7da' colspan='".(1 + count($users))."'>&nbsp;</td>";
	echo "<td class='nopad' style='height: 17px; width: 17px; background: url(img/blue/br.gif);'></td>";
	echo "</tr>";
	
	echo "</table>";
	
	
	echo "<input type='submit' value='Create new user' style='margin-top: 30px;' onclick=\"process_setting({special_state: 'add_user'});\" />";
	
?>