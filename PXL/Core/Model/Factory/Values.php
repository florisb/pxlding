<?php
	namespace PXL\Core\Model\Factory;

	class Values extends AbstractFactory {

		public static function __callStatic($method,$params) {
			return self::_fetchValue($method, $params);
		}
		
		public function __call($method, $params) {
			return self::_fetchValue($method, $params);
		}
		
		protected static function _fetchValue($method, $params) {
			$possibleClasses  = array();
			$camelCasedMethod = preg_split('/(?=[A-Z])/', $method);
			array_shift($camelCasedMethod);
			$camelCasedMethod = implode('\\', $camelCasedMethod);

			$possibleClasses[] = "Model\Value\\$method";
			$possibleClasses[] = "Model\Value\\$camelCasedMethod";
			$possibleClasses[] = "PXL\Core\Model\Value\\$method";
			$possibleClasses[] = "PXL\Core\Model\Value\\$camelCasedMethod";


			$possibleClasses = array_unique($possibleClasses);
			foreach($possibleClasses as $className) {
				if (class_exists($className)) {
					return new $className($params[0]);
				}
			}

			throw new \InvalidArgumentException("Value object \"$method\" not found. Attempts: [".implode(', ', $possibleClasses).']');
		}
}