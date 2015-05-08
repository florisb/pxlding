<?php
	spl_autoload_register(function ($class) {
		$classComponents = explode('\\', $class);
		
		$class    = $classComponents ? implode('\\', $classComponents) : $class;
		$fileName = end($classComponents);
	
		$possibleLocations   = array();
		$possibleLocations[] = path(APPLICATION_PATH . "App/$class.php", '\\');
		$possibleLocations[] = path(APPLICATION_PATH . "$class.php", '\\');
		$possibleLocations[] = path(APPLICATION_PATH . "$class\\$fileName.php", '\\');

		// Try lowercase as well
		if ($classComponents) {
			$class = lcfirst(array_pop($classComponents));
			$class = implode('\\', $classComponents) . '\\' . $class;
		} else {
			$class = lcfirst($class);
		}

		$fileName            = lcfirst($fileName);
		$possibleLocations[] = path(APPLICATION_PATH . "App/$class.php", '\\');
		$possibleLocations[] = path(APPLICATION_PATH . "$class.php", '\\');
		$possibleLocations[] = path(APPLICATION_PATH . "$class\\$fileName.php", '\\');
		
		foreach($possibleLocations as $possibleLocation) {
			if (is_file($possibleLocation)) {
				require_once($possibleLocation);
				return true;
			}
		}
		
		return false;
	}, false);