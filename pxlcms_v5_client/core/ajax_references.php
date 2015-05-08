<?php
	$REF_CMS = new CMS();
	$REF_CMS->setModule($_POST['referer_module_id']);
	$REF_CMS->recursive = 0;
	$REF_CMS->generate_identifier = true;
	$REF_CMS->active_entries_only = false;
	if(isset($_POST["module_id"])) {
		$CMS = new CMS($_POST["module_id"]);
	} else {
		$CMS = new CMS($CMS_SESSION['module_id']);
	}
	$field = $CMS->fieldData($_POST['field_id']);
	
	if ($_POST['action'] == 'search') {
		if($REF_CMS->module_info["search_referenced_identifiers"]) {
		
			/** easiest solution: get all entries from referenced module, and do a preg-match on the _identifier... */
			$REF_CMS->generate_identifier = true;
			$references = $REF_CMS->getEntries(); //gotta catch m all!
	
			foreach ($references as $id => $r) {
				if (!preg_match('@'.$_POST['searchterm'].'@i', $r['_identifier'])) unset($references[$id]);
			}
		
		} else {
		
			// FAST CODE 
			$REF_CMS->limit_sql = "LIMIT 25";
			
			$fields = $REF_CMS->fields();
			
			$conditions_or = array();
			
			foreach ($fields as $f) {
				if ($f['identifier'] && $f['db_field'] != '#REF') $conditions_or[] = "`".$f['cms_name']."` LIKE '%".pxl_db_safe($_POST['searchterm'])."%'";
			}
	
			$conditions    = array();
			$conditions[] = "( ".implode(' OR ', $conditions_or)." )";
			
			if ($REF_CMS->module_info['view_own_entries_only']) {
				$conditions[] = "`e_user_id` = ".$CMS_SESSION['user']['id'];
			}
			
			$REF_CMS->setConditions(implode(" AND ", $conditions));
			
			$references = $REF_CMS->getEntries();
		}
		echo "<select id='ref_search_select_".$field['id']."' style='width: 100%; overflow: hidden;'>";
		foreach ($references as $ref) {
			echo "<option style='".($ref['e_active'] ? '' : 'color: #999;')."' value='".$ref['id']."' ".($ref['id'] == $values[$i] ? 'selected' : '').">".$ref['_identifier']."</option>";
		}
		echo "</select>";
		
		echo "<input id='ajax_ref_add_allow_".$field['id']."' type='submit' value='Select' onclick=\"ajax_ref_select($('ref_overview_".$field['id']."').childElements().length, 'ref_overview_".$field['id']."', '".$field['id']."', $('ref_search_select_".$field['id']."').value, ".$_POST['referer_module_id']."); return false;\" />";
		echo "<div style='display: none; margin-bottom: 16px; padding-top: 5px;' id='ajax_ref_add_disallow_".$field['id']."'>You can not refer to any more items. The maximum number of references from this field is ".$field['value_count'].".</div>";
	}
	else if ($_POST['action'] == 'save' && ((int) $_POST['to_entry_id']))
	{
		// CHECK IF existing_count < ALLOWED VALUE COUNT
		if ($_POST['existing_count'] < $field['value_count'] || $field['value_count'] == 0)
		{
			$entry = array_pop($REF_CMS->getEntry($_POST['to_entry_id']));
			$render = new FormRenderer($REF_CMS);
			echo $render->renderReferenceOverview($entry, $field['id']);
			echo "<script type='text/javascript'>Sortable.create('ref_overview_".$field['id']."', { tag: 'div' } );</script>";
			$_POST['existing_count']++;
		}
	}
	
	if  (isset($_POST['existing_count'])) {
		echo "<script type='text/javascript'>";
		if ($_POST['existing_count'] < $field['value_count'] || $field['value_count'] == 0)
		{
			echo "if ($('ajax_ref_add_disallow_".$field['id']."')) $('ajax_ref_add_disallow_".$field['id']."').hide();";
			echo "if ($('ajax_ref_add_allow_".$field['id']."')) $('ajax_ref_add_allow_".$field['id']."').show();";
		}
		else
		{
			echo "if ($('ajax_ref_add_disallow_".$field['id']."')) $('ajax_ref_add_disallow_".$field['id']."').show();";
			echo "if ($('ajax_ref_add_allow_".$field['id']."')) $('ajax_ref_add_allow_".$field['id']."').hide();";		
		}
		echo "</script>";
	}
?>