<?php
	$CMS = new CMS($CMS_SESSION['module_id']);
	
	$render = new FormRenderer($CMS);

	$fid = max($_POST['field_id'], $CMS_SESSION['field_id']);
	$eid = max($_POST['entry_id'], $CMS_SESSION['entry_id']);

	$field = $CMS->fieldData($fid);
	
	list($overview, $javascript) = $render->renderImageMultiOverview($fid, $eid, $_POST['size'], $field['value_count']);
	
	echo $overview;
	echo $javascript;
	
	// echo $CMS->showLog();
?>