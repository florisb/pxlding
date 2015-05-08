<?php
	namespace PXL\Hornet\View;

	require_once(APPLICATION_PATH . path('PXL/Hornet/lib/lightncandy.php'));

	use Exception;
	use LightnCandy;
	use BadMethodCallException;
	use PXL\Core\Exception as E;
	use PXL\Hornet\Routing\Router;
	use PXL\Core\Tools;
	use PXL\Hornet\Seo\Seo;
	use PXL\Core\Config;
	use PXL\Hornet\Request\Request;
	use PXL\Hornet\Application\Application;
	
	use PXL\Hornet\Controller\Helper as ControllerHelper;
	
	class View implements iView {	
	
		protected $_template  = 'default';
		protected $_view;
		protected $_variables       = array();
		protected $_resultData      = null;
		protected $_metaVariables   = array();
		protected $_fbMetaVariables = array();
		protected $_globalVariables = array();
		
		protected $_viewBasePaths     = array();
		protected $_templateBasePaths = array();
		
		protected $_action     = null;
		protected $_controller = null;
		
		protected $_overriddenViewFile = null;
		
		protected $_css = array();
		protected $_js  = array();
		
		protected $_buffer = '';
		
		protected $_helpers = array();
		
		protected static $_instance = null;

		protected $handleBarRenderers = array();

		public static $defaultPartialController = null;

		protected function __construct() {
			$this->init();
			
			/**
			 * Start output buffering at this point as header ouput
			 * is done afterwards
			 */
			ob_start();
		}
		
		protected function __clone() { }
		
		public function __call($method, $arguments) {
			if (preg_match('#^helper([[:upper:]]{1}[\w]+)$#', $method, $matches)) {
				$methodName = $matches[1];
				$className  = "App\ViewHelper\\$methodName";
				
				if (!array_key_exists($methodName, $this->_helpers)) {
					$this->_helpers[$methodName] = new $className($this);
				}
				
				return call_user_func_array(array($this->_helpers[$methodName], 'run'), $arguments);
			} else {
				throw new BadMethodCallException("Call to undefined method " . __CLASS__ . "::$method()");
			}
		}
		
		protected function init() {
			// Set correct defaults
			$this->_viewBasePaths[]     = path('Views/');
			$this->_templateBasePaths[] = path('Views/_templates/');
			$this->_templateBasePaths[] = path(dirname(__FILE__) . '/Templates/');
		
			/**
			 * Determine type of request if possible. In command-line
			 * environments, this call will trigger an exception because
			 * there isn't a request being done.
			 */
			try {
				$request = Application::getInstance()->getRequest();
			
				if ($request->isAsyncNav()) {
					$this->_template = 'asyncnav';
				} elseif ($request->isXhr()) {
					$this->_template = 'ajax';
				} else {
					$this->_template = 'default';
				}	
			} catch (Exception $e) {
				$this->_template = 'default';
			}
		}
		
		public static function getInstance() {
			if (!self::$_instance) {
				self::$_instance = new static();
			}
			
			return self::$_instance;
		}
		
		public function getViewBasePaths() {
			return $this->_viewBasePaths;
		}
		
		public function setTemplateBasePath($path) {
			$this->_templateBasePath = $path;
			
			return $this;
		}
		
		public function overrideController($controller) {
			$this->_controller = $controller;
			
			return $this;
		}
		
		public function overrideAction($action) {
			$this->_action = $action;
			
			return $this;
		}
		
		public function overrideView($viewFile) {
			$this->_overriddenViewFile = $viewFile;
			
			return $this;
		}

		public function render() {
			//Determine template view path
			foreach($this->_templateBasePaths as $basePath) {
				$templateView = path("{$basePath}{$this->_template}.phtml");
				if (is_file($templateView) && is_readable($templateView)) {
					break;
				}
			}
			
			ob_start();
			echo $this->renderView($templateView, $this->_variables);
			$this->_buffer = ob_get_clean();
		}
		
		public function contents() {
			$this->_controller = $this->_controller ?: $this->_determineCurrentControllerName();
			$this->_action     = $this->_action ?: $this->_determineCurrentActionName();

			//Determine view path
			if ($this->_overriddenViewFile) {
				$view = $this->_overriddenViewFile;
			} else {
				foreach($this->_viewBasePaths as $basePath) {
					$view = path("{$basePath}{$this->_controller}/{$this->_action}.phtml");
					if (is_file($view) && is_readable($view)) {
						break;
					}
				}
			}
			
			/**
			 * Output contents in buffer prior to rendering the view
			 */
			ob_get_flush();
			
			// Render view
			echo $this->renderView($view, $this->_variables);
		}
		
		public function clean() {
			$this->_buffer = '';
		}
		
		public function flush() {
			echo $this->_buffer;
			$this->clean();
		}
		
		public function includeCss($cssFiles, $prefixBaseUrl = true) {
			if (!is_array($cssFiles)) {
				$cssFiles = array($cssFiles);
			}
			
			foreach($cssFiles as $cssFile) {
				if ($prefixBaseUrl) {
					$this->_css[] = Application::getInstance()->getRequest()->getBaseUrl() . $cssFile;
				} else {
					$this->_css[] = $cssFile;
				}
			}
			
			return $this;
		}
		
		public function css() {
			foreach($this->_css as $css) {
				echo "<link rel=\"stylesheet\" href=\"$css\" />\n";
			}
			
			return $this;
		}
		
		public function includeJs($jsFiles, $prefixBaseUrl = true) {
			if (!is_array($jsFiles)) {
				$jsFiles = array($jsFiles);
			}
			
			foreach($jsFiles as $jsFile) {
				if ($prefixBaseUrl) {
					$this->_js[] = Application::getInstance()->getRequest()->getBaseUrl() . $jsFile;
				} else {
					$this->_js[] = $jsFile;
				}
			}
			
			return $this;
		}
		
		public function js() {
			foreach($this->_js as $js) {
				echo "<script src=\"$js\"></script>";
			}
			
			return $this;
		}
		
		public function partial($action, $variables = array(), $controller = null) {
			//Determine controller name
			$controller = $controller ?: self::$defaultPartialController ?: $this->_determineCurrentControllerName();
		
			//Determine view path
			foreach($this->_viewBasePaths as $basePath) {
				$view = path("{$basePath}{$controller}/{$action}.phtml");
				if (is_file($view) && is_readable($view)) {
					break;
				}
			}
			
			echo $this->renderView($view, $variables);
			
			return $this;
		}
		
		public function partialLoop($action, $traversable, $variables = array(), $controller = null, $prepend = null, $append = null, $fillColumnAmount = null) {
			//Determine controller name
			$controller = $controller ?: self::$defaultPartialController ?: $this->_determineCurrentControllerName();
			
			if (!is_array($traversable) && !array_key_exists('Traversable', class_implements($traversable)) && !($traversable instanceof \StdClass)) {
				throw new E\InvalidVariableTypeException();
			}
			
			//Determine view path
			foreach($this->_viewBasePaths as $basePath) {
				$view = path("{$basePath}{$controller}/{$action}.phtml");
				if (is_file($view) && is_readable($view)) {
					break;
				}
			}
			
			$counter = 0;
			foreach($traversable as $key => $element) {
				$variables['_element'] = $element;
				$variables['_counter'] = $counter;
				$variables['_key']     = $key;
				$variables['_length']  = count($traversable);
				$counter++;
				
				if (!is_null($prepend)) {
					echo $prepend;
				}
				
				echo $this->renderView($view, $variables);
				
				if (!is_null($append)) {
					echo $append;
				}
			}

			if (!is_null($fillColumnAmount)) {
				$fillColumnAmount = abs((int) $fillColumnAmount);
				if (count($traversable) % $fillColumnAmount) {
					for($i = 0; $i < ($fillColumnAmount - count($traversable) % $fillColumnAmount); $i++) {
						echo "{$prepend}{$append}";
					}
				}
			}
			
			return $this;
		}
		
		public function shorten_text($str, $length, $breakWords = true, $append = '…') {
			return Tools\String::shorten_text($str, $length, $breakWords, $append);
		}
		
		public function set($key, $value, $availableGlobally = false) {
			$this->_variables[$key] = $value;
			
			if ($availableGlobally) {
				$this->_globalVariables[] = $key;
			}
			
			return $this;
		}
		
		public function setResult($value) {
			$this->_resultData = $value;
		}
		
		public function setFbMeta($key, $value) {
			$this->_fbMetaVariables[$key] = $value;
		}
		
		public function getFbMeta($key) {
			return array_key_exists($key, $this->_fbMetaVariables) ? $this->_fbMetaVariables[$key] : null;
		}
		
		public function setMeta($key, $value) {
			$this->_metaVariables[$key] = $value;
		}
		
		public function getMeta($key) {
			return array_key_exists($key, $this->_metaVariables) ? $this->_metaVariables[$key] : null;
		}
		
		public function get($key) {
			return array_key_exists($key, $this->_variables) ? $this->_variables[$key] : null;
		}
		
		public function view($action, $controller = null) {
			$this->_action     = $action;
			$this->_controller = $controller ?: null;
		}
		
		public function template($name = null) {
			if (is_null($name)) {
				return $this->_template;
			} else {
				$this->_template = $name;
			
				return $this;
			}
		}
		
		public function renderView($view, $variables = array()) {
			//Check if template file exists
			if (!is_file($view)) {
				throw new E\FileNotFoundException("File '$view' not found");
			}
		
			// Make sure important variables are available at all times
			try {
				$variables['_baseurl']    = Application::getInstance()->getRequest()->getBaseUrl();
				$variables['_action']     = $this->_determineCurrentActionName();
				$variables['_controller'] = $this->_determineCurrentControllerName();
				$variables['_route_name'] = $this->_determineCurrentRouteName();
			} catch (Exception $e) {
				// Continue gracefully
			}
			
			foreach($this->_globalVariables as $_var) {
				if (array_key_exists($_var, $this->_variables)) {
					$variables[$_var] = $this->_variables[$_var];
				}
			}
			unset($_var);
			
			if (!is_null($this->_resultData)) {
				$variables['_result'] = $this->_resultData;
			}

			extract($variables);

			ob_start();
			include($view);
			return ob_get_clean();
		}

		public function handleBar($view, array $variables = array()) {
			$view = APPLICATION_PATH . path('webroot/templates/' . $view . '.tpl');

			if (!is_file($view)) {
				throw new E\FileNotFoundException("File '$view' not found");
			}

			if (!($renderer = $this->handleBarRenderers[$view])) {
				$phpStr   = LightnCandy::compile(file_get_contents($view));
				$renderer = LightnCandy::prepare($phpStr);

				$this->handleBarRenderers[$view] = $renderer;
			}

			echo $renderer($variables);
		}
		
		public function route($controller = null, $action = null, array $params = array(), $routeName = null, $includeBaseUrl = false, $queryVariables = array()) {
			if (is_null($routeName) && Config::has('route.defaultrouteassembler')) {
				$routeName = Config::read('route.defaultrouteassembler');
			}
		
			$router = Router::getInstance();
			$route  = $router->getRoute($routeName ?: 0);
			
			if (is_null($route)) {
				throw new \InvalidArgumentException("Unknown route $routeName");
			}
			
			$url = $route->reverseAssemble(array(
				'controller' => $controller,
				'action'     => $action
			) + $params);
			
			if ($includeBaseUrl) {
				$request = new Request();
				$url     = $request->getBaseUrl() . $url;
			}
			
			if (!empty($queryVariables)) {
				$url .= '?' . http_build_query($queryVariables);
			}
			
			return $url;
		}
		
		protected function _determineCurrentControllerName() {
			//Retrieve request object and retrieve current controller name
			$request    = Application::getInstance()->getRequest();
			$controller = $request->getControllerName();
			
			return $controller;
		}
		
		protected function _determineCurrentActionName() {
			$request = Application::getInstance()->getRequest();
			$action  = $request->getActionName();
			
			return $action;
		}
		
		protected function _determineCurrentRouteName() {
			return Router::getInstance()->getMatchedRouteName();
		}
		
		protected function seo() {
			echo $this->renderView(path(dirname(__FILE__) . '/Templates/seo.phtml'), Seo::getDataAsArray() + array('meta' => $this->_metaVariables, 'fbmeta' => $this->_fbMetaVariables));
		}
		
		protected function FlashMessenger() {
			return ControllerHelper\FlashMessenger::getInstance();
		}

		public function getSecureToken($namespace = null, $returnAsArray = false) {
			$token = Tools\CSRF::getToken();
			$name  = Tools\CSRF::CSRF_NAME;

			if ($namespace) {
				$name = "{$namespace}[{$name}]";
			}

			return $returnAsArray ? array($name, $token) : "<input type=\"hidden\" value=\"$token\" name=\"$name\" />";
		}
	}
