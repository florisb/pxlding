<?php
	namespace PXL\Hornet\Controller\Plugin;
	
	use PXL\Core\Config;
	use PXL\Hornet\Application\Application;
	
	/**
	 * Ssl class.
	 * 
	 * Handles enforcement of SSL on a controller-basis.
	 *
	 * @extends AbstractPlugin
	 * @author  Max van der Stam <max@pixelindustries.com>
	 */
	class Ssl extends AbstractPlugin {
		
		public function preDispatch(Application $application) {
			$request = $application->getRequest();
			$baseUrl = $request->getBaseUrl();
			
			if (Config::has('controllerplugin.ssl.sitewide')) {
				$controllerSsl = true;
			} elseif (Config::has('controllerplugin.ssl.controllers')) {
				$sslConfig = Config::read('controllerplugin.ssl.controllers');
				
				if (is_array($sslConfig)) {
					$sslConfig = array_map('trim', $sslConfig);
				} elseif (strstr($sslConfig, ',')) {
					$sslConfig = array_map('trim', explode(',', $sslConfig));
				}
			
				if (empty($sslConfig)) {
					return;
				} else {
					$controllerSsl = in_array($request->getControllerName(), $sslConfig);
				}
			} else {
				return;
			}
			
			if ($request->isSecure()) {
				if (Config::has('controllerplugin.ssl.sitewide')) {
					header('Strict-Transport-Security: max-age=86400; includeSubDomains');
				}
				if ($controllerSsl) {
					return;
				} else {
					$request->setBaseUrl(preg_replace('#^https://(.*)#', 'http://\1', $baseUrl));
				}
			} else {
				if ($controllerSsl) {
					$request->setBaseUrl(preg_replace('#^http://(.*)#', 'https://\1', $baseUrl));
				} else {
					return;
				}
			}
			
			// Redirect to same request URI, but with correct protocol
			$request->redirect($request->getRequestUri(), true);
		}
	}