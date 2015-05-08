<?php
	namespace PXL\Hornet;

	// Set correct internal encoding
	mb_internal_encoding('UTF-8');

	// Include library -- PXL library is now available!
	include('lib' . DIRECTORY_SEPARATOR . 'library.php');
	include('lib' . DIRECTORY_SEPARATOR . 'password.php');

	// Include autoloader -- full framework is now available!
	include(path('lib/autoloader.php'));
	
	// Define application environment
	if (!defined('APPLICATION_ENV')) {

		if (gethostname() === 'homestead') {
			putenv('APPLICATION_ENV=development');
		}

		define('APPLICATION_ENV', getenv('APPLICATION_ENV') ?: 'production');
	}
	
	// Set correct timezone
	ini_set('date.timezone', 'Europe/Amsterdam');
	date_default_timezone_set('Europe/Amsterdam');
	
	// Define important constants
	if (!defined('APPLICATION_PATH')) {
		define('APPLICATION_PATH', realpath(path(__DIR__ . '/../../')) . DIRECTORY_SEPARATOR);
	}

	// Include vendor autoloader if necessary
	$vendorAutoloader = APPLICATION_PATH . path('vendor/autoload.php');
	if (is_file($vendorAutoloader)) {
		include($vendorAutoloader);
	}
	
	// Backward-compatiblity with applications using pxlcms_v5_client
	$pxlcmsPath = path(APPLICATION_PATH . 'pxlcms_v5_client/');
	if (is_dir($pxlcmsPath)) {
		include path($pxlcmsPath.'config/config.php');
		include path($pxlcmsPath.'includes/read_config.php');

		// Define logs path
		$logsPath = path(APPLICATION_PATH.'pxlcms_v5_client/logs/');
		
		if (!is_dir($logsPath)) {
			if (@mkdir($logsPath, 0777, true)) {
				ini_set('error_log', $logsPath.'errors_'.date('Ymd').'.log');
			}
		} elseif (is_writable($logsPath)) {
			ini_set('error_log', $logsPath.'errors_'.date('Ymd').'.log');
		}
	}

	// Show all errors except for E_STRICT and E_NOTICE
	error_reporting(E_ALL & (~E_STRICT & ~E_NOTICE & ~E_DEPRECATED));
	ini_set('error_prepend_string', null);
	ini_set('error_append_string',  null);

	// Determine error reporting based on environment
	if (in_array(APPLICATION_ENV, array('development', 'staging'))) {
		ini_set('display_errors', '1');
		ini_set('html_errors', '1');
		ini_set('log_errors', '0');
	} else {
		ini_set('display_errors', '0');
		ini_set('html_errors', '0');
		ini_set('log_errors', '1');
	}
	
	// Set correct include paths
	set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . PATH_SEPARATOR . APPLICATION_PATH);

	// Make sure exceptions nicely logged that aren't caught on production servers
	if (APPLICATION_ENV === 'production') {
		set_exception_handler('\PXL\Core\Tools\Logger::log');
	}

	if (getenv('SERVER_TYPE') == 'JSONRPC') {
		ini_set('html_errors', '0');

		$request = new Request\JSONRequest();
		// Get application instance and create a new request object instance
		$application = Application\Application::getInstance($request);
		
		//Run initialization code and dispatch request
		try {
			set_error_handler(function($errno, $errstr, $errfile, $errline) {
				throw new \Exception($errstr . ' in ' . $errfile . ' line: ' . $errline);
			},  E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);
		
			$application->initConfig()
						->initDb()
						->initView()
						->initIncludes()
						->initControllerPlugins()
						->dispatch();
									
			restore_error_handler();
		} catch(\Exception $e) { // Make sure all exceptions are caught
		
			header('Content-type: application/json');
			$errorResult          = (object) null;
			$errorResult->error   = (object) null;
			$errorResult->jsonrpc = '2.0';
			$errorResult->id      = $request->getRequestId();

			switch(true) {
				case ($e instanceof \PXL\Core\Exception\ClassNotFoundException):
				case ($e instanceof \PXL\Core\Exception\ActionNotFoundException):
				case ($e instanceof \PXL\Core\Exception\ControllerEmptyException):
					$errorResult->error->code    = -32601;
					$errorResult->error->message = 'Method not found';
					break;
					
				case ($e instanceof \PXL\Core\Exception\ValueInvalidException):
				case ($e instanceof \PXL\Core\Exception\ValueEmptyException):
				case ($e instanceof \PXL\Core\Exception\DataErrorsException):
					$errorResult->error->code    = -32602;
					$errorResult->error->message = 'Invalid params';
					break;
					
				case ($e instanceof \PXL\Core\Exception\JsonRpcErrorException):
					$errorResult->error->code    = $e->getCode();
					$errorResult->error->message = $e->getMessage();
					break;
					
				default:
					$errorResult->error->code    = $e->getCode()    ?: -32603;
					$errorResult->error->message = $e->getMessage() ?: 'Internal server error';
					break;
			}
			
			if (APPLICATION_ENV === 'development') {
				$errorResult->error->message = $e->getMessage();
				
				switch(true) {
					case ($e instanceof \PXL\Core\Exception\DataErrorsException):
						$errorResult->error->message = 'Data validation error(s)';
						$errorResult->error->data    = $e->getErrors();
						break;
					
					case ($e instanceof \PXL\Core\Exception\JsonRpcErrorException):
						$errorResult->error->data = $e->getData();
						break;
						
					default:
						$errorResult->error->data = $e;
						break;
				}
			}
			
			exit(json_encode($errorResult));
		}
	} else {
		set_error_handler(function($errno, $errstr, $errfile, $errline) {
				throw new \Exception($errstr . ' in ' . $errfile . ' line: ' . $errline);
		},  E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED);

		$request = new Request\Request();
		// Get application instance and create a new request object instance
		$application = Application\Application::getInstance($request);
		
		//Run initialization code and dispatch request
		$application->initConfig()
								->initDb()
								->initView()
								->initRoutes()
								->initIncludes()
								->initControllerPlugins()
								->dispatch();
	}
