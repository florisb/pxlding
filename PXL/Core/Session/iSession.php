<?php
	namespace PXL\Core\Session;
	
	interface iSession {
		
		public static function connectAdapter(Adapter\iAdapter $adapter);
		
		public static function get($key);
		
		public static function set($key, $value);
		
		public static function delete($key);
	}