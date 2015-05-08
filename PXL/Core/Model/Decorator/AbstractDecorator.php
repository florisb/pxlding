<?php
	namespace PXL\Core\Model\Decorator;

	use Traversable;
	use PXL\Core\Db;
	use PXL\Core\Session;

	abstract class AbstractDecorator {

		protected $data = null;

		protected static $includeNamespaces = array('PXL\Core\Model\Decorator', 'Model\Decorator') ;

		public function __construct(Traversable $data) {
			$this->data = $data;
		}

		public function __call($method, $arguments) {
			if (method_exists($this, $method)) {
				call_user_func_array(array(&$this, $method), $arguments);

				return $this;
			}
		}

		protected function db() {
			return Db\Db::getInstance();
		}

		protected function stmt($q, array $data = array()) {
			return new Db\Statement($q, $data);
		}

		protected function session($key, $value = null) {
			if (is_null($value)) {
				return Session\Session::get($key);
			} else {
				Session\Session::set($key, $value);
			}
		}

		protected function factory($factoryName) {
			return \PXL\Core\Model\Factory\AbstractFactory::getInstanceByName($factoryName);
		}

		protected function debug($message) {
			\PXL\Core\Tools\Logger::debug($message);
		}

		public static function getInstanceByName($decoratorName, Traversable $data) {
			foreach(static::$includeNamespaces as $namespace) {
				$decoratorNamespacedClassName = $namespace . '\\' . $decoratorName;

				if (class_exists($decoratorNamespacedClassName)) {
					return new $decoratorNamespacedClassName($data);
				}
			}

			if (class_exists($decoratorName)) {
				return new $decoratorNamespacedClassName($data);
			}

			throw new \BadMethodCallException('No decorator with name ' . $decoratorName . ' found.');
		}

		public static function addIncludeNameSpace($namespace) {
			if (!in_array($namespace, static::$includeNamespaces)) {
				static::$includeNamespaces[] = $namespace;
			}
		}
	}