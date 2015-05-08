<?php
	namespace PXL\Core\Model\Value;

	class Email extends AbstractValue {

		protected function _checkValue($value){
			return (boolean) filter_var($value, FILTER_VALIDATE_EMAIL);
		}
	}