<?php
	namespace Controller;

	use Model\Factory;
	use PXL\Core\Tools;

	class Faq extends BaseController {
		
		public function indexAction() {
			$faq    = Factory\Faq::getAll();
			$this->set('faq', $faq);	
		}

	}