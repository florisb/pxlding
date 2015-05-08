<?php

namespace PXL\Core\Tools;
use PXL\Core\Collection as C;

abstract class Registry {
	static protected $data;

	public static function __callStatic($name, $args) {
		if (!(self::$data instanceof C\SimpleMap)) self::$data = new C\SimpleMap();
		return call_user_func_array(array(self::$data, $name), $args);
	}
}