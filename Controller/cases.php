<?php
	namespace Controller;

	use Model\Factory;

	class Cases extends BaseController {

		public function indexAction() {
			$cases     = Factory\Cases::getAll();
			// $offline   = Factory\Cases::getAll(false, true);
			$showCases = Factory\Cases::getShowCased();

			$this->set('showCases', $showCases);
			$this->set('cases',     $cases);
			// $this->set('offline',   $offline);
		}


		public function detailsAction() {

			$slug     = $this->getParam('slugparam');

			$case     = Factory\Cases::getBySlug($slug);
			$gallery  = Factory\Cases::getGalleryById($case->cid);

			// until we got the generator fixed, insert mock stuff
			$case->text_section_1 = "<h2>testing something</h2><p>text</p>";
			$case->text_section_2 = "<h2>testing something</h2><p>text</p>";
			$case->text_section_3 = "  &nbsp; fdgdfg";
			$case->text_section_4 = "<h2>testing something</h2><p>text</p>";
			$case->text_section_5 = "<h2>testing something END</h2><p>text</p>";


			$this->set('case',    $case);
			$this->set('gallery', $gallery);
		}
	}