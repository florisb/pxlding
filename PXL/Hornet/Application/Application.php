<?php
	namespace PXL\Hornet\Application;
	
	use PXL\Core;
	use PXL\Core\Db;
	use PXL\Core\Config;
	use PXL\Core\Session;
	use PXL\Hornet\Routing;
	use PXL\Core\Cache\Cache;
	use PXL\Hornet\Routing\Router;
	use PXL\Hornet\Request;
	use PXL\Core\Exception;
	use PXL\Hornet\View\View;
	use PXL\Hornet\Scaffolding\Scaffolding;
	
	/**
	 * Application
	 *
	 * Main class for Hornet application.
	 *
	 * @author  Max van der Stam <max@pixelindustries.com>
	 */
	class Application implements iApplication {
		
		protected static $_instance = null;
		
		protected $_request = null;
		protected $_cache   = null;
		protected $_view    = null;
		
		protected $_preDispatched  = false;
		protected $_postDispatched = false;
		
		protected $_observers = array();
		
		protected function __construct(Request\Request $request = null) {
			if (is_null($request)) {
				throw new \BadMethodCallException('No request object.');
			}
		
			$this->_request = $request;
		}
		
		protected function __clone() { }
		
		public static function getInstance(Request\Request $request = null) {
			if (!self::$_instance) {
				self::$_instance = new static($request);
			}
			
			return self::$_instance;
		}
		public function initConfig() {
			if (getenv('SERVER_TYPE') === 'JSONRPC') {
				Config::addFile(path('Config/jsonrpc.ini'), self::getEnvironment());
			} else {
				Config::addFile(path('Config/config.ini'), self::getEnvironment());
			}
			
			return $this;
		}
		
		public function initView() {
			if (Config::has('view.classname')) {
				$this->_view = call_user_func(array(Config::read('view.classname'), 'getInstance'));
			} else {
				$this->_view = View::getInstance();
			}
			
			return $this;
		}
		
		public function getView() {
			return $this->_view;
		}
		
		public function initControllerPlugins() {
			if (Config::has('controllerplugin.active')) {
				$controllerPlugins = Config::read('controllerplugin.active');
				
				if (is_array($controllerPlugins)) {
					$controllerPlugins = array_map('trim', $controllerPlugins);
				} elseif (strstr($controllerPlugins, ',')) {
					$controllerPlugins = array_map('trim', explode(',', $controllerPlugins));
				} else {
					$controllerPlugins = array(trim($controllerPlugins));
				}
			}
			
			if (!empty($controllerPlugins)) {
				foreach($controllerPlugins as $className) {
					$this->attach(new $className());
				}
			}
			
			return $this;
		}

		public function initIncludes() {
			$files = array();
			$dirs = array();
			if (Config::has('autoinclude.files')) {
				$files = Config::read('autoinclude.files');
				if (strstr($files, ',')) {
					$files = array_map('trim', explode(',', $files));
				} else {
					$files = array(trim($files));
				}
			}
			if (count($files)) {
				foreach ($files as $file) {
					include($file);
				}
			}

			if (Config::has('autoinclude.dirs')) {
				$dirs = Config::read('autoinclude.dirs');
				if (strstr($dirs, ',')) {
					$dirs = array_map('trim', explode(',', $dirs));
				} else {
					$dirs= array(trim($dirs));
				}
			}
			if (count($dirs)) {
				foreach ($dirs as $dir) {
					$dir = str_replace('\\', '/', $dir);
					$blocked = array('.AppleDouble');
					$i = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS | \FilesystemIterator::KEY_AS_PATHNAME | \FilesystemIterator::CURRENT_AS_FILEINFO), \RecursiveIteratorIterator::CHILD_FIRST);
					foreach ($i as $k => $v) {
						if ($v->isDir()) continue;
						foreach ($blocked as $pattern) {
							if (strstr($k, $pattern)) continue 2;
						}
						if ($v->getExtension() == 'php') {
							include($k);
						}
					}
					exit;
				}
			}

			return $this;
		}
		
		public function initDb() {
			if (Config::has('db.dsn')) {
				Db\Db::storeConnectionInfo(new Db\DbConnectionInfo(Config::read('db.dsn')));
			}

			return $this;
		}
		
		public function getEnvironment() {
			$env = getenv('APPLICATION_ENV') ?: 'production';
			
			return $env;
		}
		
		public function initSession() {
			if (Config::has('session.adapter')) {
				$sessionAdapter = Config::read('session.adapter');
				
				// Set important session ini settings
				ini_set('session.cookie_httponly', true);
				
				// Connect adapter to main session class
				Session\Session::connectAdapter(new $sessionAdapter());

				session_start();
			}
		
			return $this;
		}
		
		public function initCache() {
			$cacheConfig = Config::getAsObject()->cache;

			if (!empty($cacheConfig))	 {
				$this->_cache = Cache::factory(
					$cacheConfig->frontend->name,
					$cacheConfig->backend->name,
					empty($cacheConfig->frontend->options) ? array() : (array) $cacheConfig->frontend->options,
					empty($cacheConfig->backend->options)  ? array() : (array) $cacheConfig->backend->options
				);
			}
			
			return $this;
		}
		
		public function getCache() {
			return $this->_cache;
		}
		
		public function hasCache($checkActive = true) {
			return !empty($this->_cache) && ($checkActive ? $this->_cache->is_active() : true);
		}
		
		public function initRoutes() {
			$router       = Routing\Router::getInstance();
			$activeRoutes = Config::read('route.active');
			$defaults     = array();
			$patterns     = array();
			
			if (empty($activeRoutes)) {
				throw new InvalidConfigurationException('Missing configuration for component "route"');
			}
			
			if (is_array($activeRoutes)) {
				$activeRoutes = array_map('trim', $activeRoutes);
			} elseif (strstr($activeRoutes, ',')) {
				$activeRoutes = array_map('trim', explode(',', $activeRoutes));
			} else {
				$activeRoutes = array(trim($activeRoutes));
			}
			
			$routeConfig = Config::getAsObject()->route;
			
			/**
			 * Determine important settings per route
			 */
			
			list($defaults, $patterns) = $this->_getRouteConfiguration($routeConfig, $activeRoutes);
			foreach($activeRoutes as $chain) {
				$chains = preg_split('#\->#', $chain);
				
				if (count($chains) > 1) {
					$routeChain = new Routing\Route\Chain();
				}
				
				foreach($chains as $routeName) {
					// Find specific configuration values
					list($specificDefaults, $specificPatterns) = $this->_getRouteConfiguration($routeConfig->$routeName);
				
					if (!empty($routeConfig->$routeName->classname)) {
						$className = $routeConfig->$routeName->classname;
					} elseif(!empty($routeConfig->$routeName->type)) {
						$type      = $routeConfig->$routeName->type;
						$className = "PXL\Hornet\Routing\Route\\$type";
						if (!class_exists($className)) {
							throw new InvalidConfigurationException("Invalid route type \"$type\". Please check your configuration file for errors.");
							$className = 'PXL\Hornet\Routing\Route\Route';
						}
					} else {
						$className = 'PXL\Hornet\Routing\Route\Route';
					}
				
					// Create new route and add to router
					$route = new $className(
						Config::read("route.$routeName.uri"),
						array_merge($defaults, $specificDefaults),
						array_merge($patterns, $specificPatterns),
						$routeName
					);
				
					if (count($chains) === 1) {
						$router->addRoute($route, $routeName);
					} else {
						$routeChain->addRoute($route, $routeName);
						
						if (end($chains) === $routeName) {
							$router->addRoute($routeChain, $chain);
						}
					}
				}
			}
			
			$router->route($this->_request);
			
			return $this;
		}
	
		public function dispatch() {
			// Send security-related headers before flushing output buffer
			header('X-Frame-Options: SAMEORIGIN');
			
			$this->runDispatch();

			$this->_view->flush();
		}
		
		public function runDispatch($renderView = true) {
			if ($this->_request->isDispatched()) {
				return;
			}
			
			$this->_view->overrideController(null)
									->overrideAction(null)
									->overrideView(null)
									->clean();
		
			/**
			 * Determine controller, action and parameters to use in dispatch
			 */
			$controller = $this->_request->getControllerName();
			$action     = $this->_request->getActionName();

			switch(getenv('SERVER_TYPE')) {
				case 'JSONRPC':
					$controllerClassName   = "\Controller\JsonRpc\\$controller";
					$controllerUcClassName = "\Controller\JsonRpc\\" . ucfirst($controller);
					$actionMethodName      = $action;
					break;
					
				default:
					$controllerClassName   = "\Controller\\$controller";
					$controllerUcClassName = "\Controller\\" . ucfirst($controller);
					$actionMethodName      = "{$action}Action";
					break;
			}

			$params = $this->_request->getUnnamedParams() ?: array();
			
			if (empty($controller)) {
				if ($this->catchable404()) {
					return $this->try404();
				} else {
					throw new Exception\ControllerEmptyException('No valid controller');
				}
			}

			// Check if controller exists
			if (!class_exists($controllerClassName) && !class_exists($controllerUcClassName)) {
				$scaffoldingActive = false;
				if (Config::has('scaffolding.active')) {
					$scaffoldingActive = (boolean) Config::read('scaffolding.active');
				}
				
				if ($scaffoldingActive) {
					if (!$this->_preDispatched) {
						$this->notify();
					}
					
					if (!Scaffolding::controller($controller, $action)) {
						return;
					}
				} else {
					if ($this->catchable404()) {
						return $this->try404();
					} else {
						throw new Exception\ClassNotFoundException("Controller '$controller' not found");
					}
				}
			}
			
			// Check if action exists if we're not in JSONRPC mode
			if (!method_exists($controllerClassName, $actionMethodName) && !method_exists($controllerUcClassName, $actionMethodName)) {
				if ($this->catchable404()) {
					return $this->try404();
				} else {
					throw new Exception\ActionNotFoundException("Action '$action' not found");
				}
			}
			
			// Run preDispatch hooks
			if (!$this->_preDispatched) {
				$this->notify();
				$this->_preDispatched = true;
			}
			
			// Create controller instance and run action with supplied parameters
			$c = new $controllerClassName();
			if ($c->preAction() !== false) {
				call_user_func_array(array($c, $actionMethodName), array_map('htmlentities', $params));
			}
			$c->postAction();

			if (!$this->_postDispatched) {
				$this->_postDispatched = true;
				$runPostDispatch = true;
			} else {
				$runPostDispatch = false;
			}

			// Set dispatched to TRUE to request object
			$this->_request->setDispatched(true);

			// Run postDispatch hooks if applicable
			if ($runPostDispatch) {
				$this->notify();
			}

			/**
			 * Handle view logic
			 */
			 if ($renderView && $runPostDispatch) {
				 $this->_view->render();
			 }
		}
		
		public function setRequest(Request\Request $request) {
			$this->_request = $request;
		}
		
		public function getRequest() {
			return $this->_request;
		}
		
		public function attach(\SplObserver $observer) {
			$this->_observers[spl_object_hash($observer)] = $observer;
		}
		
		public function detach(\SplObserver $observer) {
			unset($this->_observers[spl_object_hash($observer)]);
		}
		
		public function notify() {
			if (!empty($this->_observers)) {
				foreach($this->_observers as $observer) {
					$observer->update($this);
				}
			}
		}
		
		protected function _getRouteConfiguration(\StdClass $routeConfig = null, array $excludedKeys = array()) {
			$defaults = $patterns = array();
		
			foreach($routeConfig as $k => $v) {
				if (in_array($k, $excludedKeys) || !($v instanceof \StdClass)) {
					continue;
				}
				
				if (!empty($v->default)) {
					$defaults[$k] = $v->default;
				}
				
				if (!empty($v->pattern)) {
					$patterns[$k] = $v->pattern;
				}
			}
		
			return array($defaults, $patterns);
		}
		
		public function catchable404() {
			if (!Config::has('error.404.catch')) {
				return false;
			} else {
				return true;
			}
		}
		
		public function try404() {
			try {
				$controller404 = Config::read('error.404.controller');
				$action404     = Config::read('error.404.action');
				
				if ($controller404 !== $this->_request->getControllerName() && $action404 !== $this->_request->getActionName()) {
					$this->_request->setControllerName($controller404)
												 ->setActionName($action404)
												 ->setDispatched(false);											
												
					http_response_code(404);
					return $this->runDispatch();
				} else {
					throw new \Exception();
				}
			} catch (\Exception $e) {
				// Last resort -- just exit with HTTP 404
				ob_end_clean();
				http_response_code(404);
				exit;
			}
		}

		public function try500() {
			try {
				$controller500 = Config::read('error.500.controller');
				$action500     = Config::read('error.500.action');

				if ($controller500 !== $this->_request->getControllerName() && $action500 !== $this->_request->getActionName()) {
					$this->_request->setControllerName($controller500)
												 ->setActionName($action500)
												 ->setDispatched(false);											
												
					http_response_code(500);
					$this->runDispatch();
					$this->_view->flush();
				} else {
					throw new \Exception();
				}
			} catch (\Exception $e) {
				// Last resort -- just exit with HTTP 500
				ob_end_clean();
				http_response_code(500);
				exit;
			}
		}
	}