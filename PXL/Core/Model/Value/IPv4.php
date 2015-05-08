<?php
	namespace PXL\Core\Model\Value;
	
	/**
	 * IPv4 class.
	 * 
	 * Represents a IPv4 address. The IPv4 address is internally stored
	 * as an unsigned integer and uses the ip2long() and
	 * long2ip() functions to translate stored integers to valid IP-addresses
	 * and back.
	 *
	 * @extends AbstractValue
	 * @author  Max van der Stam <max@pixelindustries.com>
	 */
	class IPv4 extends AbstractValue {
		
		protected function _checkValue($value) {
			return (filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) || long2ip($value));
		}
		
		protected function _formatValue($value) {
			return filter_var($value, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) ? sprintf('%u', ip2long($value)) : $value;
		}
		
		public function __toString() {
			return long2ip($this->value);
		}
	}