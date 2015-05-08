<?php
	namespace PXL\Core\Collection;

	require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, array('..', '..', 'JsonSerializable.php')));
	require_once('Map.php');
	require_once('SimpleMapEntry.php');
	require_once('SimpleSet.php');
	require_once('MapIterator.php');

	use JsonSerializable;

	class FastMap implements Map, JsonSerializable {

		protected $entries = array();
		protected $keySet  = array();
		protected $values  = array();

		public function __construct(Map $m = null) {
			if ($m instanceof Map) {
				$this->putAll($m);
			}
		}

		public function count() {
			return count($this->entries);
		}

		public function isEmpty() {
			return empty($this->entries);
		}

		public function getIterator() {
			return new MapIterator($this);
		}

		public function containsValue($v) {
			return in_array($v, $this->values);
		}

		public function containsKey($k) {
			return in_array($k, $this->keySet);
		}

		public function get($k) {
			if (!$this->containsKey($k)) {
				return null;
			}

			foreach($this->entries as $entry) {
				list($keyIdx, $valueIdx) = $entry;

				if ($this->keySet[$keyIdx] === $k) {
					return $this->values[$valueIdx];
				}
			}
		}

		public function put($k, $v) {
			$oldVal = null;

			if ($this->containsKey($k)) {
				foreach($this->entries as $entryIdx => $entry) {
					list($keyIdx, $valueIdx) = $entry;
					if ($k === $this->keySet[$keyIdx]) {
						$oldVal                  = $this->values[$valueIdx];
						$this->values[$valueIdx] = $v;

						unset($this->entries[$entryIdx]);
					}
				}
			} else {
				$this->keySet[] = $k;
				$this->values[] = $v;
			}

			$this->entries[] = array(array_search($k, $this->keySet, true), array_search($v, $this->values, true));

			return $oldVal ?: true;
		}

		public function __get($k) {
			return $this->get($k);
		}

		public function __set($k, $v) {
			return $this->put($k, $v);
		}

		public function remove($k) {
			if ($this->containsKey($k)) {
				foreach($this->entries as $entryIdx => $entry) {
					list($keyIdx, $valueIdx) = $entry;
					if ($k === $this->keySet[$keyIdx]) {
						$val = $this->values[$valueIdx];

						unset($this->keySet[$keyIdx]);
						unset($this->values[$valueIdx]);
						unset($this->entries[$entryIdx]);

						return $val;
					}
				}
			} else {
				return null;
			}
		}

		public function putAll(Map $m) {
			foreach ($m->entrySet() as $entry) {
				$this->put($entry->getKey(), $entry->getValue());
			}
		}

		public function clear() {
			$this->entries = array();
			$this->keySet  = array();
			$this->values  = array();
		}

		public function keySet() {
			return SimpleSet::createFromArray($this->keySet);
		}

		public function values() {
			return SimpleSet::createFromArray($this->values);
		}

		public function entrySet() {
			return SimpleSet::createFromArray($this->toArray());
		}

		public function toArray() {
			$result = array();

			foreach($this->entries as $entry) {
				list($keyIdx, $valueIdx) = $entry;
				$result[]                = new SimpleMapEntry($this->keySet[$keyIdx], $this->values[$valueIdx]);
			}

			return $result;
		}

		public function toAssocArray() {
			$result = array();
			foreach($this->entries as $entry) {
				list($keyIdx, $valueIdx)        = $entry;
				$result[$this->keySet[$keyIdx]] = $this->values[$valueIdx];
			}

			return $result;
		}

		public function fromArray(array $a, $replace = true) {
			if ($replace) {
				$this->clear();
			}

			$this->keySet  = array_keys($a);
			$this->values  = array_values($a);
			$size          = count($a);
			
			// Reset key and value arrays
			reset($this->keySet);
			reset($this->values);

			for($i = 0; $i < $size; $i++) {
				$this->entries[] = array(key($this->keySet), key($this->values));

				next($this->keySet);
				next($this->values);
			}

			return $this;
		}

		public function JsonSerialize() {
			return $this->toAssocArray();
		}

		public function serialize() {
			return serialize($this->toAssocArray());
		}

		public function unserialize($serialized) {
			$this->fromArray(unserialize($serialized));
		}

		public function immutable() {
			return new ImmutableMap($this);
		}

		public static function createFromArray(array $data = array()) {
			$ret = new static();

			$ret->fromArray($data);

			return $ret;
		}
	}