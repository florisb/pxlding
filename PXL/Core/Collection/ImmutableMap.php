<?php

namespace PXL\Core\Collection;

require_once('SimpleMap.php');
require_once('SimpleSet.php');
require_once('ImmutableSet.php');
require_once('SimpleImmutableMapEntry.php');
require_once('SimpleCollection.php');
require_once('ImmutableCollection.php');

class ImmutableMap extends SimpleMap {
	public function __construct(Map $m = null) {
		parent::__construct($m);
		$entries = new SimpleSet();
		foreach ($this->entries as $me) {
			$entries->add(new SimpleImmutableMapEntry($me));
		}
		$this->entries = new ImmutableSet($entries);
	}

	public function put($k, $v) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function remove($k) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function putAll(Map $m) {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function clear() {
		throw new \BadMethodCallException("Immutable object.");
	}

	public function keySet() {
		if (is_null($this->keySet)) {
			$this->keySet = new ImmutableSet(parent::keySet());
		}
		return $this->keySet;
	}

	public function values() {
		if (is_null($this->values)) {
			$this->values = new ImmutableCollection(parent::values());
		}
		return $this->values;
	}
}