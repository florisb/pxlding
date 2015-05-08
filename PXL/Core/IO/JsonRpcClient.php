<?php
	namespace PXL\Core\IO;
	
	use PXL\Core\Exception;

	/**
	 * JsonRpcClient
	 *
	 * Simple to use client that follows the JSON-RPC 2.0
	 * working draft.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	class JsonRpcClient {
		
		protected $_url = null;
		protected $_ch  = null;
		protected $_id  = 1;
		
		protected $_debug   = false;
		protected $_verbose = null;
		
		protected $_debugInfo = array();
		
		public function __construct($url, $debug = false) {
			$this->_url   = $url;
			$this->_debug = (boolean) $debug;
			
			$this->_ch = curl_init($this->_url);
			curl_setopt($this->_ch, CURLOPT_RETURNTRANSFER, true);
			
			if ($this->_debug) {
				$this->_verbose = fopen('php://temp', 'rw+');
				curl_setopt($this->_ch, CURLOPT_STDERR, $this->_verbose);
				curl_setopt($this->_ch, CURLOPT_VERBOSE, true);
			}
			
			if (APPLICATION_ENV === 'development') {
				curl_setopt($this->_ch, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($this->_ch, CURLOPT_SSL_VERIFYPEER, false);
			}
		}

		public function __call($method, $params) {

			if (empty($params)) {
				$params = array(array());
			}

			return $this->request(strtolower(implode('.', preg_split('/(?=[A-Z])/', $method))), $params[0], 'POST');
		}

		protected function request($method, array $params, $httpMethod) {
			curl_setopt_array($this->_ch, array(
				CURLOPT_CUSTOMREQUEST => strtoupper($httpMethod),
				CURLOPT_POSTFIELDS    => $this->_buildJsonRpcRequest($method, $params)
			));
			
			$this->_id++;
			
			$response = curl_exec($this->_ch);
			
			if ($this->_debug) {
				$this->_storeDebugInfo();
				return $response;
			} else {
				$jsonresponse = @json_decode($response);
				if ($jsonresponse && property_exists($jsonresponse, 'error')) {
					throw new Exception\JsonRpcClientErrorException($jsonresponse->error->message . ($jsonresponse->error->data ? ' ' . json_encode($jsonresponse->error->data) : ''), $jsonresponse->error->code);
				} else if ($jsonresponse && property_exists($jsonresponse, 'result')) {
					return $jsonresponse->result;
				} else {
					throw new Exception\JsonRpcClientErrorException('Invalid response from JSONRPC: ' . $response);
				}
			}
		}
		
		public function getDebugInfo() {
			return $this->_debugInfo;
		}
		
		protected function _buildJsonRpcRequest($method, $params) {
			$request = array(
				'jsonrpc' => '2.0',
				'method'  => $method,
				'params'  => $params,
				'id'      => $this->_id
			);
			
			return json_encode($request, JSON_FORCE_OBJECT);
		}
		
		protected function _storeDebugInfo() {
			@rewind($this->_verbose);
			$this->_debugInfo[] = stream_get_contents($this->_verbose);
		}
	}