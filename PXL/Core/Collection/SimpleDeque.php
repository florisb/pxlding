<?php

namespace PXL\Core\Collection;

require_once('Deque.php');
require_once('SimpleQueue.php');
require_once('DescendingCollectionIterator.php');

class SimpleDeque extends SimpleQueue implements Deque {
	public function addFirst($e) {
		if ($this->maxSize > 0 && $this->count() >= $this->maxSize) throw new \OverflowException("Maximum length reached.");
		return $this->offerFirst($e);
	}

	public function addLast($e) {
		if ($this->maxSize > 0 && $this->count() >= $this->maxSize) throw new \OverflowException("Maximum length reached.");
		return $this->offerLast($e);
	}

	public function offerFirst($e) {
		if (is_null($e)) throw new \InvalidArgumentException("NULL-values are not allowed.");
		if ($this->maxSize > 0 && $this->count() >= $this->maxSize) return false;
		array_unshift($this->elements, $e);
		return true;
	}

	public function offerLast($e) {
		if (is_null($e)) throw new \InvalidArgumentException("NULL-values are not allowed.");
		if ($this->maxSize > 0 && $this->count() >= $this->maxSize) return false;
		array_push($this->elements, $e);
		return true;
	}

	public function removeFirst() {
		if (0 == $this->count()) throw new \UnderflowException("Deque is empty.");
		return $this->pollFirst();
	}

	public function removeLast() {
		if (0 == $this->count()) throw new \UnderflowException("Deque is empty.");
		return $this->pollLast();
	}

	public function pollFirst() {
		return array_shift($this->elements);
	}

	public function pollLast() {
		return array_pop($this->elements);
	}

	public function getFirst() {
		if (0 == $this->count()) throw new \UnderflowException("Deque is empty.");
		return $this->peekFirst();
	}

	public function getLast() {
		if (0 == $this->count()) throw new \UnderflowException("Deque is empty.");
		return $this->peekLast();
	}

	public function peekFirst() {
		$ret = array_shift($this->elements);
		array_unshift($this->elements, $ret);
		return $ret;
	}

	public function peekLast() {
		$ret = array_pop($this->elements);
		array_push($this->elements, $ret);
		return $ret;
	}

	public function removeFirstOccurrence($o) {
		foreach ($this->elements as $k => $e) {
			if ($o === $e) {
				unset($this->elements[$k]);
				return true;
			}
		}
		return false;
	}

	public function removeLastOccurrence($o) {
		$temp = array_reverse($this->elements);
		foreach ($temp as $k => $e) {
			if ($o === $e) {
				unset($temp[$k]);
				$this->elements = array_reverse($temp);
				return true;
			}
		}
		return false;
	}

	public function remove($o = null) {
		if (is_null($o)) {
			return parent::remove();
		} else {
			return $this->removeFirstOccurrence($o);
		}
	}

	public function push($e) {
		return $this->addFirst($e);
	}

	public function pop() {
		return $this->removeFirst();
	}

	public function descendingIterator() {
		return new DescendingCollectionIterator($this);
	}
}