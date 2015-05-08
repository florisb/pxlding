<?php
	namespace PXL\Core\Model\Value\Numeric;

	use RangeException;
	use PXL\Core\Model\Value;
	use UnexpectedValueException;

	class Integer extends Value\AbstractValue {

		protected $_min = null;
		protected $_max = null;

		public function __construct($value) {
			if (!is_null($this->_min) && !is_integer($this->_min)) {
				throw new UnexpectedValueException('Unexpected type for Value\Numeric\Integer::$_min, integer expected');
			}

			if (!is_null($this->_max) && !is_integer($this->_max)) {
				throw new UnexpectedValueException('Unexpected type for Value\Numeric\Integer::$_max, integer expected');
			}

			return parent::__construct($value);
		}

		protected function _checkValue($value) {
			if (!is_numeric($value)) {
				return false;
			} else {
				$value = (int) $value;
			}

			if (!is_null($this->_min) && $value < $this->_min) {
				return false;
			} elseif (!is_null($this->_max) && $value > $this->_max) {
				return false;
			} elseif (!is_null($this->_min) && !is_null($this->_max) && (($this->_max - $this->_min) <= 0)) {
				throw new RangeException("Invalid integer checking range [{$this->_min}, {$this->_max}]");
			} else {
				return true;
			}
		}

		protected function _formatValue($value) {
			return (int) $value;
		}

		public function jsonSerialize() {
			return $this->_value;
		}
	}