<?php
	namespace PXL\Core\Model\Value;
	
	/**
	 * Rar class.
	 * 
	 * Value object representing a *valid* RAR archive.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 * @extends File
	 */
	class Rar extends File {
		
		protected $_validMimetypes = array(
			'application/x-rar-compressed' => 'rar',
			'application/octet-stream'     => 'rar',
			'application/x-rar'            => 'rar',
			'application/rar'              => 'rar'
		);
		
		const RAR_MAGIC_NUMBER = '526172211a0700';
		
		protected function _checkValue($value) {
			// Check for the RAR "magic number" to determine whether this is actually a valid RAR archive
			return (parent::_checkValue($value) && (bin2hex(file_get_contents($value, false, null, 0, 7)) == self::RAR_MAGIC_NUMBER));
		}
	}