<?php

namespace PXL\Core\Collection;

class FilterableSet extends SimpleSet {
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
		parent::add($e);
	}
}
