<?php

namespace PXL\Core\Collection;

require_once('MapEntry.php');

class SimpleMapEntry implements MapEntry {
	protected $key;
	protected $value;

	public function __construct($kme, $v = null) {
		if ($kme instanceof MapEntry) {
			$this->key = $kme->getKey();
			$this->value = $kme->getValue();
		} else {
			if (is_null($v)) {
				throw new \InvalidArgumentException("Single argument constructor needs a MapEntry.");
			}
			$this->key = $kme;
			$this->value = $v;
		}
	}

	public function getKey() {
		return $this->key;
	}

	public function getValue() {
		return $this->value;
	}

	public function setValue($v) {
		if (is_null($v)) {
			throw new \InvalidArgumentException("Value must not be null.");
		}
		$o = $this->value;
		$this->value = $v;
		return $o;
	}

	public function __toString() {
		return $this->key.'='.$this->value;
	}

	public function serialize() {
		return serialize(array(
				'k' => serialize($this->key),
				'v'	=> serialize($this->value)
			));
	}

	public function unserialize($serialized) {
		$data			= unserialize($serialized);
		$this->key 		= unserialize($data['k']);
		$this->value 	= unserialize($data['v']);
	}
}