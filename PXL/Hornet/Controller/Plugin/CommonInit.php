<?php
	namespace PXL\Hornet\Controller\Plugin;
	
	use PXL\Hornet\Application\Application;
	
	class CommonInit extends AbstractPlugin {
		
		public function preDispatch(Application $application) {
			$application->initCache()
									->initSession();
		}
	}