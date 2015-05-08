<?php

namespace PXL\Core\Collection;

abstract class CollectionFilter implements Filter {
	public function filter(Collection $c, $returnNew = false) {
		if ($returnNew) {
			$cname = get_class($c);
			$c = new $cname($c);
		}
		$i = $c->getIterator();
		$i->rewind();
		while($i->valid()) {
			if (!$this->test($i->current())) $i->remove();
			$i->next();
		}
		return $c;
	}
}