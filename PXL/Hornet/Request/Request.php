<?php
	namespace PXL\Hornet\Request;

	require_once('iRequest.php');

	class Request implements iRequest {
		
		protected $_controllerName = null;
		protected $_actionName     = null;
		protected $_baseUrl        = null;
		protected $_params         = array();
		protected $_dispatched     = false;
		
		protected $_userAgent  = null;
		protected $_requestUri = null;
		protected $_server     = array();
		
		const USER_AGENT_IE7  = 1;
		const USER_AGENT_IE8  = 2;
		const USER_AGENT_IE9  = 4;
		const USER_AGENT_IE10 = 8;
		
		public function __construct() {
			// Copy server superglobal to protected member
			$this->_server = $_SERVER;

			// Determine base url and request URI
			$this->_determineBaseUrl();
			
			// Determine user agent
			$this->_determineUserAgent();
		}
		
		public function isGet() {
			return ($this->_server['REQUEST_METHOD'] === 'GET');
		}
		
		public function isPost() {
			return ($this->_server['REQUEST_METHOD'] === 'POST');
		}
		
		public function isPut() {
			return ($this->_server['REQUEST_METHOD'] === 'PUT');
		}
		
		public function isDelete() {
			return ($this->_server['REQUEST_METHOD'] === 'DELETE');
		}
		
		public function isHead() {
			return ($this->_server['REQUEST_METHOD'] === 'HEAD');
		}
		
		public function isOptions() {
			return ($this->_server['REQUEST_METHOD'] === 'OPTIONS');
		}
		
		public function isXhr() {
			return (!empty($this->_server['HTTP_X_REQUESTED_WITH']) && strtolower($this->_server['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
		}
		
		public function isAsyncNav() {
			return array_key_exists('HTTP_PXL_ASYNC_NAV', $this->_server);
		}
		
		public function isJsonRpc() {
			return (array_key_exists('CONTENT_TYPE', $this->_server) && $this->_server['CONTENT_TYPE'] === 'application/json-rpc');
		}
		
		public function isSecure() {
			return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === "on") ?: (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' || !empty($_SERVER['HTTP_X_FORWARDED_SSL']) && $_SERVER['HTTP_X_FORWARDED_SSL'] == 'on');
		}
		
		public function getRequestUri() {
			return $this->_requestUri;
		}
		
		public function getFullRequestURL() {
			return $this->_baseUrl . $this->_requestUri;
		}
		
		public function getControllerName() {
			return $this->_controllerName;
		}
		
		public function setControllerName($value) {
			$this->_controllerName = $value;
			
			return $this;
		}
		
		public function getActionName() {
			return $this->_actionName;
		}
		
		public function setActionName($value) {
			$this->_actionName = $value;
			
			return $this;
		}
		
		public function getParam($key) {
			return $this->hasParam($key) ? $this->_params[$key] : null;
		}
		
		public function hasParam($key) {
			return array_key_exists($key, $this->_params);
		}
		
		public function getParams($enumerated = false) {
			ksort($this->_params);
			return $enumerated ? array_values($this->_params) : $this->_params;
		}
		
		public function getUnnamedParams() {
			ksort($this->_params);
			$params = array();
			foreach($this->_params as $k => $v) {
				if (is_numeric($k)) {
					$params[] = $v;
				}
			}
			
			return $params;
		}
		
		public function setParam($value, $key = null) {
			if (is_null($key)) {
				if (!in_array($value, $this->_params)) {
					$this->_params[] = $value;
				}
			} else {
				$this->_params[$key] = $value;
			}
			
			return $this;
		}
		
		public function setParams(array $params = array()) {
			foreach($params as $k => $v) {
				$this->setParam($v, $k);
			}
			
			return $this;
		}
		
		public function setDispatched($dispatched = true) {
			$this->_dispatched = $dispatched;
			
			return $this;
		}
		
		public function isDispatched() {
			return $this->_dispatched;
		}
		
		public function getBaseUrl() {
			return $this->_baseUrl;
		}
		
		public function setBaseUrl($value) {
			$this->_baseUrl = $value;
			
			return $this;
		}
		
		public function getServerName() {
			return $this->_server['SERVER_NAME'];
		}
		
		public function redirect($url, $http301 = false) {
			// Check if this is an absolute URL or a relative URL
			if (!preg_match('#^[a-z]+://#', $url)) {
				$url = $this->_baseUrl . ltrim($url, '/');
			}

			http_response_code($http301 ? 301 : 302);
			header("Location: $url");
			exit;
		}
		
		public function isIE7() {
			return ($this->_userAgent === self::USER_AGENT_IE7);
		}
		
		public function isIE8() {
			return ($this->_userAgent === self::USER_AGENT_IE8);
		}
		
		public function isIE9() {
			return ($this->_userAgent === self::USER_AGENT_IE9);
		}
		
		public function isIE10() {
			return ($this->_userAgent === self::USER_AGENT_IE10);
		}
		
		protected function _determineBaseUrl() {
			if (stripos($_SERVER['SERVER_SOFTWARE'], 'nginx') !== false) {
				$_SERVER['REQUEST_URI'] = str_replace('/webroot/', '', $_SERVER['REQUEST_URI']);
				$_SERVER['SCRIPT_NAME'] = str_replace('/webroot/', '', $_SERVER['SCRIPT_NAME']);
			}

			$serverProtocol   = $this->isSecure() ? 'https' : 'http';
			$serverHost       = $this->_server['HTTP_HOST'];
			$serverBase       = explode('/', trim($this->_server['SCRIPT_NAME'], '/'));
			
			// Pop array twice to get rid of webroot/index.php
			array_pop($serverBase);
			array_pop($serverBase);

			$serverRequestUri = explode('/', trim($this->_server['REQUEST_URI'], '/'));
			$serverRequestUri = array_diff($serverRequestUri, $serverBase);
			$serverBase       = implode('/', $serverBase);

			if ($serverBase) {
				$serverBase .= '/';
			}
			
			$this->_requestUri = implode('/', $serverRequestUri);
			$this->_requestUri = preg_replace('#\?(.*)$#', '', $this->_requestUri); // Remove query string from URI
			$this->_requestUri = urldecode($this->_requestUri);
			
			$this->_baseUrl    = "$serverProtocol://$serverHost/$serverBase";
		}
		
		protected function _determineUserAgent() {
			if (empty($_SERVER['HTTP_USER_AGENT'])) {
				return;
			}
			
			preg_match('/MSIE (.*?);/', $_SERVER['HTTP_USER_AGENT'], $matches);
			
			if (count($matches) < 2) {
				preg_match('/Trident\/\d{1,2}.\d{1,2}; rv:([0-9]*)/', $_SERVER['HTTP_USER_AGENT'], $matches);
			}

			if (count($matches) > 1) {
				//Then we're using IE
				$version = (int) $matches[1];

				switch (true) {
					case ($version === 7):
						$this->_userAgent = self::USER_AGENT_IE7;
						break;
				
					case ($version === 8):
						$this->_userAgent = self::USER_AGENT_IE8;
						break;
						
					case ($version === 9):
						$this->_userAgent = self::USER_AGENT_IE9;
						break;
						
					case ($version === 10):
						$this->_userAgent = self::USER_AGENT_IE10;
						break;
						
					default:
						// Unknown version
						break;
				}
			}
		}
	}