<?php
	namespace App\Routing\Route;
	
	use Model\Factory;
	use PXL\Core\Session\Session;
	use PXL\Hornet\Request\iRequest;
	use PXL\Hornet\Routing\Route\Route;
	
	class SlugRoute extends Route {
		
		public function match(iRequest $request) {
			// Check if route matches structurally
			if (parent::match($request)) {
				$slugsFactory   = Factory\Slugs::getInstance();
				$contentFactory = Factory\Content::getInstance();
			
				// Retrieve slug
				$slug = $slugsFactory->getBySlug($this->_matchedValues['slug']);
				
				// No slug object found means route didn't match after all
				if (empty($slug)) {
					return false;
				}
				
				if (array_key_exists('slugparam', $this->_matchedValues)) {
					$slugParam = $slugsFactory->getBySlug($this->_matchedValues['slugparam']);
					if (empty($slugParam)) {
						return false;
					}
				} else {
					$slugParam = null;
				}
				
				// Check if we need to redirect to a newer URL
				if ($slug->isOld() || ($slugParam && $slugParam->isOld())) {
					$components = array();
					$components['slug']     = $slug->newest_slug;
					$components['language'] = $this->_matchedValues['language'];
					
					if ($slugParam) {
						$components['slugparam'] = $slugParam->newest_slug;
					}
					
					$request->redirect($this->reverseAssemble($components), true);
				}
				
				// Retrieve further routing information for the current slug
				$content = $contentFactory->getRoutingInformation($slug->entry_id);

				if (!is_null($content)) {
					if ($content->get('type_of_page')) {
						$controller = str_replace(' ', '', $content->get('type_of_page'));
					} else {
						$controller = 'home';
					}
				
					$this->_matchedValues['controller'] = $controller;
					$this->_matchedValues['action']     = $slugParam ? 'details' : 'index';

					return true;
				}
			}
			
			return false;
		}
	}