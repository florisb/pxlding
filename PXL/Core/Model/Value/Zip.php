<?php
	namespace PXL\Core\Model\Value;
	
	/**
	 * Zip class.
	 * 
	 * Value object representing a *valid* ZIP archive.
	 *
	 * @extends File
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	class Zip extends File {
		
		protected $_validMimetypes = array(
			'application/x-zip-compressed' => 'zip',
			'application/octet-stream'     => 'zip',
			'application/x-zip'            => 'zip',
			'application/zip'              => 'zip'
		);
		
		const ZIP_MAGIC_NUMBER = 'PK';
		
		protected function _checkValue($value) {
			// Check for the ZIP "magic number" to determine whether this is actually a valid ZIP archive
			return (parent::_checkValue($value) && (substr(file_get_contents($value, false, null, 0, 7), 0, 2) === self::ZIP_MAGIC_NUMBER));
		}
	}