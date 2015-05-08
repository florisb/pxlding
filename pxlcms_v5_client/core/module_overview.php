<?php
	$overview = true;

	$CMS->recursive = 1;
	$users = $CMS->getUsers();
	$users[0] = array('username' => '<i>Administrator</i>');

	$fields         = $CMS->fields();
	$field_types    = array();
	$display_fields = array();
	$usr_ref_filter = false;

	foreach ($fields as $field) {
		if ($field['refers_to_module'] > 0 && $field['refers_to_module'] == $CMS_SESSION['user']['ref_filter_module_id'] && $field['field_type_id'] == 16) {
			$usr_ref_filter = $field;
		}

		if ($field['identifier']) {
			if ($field['refers_to_module'] > 0) {
				if ($field['refers_to_module'] != $CMS_SESSION['user']['ref_filter_module_id']) {
					$display_fields[] = $field;
				}
			} else {
				$display_fields[] = $field;
			}
		}
		$field_types[$field['cms_name']] = $field['form_element'];
	}

	if ($CMS_SESSION['user']['id'] === 0 && $CMS->module_info['view_own_entries_only']) {
		$display_fields[] = 'USERNAME';
	}

	if (count($display_fields) == 0) {
		echo "<h1>Invalid module-display configuration.</h1>";
		echo "This can have 2 reasons:";
		echo "<ol>";
		echo "<li style='margin-bottom: 40px;'>You are trying to view a non-existing module<br/><br/><i>This happens when multiple CMS v5 are installed on the same server, and a user switches CMS without logging out / logging in. Try to solve the issue by changing modules through the menu on the left, or by logging out and in again.</i></li>";
		echo "<li>No fields were marked as identifier<br/><br/><i>Please notify your Pixelindustries developer</i></li>";
		echo "</ol>";
		return;
	}

	//set search value to variable
	if(isset($_POST['search'])) {
		$CMS_SESSION['search'] = array();
		$CMS_SESSION['search'][$CMS_SESSION['module_id']] = $_POST['search'];
		$CMS_SESSION['page'] = 0;
	}
	if(isset($CMS_SESSION['search'][$CMS_SESSION['module_id']]) && !empty($CMS_SESSION['search'][$CMS_SESSION['module_id']])) {
		$_search = $CMS_SESSION['search'][$CMS_SESSION['module_id']];
	}

	// allow CMS_EXTRA related items filter for backwards compatibility
	// (its removed from config.example.php so shouldnt be propagated)
	if ($CMS_EXTRA['related_items_filter'] || $CMS->module_info['related_items_filter']) {
		$passive_referencing_fields = $CMS->getPassiveReferencingFields();
	} else {
		$passive_referencing_fields = array();
	}

	/************************
	       HEADER BOXES
	*************************/
	if ($CMS->module_info['introduction'] != '') {
		echo "<div style='padding: 10px 10px 10px 71px; background: url(img/information.jpg) 10px 6px no-repeat #f0f0f0; border: 1px solid #ccc; margin: 0 0 20px 0;'>";
		echo "<b>".$CMS->module_info['name']."</b><br/><br/>";
		echo nl2br($CMS->module_info['introduction']);
		echo "</div>";
	}

	if (isset($_search)) {
		echo "<div style='padding: 10px 10px 10px 71px; background: url(img/search.jpg) 10px 6px no-repeat #f0f0f0; border: 1px solid #ccc; margin: 0 0 20px 0;'>";
		echo "<b>Search results for '".$_search."'</b><br/><br/>";
		echo "The current list of items is a search results view. It shows items from any folder. To add an item or folder, please open this module in normal view.";
		echo "</div>";
	}


	/************************
	       SEARCH
	*************************/
	if ($CMS->module_info['searchable']) {
		echo "<div style='text-align: right; padding-right: 2px; margin-bottom: 10px;'>";
			echo "<form onsubmit=\"refresh('search='+encodeURIComponent($('cms_search_term').value)); return false; \">";
				echo "<input type='text' id='cms_search_term' name='search' style='display: inline; margin: 0 0 0 10px;' />";
				echo "<input type='submit' value='Search' style='display: inline; margin: 0 0 -2px 10px;' />";
			echo "</form>";
		echo "</div>";
	}

	/************************
	       CSV EXPORT
	*************************/
	if ($CMS->module_info['csv_export']) {
		echo "<div style='text-align: right; padding-right: 2px; margin-bottom: 10px;'>";
			echo "<form onsubmit=\"window.location.href = 'core/dump.php'; return false; \">";
				echo "<img src='img/icons/excel.png' alt='' />";
				echo "<input type='submit' value='Download CSV' style='display: inline; margin: 0 0 -2px 5px;' />";
			echo "</form>";
		echo "</div>";
	}


	/************************
	  TABLE GENERATION CODE
	*************************/

	$column_pointer = 0;

	// configure column widths
	// 1st = 24px
	// rest = rest divided by (# displayed fields)
	// actions = 20 * ( (# actions = 3) + (# passive ref) ) + 8
	$column_widths  = array();
	$first_column   = 30;
	$actions_column = $first_column * 3;
	$last_column    = $first_column * count($passive_referencing_fields);
	$column_width   = floor(($CMS_SESSION['window_width'] - ($first_column + $actions_column + $last_column + 210 + $CMS_EXTRA['menu_width'])) / count($display_fields));

	$column_widths[] = $first_column;
	for  ($i = 0; $i < count($display_fields); $i++) {
		$column_widths[] = $column_width;
	}
	$column_widths[] = $actions_column;
	if (count($passive_referencing_fields)) $column_widths[] = $last_column;


	function row_start($id = null, $class = '', $properties = array()) {
		global $column_pointer, $column_widths;
		$column_pointer = 0;
		if (!isset($properties['style'])) $properties['style'] = '';

		$properties['border']      = 0;
		$properties['cellspacing'] = 0;
		$properties['cellpadding'] = 0;
		$properties['class']       = $class;
		$properties['id']          = 'row_'.$id;
		$properties['style']       .= 'width: 100%; border-bottom: 1px solid #fff; border-right: 1px solid #fff;';

		echo "<table ".Tools::properties_html($properties).">";
		echo "<tr>";
	}

	function column($b, $col_atts = '', $style_extras = '', $expand = true) {
		global $column_widths, $column_pointer;
		echo "<td ".$col_atts." style='border-left: 1px solid #fff; width: ".($column_widths[$column_pointer] - 10)."px; padding: 5px; ".$style_extras."'><div style='height: 17px; width: ".($column_widths[$column_pointer] - 10)."px; overflow: hidden;'".($expand ? " onclick='expand(this);'" : "").">".$b."</div></td>";
		$column_pointer++;
	}

	function row_stop() {
		echo "</tr>";
		echo "</table>";
	}

	/************************
	       TABLE HEADER
	*************************/
	row_start('', 'header');
	column('', '', '', false);

	// process sorting configuration
	if (isset($_POST['cms_sorting'])) {
		$CMS_SESSION['cms_sorting'][$CMS_SESSION['module_id']] = $_POST['cms_sorting'];
		$CMS_SESSION['cms_direction'][$CMS_SESSION['module_id']] = $_POST['cms_direction'];
	}

	foreach ($display_fields as $field) {
		$suffix = '';
		if ($field['multilingual']) {
			$suffix = " <span style='color: #ccc; font-style: italic;'>(".$CMS_SESSION['language']['language'].")</span> <img src='img/icons/multilingual.gif' style='vertical-align: middle; margin-left: 5px;'/>";
		}
		if(isset($field['display_name']) && !empty($field['display_name'])) {
			$field['name'] = $field['display_name'];
		}
		if ($field == 'USERNAME') {
			column('User', '', '', false);
		} else if ($field['db_field'] != '#REF' && $CMS->module_info['allow_column_sorting'] && !$CMS->module_info['sort_entries_manually']) {
			$direction = ($CMS_SESSION['cms_direction'][$CMS_SESSION['module_id']] == 'asc' && $CMS_SESSION['cms_sorting'][$CMS_SESSION['module_id']] == $field['cms_name'] ? 'desc' : 'asc');
			$column_attributes = "onmouseover=\"this.style.background = '#3e5582';\" onmouseout=\"this.style.background = 'transparent';\" onclick=\"refresh('cms_sorting=".$field['cms_name']."&cms_direction=".$direction."');\"";
			$style_extras      = 'cursor: pointer;';

			if ($CMS_SESSION['cms_sorting'][$CMS_SESSION['module_id']] == $field['cms_name']) {
				$style_extras .= 'background: #273755;';
				$column_attributes = str_replace("transparent", '#273755', $column_attributes); // ugly work around
				column($field['name'].$suffix.' '.($CMS_SESSION['cms_direction'][$CMS_SESSION['module_id']] == 'asc' ? "&nbsp; &uArr; <span style='color: #d0d3d7; font-size: 11px;'>a-z</span>" : "&nbsp; &dArr; <span style='color: #d0d3d7; font-size: 11px;'>z-a</span>"), $column_attributes, $style_extras, false);
			} else {
				column("<div>".$field['name'].$suffix."</div>", $column_attributes, $style_extras, false);
			}
		} else {
			column($field['name'].$suffix, '', '', false);
		}
	}
	column('Actions', '', '', false);
	if (count($passive_referencing_fields)) {
		if (count($passive_referencing_fields) > 2) column('Relations', '', '', false);
		else column("&nbsp;", '', '', false);
	}
	row_stop();



	/************************
	   FOLDERS (CATEGORIES)
	*************************/
	// read

	if (!isset($_search) && empty($CMS_SESSION['categories_simulator']))
	{
		$CMS->setCategory($CMS_SESSION['category_id']);
		$categories = $CMS->getCategories();
		// simulate parent category?
		if ($current_category_depth >= 1) {
			$c = $CMS->getCategory($CMS_SESSION['category_id']);
			$p = $c['parent_category_id'];
			if ($p == $CMS_SESSION['category_id']) $p = '';
			$categories = array_merge(array($p => array('id' => $p, 'name' => '&lArr; ...', 'folder_up' => true)), $categories);
		}

		// render
		echo "<div id='categories'>";
		$properties = ($CMS->module_info['client_cat_control'] && count($categories) > 1) ? array('style' => 'cursor: move;') : array();
		foreach ($categories as $category) {
			row_start('folder_'.$category['id'], 'folder', $properties);
			column("<img src='img/icons/folder.gif' />", '', '', false);
			$o = "<a href='#' style='cursor: pointer;' onclick=\"process_setting({'category_id': '".$category['id']."'}); return false;\">".$category['name']."</a>";
			$s = array();
			if ($category['statistics']['categories']) $s[] = "<i>".$category['statistics']['categories']." <img src='img/icons/folder.gif' /></i>";
			if ($category['statistics']['entries'])    $s[] = "<i>".$category['statistics']['entries']." <img src='img/icons/file.gif' /></i>";
			$s = implode(' &nbsp; ', $s);

			column($o, '', '', false);
			if (count($display_fields) > 1) column($s, '', '', false);

			for ($f = 2; $f < count($display_fields); $f++) {
				column('&nbsp;', '', '', false);
			}

			if (isset($category['folder_up'])) {
				column('&nbsp;', '', '', false);
			} else {
				$buttons = array();
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) $buttons[] = "<img src='img/icons/edit.gif' alt='Edit this category' title='Edit this category' style='cursor: pointer;' onclick=\"process_setting({'cms_state': 'category_edit', 'category_id': ".$category['id']."});\" />";
				if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'delete')) $buttons[] = "<img src='img/icons/delete.gif' alt='Delete this category' title='Delete this category' style='cursor: pointer;' onclick=\"if (confirm('Delete this CATEGORY and all entries within?')) refresh('form_processing=category_delete&category_id=".$category['id']."');\" />";
				column(implode(" ", $buttons), '', '', false);

			}
			if (count($passive_referencing_fields)) column('&nbsp;', '', '', false);
			row_stop();
		}
		echo "</div>";
	}


	/************************
	     FILES (ENTRIES)
	*************************/
	echo "<div id='entries'>";
	$properties = array();
	if ($CMS->module_info['sort_entries_manually']) $properties['style'] = 'cursor: move;';

	// pagination
	$CMS->find_total_count    = true;
	$CMS->active_entries_only = false;
	$CMS->generate_identifier = true;

	$CMS->debug = true;

	// limit # items (also for search!)
	$CMS->limit_sql = "LIMIT ";
	if ($CMS_SESSION['page']) $CMS->limit_sql .= ($CMS_SESSION['items_per_page'] * $CMS_SESSION['page']).',';
	$CMS->limit_sql .= $CMS_SESSION['items_per_page'];

	$conditions = array();

	function getSearchIDsSubQuery($module_id, $_search, $level = 0) {
		$TMP_CMS = new CMS();
		$TMP_CMS->setModule($module_id);
		$TMP_CMS->active_entries_only = false;
		$TMP_CMS->generate_identifier = true;
		$conditions_or = array();
		$levels = 'efghijklmnopqrstuvwxyz';
		$tablealias = $levels{$level};
		foreach ($TMP_CMS->fields() as $field) {
			if ($field['identifier']) {
				if ($field['field_type_id'] == '16') { // Reference 1:1
					$conditions_or[] = '`'.$tablealias.'`.`'.$field['cms_name'].'` IN ('.getSearchIDsSubQuery($field['refers_to_module'], $_search, $level + 1).')';
				} else if ($field['field_type_id'] == '17') { // Reference 1:N
					$conditions_or[] = '`'.$tablealias.'`.`id` IN (SELECT `from_entry_id` FROM `cms_m_references` WHERE `from_field_id` = '.$field['id'].' AND `to_entry_id` IN ('.getSearchIDsSubQuery($field['refers_to_module'], $_search, $level + 1).'))';
				} else if ($field['db_field'] == '#REF') {
					continue;
				} else {
					$conditions_or[] = '`'.$tablealias.'`.`'.$field['cms_name']."` LIKE '%".pxl_db_safe($_search)."%'";
				}
			}
		}
		if (count($conditions_or)) {
			return 'SELECT `'.$tablealias.'`.`id` FROM `'.$TMP_CMS->table().'` AS `'.$tablealias.'` WHERE ('.implode(' OR ', $conditions_or).')';
		} else {
			return '0';
		}
	}

	if (isset($_search)) {
		$conditions_or = array();
		foreach ($fields as $field) {
			if ($field['identifier']) {
				if ($field['field_type_id'] == '16' && $CMS->module_info["search_referenced_identifiers"]) { // Reference 1:1
					$conditions_or[] = '`d`.`'.$field['cms_name'].'` IN ('.getSearchIDsSubQuery($field['refers_to_module'], $_search).')';
				} else if ($field['field_type_id'] == '17' && $CMS->module_info["search_referenced_identifiers"]) { // Reference 1:N
					$conditions_or[] = '`d`.`id` IN (SELECT `from_entry_id` FROM `cms_m_references` WHERE `from_field_id` = '.$field['id'].' AND `to_entry_id` IN ('.getSearchIDsSubQuery($field['refers_to_module'], $_search).'))';
				} else if ($field['db_field'] == '#REF') {
					continue;
				} else if ($field['multilingual'] == 1) {
					$conditions_or[] = "`ml`.`".$field['cms_name']."` LIKE '%".pxl_db_safe($_search)."%'";
				} else {
					$conditions_or[] = "`d`.`".$field['cms_name']."` LIKE '%".pxl_db_safe($_search)."%'";
				}
			}
		}
		if(is_numeric($_search)) {
			$conditions_or[] = "`d`.`id` = '".pxl_db_safe($_search)."'";
		}
		$conditions[] = "(".implode(' OR ', $conditions_or).")";
	}

	if (is_array($usr_ref_filter)) {
		$conditions[] = "`".$usr_ref_filter['cms_name']."` = '".$CMS_SESSION['user']['ref_filter_entry_id']."'";
	}

	if ($CMS->module_info['view_own_entries_only'] && $CMS_SESSION['user']['id'] !== 0) {
		$conditions[] = "`e_user_id` = ".$CMS_SESSION['user']['id'];
	}

	if (is_array($CMS_SESSION['categories_simulator']) && count($CMS_SESSION['categories_simulator'])) {
		$filter = $CMS_SESSION['categories_simulator'][count($CMS_SESSION['categories_simulator'])-1];
		$filter_field = $CMS->fieldData($filter['field_id']);
		if ($filter_field['form_element'] == 'reference') {
			$conditions[] = "`".$filter_field['cms_name']."` = ".$filter['entry_id'];
		} else {
			// MANUAL CACHING OF RELATED ENTRIES FOR PERFORMANCE
			// mysql's subquery optimization sucks..., otherwise we
			// could put the following query directly in the IN() clause :(
			$eids = CMS_DB::mysql_query("SELECT `from_entry_id` FROM `".$CMS->tables_prefix."m_references` WHERE `from_field_id` = ".$filter['field_id']." AND `to_entry_id` = ".$filter['entry_id']);
			$target_eids = array();
			while ($eid = mysql_fetch_assoc($eids)) {
				$target_eids[] = $eid['from_entry_id'];
			}
			if (count($target_eids)) {
				$conditions[] = "`id` IN (".implode(",", $target_eids).")";
			} else {
				$conditions[] = '0'; // no related items... let whole condition fail!
			}
		}
		row_start($entry['id'], 'dark', $properties);
		column('', '', '', false);
		$first = true;
		foreach ($display_fields as $field) {
			if ($first) {
				if ($CMS->module_info['simulate_categories_for']) {
					$ref = CMS_DB::mysql_query("SELECT `name` FROM `".$CMS->tables_prefix."fields` WHERE `module_id` = ".$CMS_SESSION['module_id']." AND `refers_to_module` = ".$CMS_SESSION['module_id']."");
					$ref = mysql_fetch_assoc($ref);
					if(!empty($ref)) {
						$query = new CMS_Query();
						$query->module_id = $CMS_SESSION['module_id'];
						$query->limit = 1;
						$query->recursion = 0;
						$query->conditions = "`id` = ".$filter["entry_id"];
						$parent = array_pop($query->entries());
						if (is_array($parent[Tools::alphanumeric($ref["name"])])) $parent = array_pop($parent[Tools::alphanumeric($ref["name"])]);
					} else {
						$parent["id"] = 0;
					}
				}
				column(($CMS->module_info['simulate_categories_for'] ? " <img class='simulated' src='img/icons/folder.gif' id='simulated_".$parent["id"]."' style='vertical-align:bottom; margin:0 3px 0 0;' /> " : "")."<a href='#' onclick=\"process_setting({ filter_update: 'up' }); return false;\">&lArr; ...</a> ");
				$first = false;
			}
			else {
				column('');
			}
		}
		if (count($passive_referencing_fields)) column('');
		column('', '', '', false);
		row_stop();
	}

	if ($CMS->module_info['simulate_categories_for'] && !isset($_search)) {
		$filter_field = $CMS->fieldData($CMS->module_info['simulate_categories_for']);
		if ($filter_field['module_id'] == $CMS->module_info['id']) {
			if (!isset($CMS_SESSION['categories_simulator']) || !is_array($CMS_SESSION['categories_simulator']) || count($CMS_SESSION['categories_simulator']) == 0) {
				if ($filter_field['form_element'] == 'reference') {
					$conditions[] = "`".$filter_field['cms_name']."` = 0";
				} else {
					// to be implemented???
				}
			}
		}
	}

	$CMS->setConditions(implode(" AND ", $conditions));

	if (isset($CMS_SESSION['cms_sorting'][$CMS_SESSION['module_id']])) {
		$CMS->sorting = $CMS_SESSION['cms_sorting'][$CMS_SESSION['module_id']].' '.$CMS_SESSION['cms_direction'][$CMS_SESSION['module_id']];
	}
	$entries = $CMS->getEntries();


	foreach ($entries as $entry) {
		if (!$entry['e_active']) {
			row_start($entry['id'], ($CMS->module_info['simulate_categories_for'] ? 'dark' : 'entry ').' normal', array_merge($properties, array('style' => 'font-style: italic; color: #aaa; '.($CMS->module_info['sort_entries_manually'] ? 'cursor: move;' : ''))));
		} else {
			row_start($entry['id'], ($CMS->module_info['simulate_categories_for'] ? 'dark' : 'entry ').' normal', $properties);
		}

		if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
			if ($entry['e_active']) {
				$button = "<img src='img/icons/activate.gif' alt='Deactivate this entry' title='Deactivate this entry' style='cursor: pointer;' onclick=\"refresh('form_processing=toggle_entry_activity&entry_id=".$entry['id']."', '');\" />";
			} else {
				$button = "<img src='img/icons/deactivate.gif' alt='Activate this entry' title='Activate this entry' style='cursor: pointer;' onclick=\"refresh('form_processing=toggle_entry_activity&entry_id=".$entry['id']."', '');\" />";
			}
		} else {
			$button = "<img src='img/icons/file.gif' />";
		}
		column($button, '', '', false);

		$rendered_category_simulator_column = false;

		foreach ($display_fields as $field) {
			if ($field == 'USERNAME') {
				column($users[$entry['e_user_id']]['username']);
			} else if (is_array($entry[$field['cms_name']])) {
				switch ($field_types[$field['cms_name']])
				{
					case 'reference':
					case 'reference_multi':
						$length = $field_types[$field['cms_name']] == 'reference' ? 80 : 20;
						$values = array();
						foreach ($entry[$field['cms_name']] as $reference) {
							if(strstr($reference['_identifier'], "<img")) {
								$values[] = "<a href='?module_id=".$field['refers_to_module']."&entry_id=".$reference['id']."'><span style='background: url(img/icons/reference.gif) 2px 2px no-repeat; padding: 0 4px 0 19px;'>".$reference['_identifier']."</span></a>";
							} else {
								$values[] = "<a href='?module_id=".$field['refers_to_module']."&entry_id=".$reference['id']."'><span style='background: url(img/icons/reference.gif) 2px 2px no-repeat; padding: 0 4px 0 19px;'>".Tools::shrink_text(strip_tags($reference['_identifier']), $length)."</span></a>";
							}
						}
						$values = "<div style='line-height: 18px;'>".(count($values) > 1 ? count($values).' references: ' : '').implode(", ", $values)."</div>";
						break;

					case 'image':
					case 'image_multi':
						$values = array();
						foreach ($entry[$field['cms_name']] as $image) {
							$values[] = "<img src='".$CMS_ENV['base_url_uploads']."pxl20_".$image['file']."' alt='' title='' ".($entry['e_active'] ? '' : "class='transparent25'")." />";
						}
						$values = implode(" ", $values);
						break;

					case 'file':
						if (count($entry[$field['cms_name']])) {
							foreach ($entry[$field['cms_name']] as &$file) {
								$file = "<span style='background: url(img/icons/icon_file.gif) 2px 2px no-repeat #fff; color: #555; padding: 0 4px 0 14px; font-family: Courier New, sans-serif; font-size: 11px;'>".Tools::shrink_text(Tools::file_name($file)).'.'.Tools::file_extension($file)."</span>";
							}
							$values = "<div style='line-height: 18px;'>".count($entry[$field['cms_name']]).' file'.(count($entry[$field['cms_name']]) > 1 ? 's' : '').': '.implode(" <span style='margin: 0 5px 0 5px; color: #666;'>,</span> ", $entry[$field['cms_name']])."</div>";
						} else {
							$values = '<i>none</i>';
						}
						break;

					case 'checkbox':
						if (count($entry[$field['cms_name']])) {
							$values = "<div style='line-height: 18px;'>".(count($entry[$field['cms_name']]) > 1 ? count($entry[$field['cms_name']]).'x: ' : '').implode(', ', $entry[$field['cms_name']])."</div>";
						} else {
							$values = '<i>none</i>';
						}
						break;

				}
				column($values);
			} else {
				switch ($field_types[$field['cms_name']])
				{
					case 'date':
						$value = $entry[$field['cms_name']] > 0 ? date('d-m-Y', $entry[$field['cms_name']]) : '&nbsp;';
						break;

					case 'color':
						$value = "<div style='width: 50px; border: 1px solid #000; background: ".$entry[$field['cms_name']].";'>&nbsp;</div>";
						break;

					case 'time':
						$value = date('d-m-Y / H:i:s', $entry[$field['cms_name']]);
						break;

					case 'boolean':
						$value = $entry[$field['cms_name']] ? 'Yes' : 'No';
						break;

					case 'htmlsource':
						$value = htmlspecialchars($entry[$field['cms_name']]);
                        break;

					case 'htmltext':
					case 'htmltext_fck':
						$value = strip_tags(str_replace("<", " <", $entry[$field['cms_name']]));
						break;

					case 'range':
						$v = json_decode($entry[$field['cms_name']]);
						$value = $v->min[0].'-'.$v->max[0];
						break;
					
					case 'location':
						$v = json_decode($entry[$field['cms_name']]);
						$value = $v->lat.', '.$v->lng;
						break;

					case 'custom_text':
						ob_start();
						include( realpath( 'fields/' . $field[ 'custom_html' ] ) );
						$value = ob_get_clean();
						break;

					default:
						$value = htmlspecialchars($entry[$field['cms_name']]);
						break;
				}

				if ($CMS->module_info['simulate_categories_for'] && !$rendered_category_simulator_column) {
					$target_ref_field = mysql_fetch_assoc(CMS_DB::mysql_query("SELECT * FROM `".$CMS_DB['prefix']."fields` WHERE `id` = ".$CMS->module_info['simulate_categories_for']));

					/*
					----------------------------
					 voor stabu
					 floris moet dit nog fixen:
					----------------------------
					$query = new CMS_Query();
					$query->module_id = $target_ref_field['module_id'];
					$query->conditions = Tools::alphanumeric($target_ref_field['name'])."='".$entry['id']."'";
					$query->recursive = 0;
					$referring = count($query->entries());
					*/
					$referring = 0;

					$filter_js = "process_setting({ filter_update: 'down', filter: { from_module_id: ".$CMS_SESSION['module_id'].", from_field_id: ".$field['id'].", module_id: ".$target_ref_field['module_id'].", field_id: ".$target_ref_field['id'].", field: '".$field['cms_name']."', entry_id: ".$entry['id']." }});";
					column(($target_ref_field['module_id'] == $CMS->module_id ? "<img class='simulated' src='img/icons/".($referring ? "folder" : "file").".gif' id='simulated_".$entry["id"]."' style='display: block; float: left; margin:0 5px 0 0;' /> " : "")."<a style='".(!$entry['e_active'] ? 'color: #888;' : '')." display: block; float: left; height: 17px;".($target_ref_field['module_id'] != $CMS->module_id ? " background: url(img/icons/folder.gif) no-repeat; padding-left: 22px;" : "")."' href='#' onclick=\"".$filter_js.";return false;\">".$value."</a>");
					$rendered_category_simulator_column = true;
				} else {
					column($value);
				}
			}
		}

		$buttons = array();

		if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) $buttons[] = "<img src='img/icons/edit.gif' alt='Edit this entry' title='Edit this entry' style='cursor: pointer;' onclick=\"process_setting({'cms_state': 'edit', ".(isset($_search) ? "'search': 1, " : "")." 'entry_id': ".$entry['id']."});\" />";
		if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'delete')) $buttons[] = "<img src='img/icons/delete.gif' alt='Delete this entry' title='Delete this entry' style='cursor: pointer;' onclick=\"if (confirm('Delete this entry?')) refresh('form_processing=entry_delete&entry_id=".$entry['id']."');\" />";
		if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'create'))  $buttons[] = "<img src='img/icons/copy.gif' alt='Duplicate this entry' title='Duplicate this entry' style='cursor: pointer;' onclick=\"if (confirm('Duplicate this entry?')) refresh('form_processing=duplicate_".($CMS->module_info['simulate_categories_for'] ? "simulated_" : "")."entry&entry_id=".$entry['id']."', '');\" />";

		column(implode("<span style='margin-right: 6px;'> </span>", $buttons), '', '', false);

		if (count($passive_referencing_fields))
		{
			foreach ($display_fields as $first_identifier_field) {
				if ($first_identifier_field['identifier']) break;
			}
			$buttons = array();
			// add icons for passive references
			foreach ($passive_referencing_fields as $ref) {
				$img = $ref['icon_image'] != '' ? "img/modules/".$ref['icon_image'] : "img/modules/_default.gif";

				// instead of the old related-entry-filtering...
				// $filter_js = "process_setting({ module_id: ".$ref['module_id'].", filter_field: ".$ref['field_id'].", filter_type: '".$ref['form_element']."', filter_value: ".$entry['id']."});";

				// ... lets just use the newer category-simulation!
				$filter_js = "process_setting({ filter_update: 'down', filter: { from_module_id: ".$CMS_SESSION['module_id'].", from_field_id: ".$first_identifier_field['id'].", module_id: ".$ref['module_id'].", field_id: ".$ref['field_id'].", field: '".$first_identifier_field['cms_name']."', entry_id: ".$entry['id']." }});";

				$title = htmlentities($ref['module_name'].' ('.$ref['name'], ENT_QUOTES).')';

				$buttons[] = "<img src='".$img."' alt='".$title."' title='".$title."' style='cursor: pointer;' onclick=\"".$filter_js."\" />";
			}
			column(implode("<span style='margin-right: 7px;'> </span>", $buttons), '', '', false);
		}

		row_stop();
	}
	echo "</div>";


	/************************
	   JAVASCRIPT SORTING
	*************************/
	if (!is_array($categories)) $categories = array();
	echo "<script type='text/javascript'>";
	foreach ($categories as $category) {
		echo "Droppables.add('row_folder_".$category['id']."', { greedy: true, accept: 'entry', hoverclass: 'move_to_category', onDrop: function(entry) { move_entry_to_category(entry.id, '".$category['id']."'); } });\n";

		// disable category-to-category dragging for now; it breaks category sorting :|
		// echo "Droppables.add('row_folder_".$category['id']."', { greedy: true, accept: 'folder', hoverclass: 'move_to_category', onDrop: function(entry) { move_category_to_category(entry.id, '".$category['id']."'); } });\n";
	}
	if($CMS->module_info['simulate_categories_for']) {
		foreach ($entries as $entry) {
			echo "if($('simulated_".$entry["id"]."') != null) { ";
			echo "Droppables.add('simulated_".$entry['id']."', { greedy: true, accept: 'simulated', hoverclass: 'move_to_category', onDrop: function(entry) { move_simulated_to_category(entry.id, '".$entry['id']."'); } });\n";
			echo "new Draggable('simulated_".$entry['id']."', { revert: true});";
			echo "}";
		}
		echo "if($('simulated_') != null) { ";
		echo "Droppables.add('simulated_', { greedy: true, accept: 'simulated', hoverclass: 'move_to_category', onDrop: function(entry) { move_simulated_to_category(entry.id, '0'); } });\n";
		echo "new Draggable('simulated_', { revert: true});";
		echo "}";
	}
	// only allow sorting when enabled and not filtering
	if (!isset($_search) && $CMS->module_info['client_cat_control'] && count($categories) > 1 && $CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update') ) {
		echo "Sortable.create('categories', { tag: 'table', onUpdate: function() { save_order('category', Sortable.serialize('categories')); } } );";
	}

	// only allow sorting when enabled
	if ($CMS->module_info['sort_entries_manually'] && $CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'update')) {
		echo "Sortable.create('entries', { tag: 'table', only: 'normal', onUpdate: function() { save_order('entry', Sortable.serialize('entries')); } } );";
	}

	//echo "disallow_move_to_category();";
	echo "</script>";


	/*************
	  PAGINATION
	*************/
	echo "<div style='text-align: center; margin-top: 15px; margin-bottom: 15px; padding: 2px 0 2px 0; height: 23px;'>";

		echo "<table border='0' cellspacing='0' cellpadding='0' style='width: 100%;'>";
			echo "<tr valign='top'>";

				// total items
				echo "<td style='width: 190px; text-align: left;'>";
					echo "<div style='font-style: italic; color: #666;'>";
						$start = $CMS_SESSION['items_per_page'] * $CMS_SESSION['page'];
						echo 'Showing: '.($CMS->total_count ? $start+1 : 0).'-'.min($start + $CMS_SESSION['items_per_page'], $CMS->total_count).' of '.$CMS->total_count.' items';
					echo "</div>";
				echo "</td>";

				// pagination
				echo "<td>";
				$pages = ceil($CMS->total_count / $CMS_SESSION['items_per_page']);
				if ($pages > 1) {
					echo "<div style='padding: 5px; line-height: 25px;'>";
						if ($CMS_SESSION['page'] > 0) {
							echo "<span class='pagination' onclick=\"process_setting({page: 0});\">&lt;&lt;</span>";
							echo "<span class='pagination' onclick=\"process_setting({page:".($CMS_SESSION['page'] - 1)."});\">&lt;</span>";
						}
						$min = $CMS_SESSION['page'] - 4;
						$max = $CMS_SESSION['page'] + 6;
						for ($i = $min; $i <= $max; $i++) {
							if ($i < 1 || $i > $pages) continue;
							echo "<span class='pagination' style='".($CMS_SESSION['page'] == $i - 1 ? 'background: #767e8d; color: #fff;' : '')."' onclick=\"process_setting({page:".($i - 1)."});\">".$i."</span> ";
						}
						if ($CMS_SESSION['page'] < $pages - 1) {
							echo "<span class='pagination' onclick=\"process_setting({page:".($CMS_SESSION['page'] + 1)."});\">&gt;</span>";
							echo "<span class='pagination' onclick=\"process_setting({page:".($pages - 1)."});\">&gt;&gt;</span>";
						}
					echo "</div>";
				}
				echo "&nbsp;</td>";

				// entries per page
				echo "<td style='width: 160px; text-align: right;'>";
					echo "Items per page: <select onchange=\"process_setting({items_per_page: this.value});\" style='display: inline; margin: 0 0 0 5px; position: relative; top: 1px;'>";
						$counts = array(5,10,20,50,100,250);
						foreach ($counts as $c) {
							echo "<option ".($CMS_SESSION['items_per_page'] == $c ? 'selected' : '')." value='".$c."'>".$c."</option>";
						}
					echo "</select>";
				echo "</td>";

			echo "</tr>";
		echo "</table>";

	echo "</div>";


	/**************
      LEGEND ????
	**************/
	echo "<div style='display: none; float: right; height: 120px; width: 140px; background: #f4f4f4; border: 1px solid #c4c4c4;'>";
		echo "<table border='0' cellspacing='0' cellpadding='4' style='width: 100%;'>";
			echo "<tr><td><img src='img/icons/activate.gif' alt='' title='' /></td><td>Deactive this item</td></tr>";
			echo "<tr><td><img src='img/icons/deactivate.gif' alt='' title='' /></td><td>Active this item</td></tr>";
			echo "<tr><td><img src='img/icons/edit.gif' alt='' title='' /></td><td>Edit this item</td></tr>";
			echo "<tr><td><img src='img/icons/delete.gif' alt='' title='' /></td><td>Delete this item</td></tr>";
			echo "<tr><td><img src='img/icons/copy.gif' alt='' title='' /></td><td>Duplicate this item</td></tr>";
		echo "</table>";
	echo "</div>";


	/*************
	  ADD BUTTONS
	**************/
	if (!isset($_search)) {
		if ($CMS->checkRights($CMS_SESSION['user']['id'], $CMS_SESSION['module_id'], 'create')) {
			if ($CMS->mayAddEntry()) echo "<input type='submit' style='display: inline; margin-right: 20px;' value='Add item' onclick=\"process_setting({'cms_state': 'add'}); return false;\" />";
			if ($CMS->module_info['client_cat_control']) {
				if ($current_category_depth < $CMS->module_info['max_cat_depth']) {
					echo "<input type='submit' style='display: inline; margin-right: 20px;' value='Add folder' onclick=\"process_setting({'cms_state': 'category_add'}); return false;\" />";
				} else {
					// echo "<div style='font-style: italic; margin-bottom: 15px;'>[max category depth reached]</div>";
				}
			} else {
				// echo "<div style='font-style: italic; margin-bottom: 15px;'>[no category control allowed]</div>";
			}
		}
	} else {
		echo "<input type='submit' value='Show all items' style='margin-right: 20px; display: inline;' onclick=\"refresh('search=');\" />";
	}

	/**************
	  MODULE ID's
	***************/

	if ($CMS_SESSION['user']['id'] == 0) {
		echo "<div style='text-align: right; clear: both; color: #fff;'>Module ID ".($CMS_SESSION['module_id'])."</div>";
	}

	/**************
	  DEBUG OUTPUT
	***************/

	// echo "<br/><br/><br/>"; echo $CMS->showLog();

?>
