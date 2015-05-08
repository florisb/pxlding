<?php
	namespace PXL\Core\Cookie;
	
	abstract class Cookie implements iCookie {
		
		public static function set($key, $value, $duration = null, $path = "/") {
			if (is_null($duration)) {
				$duration = time() + 60 * 60 * 24 * 365; // one year default
			}
			setcookie($key, self::encode($value), $duration, $path);
			$_COOKIE[$key] = self::encode($value);
		}
		
		public static function get($key) {
			return (isset($_COOKIE[$key]) && $_COOKIE[$key] != '') ? self::decode($_COOKIE[$key]) : null;
		}
		
		public static function delete($key, $path = "/") {
			setcookie($key, '', time() - 360000, $path);
			unset($_COOKIE[$key]);
		}
		
		protected static function encode($value) {
			return json_encode($value);
		}
		
		protected static function decode($value) {
			return json_decode($value);
		}
		
		public static function debug($key) {
			pr(self::get($key));
		}
	}