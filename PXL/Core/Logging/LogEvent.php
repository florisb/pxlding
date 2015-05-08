<?php
	namespace PXL\Core\Logging;

	class LogEvent {
		protected $message;
		protected $plainmessage;
		protected $level;
		protected $timestamp;

		public function __construct() {
			$this->timestamp = floor(microtime(true));
		}

		public function __set($var, $val) {
			if (property_exists($this, $var)) $this->$var = $val;
		}

		public function __get($var) {
			if ($var == 'level') return $this->getLevel();
			if (property_exists($this, $var)) return $this->$var;
			return null;
		}

		public function getLevel() {
			switch ((int) $this->level) {
				case E_ERROR: return 'E_ERROR';
				case E_WARNING: return 'E_WARNING';
				case E_PARSE: return 'E_PARSE';
				case E_NOTICE: return 'E_NOTICE';
				case E_CORE_ERROR: return 'E_CORE_ERROR';
				case E_CORE_WARNING: return 'E_CORE_WARNING';
				case E_COMPILE_ERROR: return 'E_COMPILE_ERROR';
				case E_COMPILE_WARNING: return 'E_COMPILE_WARNING';
				case E_USER_ERROR: return 'E_USER_ERROR';
				case E_USER_WARNING: return 'E_USER_WARNING';
				case E_USER_NOTICE: return 'E_USER_NOTICE';
				case E_STRICT: return 'E_STRICT';
				case E_RECOVERABLE_ERROR: return 'E_RECOVERABLE_ERROR';
				case E_DEPRECATED: return 'E_DEPRECATED';
				case E_USER_DEPRECATED: return 'E_USER_DEPRECATED';
				case E_ALL: return 'E_ALL';
				default: return 'UNKNOWN ERROR LEVEL';
			}
		}
	}