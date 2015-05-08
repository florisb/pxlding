<?php
	namespace Controller\Plugin;

	use PXL\Core\Session\Session;	
	use PXL\Hornet\Routing\Router;
	use PXL\Hornet\Controller\Plugin as HornetPlugins;
	
	/**
	 * CheckLanguage class.
	 * 
	 * Extends the standard CheckLanguage plugin for more support for slugs
	 *
	 * @extends HornetPlugins
	 */
	class CheckLanguage extends HornetPlugins\CheckLanguage {
	
		protected function _getRedirectUrl($request) {
			$matchedRoute = Router::getInstance()->getMatchedRoute();
			
			if ($matchedRoute instanceof \App\Routing\Route\SlugRoute) {
				try {
					$assembledUrl = Router::getInstance()->getRoute('slugparams')->reverseAssemble($request->getParams());
				} catch(\Exception $e) {
					$assembledUrl = Router::getInstance()->getRoute('slugs')->reverseAssemble($request->getParams());
				}
			} else {
				$assembledUrl = Router::getInstance()->getRoute('defaultml')->reverseAssemble(array(
					'controller' => $request->getControllerName() === 'home' ? null : $request->getControllerName(),
					'action'     => $request->getActionName() === 'index'    ? null : $request->getActionName(),
				) + $request->getParams());
			}
				
			// Append query string if necessary
			if (!empty($_GET)) {
				$assembledUrl .= '?' . http_build_query($_GET);
			}
				
			return $assembledUrl;
		}
	}