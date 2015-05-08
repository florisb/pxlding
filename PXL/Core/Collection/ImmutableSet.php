<?php

namespace PXL\Core\Collection;

require_once('ImmutableCollection.php');
require_once('Set.php');

class ImmutableSet extends ImmutableCollection implements Set {
	public function add($e) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function clear() {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function remove($o) {
		throw new \BadMethodCallException("Immutable object.");
	}
}