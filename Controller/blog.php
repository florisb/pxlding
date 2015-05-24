<?php
	namespace Controller;

	use Model\Factory;
	use PXL\Core\Session\Session;
	use PXL\Hornet\Seo\Seo;

	class Blog extends BaseController {

		const BLOG_PAGE_SIZE = 6;


		public function indexAction() {

			$page = $this->getParam('page');
			$ajax = ($this->isXhr());

			if (empty($page)) {
				$page = 1;
			}

			// remember current page for back links
			Session::set('blog-page',   $page);
			// remember whether we were searching before
			Session::set('blog-search', null);

			$blog  = Factory\Blog::getAll($page, self::BLOG_PAGE_SIZE, ! $ajax);

			$this->set('blog',         $blog);
			$this->set('currentPage',  $page);


			// for ajax loading, only show the extra posts (for masonry)
			if ($ajax) {
				$this->view('_ajax_page');

			} else {

				// stop loader after all pages shown
				$count     = Factory\Blog::getCount();
				$finalPage = ceil($count / self::BLOG_PAGE_SIZE);

				$this->set('finalPage', $finalPage);

				// masonry js stuff going on here
				$this->set('includeJsMasonry', true, true);

				// make sure canonical is without page number
				Seo::setCanonical($this->route('blog'));
			}
		}


		public function searchAction() {

			if (isset($_POST['search'])) {
				$search = trim($_POST['search']);
			} else {
				$search = $this->getParam('search');
			}

			$ajax = ($this->isXhr());

			if (empty($search)) {
				$this->view('index');
				$this->indexAction();

			} else {

				// remember that we were searching
				Session::set('blog-search', $search);

				$blog = Factory\Blog::getFiltered($search);

				$this->set('blog',   $blog);
				$this->set('search', $search);

				// for ajax loading, only show the extra posts (for masonry)
				if ($ajax) {
					$this->view('_ajax_page');
				} else {
					$this->view('index');

					// masonry js stuff going on here
					$this->set('includeJsMasonry', true, true);
				}


				$this->set('noAjaxPages',      true, true);
				$this->set('alwaysFocusInput', true, true);
			}
		}


		public function detailsAction() {

			$slug    = $this->getParam('slug');

			$searchingFor = Session::get('blog-search');

			if ( ! empty($searchingFor)) {
				$returnLink = $this->route( 'blog', 'search', array($searchingFor) );
			} else {
				$returnLink = $this->route( 'blog', '', array(Session::get('blog-page')) );
			}


			$post    = Factory\Blog::getBySlug($slug);
			$gallery = Factory\Blog::getGalleryById($post->bid);

			$this->set('post',     $post);
			$this->set('gallery',  $gallery);

			$this->set('returnLink',  $returnLink);
			$this->set('currentPage', Session::get('blog-page'));

			// special parallax effect for this page
			$this->set('hasParallax',    true, true);
			$this->set('parallaxHeight', 418,  true);
		}
	}