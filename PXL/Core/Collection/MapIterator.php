<?php

namespace PXL\Core\Collection;

class MapIterator implements \Iterator {
	protected $map;
	protected $entries;
	protected $counter = 0;

	public function __construct(Map $m) {
		$this->map = $m;
		$this->entries = array_values($m->entrySet()->toArray());
	}

	public function current() {
		return $this->entries[$this->counter]->getValue();
	}
	
	public function key() {
		return $this->entries[$this->counter]->getKey();
	}
	
	public function next() {
		$this->counter++;
	}
	
	public function rewind() {
		$this->counter = 0;
	}

	public function previous() {
		$this->counter--;
	}
	
	public function valid() {
		return ($this->counter >= 0 && $this->counter < count($this->map));
	}

	public function remove() {
		$this->map->remove($this->key());
		$this->entries = array_values($this->map->entrySet()->toArray());
	}
}