<?php
	namespace PXL\Core\Model\Value;
	
	class Zipcode extends AbstractValue {
		
		protected function _checkValue($zipCode){
			return preg_match('/^(\d{4} ?[A-z]{2})$/', $zipCode);
		}

		protected function _formatValue($zipCode){
			return str_replace(' ','',$zipCode);
		}
		
		public function __toString() {
			preg_match('/^(\d{4}) ?([A-z]{2})$/', $this->_value, $matches);
			
			return $matches[1] . ' ' . $matches[2];
		}
	}