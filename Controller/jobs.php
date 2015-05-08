<?php
	namespace Controller;

	use Model\Factory;

	class Jobs extends BaseController {
		
		public function indexAction() {
			$jobs = Factory\Jobs::getAll();

			$this->set('jobs', $jobs, true);
		}

		public function detailsAction() {
		}
	}