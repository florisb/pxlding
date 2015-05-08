<?php

namespace PXL\Core\Collection;

class DescendingCollectionIterator extends CollectionIterator {
	public function __construct(Collection $c) {
		parent::__construct($c);
		$this->elements = array_reverse($this->elements, true);
	}
}