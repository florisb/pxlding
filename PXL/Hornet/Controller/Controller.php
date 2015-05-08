<?php
	namespace PXL\Hornet\Controller;

	use Exception;
	use PXL\Core\Tools;
	use PXL\Core\Config;
	use PXL\Core\Collection;
	use PXL\Core\Model\Value;
	use PXL\Hornet\View\View;
	use BadMethodCallException;
	use InvalidArgumentException;
	use PXL\Hornet\Application\Application;
	
	abstract class Controller implements iController {
		
		public function preAction() { }
		
		public function postAction() { }
		
		public function __call($methodName, $arguments) {
			if (preg_match('#^(request|seo|view)([[:upper:]]{1}[\w]+)$#', $methodName, $matches)) {
				$methodName = lcfirst($matches[2]);
				
				switch($matches[1]) {
					case 'request':
						return call_user_func_array(array(Application::getInstance()->getRequest(), $methodName), $arguments);
						break;
						
					case 'seo':
						return call_user_func_array(array('PXL\Hornet\Seo\Seo', $methodName), $arguments);
						break;
						
					case 'view':
						return call_user_func_array(array(Application::getInstance()->getView(), $methodName), $arguments);
						break;
				}
			} else {
				throw new BadMethodCallException("Call to undefined method " . __CLASS__ . "::$methodName()");
			}
		}

		public static function __callStatic($methodName, $arguments) {
			if (method_exists(get_called_class(), $methodName)) {
				return call_user_func_array(array(get_called_class(), $methodName), $arguments);
			} else {
				throw new BadMethodCallException('Call to undefined method ' . __CLASS__ . "::$methodName()");
			}
		}
		
		protected function init() { }
		
		public function __construct() {
			$this->init();
		}
		
		/**
		 * Proxy methods for convenience
		 */
		
		
		/**
		 * getImages function.
		 * 
		 * Shortcut for getFiles to use Value\Image objects by default.
		 *
		 * @access protected
		 * @param mixed $namespaces (default: null)
		 * @param bool $useRandomFilename (default: false)
		 * @return void
		 */
		protected function getImages($namespaces = null, $useRandomFilename = false, $className = '\PXL\Core\Model\Value\Image') {
			return static::getFiles($namespaces, $useRandomFilename, $className);
		}
		
		protected function getFile($namespaces = null, $useRandomFilename = false, $className = 'PXL\Core\Model\Value\File') {
			$files = static::getFiles($namespaces, $useRandomFilename, $className);
			
			if (!count($files)) {
				return null;
			} else {
				return array_peek($files);
			}
		}
		
		/**
		 * getFiles function.
		 * 
		 * Provides a simple but flexible way to quickly
		 * get uploaded images into a collection of
		 * object instances.
		 *
		 * This method is very forgiving and will not
		 * throw Exceptions and/or other errors, although
		 * Exceptions from the returned objects *can* be
		 * thrown. This method will *always* return a Collection\SimpleMap
		 * instance.
		 *
		 * @access protected
		 * @param mixed $namespaces (default: null)
		 * @param bool $useRandomFilename (default: false)
		 * @author Max van der Stam <max@pixelindustries.com>
		 * @return Collection
		 */
		protected function getFiles($namespaces = null, $useRandomFilename = false, $className = 'PXL\Core\Model\Value\File') {
			$result                = new Collection\SimpleMap();
			$returnSingleNamespace = null;
			
			switch(true) {
				default:
				case (is_null($namespaces)):
					$namespaces = array_keys($_FILES);
					break;
					
				case ($namespaces instanceof Collection\SimpleList):
					$namespaces = $namespace->toArray();

				case (is_scalar($namespaces)):
					$returnSingleNamespace = (string) $namespaces;
					$namespaces            = array($returnSingleNamespace);
					
				case (is_array($namespaces)):
					foreach($namespaces as &$n) {
						if (is_numeric($n)) {
							if ($n >= (count($_FILES) - 1)) {
								$n = array_peek(array_keys($_FILES), $n);
							} else {
								continue;
							}
						}
					}
					unset($n);
					break;
			}

			foreach($namespaces as $namespace) {
				$collectionKey = $namespace;
				if (!$result->containsKey($collectionKey)) {
					$result->put($collectionKey, new Collection\SimpleMap());
				}

				if(preg_match("/^(\w+)((?:\[\w+\])+)$/", $namespace, $matches)) {
					$namespace = array($matches[1]);
					if (count($matches) === 3) {
						if (preg_match_all("/\[(\w+)\]/", $matches[2], $matches)) {
							$namespace = array_merge($namespace, $matches[1]);
						}
					}
				}

				$toplevelNamespace = is_array($namespace) ? array_shift($namespace) : $namespace;
				if (array_key_exists($toplevelNamespace, $_FILES)) {
					if (is_array($namespace)) {
						$fileData = array();
						foreach(array('tmp_name', 'name', 'type', 'size') as $k) {
							$fileData[$k] = eval("return \$_FILES['$toplevelNamespace']['$k']['" . implode("']['", $namespace) . "'];");
						}
					} else {
						$fileData = $_FILES[$toplevelNamespace];
					}

					if (!is_array($fileData['tmp_name'])) {
						$fileData['tmp_name'] = array($fileData['tmp_name']);
						$fileData['name']     = array($fileData['name']);
						$fileData['type']     = array($fileData['type']);
						$fileData['size']     = array($fileData['size']);
					}
					
					foreach($fileData['tmp_name'] as $key => $file) {
						if (@is_uploaded_file($file)) {
							$mediatype = $fileData['type'][$key];
							$filename  = $fileData['name'][$key];
							
							if ($useRandomFilename) {
								$tmpName   = end(explode('/', $file));
								$extension = pathinfo($filename, PATHINFO_EXTENSION);
								$filename  = md5(uniqid($tmpName, true)) . ".$extension";
								$size      = $fileData['size'][$key];
							}
							
							if (is_null($className)) {
								$value = (object) array('file' => $file, 'mediatype' => $mediatype, 'name' => $filename, 'size' => $size, 'extension' => $extension, 'tmp_name' => $fileData['tmp_name'][$key]);
							} else {
								$value = new $className($file, $mediatype, $filename, $fileData['tmp_name'][$key]);
							}
							
							$result->get($collectionKey)->put($key, $value);
						}
					}
				}
			}
			
			if (is_null($returnSingleNamespace)) {
				return $result;
			} elseif(is_numeric($returnSingleNamespace)) {
				return ((int) $returnSingleNamespace <= (count($result) - 1)) ? array_peek($result, (int) $returnSingleNamespace) : new Collection\SimpleMap();
			} else {
				return $result->get($returnSingleNamespace);
			}
		}
		
		protected function getQuery($fieldname = null, $sanitize = true, $validateCSRFToken = false) {
			if (is_null($fieldname)) {
				$data = $_GET;
			} elseif(preg_match("/^([\w\-]+)((?:\[[\w\-]+\])+)?$/", $fieldname, $matches)) {
				$namespace = array($matches[1]);
				if (count($matches) === 3) {
					if (preg_match_all("/\[([\w\-]+)\]/", $matches[2], $matches)) {
						$namespace = array_merge($namespace, $matches[1]);
					}
				}

				$data = eval('return $_GET[\'' . implode('\'][\'', $namespace) . '\'];');
			} else {
				$data = null;
			}
				
			$tokenFound = false;
			if ($data) {
				$sanitizeRecursively = function($v) use (&$sanitizeRecursively, $validateCSRFToken, &$tokenFound) {
					if (is_array($v)) {
						$data = array();
						foreach($v as $k => $_v) {
							if ($validateCSRFToken && $k === Tools\CSRF::CSRF_NAME) {
								$tokenFound = true;

								if (!Tools\CSRF::validateToken($_v)) {
									throw new \PXL\Core\Exception\CSRFTokenInvalidException();	
								}
							} else {
								$data[$k] = $sanitizeRecursively($_v);
							}
						}

						return $data;
					} else {
						return htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
					}
				};
				
				if ($sanitize) {
					$data = $sanitizeRecursively($data);
				}

				if ($validateCSRFToken && !$tokenFound) {
					throw new \PXL\Core\Exception\CSRFTokenInvalidException();
				}
				
				return $data ?: null;
			} else {
				return null;
			}
		}
		
		protected function getPost($fieldname = null, $sanitize = true, $validateCSRFToken = false) {
			if (is_null($fieldname)) {
				$data = $_POST;
			} elseif(preg_match("/^([\w\-]+)((?:\[[\w\-]+\])+)?$/", $fieldname, $matches)) {
				$namespace = array($matches[1]);
				if (count($matches) === 3) {
					if (preg_match_all("/\[([\w\-]+)\]/", $matches[2], $matches)) {
						$namespace = array_merge($namespace, $matches[1]);
					}
				}

				$data = eval('return $_POST[\'' . implode('\'][\'', $namespace) . '\'];');
			} else {
				$data = null;
			}

			$tokenFound = false;
			if ($data) {
				$sanitizeRecursively = function($v) use (&$sanitizeRecursively, $validateCSRFToken, &$tokenFound) {
					if (is_array($v)) {
						$data = array();
						foreach($v as $k => $_v) {
							if ($validateCSRFToken && $k === Tools\CSRF::CSRF_NAME) {
								$tokenFound = true;

								if (!Tools\CSRF::validateToken($_v)) {
									throw new \PXL\Core\Exception\CSRFTokenInvalidException();	
								}
							} else {
								$data[$k] = $sanitizeRecursively($_v);
							}
						}

						return $data;
					} else {
						return htmlspecialchars($v, ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
					}
				};
				
				if ($sanitize) {
					$data = $sanitizeRecursively($data);
				}

				if ($validateCSRFToken && !$tokenFound) {
					throw new \PXL\Core\Exception\CSRFTokenInvalidException();
				}
				
				return $data ?: null;
			} else {
				return null;
			}
		}
		 
		protected function requireModule($moduleName) {
			\PXL\Hornet\Controller\Plugin\RequireJs\RequireJs::addModule($moduleName);
		}
		 
		protected function getControllerName() {
			return Application::getInstance()->getRequest()->getControllerName();
		}
		
		protected function getActionName() {
			return Application::getInstance()->getRequest()->getActionName();
		}
		
		protected function view($action, $controller = null) {
			Application::getInstance()->getView()->view($action, $controller);
		}
		
		protected function set($key, $value, $availableGlobally = false) {
			return Application::getInstance()->getView()->set($key, $value, $availableGlobally);
		}
		
		protected function setResult($value) {
			return Application::getInstance()->getView()->setResult($value);
		}
		
		protected function get($key) {
			return Application::getInstance()->getView()->get($key);
		}
		
		protected function setMeta($key, $value) {
			return Application::getInstance()->getView()->setMeta($key, $value);
		}
		
		protected function getMeta($key) {
			return Application::getInstance()->getView()->getMeta($key);
		}
		
		protected function setFbMeta($key, $value) {
			return Application::getInstance()->getView()->setFbMeta($key, $value);
		}
		
		protected function getFbMeta($key) {
			return Application::getInstance()->getView()->getFbMeta($key);
		}
		
		protected function template($name = null) {
			return Application::getInstance()->getView()->template($name);
		}
		
		protected function getEnvironment() {
			return Application::getInstance()->getEnvironment();
		}
		
		protected function getRequest() {
			return Application::getInstance()->getRequest();
		}
		
		protected function isGet() {
			return Application::getInstance()->getRequest()->isGet();
		}
		
		protected function isPost() {
			return Application::getInstance()->getRequest()->isPost();
		}

		protected function isPut() {
			return Application::getInstance()->getRequest()->isPut();
		}

		protected function isDelete() {
			return Application::getInstance()->getRequest()->isDelete();
		}
		
		protected function isXhr() {
			return Application::getInstance()->getRequest()->isXhr();
		}
		
		protected function isAsyncNav() {
			return Application::getInstance()->getRequest()->isAsyncNav();
		}
		
		protected function isSecure() {
			return Application::getInstance()->getRequest()->isSecure();
		}
		
		protected function getParam($key) {
			return Application::getInstance()->getRequest()->getParam($key);
		}
		
		protected function hasParam($key) {
			return Application::getInstance()->getRequest()->hasParam($key);
		}
		
		protected function getParams($enumerated = false) {
			return Application::getInstance()->getRequest()->getParams($enumerated);
		}
		
		protected function includeCss($css, $prefixBaseUrl = true) {
			return Application::getInstance()->getView()->includeCss($css, $prefixBaseUrl);
		}
		
		protected function includeJs($js, $prefixBaseUrl = true) {
			return Application::getInstance()->getView()->includeJs($js, $prefixBaseUrl);
		}
		
		protected function redirect($url, $http301 = false) {
			Application::getInstance()->getRequest()->redirect($url, $http301);
		}
		
		protected function forward($controller = 'home', $action = 'index', $renderView = true) {
			static::getRequest()->setControllerName($controller)
												 ->setActionName($action)
												 ->setDispatched(false);

			Application::getInstance()->runDispatch($renderView);
		}
		
		/**
		 * route function.
		 * 
		 * Proxy of View::route
		 *
		 * @access protected
		 * @return void
		 */
		protected function route() {
			return call_user_func_array(array(Application::getInstance()->getView(), 'route'), func_get_args());
		}
		
		protected function factory($factoryName) {
			return \PXL\Core\Model\Factory\AbstractFactory::getInstanceByName($factoryName);
		}

		protected function decorator($decoratorName, \Traversable $data) {
			return \PXL\Core\Model\Decorator\AbstractDecorator::getInstanceByName($decoratorName, $data);
		}
		
		protected function try404() {
			Application::getInstance()->try404();
		}

		protected function try500() {
			Application::getInstance()->try500();	
		}
		
		protected function log($message, $sendMail = true) {
			if ($sendMail && Config::has('developer.email') && APPLICATION_ENV !== 'development') {
				$email = Config::getAsObject()->developer->email;

				if (is_array($email)) $email = implode('; ', $email);
			} else {
				$email = null;
			}

			\PXL\Core\Tools\Logger::log($message, $email);
		}
		
		protected function debug($message) {
			\PXL\Core\Tools\Logger::debug($message);
		}
		
		/**
		 * Helper proxy methods
		 */
		 
		protected function FlashMessenger() {
			return Helper\FlashMessenger::getInstance();
		}
	}