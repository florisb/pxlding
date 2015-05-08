<?php
	namespace PXL\Core\Model\Value;

	use BadMethodCallException;
	
	/**
	 * Image class.
	 * 
	 * Offers the programmer a abstraction layer for
	 * handling images in a uniform and sensible way.
	 *
	 * @extends AbstractValue
	 * @author  Max van der Stam <max@pixelindustries.com>
	 */
	class Image extends File {
		
		protected $_validMimetypes = array(
			'image/png'  => 'png',
			'image/gif'  => 'gif',
			'image/jpg'  => 'jpg',
			'image/jpeg' => 'jpeg'
		);
	}