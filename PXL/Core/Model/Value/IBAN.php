<?php
	namespace PXL\Core\Model\Value;
	
	class IBAN extends AbstractValue {
		
		protected function _checkValue($value) {
			return preg_match('#^[a-zA-Z]{2}\s*[0-9]{2}\s*[a-zA-Z0-9]{4}\s*[0-9]{7}\s*([a-zA-Z0-9]?){0,16}$#', $value);
		}
		
		protected function _formatValue($value) {
			return str_replace(' ', '', $value);
		}
		
		public function __toString() {
			preg_match('#^([a-zA-Z]{2})\s*([0-9]{2})\s*([a-zA-Z0-9]{4})\s*([0-9]{7})\s*([a-zA-Z0-9]?){0,16}$#', $this->value, $matches);
			
			array_shift($matches);
			
			return trim(implode(' ', $matches));
		}
	}