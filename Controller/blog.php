<?php
	namespace Controller;

	use Model\Factory;

	class Blog extends BaseController {

		public function indexAction($page = null) {

			if (empty($page)) {
				$page = 1;
			}

			$blog = Factory\Blog::getAll($page);

			$this->set('blog', $blog);

			// masonry js stuff going on here
			$this->set('includeJsMasonry', true, true);
		}


		public function detailsAction() {

			$slug     = $this->getParam('slugparam');

			$blogpost = Factory\Blog::getBySlug($slug);
			$gallery  = Factory\Cases::getGalleryById($blogpost->id);

			$this->set('blogpost', $blogpost);
			$this->set('gallery',  $gallery);
		}
	}