<?php
	namespace PXL\Core\Logging\Backend;
	use PXL\Core\Logging as L;

	class Display implements L\Backend {
		protected $stack;

		public function __construct() {
			$stack = array();
		}

		public function handle(L\LogEvent $message) {
			$this->stack[] = <<<HTML
	<div class="logmessage">
		<div class="datetime">
			{date('d-m-Y', $message->timestamp)}<br />
			{date('H:i:s', $message->timestamp)}
		</div>
		<div class="message">
			{$message->message}
		</div>
		<div class="clear"></div>
		<div class="meta">
			Message logged with level "{$message->level}"
		</div>
	</div>
HTML;
		}

		public function __destruct() {
			if (count($this->stack)) {
				echo <<<HTML
<style>
	#pxl-core-logmessages {
		width: 100%;
	}
	#pxl-core-logmessages .logmessage {
		width: 100%;
		padding-bottom: 15px;
		margin-bottom: 15px;
		border-bottom: 1px solid blue;
	}
	#pxl-core-logmessages .logmessage .datetime {
		width: 20%;
		float: left;
	}
	#pxl-core-logmessages .logmessage .message {
		width: 80%;
		float: right;
	}
	#pxl-core-logmessages .logmessage .clear {
		clear: both;
	}
	#pxl-core-logmessages .logmessage .meta {
		width: 100%;
		font-size: 90%;
	}
</style>
<div id="pxl-core-logmessages">
	{implode('', $this->stack)}
</div>
HTML;
			}
		}
	}