<?php

namespace PXL\Core\Collection;

require_once('SimpleCollection.php');
require_once('Set.php');

class SimpleSet extends SimpleCollection implements Set {

	public function __construct(Collection $c = null) {
		if ($c instanceof Collection) {
			foreach ($c as $e) {
				if (!$this->contains($e)) $this->elements[] = $e;
			}
		}
	}

	public function add($e) {
		if ($this->contains($e)) return false;
		return parent::add($e);
	}

	public function immutable() {
		return new ImmutableSet($this);
	}
}