<?php
	namespace PXL\Core\Model\Value;
	
	/**
	 * Text class.
	 * 
	 * @extends AbstractValue
	 * @author  Max van der Stam <max@pixelindustries.com>
	 */
	class Text extends AbstractValue {
		
		/**
		 * _maximumLength
		 * 
		 * If not null, this Value object will throw an
		 * ValueInvalidException when the string length of
		 * the value exceeds the value of $_maximumLength.
		 *
		 * By default, strings are considered valid when their length
		 * is less than or equal to a MySQL TEXT field (=65535 bytes).
		 *
		 * Please note that this number indicates a byte-length
		 * rather then a character length. This is especially
		 * important when dealing with multibyte strings.
		 *
		 * (default value: 65535)
		 * 
		 * @var int
		 * @access protected
		 */
		protected $_maximumLength = 65535;
		
		protected function _checkValue($value) {
			if (!is_null($this->_maximumLength)) {
				return strlen($value) <= (int) $this->_maximumLength;
			} else {
				return true;
			}
		}
		
		protected function _formatValue($value) {
			return preg_replace('#[[:blank:]]+#m ', ' ', strip_tags($value));
		}
	}