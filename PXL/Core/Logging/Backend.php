<?php
	namespace PXL\Core\Logging;

	interface Backend {
		public function handle(LogEvent $message);
	}