<?php

namespace PXL\Core\Collection;

require_once('Queue.php');
require_once('SimpleCollection.php');

class SimpleQueue extends SimpleCollection implements Queue {
	protected $maxSize = 0;

	public function add($e) {
		if ($this->maxSize > 0 && $this->count() >= $this->maxSize) throw new \OverflowException("Maximum length reached.");
		return $this->offer($e);
	}

	public function remove($o = null) {
		if (0 == $this->count()) throw new \UnderflowException("Queue is empty.");
		return $this->poll();
	}

	public function element() {
		if (0 == $this->count()) throw new \UnderflowException("Queue is empty.");
		return $this->peek();
	}

	public function addAll(Collection $c) {
		if ($this === $c) throw new \InvalidArgumentException("Queue cannot add to itself.");
		return parent::addAll($c);
	}

	public function offer($e) {
		if (is_null($e)) throw new \InvalidArgumentException("NULL-values are not allowed.");
		if ($this->maxSize > 0 && $this->count() >= $this->maxSize) return false;
		return parent::add($e);
	}

	public function poll() {
		return array_shift($this->elements);
	}

	public function peek() {
		$ret = array_shift($this->elements);
		array_unshift($this->elements, $ret);
		return $ret;
	}

	public function setLimit($limit = 0) {
		$this->maxSize = (int) $limit;
	}
}