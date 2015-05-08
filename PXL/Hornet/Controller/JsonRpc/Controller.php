<?php
	namespace PXL\Hornet\Controller\JsonRpc;

	use PXL\Hornet\View;
	use PXL\Core\Exception;
	use PXL\Core\Collection;
	use PXL\Hornet\Controller\Controller as C;
	
	abstract class Controller extends C {
		
		const JSONRPC_RESULT_KEY = '__JSONRPC_RESULT__';
		
		public static $results = array();
		
		protected function _error($code, $message, $data = null) {
			throw new Exception\JsonRpcErrorException($message, $code, $data);
		}
		
		protected function set($key, $value, $availableGlobally = false) {
			self::$results[$key] = $value;
			
			return $this;
		}
		
		
		/**
		 * setResult function.
		 * 
		 * Sets top-level data that will populate the result object sent
		 * back as a result of handling the JSON-RPC request. When this
		 * method is used, data stored using JsonRpc\Controller::set() calls
		 * will _not_ be sent back, as this method overrides those calls.
		 *
		 * @access protected
		 * @param mixed $value
		 * @return void
		 */
		protected function setResult($value) {
			self::$results[self::JSONRPC_RESULT_KEY] = $value;
			
			return $this;
		}
		
		protected function get($key) {
			if (array_key_exists($key, self::$results)) {
				return self::$results[$key];
			} else {
				return null;
			}
		}
	}