<?php
	$cms = new CMS();
	
	if (isset($_POST['entry_id'])) {
		$user = array();
		$user['id']                   = (int) $_POST['user_id'];
		$user['ref_filter_module_id'] = (int) $_POST['module_id'];
		$user['ref_filter_entry_id']  = (int) $_POST['entry_id'];
		
		$_POST['selected_ref_filter'] = $user['ref_filter_entry_id'];
		
		$cms->saveUser($CMS_SESSION['user']['id'], $user);
		echo 'Saved';
	}
	
	$cms->setModule((int) $_POST['module_id']);
	$cms->recursive = 0;
	$cms->generate_identifier = true;

	$entries = $cms->getEntries();
	
	echo "<select onchange=\"set_reference_filter('entries_".$_POST['user_id']."', ".$_POST['user_id'].", ".$_POST['module_id'].", this.value);\">";
		echo "<option value=''></option>";
		foreach ($entries as $entry) {
			echo "<option ".($_POST['selected_ref_filter'] == $entry['id'] ? 'selected' : '')." value='".$entry['id']."'>".$entry['_identifier']."</option>";
		}
	echo "</select>";
