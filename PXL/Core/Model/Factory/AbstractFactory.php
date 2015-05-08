<?php
	namespace PXL\Core\Model\Factory;
	
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', '..', 'Db',         'Db.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', '..', 'Session',    'Session.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', '..', 'Collection', 'SimpleMap.php')));
	
	use PXL\Core\Db;
	use PXL\Core\Session;
	use PXL\Core\Collection;
	
	/**
	 * Abstract AbstractFactory class.
	 *
	 * @abstract
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	abstract class AbstractFactory {
		
		protected $_buffer = null;
		
		protected static $_instances = array();

		protected static $includeNamespaces = array('PXL\Core\Model\Factory', 'Model\Factory');

		protected function __construct() {
			$this->_buffer = new Collection\SimpleMap();
		}
		
		public static function getInstance() {
			$calledClassName = get_called_class();
			if (!array_key_exists($calledClassName, self::$_instances)) {
				self::$_instances[$calledClassName] = new static();
			}
			
			return self::$_instances[$calledClassName];
		}
		
		/**
		 * getInstanceByName function.
		 * 
		 * @access public
		 * @static
		 * @param mixed $factoryName
		 * @return void
		 */
		public static function getInstanceByName($factoryName) {
			foreach(static::$includeNamespaces as $namespace) {
				$factoryNamespacedClassName = $namespace . '\\' . $factoryName;

				if (class_exists($factoryNamespacedClassName)) {
					return call_user_func(array($factoryNamespacedClassName, 'getInstance'));
				}
			}

			if (class_exists($factoryName)) {
				return call_user_func(array($factoryName, 'getInstance'));
			}

			throw new \BadMethodCallException('No factory with name ' . $factoryName . ' found.');
		}

		public static function addIncludeNameSpace($namespace) {
			if (!in_array($namespace, static::$includeNamespaces)) {
				static::$includeNamespaces[] = $namespace;
			}
		}
		
		protected function __clone() { }
		
		protected function db() {
			return Db\Db::getInstance();
		}
		
		protected function stmt($q, $params = null) {
			return new Db\Statement($q, $params);
		}
		
		protected function session($key, $value = null) {
			if (is_null($value)) {
				return Session\Session::get($key);
			} else {
				Session\Session::set($key, $value);
			}
		}

		protected function debug($message) {
			\PXL\Core\Tools\Logger::debug($message);
		}
	}