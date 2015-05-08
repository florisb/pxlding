<?php

namespace PXL\Core\Collection;

require_once('SimpleCollection.php');
require_once('Enumerated.php');
require_once('ListIterator.php');

class SimpleList extends SimpleCollection implements Enumerated {
	protected $elements = array();
	protected $offset = null;
	protected $parent = null;

	public function add($e, $i = null) {
		if ($this->parent) {
			throw new \BadMethodCallException("Method not implemented.");
		}
		if (!is_null($i)) $i = (int) $i;
		if ($i > $this->count()) {
			throw new \OverflowException("Index larger than size.");
		}
		if ($i < 0) {
			throw new \UnderflowException("Index smaller than 0.");
		}
		if (is_null($i) || $this->count() == $i) {
			$this->elements[] = $e;
		} elseif (0 == $i) {
			array_unshift($this->elements, $e);
		} else {
			$back = array_splice($this->elements, $i);
			$this->elements = array_merge($this->elements, array($e), $back);
		}
		return true;
	}

	public function get($i) {
		$i = (int) $i;

		if (!(isset($this->elements[$i]) || array_key_exists($i, $this->elements))) {
			throw new \OutOfBoundsException('Index out of bounds.');
		}

		return $this->elements[$i];
	}

	public function set($i, $e) {
		$i = (int) $i;

		if (!(isset($this->elements[$i]) || array_key_exists($i, $this->elements))) {
			throw new \OutOfBoundsException('Index out of bounds.');
		}

		$ret = $this->elements[$i];
		$this->elements[$i] = $e;
		if ($this->parent) {
			$this->parent->set($i + $this->offset, $e);
		}

		return $ret;
	}

	public function remove($i) {
		$i = (int) $i;
		
		if (!(isset($this->elements[$i]) || array_key_exists($i, $this->elements))) {
			throw new \OutOfBoundsException('Index out of bounds.');
		}

		$ret = $this->elements[$i];
		unset($this->elements[$i]);
		//renumber indices!
		$this->elements = array_merge($this->elements);
		if ($this->parent) {
			$this->parent->remove($i + $this->offset);
		}
		return $ret;
	}

	public function removeFrom($i) {
		$i = (int) $i;
		
		if (!(isset($this->elements[$i]) || array_key_exists($i, $this->elements))) {
			throw new \OutOfBoundsException('Index out of bounds.');
		}
		
		$i = $this->listIterator($i);
		while ($i->valid()) {
			$i->remove();
			//No call to next, since the removal of this position causes all following positions to shift to this position.
		}

	}

	public function indexOf($o) {
		foreach ($this->elements as $k => $v) if ($v === $o) return $k;
		return -1;
	}

	public function lastIndexOf($o) {
		for ($i = count($this->elements) - 1; $i >= 0; $i--) if ($this->elements[$i] === $o) return $i;
		return -1;
	}

	public function clear() {
		if ($this->parent) {
			$this->parent->removeRange($this->offset, $this->offset + $this->count());
		}
		$this->elements = array();
	}

	public function addAll(Collection $c, $i = null) {
		if (!is_null($i)) $i = (int) $i;
		if ($this->parent) {
			throw new \BadMethodCallException("Method not implemented.");
		}
		foreach ($c as $e) {
			$this->add($e, $i);
			if (!is_null($i)) $i++;
		}
	}

	public function getIterator() {
		return new ListIterator($this);
	}

	public function listIterator($index = 0) {
		$i = $this->getIterator();
		$i->seek($index);
		return $i;
	}

	public function subList($from, $to) {
		if (!(isset($this->elements[$from]) || array_key_exists($from, $this->elements)) || !(isset($this->elements[$to]) || array_key_exists($to, $this->elements))) {
			throw new \OutOfBoundsException('Index out of bounds.');	
		}

		$l = new static();
		$l->parent = $this;
		$l->offset = $from;
		for (;$from < $to; $from++) $l->elements[] = $this->elements[$from];
		return $l;
	}

	public function removeRange($from, $to) {
		if (!(isset($this->elements[$from]) || array_key_exists($from, $this->elements)) || !(isset($this->elements[$to]) || array_key_exists($to, $this->elements))) {
			throw new \OutOfBoundsException('Index out of bounds.');	
		}

		$i = $this->listIterator($from);
		while ($i->valid() && $i->key() < $to) {
			$i->remove();
			$to--;
		}
		if ($this->parent) {
			$this->parent->removeRange($from + $this->offset, $to + $this->offset);
		}
	}

	public function immutable() {
		return new ImmutableList($this);
	}

	public function fromArray(array $a, $replace = true) {
		if ($replace) {
			$this->clear();
		}

		$this->elements = $a;
	}
}