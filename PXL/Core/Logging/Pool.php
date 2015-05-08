<?php
	namespace PXL\Core\Logging;
	use PXL\Core\Collection as C;

	abstract class Pool {
		protected static $loggers;

		static public function log($message, $level = E_USER_NOTICE) {
			foreach ($loggers as $lvl => $loggers) {
				if (((int)$lvl & (int)$level) == $level) {
					foreach ($loggers as $logger) {
						$logger->log($message);
					}
				}
			}
		}

		static public function registerLogger(iLogger $l, $level = E_ALL) {
			if (!is_array(self::$loggers)) self::$loggers = array();
			if (!array_key_exists((int)$level, self::$loggers)) self::$loggers[(int)$level] = new C\SimpleSet();
			self::$loggers[(int)$level]->add($l);
		}
	}