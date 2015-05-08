<?php
	namespace PXL\Core\Db;
	
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'Collection', 'SimpleMapEntry.php')));
	
	use PXL\Core\Collection as C;
	
	class DbResultMapEntry extends C\SimpleMapEntry {
		
		public function __construct($kme, $v = null) {
			if ($kme instanceof MapEntry) {
				$this->key = $kme->getKey();
				$this->value = $kme->getValue();
			} else {
				$this->key = $kme;
				$this->value = $v;
			}
		}
		
		public function setValue($v) {
			$o = $this->value;
			$this->value = $v;
			return $o;
		}
	}