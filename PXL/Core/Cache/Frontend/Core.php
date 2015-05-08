<?php
	namespace PXL\Core\Cache\Frontend;
	
	require_once('iFrontend.php');
	
	use PXL\Core\Cache\Cache;
	use PXL\Core\Cache\Backend\iBackend;

	use PXL\Core\Exception as CoreException;
	
	/**
	 * Abstract Core class.
	 * 
	 * Abstract class that contains most common functionality that is needed
	 * by cache frontends. When implementing new cache frontends, they should
	 * always extend this class.
	 *
	 * @implements iFrontend
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	class Core implements iFrontend {
		
		protected $_backend;
		
		protected $_caching_active            = false;
		protected $_cache_id_prefix           = null;
		protected $_lifetime                  = 3600;
		protected $_write_control             = true;
		protected $_automatic_serialization   = true;
		protected $_automatic_cleaning_factor = 10;
		
		public function __construct(iBackend $backend, array $options = array()) {
			$this->_backend = $backend;
			
			$this->_setOptions($options);
		}
		
		public function load($id, $forceLoad = false) {
			if (!$this->_caching_active && !$forceLoad) {
				return false;
			}
			
			try {
				$data = $this->_backend->retrieve($id);
			} catch (CoreException\CacheErrorException $e) {
				return false;
			}
			
			if ($data !== false) {
				$unserializedData = @unserialize($data);
				
				if ($unserializedData !== false) {
					$data = $unserializedData;
				}
			}
			
			return $data;
		}
		
		public function test($id) { 
			return $this->_backend->has($id);
		}
		
		public function save($data, $id, array $tags = array(), $specificTime = false) {
			if (!is_scalar($data) && $this->_automatic_serialization) {
				$data = serialize($data);
			}
			
			// Write data to backend
			try {
				$this->_backend->store($data, $id, $tags, $specificTime);

				// Run GC
				$this->_gc();
			} catch (CoreException\CacheErrorException $e) {
				// Continue
			}
		}
		
		public function clean($mode, array $tags = array()) {
			if ($mode & Cache::CLEAN_MODE_ALL) {
				$this->_backend->removeAll();
			}
			
			if ($mode & Cache::CLEAN_MODE_OLD) {
				$this->_backend->removeOld();
			}
			
			if ($mode & Cache::CLEAN_MODE_MATCHING_TAG) {
				$this->_backend->removeByTags($tags);
			}
			
			if ($mode & Cache::CLEAN_MODE_MATCHING_ANY_TAG) {
				$this->_backend->removeByAnyTags($tags);
			}
			
			if ($mode & Cache::CLEAN_MODE_NOT_MATCHING_TAG) {
				$this->_backend->removeByNotTags($tags);
			}
		}
		
		public function remove($id) {
			$this->_backend->remove($id);
		}
		
		public function is_active() {
			return $this->_caching_active;
		}
		
		protected function _gc() {
			switch(true) {
				default:
				case ($this->_automatic_cleaning_factor === 0):
					break;
					
				case ($this->_automatic_cleaning_factor === 1):
					$this->_backend->removeOld();
					break;
					
				case ($this->_automatic_cleaning_factor > 1):
					$factor = $this->_automatic_cleaning_factor;
					if ($factor && mt_rand(1, $factor) == 1) {
            $this->_backend->removeOld();
					}
					break;
			}
		}
		
		protected function _setOptions($options) {
			foreach($options as $k => $v) {
				switch($k) {
					case 'caching_active':
						$this->_caching_active = (boolean) $v;
						break;
						
					case 'cache_id_prefix':
						$this->_cache_id_prefix = (string) $v;
						break;
						
					case 'lifetime':
						$this->_lifetime = (int) $v;
						break;
						
					case 'write_control':
						$this->_write_control = (boolean) $v;
						break;
						
					case 'automatic_serialization':
						$this->_automatic_serialization = (boolean) $v;
						break;
						
					case 'automatic_cleaning_factor':
						$this->_automatic_cleaning_factor = abs((int) $v);
						break;
						
					default:
						break;
				}
			}
		}
	}