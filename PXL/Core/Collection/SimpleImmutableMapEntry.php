<?php

namespace PXL\Core\Collection;

require_once('SimpleMapEntry.php');

class SimpleImmutableMapEntry extends SimpleMapEntry {
	public function setValue($v) {
		throw new \BadMethodCallException("Method not implemented.");
	}
}