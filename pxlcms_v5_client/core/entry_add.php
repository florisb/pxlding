<?php

	$CMS_SESSION['entry_id'] = null;
	
	$CMSForm = new FormRenderer($CMS);
	$CMSForm->custom_rendering = $CMS->module_info['custom_rendering'];
	$CMSForm->module_id = $CMS_SESSION['module_id'];
	$CMSForm->setFormat($CMS->getModuleFormat());
	$CMSForm->setTabs($CMS->getTabs());
	$CMSForm->renderForm();
	
	// echo $CMS->showLog();
	
?>