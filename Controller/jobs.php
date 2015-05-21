<?php
	namespace Controller;

	use Model\Factory;
	use ML;

	class Jobs extends BaseController {

		public function indexAction() {

			$jobsContent = Factory\Content::getSettingsForPage('jobs');
			$jobs        = Factory\Jobs::getAll();

			$this->set('pageTitle', $jobsContent->page_text);
			$this->set('jobs',      $jobs, true);

			$this->set('hasParallax',    true, true);
			$this->set('parallaxHeight', 470,  true);
		}


		public function detailsAction() {

			$slug  = $this->getParam('slug');

			$job   = Factory\Jobs::getBySlug($slug);
			$icons = Factory\Jobs::getIconsById($job->id);

			$this->set('job',   $job,   true);
			$this->set('icons', $icons, true);

			$this->set('hasParallax',    true, true);
			$this->set('parallaxHeight', 470,  true);
		}
	}