<?php
	namespace PXL\Core\Model\Value;
	
	class URL extends AbstractValue {
		
		protected function _checkValue($value) {
			return (boolean) filter_var('http://' . str_ireplace(array('http://', 'https://'), '', $value), FILTER_VALIDATE_URL);
		}
		
		protected function _formatValue($value) {
			return str_ireplace(array('http://', 'https://'), '', $value);
		}
	}