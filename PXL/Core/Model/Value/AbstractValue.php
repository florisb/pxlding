<?php
	namespace PXL\Core\Model\Value;

	require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'JsonSerializable.php')));

	use JsonSerializable;
	use PXL\Core\Exception;

	abstract class AbstractValue implements JsonSerializable {

		protected $_value;

		public function __construct($value) {
			if (is_string($value)) {
				$value = trim($value);
			}
			
			switch(true) {
				case (is_string($value) && !strlen($value)):
				case (is_null($value)):
					throw new Exception\ValueEmptyException();
					break;
			}

			if(!$this->_checkValue($value)) {
				throw new Exception\ValueInvalidException();
			}
			
			$this->_value = $this->_formatValue($value);
		}

		abstract protected function _checkValue($value);
	
		protected function _formatValue($value) {
			return $value;
		}

		public function __get($key) {
			switch($key) {
				case 'value':
					return $this->_value;
					
				default:
					break;
			}
		}
		
		public function __isset($key) {
			switch($key) {
				case 'value':
					return isset($this->_value);
					
				default:
					return false;
			}
		}

		public function __toString() {
			return (string) $this->_value;
		}
		
		public function toStorage() {
			return $this->_value;
		}
		
		public function jsonSerialize() {
			return $this->_value;
		}
	}