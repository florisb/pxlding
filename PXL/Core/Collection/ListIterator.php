<?php

namespace PXL\Core\Collection;

class ListIterator implements \SeekableIterator {
	protected $list;
	protected $counter = 0;

	public function __construct(Enumerated $l) {
		$this->list = $l;
	}

	public function current() {
		return $this->list->get($this->counter);
	}
	
	public function key() {
		return $this->counter;
	}
	
	public function next() {
		$this->counter++;
	}

	public function previous() {
		$this->counter--;
	}
	
	public function rewind() {
		$this->counter = 0;
	}
	
	public function valid() {
		return ($this->counter >= 0 && $this->counter < count($this->list));
	}

	public function seek($index) {
		$this->counter = $index;
		if (!$this->valid()) throw new OutOfBoundsException("Invalid position seeked.");
	}

	public function remove() {
		$this->list->remove($this->counter);
	}
}