<?php
	/**
	 * HornetCache class.
	 *
	 * Provides application-level caching functionality for projects powered by
	 * Hornet.
	 *
	 * Because of the singular and "one-time initialisation"-nature of this functionality, this class is used
	 * as a singleton. Therefore, this class may not be instantiated directly but only through the usage of
	 * HornetCache::getInstance().
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	class HornetCache {
		
		protected static $_instance = null;
		
		protected $_cache = null;
		
		public static function getInstance() {
			if (is_null(self::$_instance)) {
				self::$_instance = new static();
			}
			
			return self::$_instance;
		}
		
		protected function __construct() {
			$this->_initDependencies();
			PXL\Core\Config::addFile(APPLICATION_PATH . 'Config/config.ini', getenv('APPLICATION_ENV') ?: 'production');
			
			$cacheConfig = PXL\Core\Config::getAsObject()->cache;

			if (!empty($cacheConfig))	 {
				$this->_cache = PXL\Core\Cache\Cache::factory(
					$cacheConfig->frontend->name,
					$cacheConfig->backend->name,
					empty($cacheConfig->frontend->options) ? array() : (array) $cacheConfig->frontend->options,
					empty($cacheConfig->backend->options)  ? array() : (array) $cacheConfig->backend->options
				);
			}
		}
		
		protected function __clone() { }
		
		public function getCache() {
			return $this->_cache;
		}
		
		public function hasCache() {
			return !is_null($this->_cache);
		}
		
		protected function _initDependencies() {
			// Define important constant so the framework knows where the application resides
			define('APPLICATION_PATH', dirname(__FILE__) . '/../../../');
			
			// Set correct include paths
			set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . PATH_SEPARATOR . APPLICATION_PATH);
			
			// Convenience method used by the autoloader
			$path = function($sPath, $sDelimiter = '/', $sReplacementDelimiter = DIRECTORY_SEPARATOR) {
				return str_replace($sDelimiter, $sReplacementDelimiter, $sPath);
			};
			
			/**
			 * Override and extend the regular (legacy) autoloader with SPL autoload. This
			 * enables dynamic loading of framework components through namespaced classes.
			 */

			spl_autoload_register(function($className) {
				global $include_paths;
				foreach ($include_paths as $path) {
					if (file_exists($path.'/'.$className.'.php')) {
						require_once($path.'/'.$className.'.php');
						return true;
					}
					if (file_exists($path.'/'.str_replace('_', '/', $className).'.php')) {
						require_once($path.'/'.str_replace('_', '/', $className).'.php');
						return true;
					}
				}
				
				return false;
			}, false);
			
			spl_autoload_register(function ($class) use ($path) {
				$classComponents = explode('\\', $class);
		
				$class    = $classComponents ? implode('\\', $classComponents) : $class;
				$fileName = end($classComponents);
	
				$possibleLocations   = array();
				$possibleLocations[] = $path(APPLICATION_PATH . "App/$class.php");
				$possibleLocations[] = $path(APPLICATION_PATH . "$class.php", '\\');
				$possibleLocations[] = $path(APPLICATION_PATH . "$class/$fileName.php", '\\');
		
				foreach($possibleLocations as $possibleLocation) {
					if (is_file($possibleLocation)) {
						require_once($possibleLocation);
						return true;
					}
				}
		
				return false;
			}, false);
		}
	}