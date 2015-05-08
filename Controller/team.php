<?php
	namespace Controller;

	use Model\Factory;

	class Team extends BaseController {
		
		public function indexAction() {

			$employees = Factory\Employees::getAll(true);

			$this->set('employees', $employees);

		}

		public function detailsAction() {
		}
	}