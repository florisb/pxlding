<?php
	if (!interface_exists('JsonSerializable', false)) {
		eval("
			interface JsonSerializable {

				public function jsonSerialize();
			}
		");
	}
