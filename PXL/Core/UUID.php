<?php

namespace PXL\Core;

abstract class UUID {

	public static function validate($uuid) {
		return (bool) preg_match('/^\{?[A-F0-9]{8}-?[A-F0-9]{4}-?[A-F0-9]{4}-?[A-F0-9]{4}-?[A-F0-9]{12}\}?$/i', $uuid);
	}

	protected static function binary($uuid) {
		if(!self::validate($uuid)) throw new \InvalidArgumentException("Provided value is not a valid UUID.");

		$clean = self::clean($uuid);
		$ret = '';
		for($i = 0; $i < strlen($clean); $i+=2) {
			$ret .= chr(hexdec($clean[$i].$clean[$i+1]));
		}
		return $ret;
	}

	public static function clean($uuid) {
		if(!self::validate($uuid)) throw new \InvalidArgumentException("Provided value is not a valid UUID.");

		return strtolower(str_replace(array('-','{','}'), '', $uuid));
	}

	public static function format($uuid) {
		if(!self::validate($uuid)) throw new \InvalidArgumentException("Provided value is not a valid UUID.");
		$uuid = strtolower(self::clean($uuid));
		return sprintf('%08s-%04s-%04s-%04s-%12s',
			substr($uuid, 0, 8),
			substr($uuid, 8, 4),
			substr($uuid, 12, 4),
			substr($uuid, 16, 4),
			substr($uuid, 20, 12)
		);		
	}

	public static function compare($sid1, $sid2) {
		return (self::clean($sid1) == self::clean($sid2));
	}

	public static function v3($namespace, $name) {		
		$hash = md5(self::binary($namespace) . $name);
		return sprintf('%08s-%04s-%04x-%04x-%12s',
			substr($hash, 0, 8),
			substr($hash, 8, 4),
			(hexdec(substr($hash, 12, 4)) & 0x0FFF) | 0x3000,
			(hexdec(substr($hash, 16, 4)) & 0x3FFF) | 0x8000,
			substr($hash, 20, 12)
		);
	}

	public static function v4() {
		return sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand(0, 0xFFFF),
			mt_rand(0, 0xFFFF),
			mt_rand(0, 0xFFFF),
			(mt_rand(0, 0xFFFF) & 0x0FFF) | 0x4000,
			(mt_rand(0, 0xFFFF) & 0x3FFF) | 0x8000,
			mt_rand(0, 0xFFFF),
			mt_rand(0, 0xFFFF),
			mt_rand(0, 0xFFFF)
		);
	}

	public static function v5($namespace, $name) {		
		$hash = sha1(self::binary($namespace) . $name);
		return sprintf('%08s-%04s-%04x-%04x-%12s',
			substr($hash, 0, 8),
			substr($hash, 8, 4),
			(hexdec(substr($hash, 12, 4)) & 0x0FFF) | 0x5000,
			(hexdec(substr($hash, 16, 4)) & 0x3FFF) | 0x8000,
			substr($hash, 20, 12)
		);
	}
}