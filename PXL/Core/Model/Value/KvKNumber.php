<?php
	namespace PXL\Core\Model\Value;
	
	class KvKNumber extends AbstractValue {
		
		protected function _checkValue($kvkNumber) {
			return preg_match('#^\d{8}$#', $kvkNumber);
		}
	}