<?php
	namespace PXL\Core\Model\Value;
	
	class MD5 extends AbstractValue {

		protected function _checkValue($value) {
			return preg_match('#^[0-9a-f]{32}$#i', $value);
		}
		
		protected function _formatValue($value) {
			return strtolower($value);
		}
	}