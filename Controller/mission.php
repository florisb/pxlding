<?php
	namespace Controller;

	use Model\Factory;

	class Mission extends BaseController {

		public function indexAction() {

			$missionContent = Factory\Content::getSettingsForPage('mission');
			$peopleContent  = Factory\Content::getSettingsForPage('people');
			$employees      = Factory\Employees::getAll(true);

			$this->set('missionContent', $missionContent);
			$this->set('peopleContent',  $peopleContent);
			$this->set('employees',      $employees);

			// special parallax effect for this page
			$this->set('hasParallax', true, true);
		}

	}