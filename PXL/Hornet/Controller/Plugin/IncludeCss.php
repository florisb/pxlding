<?php
	namespace PXL\Hornet\Controller\Plugin;
	
	use PXL\Hornet\View\View;
	use PXL\Hornet\Application\Application;
	
	class IncludeCss extends AbstractPlugin {
		
		public function postDispatch(Application $application) {
			$controllerName = $application->getRequest()->getControllerName();
			$cssFile        = path(APPLICATION_PATH . "webroot/css/content/$controllerName.css");
			
			// Check if this css file exists and include if possible
			if (is_file($cssFile)) {
				Application::getInstance()->getView()->includeCss("css/content/$controllerName.css");
			}
		}
	}