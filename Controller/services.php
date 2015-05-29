<?php
	namespace Controller;

	use Model\Factory;
	use PXL\Hornet\Seo\Seo;

	class Services extends BaseController {

		const DEFAULT_SERVICE_SLUG = 'online-strategie';

		public function indexAction() {

			$slug = $this->getParam('slug');

			if (empty($slug) || $slug == "services") {
				$slug = self::DEFAULT_SERVICE_SLUG;
			}

			$services = Factory\Services::getAll();
			$service  = Factory\Services::getBySlug($slug);
			$cases    = Factory\Cases::getByService($service);

			$this->set('services', $services);
			$this->set('service', $service);
			$this->set('cases', $cases);

			Seo::addTitle($service->title);
		}

	}