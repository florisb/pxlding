<?php

namespace PXL\Core\Collection;

require_once('SimpleCollection.php');

class ImmutableCollection extends SimpleCollection {
	public function add($e) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function addAll(Collection $c) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function clear() {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function remove($o) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function removeAll(Collection $c) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function retainAll(Collection $c) {
		throw new \BadMethodCallException("Immutable object.");
	}
}