<?php
	namespace PXL\Core\Logging\Frontend;
	use PXL\Core\Logging as L;

	class Benchmark implements L\iLogger {
		protected $backends;
		protected $marks;

		public function __construct() {
			$this->marks = array();
		}

		public function registerBackend(Backend $lb, $level = E_ALL) {
			if (!is_array(self::$backends)) self::$backends = array();
			if (!array_key_exists($level, self::$backends)) self::$backends[$level] = new C\SimpleSet();
			$this->backends[$level]->add($lb);
		}

		public function log($message, $level = E_USER_NOTICE) {
			$ts = microtime(true);
			$mem = memory_get_usage(true);
			$mem_peak = memory_get_peak_usage(true);
			$label = $message;
			if ($label == '') $label = count($this->marks);
			$this->marks[$label] = array(
				'timestamp'		=> $ts,
				'memory'		=> $mem,
				'memory_peak'	=> $mem_peak
			);
		}

		public function __destruct() {
			if (count($this->marks)) {
				$message = $this->createEvent();
				foreach ($this->backends as $lbs) foreach ($lbs as $lb) {
					$lb->handle($message);
				}
			}
		}

		protected function createEvent() {
			$le = new L\LogEvent();
			$message = '<table><thead><tr><th>Mark</th><th>Time</th><th>Elapsed Time</th><th>Memory</th><th>Memory (peak)</th></tr></thead><tbody>';
			$plainmessage = '';
			$lastmark = '';
			foreach ($this->marks as $m) {
				$plainmessage .= $m['label'].': '.$m['timestamp']."\n".'Memory: '.$this->readableBytes($m['memory'])."\n".'Memory (peak): '.$this->readableBytes($m['memory_peak'])."\n\n";
				$message .= <<<HTML
	<tr>
		<td>{$m['label']}</td>
		<td>{$m['timestamp']}</td>
		<td>{(strlen($lastmark) ? $this->determinePassedTime($m['label'], $lastmark) : '-')}</td>
		<td>{$this->readableBytes($m['memory'])}</td>
		<td>{$this->readableBytes($m['memory_peak'])}</td>
	</tr>
HTML;
				$lastmark = $m['label'];
			}
			$message .= '</tbody></table>';
			$le->message = $message;
			$le->plainmessage = $plainmessage;
			$le->level = E_USER_NOTICE;
			return $le;
		}

		protected function readableBytes($bytes) {
			$suffixes = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB'); //I don't know what comes after a yottabyte, and frankly, I don't care, since it's 1024^5 GiB. If your memory-usage is that big you have other problems to be concerned about :P
			$power = floor(log($bytes, 1024)); //use logarithm to determine what power of 1024 fits in the number of bytes
			$bytes = $bytes/pow(1024, $power); //normalize the number of bytes to use with the correct suffix

			return round($bytes, 2).' '.$suffixes[$power]; //string it all together :)
		}

		protected function determinePassedTime($label1, $label2) {
			return abs($this->marks[$label1]['timestamp'] - $this->marks[$label2]['timestamp']);
		}
	}