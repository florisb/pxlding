<?php
	namespace PXL\Core\Cache;
	
	/**
	 * iCache interface.
	 *
	 * Within the PXL framework, caching may be done in various ways in both
	 * frontend and backend contexts. As such, the creation and initialization
	 * of cache objects that handle caching is abstracted through the
	 * factory-pattern.
	 *
	 * Cache objects should *never* be instantiated directly by the developer;
	 * instead, please use Cache::factory() to create cache objects.
	 *
	 * In every situation where caching is needed, 2 components
	 * work together to form the functionality neccessary to
	 * provide data caching:
	 *
	 * -frontend
	 * -backend
	 *
	 * Please refer to the documentation in iFrontend and
	 * iBackend for more information regarding the functionality
	 * of front- and backends.
	 *
	 * Because of the bilateral way caches work, *any* frontend
	 * will work with *any* backend, as long as interfaces are
	 * correctly implemented in both components.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	interface iCache {
		
		public static function factory($frontendClassName, $backendClassName, array $frontendOptions = array(), array $backendOptions = array());
	}