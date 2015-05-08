<?php

namespace PXL\Core\Collection;

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, array('..', '..', 'JsonSerializable.php')));
require_once('Map.php');
require_once('SimpleMapEntry.php');
require_once('SimpleSet.php');
require_once('MapIterator.php');

use JsonSerializable;

class SimpleMap implements Map, JsonSerializable {
	protected $entries;
	protected $keySet;
	protected $values;

	public function __construct(Map $m = null) {
		if ($m instanceof Map) {
			$this->entries = clone $m->entrySet();
		} else {
			$this->entries = new SimpleSet();
		}
	}

	public function count() {
		return $this->entrySet()->count();
	}

	public function isEmpty() {
		return $this->count() == 0;
	}
	public function getIterator() {
		return new MapIterator($this);
	}

	public function containsValue($v) {
		foreach ($this->entrySet() as $entry) {
			if ($entry->getValue() === $v) return true;
		}
		return false;
	}

	public function containsKey($k) {
		foreach ($this->entrySet() as $entry) {
			if ($entry->getKey() === $k) return true;
		}
		return false;
	}

	public function get($k) {
		foreach ($this->entrySet() as $entry) {
			if ($entry->getKey() === $k) return $entry->getValue();
		}
		return null;
	}

	public function put($k, $v) {
		$oldVal = null;
		
		foreach ($this->entrySet() as $entry) {
			if ($entry->getKey() === $k) $oldVal = $entry->setValue($v);
		}
		if (!is_null($this->keySet)) $this->keySet->add($k);
		if (!is_null($this->values)) {
			if (!is_null($oldVal)) $this->values->remove($oldVal);
			$this->values->add($v);
		}
		return ( !is_null($oldVal) ? $oldVal : $this->entrySet()->add(new SimpleMapEntry($k, $v)) );
	}

	public function remove($k) {
		foreach ($this->entrySet() as $entry) {
			if ($entry->getKey() === $k) {
				$this->entrySet()->remove($entry);
				if (!is_null($this->keySet)) $this->keySet->remove($entry->getKey());
				if (!is_null($this->values)) $this->values->remove($entry->getValue());
				return $entry->getValue();
			}
		}
		return null;
	}

	public function putAll(Map $m) {
		foreach ($m->entrySet() as $entry) {
			$this->put($entry->getKey(), $entry->getValue());
		}
	}

	public function clear() {
		$this->entrySet()->clear();
		if (!is_null($this->keySet)) $this->keySet->clear();
		if (!is_null($this->values)) $this->values->clear();
	}

	public function keySet() {
		if (is_null($this->keySet)) {
			$this->keySet = new SimpleSet();
			foreach ($this->entrySet() as $entry) {
				$this->keySet->add($entry->getKey());
			}
		}
		return $this->keySet;
	}

	public function values() {
		if (is_null($this->values)) {
			$this->values = new SimpleCollection();
			foreach ($this->entrySet() as $entry) {
				$this->values->add($entry->getValue());
			}
		}
		return $this->values;
	}

	public function entrySet() {
		return $this->entries;
	}

	public function __toString() {
		$entrystrings = array();
		foreach ($this->entrySet() as $entry) {
			$entrystrings[] = $entry->__toString();
		}
		return '{'.implode(', ', $entrystrings).'}';
	}

	public function __clone() {
		$this->entries = clone $this->entries;
		if (!is_null($this->keySet)) $this->keySet = clone $this->keySet;
		if (!is_null($this->values)) $this->values = clone $this->values;
	}

	public function toArray() {
		return $this->entries->toArray();
	}
	
	
	/**
	 * toAssocArray function.
	 * 
	 * Actually returns a associative array consisting of key/values.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 * @access public
	 * @return array
	 */
	public function toAssocArray() {
		$result = array();
		foreach ($this->toArray() as $entry) {
			$result[$entry->getKey()] = $entry->getValue();
		}
		
		return $result;
	}

	public function fromArray(array $a, $replace = true) {
		if ($replace) {
			$this->clear();
		}
		
		foreach ($a as $k => $v) {
			$this->put($k, $v);
		}
		
		return $this;
	}

	public function JsonSerialize() {
		return $this->toAssocArray();
	}

	public function serialize() {
		return serialize($this->entries);
	}

	public function unserialize($serialized) {
		$this->entries = unserialize($serialized);
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