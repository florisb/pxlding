<?php
	namespace PXL\Core\Logging;
	use PXL\Core\Collection as C;

	class Logger implements iLogger {
		protected $backends;

		public function registerBackend(Backend $lb, $level = E_ALL) {
			if (!is_array(self::$backends)) self::$backends = array();
			if (!array_key_exists($level, self::$backends)) self::$backends[$level] = new C\SimpleSet();
			$this->backends[$level]->add($lb);
		}

		public function log($message, $level = E_USER_NOTICE) {
			$le = $this->createEvent($message);
			foreach (self::$backends as $lvl => $lbs) {
				if (($lvl & $level) == $level) {
					foreach ($lbs as $lb) {
						$lb->handle($le);
					}
				}
			}
		}

		protected function createEvent($message, $level) {
			$le = new LogEvent();
			$le->plainmessage = $message;
			$le->message = $message;
			$le->level = $level;
			return $le;
		}
	}