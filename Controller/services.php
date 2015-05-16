<?php
	namespace Controller;

	use Model\Factory;

	class Services extends BaseController {

		const DEFAULT_SERVICE_SLUG = 'online-strategie';

		public function indexAction() {

			$slug = $this->getParam('slug');

			if (empty($slug)) {
				$slug = self::DEFAULT_SERVICE_SLUG;
			}

			$services = Factory\Services::getAll();
			$service  = Factory\Services::getBySlug($slug);


			$this->set('services', $services);
			$this->set('service', $service);
		}

	}