<?php
	namespace PXL\Core\Cache\Frontend;
	
	use PXL\Core\Cache\Backend\iBackend;
	
	/**
	 * iFrontend interface.
	 *
	 * Cache frontends are the objects that are
	 * interfaced with by appication code written
	 * by the developer. Frontend cache objects
	 * additionally determine the way caching is
	 * done. For instance, a resultset from a
	 * database call may be cached, or the result
	 * from output buffering.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	interface iFrontend {
		
		public function __construct(iBackend $backend, array $options = array());
		
		/**
		 * load function.
		 * 
		 * Loads previously cached data stored under an
		 * unique ID and returns it. If the data was not
		 * found, a boolean false will be returned indicating
		 * a cache miss.
		 *
		 * @access public
		 * @param mixed $id
		 * @param boolean $forceLoad
		 * @return $data | false
		 */
		public function load($id, $forceLoad = false);
		
		/**
		 * test function.
		 * 
		 * Checks if a cache entry exists. This method should be
		 * used in cases where cached data may contain data that
		 * evaluate to false, or even false boolean values.
		 * To prevent unneccessary overhead in method calls and
		 * data lookups, this method should only be used in cases
		 * where the result of load() cannot be used to determine
		 * whether or not a cached value exist.
		 *
		 * @access public
		 * @param mixed $id
		 * @return boolean
		 */
		public function test($id);
	
		/**
		 * save function.
		 * 
		 * Stores data under a specific id with an optional
		 * array of tags. If there already was data stored under
		 * the supplied id, the data will be overwritten. 
		 *
		 * @access public
		 * @param mixed $data
		 * @param mixed $id
		 * @param array $tags (default: array())
		 * @param bool $specificTime (default: false)
		 * @return void
		 */
		public function save($data, $id, array $tags = array(), $specificTime = false);
		
		/**
		 * clean function.
		 * 
		 * @access public
		 * @param mixed $mode
		 * @param array $tags() (default: array())
		 * @return void
		 */
		public function clean($mode, array $tags = array());
		
		/**
		 * remove function.
		 * 
		 * @access public
		 * @param mixed $id
		 * @return void
		 */
		public function remove($id);
		
	}