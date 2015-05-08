<?php

namespace PXL\Core\Collection;

require_once('SimpleList.php');
require_once('SimpleSet.php');
require_once('Filter.php');

class FilterableList extends SimpleList {
	protected $filters;

	public function addFilter(Filter $f) {
		if (!($this->filters instanceof Collection)) $this->filters = new SimpleSet();
		return $this->filters->add($f);
	}

	public function removeFilter(Filter $f) {
		return $this->filters->remove($f);
	}

	public function add($e, $i = null) {
		if (count($this->filters)) {
			foreach ($this->filters as $f) {
				if (!$f->test($e)) throw new \InvalidArgumentException("Provided element doesn't meet set tests.");
			}
		}
		return parent::add($e, $i);
	}

	public function set($i, $e) {
		if (count($this->filters)) {
			foreach ($this->filters as $f) {
				if (!$f->test($e)) throw new \InvalidArgumentException("Provided element doesn't meet set tests.");
			}
		}
		return parent::set($i, $e);
	}
}