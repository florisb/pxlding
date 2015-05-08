<?php
	namespace PXL\Core\Cache\Backend;
	
	/**
	 * iBackend interface.
	 *
	 * Cache backends are the classes that are not
	 * communicated directly with by application code,
	 * but rather by a cache frontend instance.
	 * Cache backends are responsible for handling
	 * storage of cached data in the broadest sense
	 * of the word. Cached data may be stored in files,
	 * databases, memory, etc.
	 *
	 * Because of the singular nature of cache backends,
	 * it is highly recommended that cache backends are
	 * defined as singletons rather than objects that
	 * may be instantiated multiple times.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	interface iBackend {
		
		public function store($data, $id, array $tags = array(), $specificLifeTime = false);
		
		public function has($id);
		
		public function retrieve($id);
	
		public function remove($id);
		
		public function removeAll();
		
		public function removeOld();
		
		public function removeByTags(array $tags = array());
		
		public function removeByAnyTags(array $tags = array());
		
		public function removeByNotTags(array $tags = array());
		
		public function setOptions(array $options = array());
	}