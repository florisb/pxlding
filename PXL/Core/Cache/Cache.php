<?php
	namespace PXL\Core\Cache;
	
	require_once('iCache.php');
	
	/**
	 * Abstract Cache class.
	 * 
	 * @abstract
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	abstract class Cache implements iCache {
		
		const CLEAN_MODE_ALL              = 1;
		const CLEAN_MODE_OLD              = 2;
		const CLEAN_MODE_MATCHING_TAG     = 4;
		const CLEAN_MODE_MATCHING_ANY_TAG = 8;
		const CLEAN_MODE_NOT_MATCHING_TAG = 16;
		
		public static function factory($frontendName, $backendName, array $frontendOptions = array(), array $backendOptions= array()) {
			
			// Check if both frontend and backend actually exist and implement the neccessary interfaces
			if (!class_exists($frontendName) || !in_array('PXL\Core\Cache\Frontend\iFrontend', class_implements($frontendName))) {
				throw new \InvalidArgumentException("Invalid cache frontend \"$frontendName\"");
			}
			
			if (!class_exists($backendName) || !in_array('PXL\Core\Cache\Backend\iBackend', class_implements($backendName))) {
				throw new \InvalidArgumentException("Invalid cache frontend \"$backendName\"");
			}
			
			// Create backend first using supplied options
			$backend = new $backendName($backendOptions);
			
			// Create frontend using supplied options and created backend
			$frontend = new $frontendName($backend, $frontendOptions);
							 
			// Return frontend
			return $frontend;
		}
	}