<?php

namespace PXL\Core\Collection;

require_once('Filter.php');
require_once('SimpleDeque.php');
require_once('SimpleSet.php');

class FilterableDeque extends SimpleDeque {
	protected $filters;

	public function addFilter(Filter $f) {
		if (!($this->filters instanceof Collection)) $this->filters = new SimpleSet();
		return $this->filters->add($f);
	}

	public function removeFilter(Filter $f) {
		return $this->filters->remove($f);
	}

	public function add($e) {
		if (count($this->filters)) {
			foreach ($this->filters as $f) {
				if (!$f->test($e)) throw new \InvalidArgumentException("Provided element doesn't meet set tests.");
			}
		}
		return parent::add($e);
	}

	public function offerFirst($e) {
		if (count($this->filters)) {
			foreach ($this->filters as $f) {
				if (!$f->test($e)) throw new \InvalidArgumentException("Provided element doesn't meet set tests.");
			}
		}
		return parent::offerFirst($e);
	}

	public function offerLast($e) {
		if (count($this->filters)) {
			foreach ($this->filters as $f) {
				if (!$f->test($e)) throw new \InvalidArgumentException("Provided element doesn't meet set tests.");
			}
		}
		return parent::offerLast($e);
	}
}