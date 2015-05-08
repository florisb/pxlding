<?php
	namespace PXL\Core\Logging;

	interface iLogger {
		public function log($message, $level = E_USER_NOTICE);
		public function registerBackend(Backend $lb, $level = E_ALL);
	}