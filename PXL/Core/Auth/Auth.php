<?php
	namespace PXL\Core\Auth;

	use PXL\Core\Config;
	use PXL\Core\Session;
	use PXL\Core\Model\Entity;
	use BadMethodCallException;
	
	class Auth implements iAuth {
		
		protected static $_instance = null;
		
		protected $_identity      = null;
		protected $_adapter       = null;     
		protected $_identityClass = null;
		
		const AUTH_FIELD_CHECK           = '__AUTHENTICATED';
		const AUTH_PERSISTENT_COOKIE_KEY = 'PXL_AUTH_ID';
		
		protected function __construct() {
			if (!Config::has('auth.adapter.class')) {
				throw new BadMethodCallException('Missing configuration data for component auth.adapter.class');
			} else {
				$adapterClass = Config::read('auth.adapter.class');
			}
			
			if (!Config::has('auth.identityclass')) {
				throw new BadMethodCallException('Missing configuration data for component auth.identityclass');
			} else {
				$this->_identityClass = Config::read('auth.identityclass');
			}
			
			$this->_adapter = new $adapterClass();
			
			// Check if there is an identity present already
			try {
				$this->_identity = call_user_func(array($this->_identityClass, 'retrieve'));
			} catch(\Exception $e) {
				// Continue gracefully and remove identity again from storage
				$this->clearIdentity();
			}
			
			if ($this->_identity && $this->_identity->{static::AUTH_FIELD_CHECK} !== true) {
				$this->clearIdentity(false);
			}
		}
		
		protected function __clone() { }
		
		public static function getInstance() {
			if (is_null(static::$_instance)) {
				static::$_instance = new static();
			}
			
			return static::$_instance;
		}
		
		public function authenticate($identity, $credentials, $persist = false) {
			if (!is_null(($result = $this->_adapter->fetchIdentityData($identity, $credentials)))) {
				$result->put(static::AUTH_FIELD_CHECK, true);
				
				// Regenerate Session ID
				Session\Session::regenerate_id();
				
				$this->_identity = new $this->_identityClass($result->toAssocArray());

				if ($persist) {
					setcookie(self::AUTH_PERSISTENT_COOKIE_KEY, md5($this->_identity->id.$identity), strtotime('now +1 year'), '/', $_SERVER['HTTP_HOST'], true, true);
				}

				return $this->_identity;
			}
			
			return false;
		}

		public function setIdentity(Entity\iPersistentEntity $identity) {
			if ($this->hasIdentity()) {
				throw new BadMethodCallException('Cannot set identity; identity already present.');
			}

			// Regenerate Session ID
			Session\Session::regenerate_id();

			$this->_identity = $identity;
		}
		
		public function getIdentity() {
			return is_null($this->_identity) ? false : $this->_identity;
		}
		
		public function clearIdentity($clearPersistent = true) {
			call_user_func(array($this->_identityClass, 'unpersist'));
			$this->_identity = null;

			// Unset persistent cookie
			if ($clearPersistent && isset($_COOKIE[self::AUTH_PERSISTENT_COOKIE_KEY])) {
				unset($_COOKIE[self::AUTH_PERSISTENT_COOKIE_KEY]);
				setcookie(self::AUTH_PERSISTENT_COOKIE_KEY, '', time() - 3600, '/', $_SERVER['HTTP_HOST'], true, true);
			}

		}
		
		public function hasIdentity() {
			return !is_null($this->_identity);
		}
	}