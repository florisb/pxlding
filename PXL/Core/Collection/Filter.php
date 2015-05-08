<?php

namespace PXL\Core\Collection;

interface Filter {
	/**
	 * Tests if a given value conforms to this filter.
	 * @param  mixed $val The value to test.
	 * @return boolean    True if the given value conforms, false otherwise.
	 */
	public function test($val);
}