<?php
	namespace PXL\Core\Session;
	
	use PXL\Core\Db\Db;
	
	abstract class Session implements iSession {
		
		protected static $_adapter;
		protected static $_prefix = null;
		
		public static function connectAdapter(Adapter\iAdapter $adapter) {
			self::$_adapter = $adapter;
			
			session_set_save_handler(
				array(self::$_adapter, 'open'),
				array(self::$_adapter, 'close'),
				array(self::$_adapter, 'read'),
				array(self::$_adapter, 'write'),
				array(self::$_adapter, 'destroy'),
				array(self::$_adapter, 'gc')
			);
		}
		
		public static function get($key) {
			if (!is_null(self::$_prefix)) {
				return isset($_SESSION[self::$_prefix][$key]) ? $_SESSION[self::$_prefix][$key] : null;;
			} else {
				return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
			}
		}
		
		public static function set($key, $value) {
			if (!is_null(self::$_prefix)) {
				$_SESSION[self::$_prefix][$key] = $value;
			} else {
				$_SESSION[$key] = $value;
			}
		}
		
		public static function has($key) {
			if (!is_null(self::$_prefix)) {
				return array_key_exists($key, $_SESSION[self::$_prefix]);
			} else {
				return array_key_exists($key, $_SESSION);
			}
		}
		
		public static function delete($key) {
			if (!is_null(self::$_prefix)) {
				unset($_SESSION[self::$_prefix][$key]);
			} else {
				unset($_SESSION[$key]);
			}
		}
		
		public static function regenerate_id($deleteOldSession = true) {
			$tmpSession = $_SESSION;

			if($deleteOldSession){
				session_destroy();
			}

			session_write_close();
			setcookie(session_name(), session_id(), time()-100000);
			session_id(sha1(mt_rand()));
			session_start();
			$_SESSION = $tmpSession;
			//session_regenerate_id($deleteOldSession);
		}
		
		public static function session_id() {
			return session_id();
		}
		
		public static function setPrefix($value) {
			self::$_prefix = $value;
		}
		
		public static function hasPrefix() {
			return !is_null(self::$_prefix);
		}
		
		public static function deletePrefix() {
			self::$_prefix = null;
		}
	}
