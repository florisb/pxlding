<?php
	namespace PXL\Core\Model\Value;

	class Password extends AbstractValue {

		protected function _checkValue($value) {
			if (strlen($value) < 6) {
				return false;
			}

			return true;
		}

		protected function _formatValue($value) {
			return password_hash($value, PASSWORD_DEFAULT);
		}

		public function __toString() {
			return null;
		}

		public function jsonSerialize() {
			return null;
		}
	}