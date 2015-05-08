<?php
	namespace PXL\Hornet\Controller\Plugin;
	
	use PXL\Core\Config;
	use PXL\Core\Collection;
	use PXL\Hornet\Application\Application;
	
	class CheckIp extends AbstractPlugin {
		
		protected $_config = null;
		
		protected function _init() {
			$this->_config = new Collection\SimpleList();
		
			$config = Config::getAsObject();
			
			if (empty($config->controllerplugin->checkip)) {
				throw new \BadMethodCallException('Missing configuration for plugin "checkip"');
			} else {
				$config = $config->controllerplugin->checkip;
			}
			
			foreach($config as $c) {
				$this->_config->add($c);
			}
		}
		
		public function preDispatch(Application $application) {
			$request = $application->getRequest();
			
			// Do not run this plugin on development environments
			if (APPLICATION_ENV === 'development') {
				return;
			}

			foreach($this->_config as $c) {
				if ($request->getControllerName() === $c->controller && (is_array($c->action) ? (in_array($request->getActionName(), $c->action)) : ($request->getActionName() === $c->action))) {
					
					// Replace special dynamic values with their explicit value
					foreach($c->ips as &$ip) {
						if ($ip === 'SERVER_ADDR') {
							$ip = $_SERVER['SERVER_ADDR'];
						}
					}
					unset($ip);
					
					if (!chkiplist($_SERVER['REMOTE_ADDR'], $c->ips)) {
						// Notify developer if possible
						if (Config::has('developer.mail')) {
							$developerMail = Config::getAsObject()->developer->mail;

							if (is_array($developerMail)) {
								$developerMail = implode(';', $developerMail);
							}

							mail($developerMail, '[PXL] Unauthorized access', "Remote IP: {$_SERVER['REMOTE_ADDR']}\nTime: " . date('d-m-Y H:i:s') . "\nRequested controller/action: {$request->getControllerName()}/{$request->getActionName()}");
						}
						
						header('HTTP/1.0 403 Forbidden');
						exit;
					}
				}
			}
		}
	}