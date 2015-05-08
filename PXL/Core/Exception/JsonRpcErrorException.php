<?php
	namespace PXL\Core\Exception;
	
	class JsonRpcErrorException extends \Exception {
		
		protected $_data = null;
		
		public function __construct($message, $code, $data = null) {
			$this->_data = $data;

			return parent::__construct($message, $code);
		}
		
		public function getData() {
			return $this->_data;
		}
	}