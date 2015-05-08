<?php
	namespace PXL\Core\Tools;

	use PXL\Core\Config;

	abstract class Logger {

		protected static $monolog   = null;
		protected static $debugLogs = array();

		public static function log($message, $mailRecipient = null) {
			if ($message instanceof \Exception) {
				$message = "{$message->getMessage()} $message";
			}
			
			// Log error
			error_log($message);

			if (!is_null($mailRecipient)) {
				$body = <<<EOF
Hello,

An exception was thrown:

----------------------------------------
$message
----------------------------------------

This exception has also been logged to the CMS logs.


Regards,

The automatic Hornet error logger.
EOF;

				mail($mailRecipient, '[HornetLog] ' . ($_SERVER['SERVER_NAME'] ?: gethostname()), $body);
			}
		}

		public static function debug($message) {
			if (APPLICATION_ENV !== 'development') {
				return;
			}

			if (!class_exists('\Monolog\Logger')) {
				throw new \Exception('Cannot find Monolog package.. Please run composer and install Monolog.');
			}

			if (is_null(self::$monolog)) {
				self::$monolog = new \Monolog\Logger('PXL.Debug');
				self::$monolog->pushHandler(new \Monolog\Handler\FirePHPHandler());
				self::$monolog->pushHandler(new \Monolog\Handler\ChromePHPHandler());
			}

			self::$monolog->addDebug($message);
		}
	}