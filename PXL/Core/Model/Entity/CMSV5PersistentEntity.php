<?php
	namespace PXL\Core\Model\Entity;
	
	use PXL\Core\Session\Session;
	
	abstract class CMSV5PersistentEntity extends CMSV5Entity implements iPersistentEntity {
	
		/**
		 * _instances
		 * 
		 * Array that stores all persistent entities
		 * 
		 * @access protected
		 */
		protected static $_instances        = array();
		
		protected static $_sessionKey       = null;
		protected static $_persistentFields = array();

		protected $_constructed = true;

		public function __construct($data = null) {
			$className = get_called_class();

			register_shutdown_function(array($this, 'persist'));

			if (array_key_exists($className, self::$_instances)) {
				throw new \BadMethodCallException("Tried to create $className instance, but an active instance was already found.");
			} else {
				self::$_instances[$className] = $this;
			}
			
			parent::__construct($data);
		}

		/**
		 * _prepareForSession
		 * 
		 * Function that prepares entity for storage in session
		 * 
		 * @access protected
		 */
		protected static function _prepareForSession($instance) {
			$data = array();
			
			foreach(static::$_persistentFields as $field) {
				$data[$field] = $instance->$field;
			}
			
			return $data;
		}

		/**
		 * _rebuild
		 * 
		 * Function that takes session data to rebuild the entity
		 * 
		 * @access protected
		 */
		protected static function _rebuild($instance,$data) {
			foreach(static::$_persistentFields as $field) {
				$instance->$field = $data[$field];
			}
			
			return $instance;
		}

		protected function __clone(){}
		
		public static function retrieve() {
			$className = get_called_class();

			if (!array_key_exists($className, self::$_instances)) {
				if(!is_null($sessionData = Session::get(self::_getSessionKey()))){
					// Rebuild instance from session
					self::$_instances[$className] = static::_rebuild(new static(),$sessionData);
				}else{
					// Create a fresh instance
					self::$_instances[$className] = new static();
				}
			}
			
			return self::$_instances[$className];
		}
		
		public static function unpersist() {
			unset(self::$_instances[get_called_class()]);
			Session::delete(self::_getSessionKey());
		}
		
		protected static function _getSessionKey() {
			return static::$_sessionKey ?: '_entity_persistent_' . get_called_class();
		}

		public function persist(){
			$className = get_called_class();
			
			if (array_key_exists($className, self::$_instances)) {
				Session::set(self::_getSessionKey(), static::_prepareForSession(self::$_instances[$className]));
				unset(self::$_instances[$className]);
			}
		}
	}
