<?php

namespace PXL\Core;

use PXL\Core\Collection as Coll;
use StdClass;

require_once('Collection/SimpleSet.php');
require_once('Collection/SimpleMap.php');

abstract class Config {
	protected static $files;
	protected static $data;
	protected static $defaults;

	public static function addFile($path, $env = 'production') {
		$check = md5($path);
		if (!(self::$files instanceof Coll\Set)) self::$files = new Coll\SimpleSet();
		if (self::$files->add($check)) {
			$defaultdata = new Coll\SimpleMap();
			$filedata = new Coll\SimpleMap();
			$rawdata = parse_ini_file($path, true);

			if ($rawdata === false) {
				throw new \InvalidArgumentException('Unable to read config-file.');
			}

			$defaultdata->fromArray($rawdata['default']);
			if (!array_key_exists($env, $rawdata)) $env = 'production';
			$filedata->fromArray($rawdata[$env]);
			if (!(self::$data instanceof Coll\Map)) self::$data = new Coll\SimpleMap();
			if (!(self::$defaults instanceof Coll\Map)) self::$defaults = new Coll\SimpleMap();
			self::$data->putAll($filedata);
			self::$defaults->putAll($defaultdata);
		}
	}

	public static function read($key) {
		$ret = self::$data->get($key) ?: self::$defaults->get($key) ?: null;
		
		if (is_null($ret)) {
			throw new \InvalidArgumentException("Invalid config variable \"$key\" encountered.");
		}
		
		$parseReplacements = function($ret, $fn) {
			if (is_array($ret)) {
				foreach($ret as &$_ret) {
					$_ret = $fn($_ret, $fn);
				}
				unset($_ret);
			} else {
				if (preg_match_all('#\{([a-z]{1}.*?)\}#', $ret, $matches, PREG_SET_ORDER)) {
					foreach ($matches as $m) {
						$ret = preg_replace('#'.$m[0].'#', \PXL\Core\Config::read($m[1]), $ret);
					}
				}
			}
			
			return $ret;
		};
		
		return $parseReplacements($ret, $parseReplacements);
	}
	
	public static function has($key) {
		return !is_null((self::$data && self::$data->get($key)) ?: (self::$defaults && self::$defaults->get($key)) ?: null);
	}

	public static function getDefaults() {
		return self::$defaults->immutable();
	}

	public static function getData() {
		return self::$data->immutable();
	}
	
	/**
	 * getAsObject function.
	 * 
	 * Retrieves the current configuration as an StdClass instance,
	 * which cascades further down per section (which are defined by
	 * dots in the ini keynames) as StdClass objects as well.
	 *
	 * @access public
	 * @static
	 * @return void
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	public static function getAsObject() {
		$configObj = new StdClass();
		
		foreach(array(self::getDefaults(), self::getData()) as $data) {	
			foreach($data as $k => $v) {
				$components = explode('.', $k);

				$currentLevel = $configObj;
				foreach($components as $component) {
					if ($component === end($components)) {
						$currentLevel->$component = self::read($k);
					} else {
						if (empty($currentLevel->$component)) {
							$currentLevel = $currentLevel->$component = new StdClass();
						} else {
							$currentLevel = $currentLevel->$component;
						}
					}
				}
			}
		}
		
		return $configObj;
	}
}