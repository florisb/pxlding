<?php
	namespace PXL\Core\Model\Value;

	use BadMethodCallException;
	
	/**
	 * File class.
	 * 
	 * Offers the programmer a abstraction layer for
	 * handling files in a uniform and sensible way.
	 *
	 * @extends AbstractValue
	 * @author  Max van der Stam <max@pixelindustries.com>
	 */
	class File extends AbstractValue {
		
		protected $_validMimetypes = array();
		
		protected $_mediatype = null;
		protected $_filename  = null;
		protected $_tmpName   = null;
		
		public function __construct($value, $mediaType = null, $filename = null, $tmpName = null) {
			$this->_tmpName   = $tmpName;
			$this->_mediatype = $mediaType;
			$this->_filename  = sanitizeFilename($filename);
			
			return parent::__construct($value);
		}
		
		protected function _checkValue($value) {
			$fp = @fopen($value, 'r');
			
			if (!$fp) {
				return false;
			}
			
			if (is_null($this->_mediatype)) {
				$meta             = stream_get_meta_data($fp);
				$this->_mediatype = $meta['mediatype'];
			}

			return (empty($this->_validMimetypes) || array_key_exists($this->_mediatype, $this->_validMimetypes));
		}
		
		protected function _formatValue($value) {
			return fopen($value, 'r');
		}
		
		public function toStorage() {
			throw new BadMethodCallException('Files cannot be stored in DB directly.');
		}
		
		public function getExtension() {
			return $this->_validMimetypes[$this->_mediatype];
		}
		
		public function getFilename() {
			return $this->_filename;
		}
		
		public function getFilesize() {
			return strlen(stream_get_contents($this->value));
		}
		
		public function getMediatype() {
			return $this->_mediatype;
		}

		public function output($filename = null) {
			header("Content-type: {$this->_mediatype}");
			header("Content-length: {$this->getFilesize()}");
			header("Cache-Control: no-cache, must-revalidate");
			header("Pragma: no-cache");
			header('Content-Transfer-Encoding: binary');
			header("Content-Disposition: attachment; filename= ".$filename ?: $this->getFilename());

			ob_end_clean();
			fpassthru($this->value);
		}
		
		/**
		 * __get function.
		 * 
		 * @access public
		 * @param mixed $key
		 * @return void
		 */
		public function __get($key) {
			switch($key) {
				case 'value':
					rewind($this->_value); // Make sure to rewind the file handle every time it is requested
					return $this->_value;
					
				case 'tmp_name':
					rewind($this->_value);
					return $this->_tmpName;
					break;
					
				default:
					break;
			}
		}

		/**
		 * jsonSerialize function
		 *
		 * Returns the current file as a RFC 2397 data-url.
		 */
		public function jsonSerialize() {
			return (string) $this;
		}
		
		/**
		 * __toString function.
		 * 
		 * Returns the current file as a RFC 2397 data-url.
		 *
		 * @access public
		 * @return void
		 */
		public function __toString() {
			return sprintf('data:%s;base64,%s', $this->_mediatype, base64_encode(stream_get_contents($this->value)));
		}
		
		/**
		 * getUniqueId function.
		 * 
		 * Returns an unique identifier based on the contents
		 * of the file.
		 *
		 * @access public
		 * @return void
		 */
		public function getUniqueId() {
			return md5(stream_get_contents($this->value));
		}
	}