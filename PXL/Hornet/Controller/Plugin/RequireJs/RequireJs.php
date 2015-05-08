<?php
	namespace PXL\Hornet\Controller\Plugin\RequireJs;
	
	require_once('jsminplus.php');
	
	use PXL\Core\Config;
	use PXL\Core\Collection;
	use PXL\Hornet\View\View;
	use PXL\Core\Cache\Cache;
	use PXL\Hornet\Application\Application;
	use PXL\Hornet\Controller\Plugin\AbstractPlugin;
	
	use JSMinPlus;
	use GlobIterator;
	use DirectoryIterator;
	use BadMethodCallException;
	
	/**
	 * RequireJs class.
	 * 
	 * Rewrite of RequireJS functionality that was used in the previous
	 * framework to be used and implemented simply as an plugin, without
	 * the need for any additional code.
	 *
	 * @extends AbstractPlugin
	 * @author  Max van der Stam <max@pixelindustries.com>
	 */
	class RequireJs extends AbstractPlugin {
		
		protected $_moduleDir = null;
		protected $_devDir    = '_dev';
		protected $_liveDir   = '_live';
		
		protected $_cache   = null;
		protected $_cacheId = '_PXL_INTERNAL_REQUIREJS_';
		
		protected static $_modules     = null;
		protected static $_initialized = false;
		
		protected function _init() {
			self::$_modules   = new Collection\SimpleList();
			$this->_moduleDir = path(APPLICATION_PATH . 'webroot/js');
			
			self::$_initialized = true;
		}
		
		public function postDispatch(Application $application) {
			// Make sure there is a cache available we can write to
			if (!$application->hasCache(false)) {
				throw new BadMethodCallException('No cache available for RequireJS metadata storage');
			}

			$this->_cache = $application->getCache();
		
			// Fetch request object
			$request = $application->getRequest();
		
			// Determine current controller and action
			$controllerName = $request->getControllerName();
			$actionName     = $request->getActionName();
			$moduleName     = "$controllerName/$actionName";
			
			if (self::$_modules->indexOf($moduleName) === -1) {
				self::$_modules->add($moduleName);
			}
			
			// Determine what action to undertake
			if ($application->getEnvironment() === 'development') {
				foreach(self::$_modules as $module) {
					$this->_checkModule($module);
				}
				$jsBase = $this->_devDir;
			} else {
				$jsBase = $this->_liveDir;
			}
			
			if (!empty($_GET['_PXL_REQUIREJS_REFRESH'])) {
				$this->_forceRefresh();
				
				exit('Forced requireJS refresh. Please update SVN.');
			}
			
			// Store important variables concerning RequireJS in view
			Application::getInstance()->getView()->set('_requirejs_modules', self::$_modules->toArray(), true)
												 									 ->set('_requirejs_base',   "js/$jsBase",                true);
		}
		
		public static function addModule($moduleName) {
			if (!self::$_initialized) {
				throw new \BadMethodCallException('Tried to add module to RequireJS, but RequireJS is not initialized.');
			}
		
			if (self::$_modules->indexOf($moduleName) === -1) {
				self::$_modules->add($moduleName);
			}
		}
		
		protected function _forceRefresh() {
			$jsBase  = $this->_devDir;
			$devPath = path("{$this->_moduleDir}/$jsBase");
			
			$oDirIterator   = new DirectoryIterator($devPath);
			$modules        = array();
			$controllerName = '';
			
			//Build array of modules we need to require
			foreach($oDirIterator as $dir) {
				if ($dir->isDot()) {
					continue;
				}
				
				$controllerName = explode(DIRECTORY_SEPARATOR, $dir->getPathname());
				$controllerName = array_peek($controllerName, count($controllerName) - 1);
				
				if (strpos($controllerName, '.') === 0) {
					continue;
				}
				
				$glob = new GlobIterator($dir->getPathname() . DIRECTORY_SEPARATOR . '*.js', GlobIterator::SKIP_DOTS | GlobIterator::KEY_AS_FILENAME);
				foreach($glob as $filename => $entry) {
					$modules[] = array($controllerName, substr($filename, 0, -3));
				}
			}
			
			//Remove cache file since we're rebuilding everything
			$this->_cache->remove($this->_cacheId);
			
			//Run the requireFile routine for each module, forcing any creation of directories and minified versions
			foreach($modules as $module) {
				$this->_checkModule("{$module[0]}/{$module[1]}");
			}
		}
		
		protected function _checkModule($moduleName) {
			list($controllerName, $actionName) = explode('/', $moduleName);
			$jsBase     = $this->_devDir;
			$directory  = path("{$this->_moduleDir}/$jsBase");

			// Fetch cached metadata
			if (!($metadata = $this->_cache->load($this->_cacheId, true))) {
				$metadata = array();
			}
			
			// Check if module file exists
			if (!is_file(path("$directory/$controllerName/$actionName.js"))) {
				$this->_initModuleFile($directory, $controllerName, $actionName);
			}
				
			// Fetch module file contents
			$moduleFileContents = file_get_contents(path("$directory/$controllerName/$actionName.js"));
			$moduleFileHash     = hash('md5', $moduleFileContents);
				
			if (!array_key_exists($moduleName, $metadata) || $metadata[$moduleName] !== $moduleFileHash) {	
				// Write live file
				$this->_writeLiveFile(path("{$this->_moduleDir}/{$this->_liveDir}"), $controllerName, $actionName, $moduleFileContents);
				
				$metadata[$moduleName] = $moduleFileHash;
			}
				
			// Store metadata back in cache
			$this->_cache->save($metadata, $this->_cacheId);
		}
		
		protected function _initModuleFile($directory, $controller, $action) {
			$directory = path("$directory/$controller");
			$filename  = path("$directory/$action.js");
			$date      = date('d-m-Y H:i:s');
		
			// Check if directory exists -- if not: create it recursively
			if (!is_dir($directory)) {
				mkdir($directory, 0777, true);
			}
			
			ob_start();

			if (Config::has('requirejs.moduletemplate') && Config::read('requirejs.moduletemplate') === 'jquery') {
				include('ModuleTemplateJquery.php');
			} else {
				include('ModuleTemplate.php');
			}
			
			file_put_contents($filename, ob_get_clean());
		}
		
		protected function _writeLiveFile($directory, $controller, $action, $moduleFileContents) {
			$directory = path("$directory/$controller");
			$filename  = path("$directory/$action.js");
			$date      = date('d-m-Y H:i:s');
			
			// Check if directory exists -- if not: create it recursively
			if (!is_dir($directory)) {
				mkdir($directory, 0777, true);
			}

			file_put_contents($filename, JSMinPlus::minify($moduleFileContents));
		}
	}
