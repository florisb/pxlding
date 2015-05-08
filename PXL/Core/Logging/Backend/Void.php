<?php
	namespace PXL\Core\Logging\Backend;
	use PXL\Core\Logging as L;

	class Void implements L\Backend {
		public function handle(L\LogEvent $message) {
			//do nothing, discard all messages;
		}
	}