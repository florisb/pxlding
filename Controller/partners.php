<?php
	namespace Controller;

	use Model\Factory;

	class Partners extends BaseController {
		
		public function indexAction() {
			$this->redirect('home');
		}

		public function detailsAction() {
			$slug      = $this->getParam('slugparam');
			$partner   = Factory\Partners::getBySlug($slug);
			$partners  = Factory\Partners::getAll(true);
 
			$this->set('partner', $partner);
			$this->set('partners', $partners);		
		}
	}