<?php
	namespace PXL\Core\Model\Value;
	
	class Phone extends AbstractValue {
		
		protected function _checkValue($value) {
			return preg_match('#^[0-9()\- ]+$#', $value);
		}
	}