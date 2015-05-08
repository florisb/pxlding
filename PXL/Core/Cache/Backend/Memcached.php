<?php
	namespace PXL\Core\Cache\Backend;
	
	require_once('AbstractBackend.php');
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', '..', 'Collection', 'SimpleMap.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', '..', 'Collection', 'SimpleList.php')));
	
	use Memcache;
	use PXL\Core\Collection;
	
	/**
	 * Memcached class.
	 * 
	 * Provides a memcache-based caching backend
	 * for high-performance caching.
	 *
	 * @extends AbstractBackend
	 * @author  Max van der Stam <max@pixelindustries.com>
	 */
	class Memcached extends AbstractBackend {
		
		protected $_host     = 'localhost';
		protected $_port     = 11211;
		protected $_timeout  = 1;
		protected $_lifetime = 1800;
		
		protected $_memcache = null;
		
		const TAGDATA_CACHE_ID = '_PXL_CACHE_TAGS';
		
		protected $_tagData = null;
		
		public function __construct(array $options = array()) {
			parent::__construct($options);
			
			$this->_memcache = new Memcache();
			
			if (!@$this->_memcache->connect($this->_host, $this->_port)) {
				throw new \BadMethodCallException("Unable to connect to memcache server on {$this->_host}:{$this->_port}");
			}
			
			$this->_fetchTagData();
			register_shutdown_function(array($this, 'storeTagData'));
		}
		
		public function store($data, $id, array $tags = array(), $specificLifeTime = false) {
			$id = sha1($id);
			
			$this->_tagData->put($id, Collection\SimpleList::createFromArray($tags));
			$this->_memcache->set($id, $data, false, $specificLifeTime === false ? $this->_lifetime : $specificLifeTime);
		}
		
		public function has($id) {
			$id = sha1($id);
			return ($this->_memcache->get($id) !== false);
		}
		
		public function retrieve($id) {
			$id = sha1($id);
			return $this->_memcache->get($id);
		}
		
		public function remove($id) {
			$id = sha1($id);
			$this->_memcache->delete($id);
		}
		
		public function removeAll() {
			$this->_memcache->flush();
		}
		
		public function removeOld() { return false; }
		
		public function removeByTags(array $tags = array()) {
			foreach($this->_tagData as $id => $idTags) {
				if (count(array_intersect($tags, $idTags->toArray())) === count($idTags)) {
					$this->_tagData->remove($id);
					$this->_memcache->delete($id);
				}
			}
		}
		
		public function removeByAnyTags(array $tags = array()) {
			foreach($this->_tagData as $id => $idTags) {
				if (count(array_intersect($tags, $idTags->toArray()))) {
					$this->_tagData->remove($id);
					$this->_memcache->delete($id);
				}
			}
		}
		
		public function removeByNotTags(array $tags = array()) {
			foreach($this->_tagData as $id => $idTags) {
				if (!count(array_intersect($tags, $idTags->toArray()))) {
					$this->_tagData->remove($id);
					$this->_memcache->delete($id);
				}
			}
		}
		
		public function setOptions(array $options = array()) {
			foreach($options as $k => $v) {
				switch($k) {
					case 'host':
						$this->_host = (string) $v;
						break;
						
					case 'port':
						$this->_port = (int) $v;
						break;
						
					case 'timeout':
						$this->_timeout = (int) $v;
						break;
						
					case 'lifetime':
						$this->_lifetime = (int) $v;
						break;
						
					default:
						break;
				}
			}
		}
		
		protected function _fetchTagData() {
			if (!($tagData = $this->_memcache->get(self::TAGDATA_CACHE_ID))) {
				$tagData = new Collection\SimpleMap();
			}
			
			$this->_tagData = $tagData;
		}
		
		public function storeTagData() {
			$this->_memcache->set(self::TAGDATA_CACHE_ID, $this->_tagData, false, 0);
		}
	}