<?php
	namespace PXL\Core\Model\Value;
	
	class TextHTML extends Text {
		protected function _formatValue($value) {
			return preg_replace('#[[:blank:]]+#m ', ' ', $value);
		}
	}