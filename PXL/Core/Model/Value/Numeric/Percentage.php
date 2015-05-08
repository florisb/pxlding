<?php
	namespace PXL\Core\Model\Value\Numeric;

	use PXL\Core\Model\Value;

	class Percentage extends Value\AbstractValue {

		protected function _checkValue($value) {
			return preg_match('/\-?\d+((,|\.)\d+)?/', preg_replace('#[[:blank:]]+#m ', '', (string) $value));
		}

		protected function _formatValue($value) {
			return (float) str_replace(',', '.', $value);
		}

		public function __toString() {
			return number_format($this->_value, 2, ',', '.') . '%';
		}

		public function rounded($decimals = 0) {
			return round($this->_value, $decimals) . '%';
		}
	}