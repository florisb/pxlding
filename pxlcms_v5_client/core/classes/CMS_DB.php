<?php
	class CMS_DB {
		protected static $_connection = false;
		
		public static function mysql_query($q) {
			if (self::$_connection === false) self::connect();
			return mysql_query($q, self::$_connection);
		}
		
		public static function connect() {
			if (self::$_connection !== false) return;
			
			global $CMS_DB;
			
			$connection = mysql_connect($CMS_DB['host'], $CMS_DB['user'], $CMS_DB['pass'], true);
			
			if ($connection !== false) {
				$exist = mysql_select_db($CMS_DB['db_name'], $connection);
				if ($exist !== false) {
					mysql_query("SET NAMES 'utf8';", $connection);
				} else {
					return false;
				}
			}
			self::$_connection = $connection;
			
			if (self::$_connection === false) {
				trigger_error("DB connection error", E_USER_ERROR);
			}
		}
	}