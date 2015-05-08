<?php

	// CMS class init
	$CMS     = new CMS();
	$CMS->setModule($CMS_SESSION['module_id']);
	if (isset($_POST['settings']) && !in_array('search', $_POST['settings'])) $CMS->setCategory($CMS_SESSION['category_id']);
	$CMS->recursive = 0; // we need not load references
	$CMS->active_entries_only = false;
	
	// Form Renderer
	$CMSForm = new FormRenderer($CMS);
	$CMSForm->module_id = $CMS_SESSION['module_id'];
	$CMSForm->custom_rendering = $CMS->module_info['custom_rendering'];
	$CMSForm->setFormat($CMS->getModuleFormat());
	$CMSForm->setTabs($CMS->getTabs());
	
	$entry = $CMS->getEntry($CMS_SESSION['entry_id']);
	$entry = array_pop($entry);
	$CMSForm->setValues($entry);

	
	// output
	$CMSForm->renderForm();
	
	// echo $CMS->showLog();

?>