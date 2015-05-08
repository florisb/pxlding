<?php

namespace PXL\Core\Collection;

class CollectionIterator implements \Iterator {
	protected $collection;
	protected $elements;
	protected $counter = 0;

	public function __construct(Collection $c) {
		$this->collection = $c;
		$this->elements = array_values($c->toArray());
	}

	public function current() {
		return $this->elements[$this->counter];
	}
	
	public function key() {
		return $this->counter;
	}
	
	public function next() {
		$this->counter++;
	}
	
	public function rewind() {
		$this->counter = 0;
	}
	
	public function valid() {
		return ($this->counter >= 0 && $this->counter < count($this->collection));
	}

	public function remove() {
		$this->collection->remove($this->elements[$this->counter]);
		$this->elements = array_values($this->collection->toArray());
	}
}