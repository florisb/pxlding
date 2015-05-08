<?php
	namespace PXL\Hornet\Controller\Plugin;
	
	use PXL\Hornet\View;
	use PXL\Hornet\Controller\JsonRpc;
	use PXL\Hornet\Application\Application;
	
	class JsonRpcResult extends AbstractPlugin {
		
		public function postDispatch(Application $application) {
			if (getenv('SERVER_TYPE') !== 'JSONRPC' || $application->getRequest()->isNotification()) {
				return;
			}
		
			$result = array();
			if (array_key_exists(JsonRpc\Controller::JSONRPC_RESULT_KEY, JsonRpc\Controller::$results)) {
				$result = JsonRpc\Controller::$results[JsonRpc\Controller::JSONRPC_RESULT_KEY];
			} else {
				foreach(JsonRpc\Controller::$results as $k => $v) {
					$result[$k] = $v;
				}
			}
			
			Application::getInstance()->getView()->set('jsonrpc', '2.0')
																					 ->set('result',  $result)
																					 ->set('id',      $application->getRequest()->getRequestId())
																					 ->template('json');
		}
	}