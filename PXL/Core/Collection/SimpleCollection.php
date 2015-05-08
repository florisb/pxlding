<?php

namespace PXL\Core\Collection;

require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, array('..', '..', 'JsonSerializable.php')));
require_once('Collection.php');
require_once('CollectionIterator.php');

use JsonSerializable;

class SimpleCollection implements Collection, JsonSerializable {
protected $elements = array();

	public function __construct(Collection $c = null) {
		if ($c instanceof Collection) {
			foreach ($c as $e) {
				$this->elements[] = $e;
			}
		}
	}

	public function add($e) {
		$this->elements[] = $e;
		return true;
	}

	public function addAll(Collection $c) {
		$ret = false;
		foreach ($c->elements as $e) {
			$ret = $this->add($e) || $ret;
		}
		return $ret;
	}

	public function clear() {
		$this->elements = array();
	}

	public function contains($o) {
		return (in_array($o, $this->elements, true) === true);
	}

	public function containsAll(Collection $c) {
		foreach ($c as $e) if (!$this->contains($e)) return false;
		return true;
	}

	public function isEmpty() {
		return $this->count() == 0;
	}

	public function getIterator() {
		return new CollectionIterator($this);
	}

	public function remove($o) {
		foreach ($this->elements as $k => $e) {
			if ($e === $o) {
				unset($this->elements[$k]);
				return true;
			}
		}
		return false;
	}

	public function removeAll(Collection $c) {
		$ret = false;
		if ($this->count() < $c->count()) { //make sure we iterate over the smallest set
			foreach ($this->elements as $e) {
				if ($c->contains($e)) {
					$ret = $this->remove($e) || $ret;
				}
			}
		} else {
			foreach ($c->elements as $e) {
				$ret = $this->remove($e) || $ret;
			}
		}
		return $ret;
	}

	public function retainAll(Collection $c) {
		$ret = false;
		foreach ($this->elements as $e) {
			if (!$c->contains($e)) {
				$ret = $this->remove($e) || $ret;
			}
		}
		return $ret;
	}

	public function count() {
		return count($this->elements);
	}

	public function toArray() {
		return $this->elements;
	}

	public function fromArray(array $a, $replace = true) {
		if ($replace) {
			$this->clear();
		}
		foreach ($a as $k => $v) {
			$this->add($v);
		}
	}

	public function jsonSerialize() {
		return $this->toArray();
	}

	public function serialize() {
		$data = array();
		foreach ($this->elements as $e) {
			$data[] = serialize($e);
		}
		return serialize($data);
	}

	public function unserialize($serialized) {
		$data = unserialize($serialized);
		foreach ($data as $e) {
			$this->elements[] = unserialize($e);
		}
	}

	public function immutable() {
		return new ImmutableCollection($this);
	}
	
	public static function createFromArray(array $data = array()) {
		$ret = new static();
		
		$ret->fromArray($data);
		
		return $ret;
	}
}