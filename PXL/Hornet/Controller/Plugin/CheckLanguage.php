<?php
	namespace PXL\Hornet\Controller\Plugin;
	
	use PXL\Core\Db\Db;
	use PXL\Core\Config;
	use PXL\Hornet\View\View;
	use PXL\Core\Db\Statement;
	use PXL\Core\Session\Session;
	use PXL\Hornet\Routing\Router;
	use PXL\Hornet\Application\Application;
	
	use InvalidArgumentException;
	use BadMethodCallException;
	
	class CheckLanguage extends AbstractPlugin {
		
		protected $_supportedLanguagesTable = null;
		protected $_routesWithoutLanguage   = array();
		
		protected function _init() {
			try {
				$this->_supportedLanguagesTable = Config::read('controllerplugin.checklanguage.supported_languages_table');
			} catch (InvalidArgumentException $e) {
				throw new BadMethodCallException('Missing table name for supported languages');
			}
			
			if(Config::has('controllerplugin.checklanguage.routeswithoutlanguage')) {
				$this->_routesWithoutLanguage = array_map('trim', explode(',', Config::read('controllerplugin.checklanguage.routeswithoutlanguage')));
			}
		}
		
		public function preDispatch(Application $application) {
			// Retrieve preferred language
			$preferredLanguages = array_keys(detectLanguage());
			$request            = $application->getRequest();
			$matchedRoute       = Router::getInstance()->getMatchedRoute();
			
			/**
			 * If the matched route is considered language-less, remove the reference of
			 * any language ID currently stored in session and stop the current routine.
			 */
			if ($matchedRoute && in_array($matchedRoute->getName(), $this->_routesWithoutLanguage)) {
				Session::delete('_language_id');
				return;
			}
			
			// Retrieve language parameter from URL (=current language)
			$currentLanguage = $request->getParam('language');
			
			// Retrieve supported languages
			$q = "
				SELECT
					`l`.`code`,
					`_l`.`id`
				FROM
					`%s` `l`
				INNER JOIN
					`cms_languages` `_l`
				ON
					(`_l`.`code`=`l`.`code`)
				WHERE
					`l`.`e_active`=1
				ORDER BY
					`l`.`e_position` ASC
			";
			
			$q                   = sprintf($q, pxl_db_safe($this->_supportedLanguagesTable));
			$stmt                = new Statement($q);
			$supportedLanguages  = Db::getInstance()->matrix($stmt, null, 'code');
			
			// Check if current language is supported
			if (!is_null($currentLanguage) && $supportedLanguages->containsKey($currentLanguage)) {
				Session::set('_language_id', $supportedLanguages->$currentLanguage->id);
				Application::getInstance()->getView()->set('_language', $currentLanguage, true);
				return; // No action neccessary
			}
			
			// Check if we can use one of the preferred languages
			foreach($preferredLanguages as $preferredLanguage) {	
				if ($supportedLanguages->containsKey($preferredLanguage)) {
					$newLanguage = $preferredLanguage;
					break;
				}
			}
			
			// Use the first (=default) language as a last resort
			if (empty($newLanguage)) {
				$defaultLanguage = array_peek($supportedLanguages);
			
				if (!empty($defaultLanguage)) {
					$newLanguage = $defaultLanguage->code;
				}
			}
			
			// Redirect to correct URL
			if (!empty($newLanguage)) {
				Session::set('_language_id', $supportedLanguages->$newLanguage->id);
				Application::getInstance()->getView()->set('_language', $newLanguage, true);
				$request->setParam($newLanguage, 'language');
				
				$controller404 = Config::read('error.404.controller');
				$action404     = Config::read('error.404.action');
						
				// Do not redirect if we're showing a 404 page
				if (!($controller404 === $request->getControllerName() && $action404 === $request->getActionName())) {
					$request->redirect($this->_getRedirectUrl($request), true);
				}
			}
		}
		
		protected function _getRedirectUrl($request) {
			$route = Router::getInstance()->getRoute('defaultml');
			
			$assembledUrl = $route->reverseAssemble(array(
				'controller' => $request->getControllerName() === 'home' ? null : $request->getControllerName(),
				'action'     => $request->getActionName() === 'index'    ? null : $request->getActionName(),
			) + $request->getParams(), 'default');
				
			// Append query string if necessary
			if (!empty($_GET)) {
				$assembledUrl .= '?' . http_build_query($_GET);
			}
				
			return $assembledUrl;
		}
	}