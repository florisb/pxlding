<?php
	namespace PXL\Core\Cookie;
	
	interface iCookie {
		
		public static function set($key, $value, $duration = null, $path = "/");
		
		public static function get($key);
		
		public static function delete($key, $path = "/");
		
		public static function debug($key);
		
	}