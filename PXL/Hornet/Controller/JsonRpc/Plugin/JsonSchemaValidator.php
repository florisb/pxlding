<?php
	namespace PXL\Hornet\Controller\JsonRpc\Plugin;

	use PXL\Hornet\Request;
	use PXL\Hornet\Application\Application;
	use PXL\Hornet\Controller\Plugin\AbstractPlugin;

	use PXL\Hornet\Controller\JsonRpc\Controller as JsonRpcController;

	class JsonSchemaValidator extends AbstractPlugin {

		public function preDispatch(Application $application) {
			$request = $application->getRequest();

			if (!$request instanceof Request\JSONRequest) {
				throw new \RuntimeException('Invalid request');
			}

			$controller = $request->getControllerName();
			$action     = $request->getActionName();
			$schemaPath = APPLICATION_PATH . path('App/JsonRpc/Schemas/' . $controller . '/');
			$schemaFile = $schemaPath . $action . '.json';

			if (!class_exists('\JsonSchema\Validator')) {
				throw new \RuntimeException('Missing JsonSchema package');
			}

			if (!is_dir($schemaPath) && !@mkdir($schemaPath, 0777, true)) {
				throw new \RuntimeException('Unable to create schema directory');
			}

			if (!is_file($schemaFile)) {
				if (APPLICATION_ENV === 'development') {
					$schema = array(
						'$schema'    => 'http://json-schema.org/draft-04/schema#',
						'title'      => "JSON Schema for $controller/$action",
						'type'       => 'object',
						'properties' => []
					);

					file_put_contents($schemaFile, json_encode($schema, JSON_PRETTY_PRINT | JSON_FORCE_OBJECT | JSON_UNESCAPED_SLASHES));
				}
			}

			// Fetch validators
			$retriever = new \JsonSchema\Uri\UriRetriever;
			$validator = new \JsonSchema\Validator();
			$schema    = $retriever->retrieve('file://' . $schemaFile);
			$params    = json_decode(json_encode($request->getParams())) ?: (object) null;

			// Check! :D
			$validator->check($params, $schema);

			if (!$validator->isValid()) {
				$errors = array();

				foreach($validator->getErrors() as $error) {
					if (!$error['property']) {
						$errors[] = $error['message'];
					} else {
						$errors[] = sprintf('[%s] %s', $error['property'], $error['message']);
					}
				}

				JsonRpcController::_error(-32602, 'Invalid request', $errors);
			}
		}
	}