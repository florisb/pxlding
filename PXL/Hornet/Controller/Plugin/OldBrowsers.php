<?php
	namespace PXL\Hornet\Controller\Plugin;

	use PXL\Core\Config;	
	use PXL\Hornet\View\View;
	use PXL\Hornet\Application\Application;
	
	class OldBrowsers extends AbstractPlugin {
		
		public function preDispatch(Application $application) {
			$userAgent = $_SERVER['HTTP_USER_AGENT'];

			// Determine if MSIE 6, MSIE 7 or MSIE 8 is present in user agent

			$msie6 = stripos($userAgent, 'MSIE 6');
			$msie7 = stripos($userAgent, 'MSIE 7');
			$msie8 = stripos($userAgent, 'MSIE 8');

			// If MSIE 6 or MSIE 7 is present in user agent, and MSIE 8 is not present before it, we are in an old IE

			if(($msie6 !== false && ($msie8 === false || $msie8 > $msie6)) || ($msie7 !== false && ($msie8 === false || $msie8 > $msie6))) {
				// Determine configuration for this plugin
				$config = array(
					'upgradecontroller' => Config::has('controllerplugin.oldbrowsers.upgradecontroller') ? Config::read('controllerplugin.oldbrowsers.upgradecontroller') : 'home',
					'upgradeaction'     => Config::has('controllerplugin.oldbrowsers.upgradeaction') ? Config::read('controllerplugin.oldbrowsers.upgradeaction') : 'upgrade',
					'upgraderoute'      => Config::has('controllerplugin.oldbrowsers.upgraderoute') ? Config::read('controllerplugin.oldbrowsers.upgraderoute') : 'default'
				);
			
				$request = $application->getRequest();
				
				if (!($request->getControllerName() === $config['upgradecontroller'] && $request->getActionName() === $config['upgradeaction'])) {
					$url = Application::getInstance()->getView()->route($config['upgradecontroller'], $config['upgradeaction'], array(), $config['upgraderoute']);
					$request->redirect($url);
				}
			}
		}
	}