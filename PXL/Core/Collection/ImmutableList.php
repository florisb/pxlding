<?php

namespace PXL\Core\Collection;

require_once('SimpleList.php');

class ImmutableList extends SimpleList {
	public function add($e, $i = null) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function set($i, $e) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function remove($i) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function clear() {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function addAll(Collection $c, $i = null) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function removeRange($from, $to) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function removeAll(Collection $c) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function retainAll(Collection $c) {
		throw new \BadMethodCallException("Immutable object.");
	}
}