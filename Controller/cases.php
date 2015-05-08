<?php
	namespace Controller;

	use Model\Factory;

	class Cases extends BaseController {
		
		public function indexAction() {
			$cases    = Factory\Cases::getAll();
			$offline  = Factory\Cases::getAll(false, true);

			$this->set('cases', $cases);
			$this->set('offline', $offline);
		}

		public function detailsAction() {
			$slug     = $this->getParam('slugparam');
			$case     = Factory\Cases::getBySlug($slug);
			$gallery  = Factory\Cases::getGalleryById($case->cid);
			$cases    = Factory\Cases::getRandom();

			$this->set('cases', $cases);
			$this->set('case', $case);	
			$this->set('gallery', $gallery);		
		}
	}