<?php
	namespace Controller;

	use Model\Factory;
	use PXL\Core\Session\Session;

	class Blog extends BaseController {

		public function indexAction($page = null) {

			if (empty($page)) {
				$page = 1;
			}

			Session::set('blog-page', $page);

			$blog = Factory\Blog::getAll($page);

			$this->set('blog', $blog);
			$this->set('currentPage', $page);

			// masonry js stuff going on here
			$this->set('includeJsMasonry', true, true);
		}


		public function detailsAction() {

			$slug    = $this->getParam('slug');

			$post    = Factory\Blog::getBySlug($slug);
			$gallery = Factory\Blog::getGalleryById($post->bid);

			$this->set('post',     $post);
			$this->set('gallery',  $gallery);

			$this->set('currentPage', Session::get('blog-page'));
		}
	}