<?php
	namespace PXL\Hornet\Request;

	require_once('Request.php');

	class JSONRequest extends Request {
		protected $requestBody;

		public function __construct() {
			parent::__construct();
			$this->requestBody = @file_get_contents('php://input');
			$this->parseRequestBody();
		}

		public function getRequestBody() {
			return $this->requestBody;
		}

		protected function parseRequestBody() {
			if (!strlen($this->requestBody)) $this->error('-32600', 'Empty requestbody found');

			$this->requestBody = json_decode($this->requestBody);

			if (!is_object($this->requestBody)) $this->error('-32600', 'Unable to parse requestbody');

			if (!$this->requestBody) $this->error('-32700');

			if (!property_exists($this->requestBody, 'jsonrpc')) $this->error('-32600', 'Missing property jsonrpc');
			//test version
			if ($this->requestBody->jsonrpc != '2.0') $this->error('-32600', 'Invalid jsonrpc version found');

			//test method
			if (!property_exists($this->requestBody, 'method')) $this->error('-32600', 'Missing property method');
			$m = $this->requestBody->method;
			if (!is_string($m)) $this->error('-32601', 'No valid method found. Please provide the method as a string.');
			if (strpos($m, '.') === false && strpos($m, '/') === false) $this->error('-32601', 'Method not found: Please use . (dot) or / (slash) as separator');
			if (strpos($m, '.') !== false) {
				$this->requestBody->method = str_replace('.', '/', $m); //normalize dot to slash if needed
			}
			$urlparts = explode('/', $this->requestBody->method);
			if (count($urlparts) != 2) $this->error('-32601');
			$this->setControllerName($urlparts[0]);
			$this->setActionName($urlparts[1]);

			$params = array();
			if (!property_exists($this->requestBody, 'params')) $this->error('-32600', 'Missing property params');
			if ($this->requestBody->params) {
				if (is_string($this->requestBody->params)) $this->requestBody->params = json_decode($this->requestBody->params);
				if (is_object($this->requestBody->params)) {
					foreach ($this->requestBody->params as $k => $v) {
						$params[$k] = $v;
					}
				} elseif (is_array($this->requestBody->params)) {
					$params = $this->requestBody->params;
				} else {
					$params = array($this->requestBody->params);
				}
			}
			$this->setParams($params);

			//parse verb
			if (property_exists($this->requestBody, 'verb') && strlen($this->requestBody->verb)) $this->_server['REQUEST_METHOD'] = strtoupper($this->requestBody->verb);
		}

		public function getRequestId() {
			if ($this->requestBody && $this->requestBody->id) return $this->requestBody->id;
			return null;
		}

		public function isNotification() {
			return is_null($this->getRequestId());
		}

		public function error($code, $msg = null) {
			header('Content-Type: application/json');
			$errors = array(
				'-32700'	=> 'Parse error',
				'-32600'	=> 'Invalid Request',
				'-32601'	=> 'Method not found',
				'-32602'	=> 'Invalid params',
				'-32603'	=> 'Internal error',
			);
			if (is_null($msg) && array_key_exists($code, $errors)) $msg = $errors[$code];
			$id = $this->getRequestId();
			die('{"jsonrpc": "2.0", "error": {"code": "'.$code.'", "message": "'.$msg.'"}, "id": '.(is_null($id) ? 'null' : '"'.$id.'"').'}');
		}

		public function result($result) {
			//header('Content-Type: application/json');
			$id = $this->getRequestId();
			die('{"jsonrpc": "2.0", "result": '.json_encode($result).', "id": '.(is_null($id) ? 'null' : '"'.$id.'"').'}');
		}
	}