<?php
	namespace PXL\Hornet\Controller\Plugin;
	
	use PXL\Hornet\Application\Application;
	
	abstract class AbstractPlugin implements \SplObserver {
	
		final public function __construct() {
			$this->_init();
		}
	
		final public function update(\SplSubject $application) {
			if ($application->getRequest()->isDispatched()) {
				$this->postDispatch($application);
			} else {
				$this->preDispatch($application);
			}
		}
		
		protected function _init() { }
		
		public function postDispatch(Application $application) { }
		
		public function preDispatch(Application $application) { }
	}