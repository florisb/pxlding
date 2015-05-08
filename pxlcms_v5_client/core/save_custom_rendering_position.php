<?php
	
	// store the dimensions and position of a field in CUSTOM-RENDERING-EDITING mode
	// ... :)
	
	$field_id  = (int) $_POST['field_id'];
	
	$positions = array(
						'render_x'  => (int) $_POST['x'],
						'render_y'  => (int) $_POST['y'],
						'render_dx' => (int) $_POST['dx'],
						'render_dy' => (int) $_POST['dy'],
						);

	$CMS = new CMS($CMS_SESSION['module_id']);
	$CMS->save_custom_rendering_position($field_id, $positions);
	echo $CMS->showLog();
?>