<?php
	namespace PXL\Core\Model\Value;

	use PXL\Core\Tools;

	class Price extends AbstractValue {

		protected function _checkValue($value) {
			return preg_match('/\-?\d+((,|\.)\d+)?/', preg_replace('#[[:blank:]]+#m ', '', (string) $value));
		}

		protected function _formatValue($value){
			return round(((float) str_replace(",",".",$value)), 2);
		}

		public function __toString(){
			if ($this->_value < 0) {
				return '- € ' . number_format(abs($this->_value), '2', ',', '.');
			} else {
				return '€ '.number_format($this->_value,'2',',','.');
			}
		}
	
		public function roundedPrice() {
			if ($this->_value < 0) {
				return '- € ' . round(abs($this->_value), 0) . ',-';
			} else {
				return '€ ' . round($this->_value, 0) . ',-';
			}
		}
		
		public function toText($uppercase = false) {
			$text = Tools\Number::toText($this->_value, $uppercase) . ' Euro';
		
			$cents = round((round($this->_value, 2) - floor($this->_value)) * 100);
			if ($cents) {
				$text .= ' ' . 'and' . ' ' . Tools\Number::toText($cents, $uppercase) . ' ' . 'eurocents';
			}
		
			if ($uppercase) {
				$text = strtoupper($text);
			}
		
			return $text;
		}
}