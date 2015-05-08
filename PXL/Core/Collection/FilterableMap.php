<?php

namespace PXL\Core\Collection;

require_once('SimpleMap.php');
require_once('Filter.php');
require_once('SimpleSet.php');

class FilterableMap extends SimpleMap {
	protected $keyFilters;
	protected $valueFilters;

	public function addKeyFilter(Filter $f) {
		if (!($this->keyFilters instanceof Set)) $this->keyFilters = new SimpleSet();
		return $this->keyFilters->add($f);
	}

	public function removeKeyFilter(Filter $f) {
		return $this->keyFilters->remove($f);
	}

	public function addValueFilter(Filter $f) {
		if (!($this->valueFilters instanceof Set)) $this->valueFilters = new SimpleSet();
		return $this->valueFilters->add($f);
	}

	public function removeValueFilter(Filter $f) {
		return $this->valueFilters->remove($f);
	}

	public function addFilter(Filter $f) {
		return $this->addValueFilter($f);
	}

	public function removeFilter(Filter $f) {
		return $this->removeValueFilter($f);
	}

	public function put($k, $v) {
		if (count($this->keyFilters)) {
			foreach ($this->keyFilters as $f) {
				if (!$f->test($k)) throw new \InvalidArgumentException("Key does not conform to test.");
			}
		}
		if (count($this->valueFilters)) {
			foreach ($this->valueFilters as $f) {
				if (!$f->test($v)) throw new \InvalidArgumentException("Value does not conform to test.");
			}
		}
		return parent::put($k, $v);
	}
}