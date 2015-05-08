<?php
	namespace Controller;

	use Model\Factory;

	class Whatwedo extends BaseController {
		
		public function indexAction() {
			$blocks    = Factory\Whatwedo::getAll();
			$this->set('blocks', $blocks);
		}

	}