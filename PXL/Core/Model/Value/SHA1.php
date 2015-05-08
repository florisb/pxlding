<?php
	namespace PXL\Core\Model\Value;
	
	class SHA1 extends AbstractValue {

		protected function _checkValue($value) {
			return preg_match('#^[0-9a-f]{40}$#i', $value);
		}
		
		protected function _formatValue($value) {
			return strtolower($value);
		}
	}