<?php
	namespace PXL\Core\Cache\Backend;
	
	require_once('iBackend.php');

	abstract class AbstractBackend implements iBackend {
		
		public function __construct(array $options  = array()) {
			$this->setOptions($options);
		}
	}