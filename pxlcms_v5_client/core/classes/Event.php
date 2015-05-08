<?php

abstract class Event {
	static $handlers;
	
	public static function register($callback, $event, $module_id) {
		$module_id = (int) $module_id;
		$event = (string) $event;
		
		if (!is_array(self::$handlers)) self::$handlers = array();
		if (!array_key_exists($module_id, self::$handlers)) self::$handlers[$module_id] = array();
		if (!array_key_exists($event, self::$handlers[$module_id])) self::$handlers[$module_id][$event] = array();
		if (is_callable($callback, false)) {
			self::$handlers[$module_id][$event][] = $callback;
		}
	}
	
	public static function unregister($callback, $event, $module_id) {
		$module_id = (int) $module_id;
		$event = (string) $event;
		
		if (is_array(self::$handlers) && array_key_exists($module_id, self::$handlers) && array_key_exists($event, self::$handlers[$module_id])) {
			$key = array_search($callback, self::$handlers[$module_id][$event]);
			if ($key !== false) {
				unset(self::$handlers[$module_id][$event][$key]);
			}
			if (count(self::$handlers[$module_id][$event]) == 0) {
				unset(self::$handlers[$module_id][$event]);
			}
			if (count(self::$handlers[$module_id]) == 0) {
				unset(self::$handlers[$module_id]);
			}
		}
	}
	
	public static function unregisterAll($callback, $module_id) {
		if (is_array(self::$handlers) && array_key_exists($module_id, self::$handlers)) {
			foreach (array_keys(self::$handlers[$module_id]) as $event) {
				self::unregister($callback, $event);
			}
		}
	}
	
	public static function fire($module_id, $event, $data) {
		$event = (string) $event;
		
		if (is_array(self::$handlers) && array_key_exists($module_id, self::$handlers) && array_key_exists($event, self::$handlers[$module_id])) {
			foreach (self::$handlers[$module_id][$event] as $callback) {
				$ret = call_user_func($callback, $data);
				if (!is_null($ret)) {
					$data = $ret;
				}
			}
		}
		
		return $data;
	}
}