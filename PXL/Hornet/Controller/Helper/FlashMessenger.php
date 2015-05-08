<?php
	namespace PXL\Hornet\Controller\Helper;

	use PXL\Core\Collection;
	use BadMethodCallException;
	use PXL\Core\Session\Session;
	
	/**
	 * FlashMessenger class.
	 *
	 * Controller helper that is used to persist simple messages
	 * across HTTP requests. The session *must* be started before
	 * using this helper.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	class FlashMessenger {
		
		protected static $_instance = null;
		
		const FLASHMESSENGER_SESSION_KEY       = '_PXL_FLASHMESSENGER_';
		const FLASHMESSENGER_DEFAULT_NAMESPACE = 'default';
		
		protected $_currentMessages  = null;
		protected $_previousMessages = null;
		protected $_currentNamespace = null;
		
		/**
		 * getInstance function.
		 * 
		 * Retrieves the current instance of FlashMessenger,
		 * creating a new instance if there isn't an active
		 * instance yet.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		public static function getInstance() {
			if (is_null(self::$_instance)) {
				self::$_instance = new static();
			}
			
			return self::$_instance;
		}
		
		protected function __construct() {
			// Check if we can use the session
			if ((function_exists('session_status') && session_status() === PHP_SESSION_NONE) || (session_id() === '')) {
				throw new BadMethodCallException('Session must be started before using FlashMessenger');
			}
		
			// Initialize messages from previous request
			if (Session::has(self::FLASHMESSENGER_SESSION_KEY)) {
				$this->_previousMessages = Collection\SimpleMap::createFromArray(Session::get(self::FLASHMESSENGER_SESSION_KEY));
			} else {
				$this->_previousMessages = new Collection\SimpleMap();
			}
			
			// Initialize current messages
			$this->_currentMessages = new Collection\SimpleMap();
			
			// Reset namespace to default namespace
			$this->resetNamespace();
			
			// Make sure the messages are written to session before script shuts down
			register_shutdown_function(array($this, 'writeToSession'));
		}
		
		protected function _clone() { }
		
		public function setNamespace($namespace = 'default') {
			$this->_currentNamespace = $namespace;
			
			return $this;
		}
		
		/**
		 * getNamespace function.
		 * 
		 * Returns the current used namespace.
		 *
		 * @access public
		 * @return string
		 */
		public function getNamespace() {
			return $this->_currentNamespace;
		}
		
		/**
		 * resetNamespace function.
		 * 
		 * Resets the namespace to its default value.
		 * This method returns the current FlashMessenger instance.
		 *
		 * @access public
		 * @return instance
		 */
		public function resetNamespace() {
			$this->_currentNamespace = self::FLASHMESSENGER_DEFAULT_NAMESPACE;
			
			return $this;
		}
		
		/**
		 * hasMessages function.
		 *
		 * @access public
		 * @param mixed $namespace (default: null)
		 * @return bool
		 */
		public function hasMessages($namespace = null) {
			return (boolean) count($this->_prevMessageList($namespace ?: $this->getNamespace()));
		}
		
		/**
		 * getMessages function.
		 * 
		 * @access public
		 * @param mixed $namespace (default: null)
		 * @return array
		 */
		public function getMessages($namespace = null) {
			return $this->_prevMessageList($namespace ?: $this->getNamespace());
		}
		
		/**
		 * clearMessages function.
		 * 
		 * @access public
		 * @param mixed $namespace (default: null)
		 * @return instance
		 */
		public function clearMessages($namespace = null) {
			if ($this->_previousMessages->containsKey($namespace)) {
				$this->_previousMessages->remove($namespace);
			}
			
			return $this;
		}
		
		/**
		 * addMessage function.
		 * 
		 *
		 * @access public
		 * @param mixed $message
		 * @param mixed $namespace (default: null)
		 * @return instance
		 */
		public function addMessage($message, $namespace = null) {
			$namespace         = $namespace ?: $this->getNamespace();
			$currMessageList   = $this->_currMessageList($namespace);
			$currMessageList[] = $message;
			
			$this->_currentMessages->put($namespace, $currMessageList);
			
			return $this;
		}
		
		/**
		 * hasCurrentMessages function.
		 * 
		 * @access public
		 * @param mixed $namespace (default: null)
		 * @return 
		 */
		public function hasCurrentMessages($namespace = null) {
			return (boolean) count($this->_currMessageList($namespace ?: $this->getNamespace()));
		}
		
		/**
		 * getCurrentMessages function.
		 * 
		 * @access public
		 * @param mixed $namespace (default: null)
		 * @return array
		 */
		public function getCurrentMessages($namespace = null) {
			return $this->_currMessageList($namespace ?: $this->getNamespace());
		}
		
		/**
		 * clearCurrentMessages function.
		 * 
		 * @access public
		 * @param mixed $namespace (default: null)
		 * @return void
		 */
		public function clearCurrentMessages($namespace = null) {
			if ($this->_currentMessages->containsKey($namespace)) {
				$this->_currentMessages->remove($namespace);
			}
			
			return $this;
		}
		
		/**
		 * writeToSession function.
		 * 
		 * Writes the current message data to the session. This
		 * method is called automatically on script shutdown
		 * and should *not* be called manually.
		 *
		 * @access public
		 * @return void
		 */
		public function writeToSession() {
			Session::set(self::FLASHMESSENGER_SESSION_KEY, $this->_currentMessages->toAssocArray());
		}

		public function getAllCurrentMessages() {
			return $this->_currentMessages->toAssocArray();
		}

		public function getAllPreviousMessages() {
			return $this->_previousMessages->toAssocArray();
		}
		
		/**
		 * _prevMessageList function.
		 *
		 * @access protected
		 * @param mixed $namespace
		 * @return array
		 */
		protected function _prevMessageList($namespace) {
			if (!$this->_previousMessages->containsKey($namespace)) {
				return array();
			} else {
				return $this->_previousMessages->get($namespace);
			}
		}
		
		/**
		 * _currMessageList function.
		 * 
		 * @access protected
		 * @param mixed $namespace
		 * @return array
		 */
		protected function _currMessageList($namespace) {
			if (!$this->_currentMessages->containsKey($namespace)) {
				return array();
			} else {
				return $this->_currentMessages->get($namespace);
			}
		}
	}