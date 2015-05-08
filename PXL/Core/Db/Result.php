<?php
	namespace PXL\Core\Db; 

	require_once('Statement.php');

    use PXL\Core\Collection;
	use PXL\Core\Db\Statement as Statement;

	/**
	 * Result class.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	class Result {

		protected $_statement;
		protected $_native;

		protected $_bound_results = array();

		protected $_last_erroneous_data = null;

		public function __construct(Statement $stmt) {
			$this->_statement = $stmt->statement;
			$this->_native    = method_exists($this->_statement, 'get_result');
		}

		public function fetch_assoc() {
			if ($this->_native && ($this->_result || ($this->_result = $this->_statement->get_result()))) {
				return $this->_result->fetch_assoc();
			}

			if (empty($this->_bound_results)) {
				$this->_bind_results();
			}

			$result = array();

			if (is_null($this->_statement->fetch())) {
				return null;
			}

			foreach($this->_bound_results as $k => $v) {
				$result[$k] = $v;
			}

			return $result;
		}

		public function fetch_object($class = null, $readOnly = false, $customParam = null) {
			if ($this->_native && ($this->_result || ($this->_result = $this->_statement->get_result()))) {
				return $this->_result->fetch_object($class ?: 'PXL\Core\Collection\FastMap', $readOnly ? array(Db::RESULT_READ_ONLY, $customParam) : array(null, $customParam));
			}

			$data = $this->fetch_assoc();

			if (is_null($data)) {
				return null;
			}

			if (is_null($class)) {
				$resultObj = new Collection\FastMap();
				foreach($data as $k => $v) {
					$resultObj->$k = $v;
				}
			} else {
				try {
					$resultObj = $this->_createInstanceSansConstructor($class, $data, $readOnly, $customParam);
				} catch (\Exception $e) {
					if (ini_get('display_errors')) {
						throw $e;
					} else {
						\PXL\Core\Tools\Logger::log($e . ' Data: ' . json_encode($data));
						$this->_last_erroneous_data = $data;
						
						return $this->_last_erroneous_data;
					}
				}
			}

			return $resultObj;
		}

		public function free() {
			if ($this->_native && ($this->_result || ($this->_result = $this->_statement->get_result()))) {
				$this->_result->free();
			} else {
				$this->_statement->free_result();
			}
		}

		public function getLastErroneousData() {
			$data = $this->_last_erroneous_data;

			$this->_last_erroneous_data = null;

			return $data;
		}

		protected function _bind_results() {
			$variables = array();
			$meta      = $this->_statement->result_metadata();

			while($field = $meta->fetch_field()) {
				$variables[] = &$this->_bound_results[$field->name];
			}

			call_user_func_array(array($this->_statement, 'bind_result'), $variables);
		}

		/**
		 * _createInstanceSansConstructor function.
		 *
		 * Simulates the fetch_object functionality that MySQLi
		 * offers by setting properties of an object prior
		 * to calling the __constructor method. When setting those
		 * properties, the magic __set() method *is* triggered.
		 *
		 * This means that data retrieved from the database is
		 * already availablefor developers in the constructor.
		 *
		 * @access protected
		 * @param mixed $class
		 * @param mixed $values
		 * @return void
		 */
		protected function _createInstanceSansConstructor($class, $values, $readOnly, $customParam) {
			$reflector   = new \ReflectionClass($class);
			$properties  = $reflector->getProperties();
			$defaults    = $reflector->getDefaultProperties();

			$serialized = 'O:' . strlen($class) . ":\"$class\":".count($properties) .':{';
			foreach ($properties as $property) {
				$name = $property->getName();
				if($property->isProtected()){
					$name = chr(0) . '*' . chr(0) . $name;
				} elseif($property->isPrivate()){
					$name = chr(0) . $class . chr(0) . $name;
				}

				$serialized .= serialize($name);
				if(array_key_exists($property->getName(), $defaults) ){
					$serialized .= serialize($defaults[$property->getName()]);
				} else {
					$serialized .= serialize(null);
				}
			}

			$serialized .= '}';

			// Retrieve object
			$obj = unserialize($serialized);

			// Set values -- this will trigger __set()
			foreach($values as $k => $v) {
				$obj->$k = $v;
			}

			// Run constructor if possible
			if (method_exists($obj, '__construct')) {
				$obj->__construct(($readOnly ? Db::RESULT_READ_ONLY : null), $customParam);
			}

			return $obj;
		}
	}
