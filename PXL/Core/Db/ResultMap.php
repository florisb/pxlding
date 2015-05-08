<?php
	namespace PXL\Core\Db;
	
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'Collection', 'FastMap.php')));
	require_once('ResultMapEntry.php');
	
	use PXL\Core\Collection;

	class ResultMap extends Collection\FastMap {

		public function toArray() {
			$result = array();

			foreach($this->entries as $entry) {
				list($keyIdx, $valueIdx) = $entry;
				$result[]                = new DbResultMapEntry($this->keySet[$keyIdx], $this->values[$valueIdx]);
			}

			return $result;
		}
	}