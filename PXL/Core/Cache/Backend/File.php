<?php
	namespace PXL\Core\Cache\Backend;
	
	require_once('AbstractBackend.php');
	
	use GlobIterator;

	use PXL\Core\Exception as CoreException;
	
	/**
	 * File class.
	 * 
	 * Provides as file-based caching backend for
	 * normal-performance caching.
	 *
	 * @extends AbstractBackend
	 * @author  Max van der Stam <max@pixelindustries.com>
	 */
	class File extends AbstractBackend {
	
		protected $_cache_directory      = null;
		protected $_lifetime             = 3600;
		protected $_write_control        = true;
		protected $_write_control_method = 'adler32';
		protected $_filename_prefix      = 'pxl_cache';		
		protected $_file_locking         = true;
		protected $_file_umask           = false;
		protected $_file_perm            = 0600;
		
		/**
		 * _supported_write_control_methods
		 * 
		 * Associative array of write-control
		 * methods and their respective hash
		 * lengths in bytes.
		 *
		 * @var mixed
		 * @access protected
		 */
		protected $_supported_write_control_methods = array(
			'adler32' => 8,
			'crc32'   => 8,
			'md5'     => 32
		);
		
		public function __construct(array $options = array()) {
			parent::__construct($options);
			
			if (is_null($this->_cache_directory)) {
				throw new \BadMethodCallException('No cache directory set!');
			}
			
			if (!is_dir($this->_cache_directory)) {
				mkdir($this->_cache_directory);
			}
		}
		
		public function store($data, $id, array $tags = array(), $specificLifeTime = false) {
			$filename    = $this->_cache_directory . $this->_filename($id) . '.dat';
			$tagfilename = substr($filename, 0, -4) . '.tag';
			$flags       = 0;
			
			if ($this->_write_control) {
				$data = hash($this->_write_control_method, $data) . $data;
			}
			
			// Write data to disk
			$wouldblock = null;
			$this->putFileContent($filename, $data, true, $wouldblock);

			if ($wouldblock) {
				$this->putFileContent($filename, $data);
			}

			$wouldblock = null;
			$this->putFileContent($tagfilename, implode("\n", $tags), true, $wouldblock);

			if ($wouldblock) {
				$this->putFileContent($tagfilename, implode("\n", $tags));
			}
			
			// Touch file to modify modification time
			$this->_touch($filename, time() + ($specificLifeTime ?: $this->_lifetime));
		}
		
		public function has($id) {
			$filename = $this->_cache_directory . $this->_filename($id) . '.dat';

			if (is_file($filename)) {
				if (time() > filemtime($filename)) {
					$this->remove($id);
					return false;
				} else {
					return true;
				}
			} else {
				return false;
			}
		}
		
		public function retrieve($id) {
			$filename = $this->_cache_directory . $this->_filename($id) . '.dat';
			
			if (!is_file($filename)) {
				return false;
			} else {
				if (time() > filemtime($filename)) {
					$this->remove($id);
					return false;
				}
			}
			
			$filedata = $this->getFileContent($filename);
			
			if ($this->_write_control) {
				$controlLength = $this->_supported_write_control_methods[$this->_write_control_method];
				
				$controlData = substr($filedata, 0, $controlLength);
				$filedata    = substr($filedata, $controlLength);
				
				// Check data for write errors
				if (hash($this->_write_control_method, $filedata) !== $controlData) {
					@unlink($filename);
					
					$tagsfile = substr($filename, 0, -4) . '.tag';
					if (is_file($tagsfile)) {
						@unlink($tagsfile);
					}
					
					return false;
				}
			}
			
			return $filedata;
		}
		
		public function remove($id) {
			$filename = $this->_cache_directory . $this->_filename($id) . '.dat';
			$tagsfile = substr($filename, 0, -4) . '.tag';
			
			if (is_file($filename)) {
				@unlink($filename);
			}
			
			if (is_file($tagsfile)) {
				@unlink($tagsfile);
			}
		}
		
		public function removeAll() {
			$flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_FILEINFO;
			$path  = $this->_cache_directory . $this->_filename_prefix . '*.dat';
			$glob  = new GlobIterator($path, $flags);
			
			foreach($glob as $entry) {
				$pathname = $entry->getPathname();
				@unlink($pathname);
				
				$tagPathname = substr($pathname, 0, -4) . '.tag';
				if (is_file($tagPathname)) {
					@unlink($tagPathname);
				}
			}
		}
		
		public function removeOld() {
			$flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_FILEINFO;
			$path  = $this->_cache_directory . $this->_filename_prefix . '*.dat';
			$glob  = new GlobIterator($path, $flags);
			$time  = time();
			
			foreach($glob as $entry) {
				try {
					$mtime = $entry->getMTime();
					if ($time > $mtime) {
						$pathname = $entry->getPathname();
						@unlink($pathname);
					
						$tagPathname = substr($pathname, 0, -4) . '.tag';
						if (is_file($tagPathname)) {
							@unlink($tagPathname);
						}
					}
				} catch(\Exception $e) {
					continue;
				}
			}
		}
		
		public function removeByTags(array $tags = array()) {
			$flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_FILEINFO;
			$path  = $this->_cache_directory . $this->_filename_prefix . '*.tag';
			$glob  = new GlobIterator($path, $flags);
			
			foreach($glob as $entry) {
				$pathname    = $entry->getPathname();
				$matchedTags = array_intersect($tags, explode("\n", $this->getFileContent($pathname)));
			
				if (count($matchedTags) === count($tags)) {
					@unlink($pathname);
					
					$dataPathname = substr($pathname, 0, -4) . '.dat';
					if (is_file($dataPathname)) {
						@unlink($dataPathname);
					}
				}
			}
		}
		
		public function removeByAnyTags(array $tags = array()) {
			$flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_FILEINFO;
			$path  = $this->_cache_directory . $this->_filename_prefix . '*.tag';
			$glob  = new GlobIterator($path, $flags);
			
			foreach($glob as $entry) {
				$pathname    = $entry->getPathname();
				$matchedTags = array_intersect($tags, explode("\n", $this->getFileContent($pathname)));
			
				if (!empty($matchedTags)) {
					@unlink($pathname);
					
					$dataPathname = substr($pathname, 0, -4) . '.dat';
					if (is_file($dataPathname)) {
						@unlink($dataPathname);
					}
				}
			}
		}
		
		public function removeByNotTags(array $tags = array()) {
			$flags = GlobIterator::SKIP_DOTS | GlobIterator::CURRENT_AS_FILEINFO;
			$path  = $this->_cache_directory . $this->_filename_prefix . '*.tag';
			$glob  = new GlobIterator($path, $flags);
			
			foreach($glob as $entry) {
				$pathname    = $entry->getPathname();
				$matchedTags = array_intersect($tags, explode("\n", $this->getFileContent($pathname)));
			
				if (empty($matchedTags)) {
					@unlink($pathname);
					
					$dataPathname = substr($pathname, 0, -4) . '.dat';
					if (is_file($dataPathname)) {
						@unlink($dataPathname);
					}
				}
			}
		}
		
		protected function _filename($id) {
			$filename = $this->_filename_prefix . md5($id);
			
			return $filename;
		}
		
		protected function _touch($file, $time = null) {
			if (is_file($file)) {
				touch($file, $time ?: time());
			}
		}
		
		public function setOptions(array $options = array()) {
			foreach($options as $k => $v) {
				switch($k) {
					case 'cache_directory':
						$this->_cache_directory = rtrim($v, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
						break;
						
					case 'lifetime':
						$this->_lifetime = (int) $v;
						break;
						
					case 'write_control':
						$this->_write_control = (boolean) $v;
						break;

					case 'lock_files':
						$this->_lock_files = (boolean) $v;
						break;
						
					case 'read_write_method':
						if (!in_array($v, $this->_supported_write_control_methods)) {
							throw new \InvalidArgumentException("Invalid write control method \"$v\".");
						} else {
							$this->_write_control_method = $v;
						}
						break;
						
					case 'filename_prefix':
						$this->_filename_prefix = $v;
						break;
						
					default:
						break;
				}
			}
		}

		protected function getFileContent($file, $nonBlocking = false, & $wouldblock = null) {
			$locking    = $this->_file_locking;
			$wouldblock = null;

			if ($locking) {
				$fp = @fopen($file, 'rb');
				if ($fp === false) {
					throw new CoreException\CacheErrorException("Error opening file '{$file}'");
				}

				if ($nonBlocking) {
					$lock = flock($fp, LOCK_SH | LOCK_NB, $wouldblock);
					if ($wouldblock) {
						fclose($fp);
						return false;
					}
				} else {
					$lock = flock($fp, LOCK_SH);
				}

				if (!$lock) {
					fclose($fp);
					throw new CoreException\CacheErrorException("Error locking file '{$file}'");
				}

				$res = stream_get_contents($fp);

				flock($fp, LOCK_UN);
				fclose($fp);

				if ($res === false) {
					throw new CoreException\CacheErrorException('Error getting stream contents');
				}

				return $res;
			} else {
				return file_get_contents($file, false);
			}
		}

		protected function putFileContent($file, $data, $nonBlocking = false, & $wouldblock = null) {
			$locking     = $this->_file_locking;
			$nonBlocking = $locking && $nonBlocking;
			$wouldblock  = null;
			$umask       = $this->_file_umask;
			$perm        = $this->_file_perm;

			if ($umask !== false && $perm !== false) {
				$perm = $perm & ~$umask;
			}

			if ($locking && $nonBlocking) {
				$umask = ($umask !== false) ? umask($umask) : false;

				$fp = fopen($file, 'cb');

				if ($umask) {
					umask($umask);
				}

				if (!$fp) {
					throw new CoreException\CacheErrorException("Error opening file '{$file}'");
				}

				if ($perm !== false && !chmod($file, $perm)) {
					fclose($fp);
					$oct = deoct($perm);
					throw new CoreException\CacheErrorException("chmod('{$file}', 0{$oct}) failed");
				}

				if (!flock($fp, LOCK_EX | LOCK_NB, $wouldblock)) {
					fclose($fp);
					if ($wouldblock) {
						return;
					} else {
						throw new CoreException\CacheErrorException("Error locking file '{$file}'");
					}
				}

				if (fwrite($fp, $data) === false) {
					flock($fp, LOCK_UN);
					fclose($fp);
					throw new CoreException\CacheErrorException("Error writing file '{$file}'");
				}

				if (!ftruncate($fp, strlen($data))) {
					flock($fp, LOCK_UN);
					fclose($fp);
					throw new CoreException\CacheErrorException("Error truncating file '{$file}'");
				}

				flock($fp, LOCK_UN);
				fclose($fp);
			} else {
				$flags = 0;
				if ($locking) {
					$flags = $flags | LOCK_EX;
				}

				$umask = ($umask !== false) ? umask($umask) : false;
				$rs    = file_put_contents($file, $data, $flags);

				if ($umask) {
					umask($umask);
				}

				if ($rs === false) {
					throw new CoreException\CacheErrorException("Error writing file '{$file}'");
				}

				if ($perm !== false && !chmod($file, $perm)) {
	                $oct = decoct($perm);
	                throw new CoreException\CacheErrorException("chmod('{$file}', 0{$oct}) failed");
	            }
			}
		}
	}