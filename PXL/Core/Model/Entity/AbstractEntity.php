<?php
	namespace PXL\Core\Model\Entity;
	
	require_once(dirname(__FILE__) . implode(DIRECTORY_SEPARATOR, array('..', '..', '..', 'JsonSerializable.php')));

	use Iterator;
	use DateTime;
	use SplSubject;
	use SplObserver;
	use PXL\Core\Db;
	use Serializable;
	use JsonSerializable;
	use PXL\Core\Exception;
	use PXL\Core\Model\Value;
	use PXL\Hornet\View\View;
	use BadMethodCallException;
	use PXL\Core\Model\Factory;
	use PXL\Core\Session\Session;
	use PXL\Hornet\Application\Application;

	/**
	 * Abstract
	 *
	 * Class that represents an single entity in the broadest sense of the word. This "entity"
	 * can be a user, order, address, etc. This class uses the table data gateway paradigm,
	 * where array keys correspond 1:1 with database fieldnames. Entities may be inserted,
	 * updated and deleted as well as iterated over.
	 *
	 * Objects may be serialized and unserialized safely without comprimising DB connectivity.
	 * Please note that object references are lost when serializing/unserializing. Therefore,
	 * it is always recommended that references to child entities within entities are made
	 * through unique identifiers instead of direct references to objects.
	 *
	 * @author     Max van der Stam <max@pixelindustries.com>
	 * @implements Iterator, Serializable, JsonSerializable, SplSubject
	 * @abstract
	 */
	abstract class AbstractEntity implements Iterator, Serializable, JsonSerializable, SplSubject, iPersistentEntity {

		/**
		 * isValid
		 * 
		 * Indicates whether this Entity is valid
		 * For example a Product without a Price would be set to isValid = false
		 * 
		 * @access public
		 */

		public $isValid = true;

		/**
		 * _data
		 *
		 * Associative array containing data. Fieldnames and values should
		 * be mapped 1:1 to database fieldnames and values.
		 *
		 * @access protected
		 */
		protected $_data = array();
		protected $_lastData = null;

		/**
		 * _checks
		 * 
		 * Associative array containing required field arrays that can be checked
		 * through magic _check[key]() calls
		 *
		 * @access protected
		 */

		protected $_checks = array();
		
		/**
		 * _db
		 *
		 * Instance of Db class used for database operations.
		 *
		 * @access protected
		 */
		public $_db = null;
		
		/**
		 * _position
		 *
		 * Current index of array pointer for the data
		 * array. Used in iterable logic.
		 *
		 * @access protected
		 */
		protected $_position = 0;
		
		/**
		 * _table
		 *
		 * Database table name where entities are stored.
		 * Used by extended classes.
		 *
		 * @access protected
		 */
		protected $_table;

		protected $_constructed      = false;
		protected $_pendingData      = array();
		protected $_read_only        = false;

		protected $_initErrors     = array();
		protected $_requiredFields = array();
		protected $_dbFields       = array();
		protected $_observers      = array();

		protected static $_persistentFields    = array();
		protected static $_persistentInstances = array();
		protected static $isPersistent         = false;
		protected static $_sessionKey          = null;

		/**
		 * _childEntities
		 * 
		 * Entities contained by this entity that aren't saved to the current
		 * entities' table
		 * 
		 * @access protected
		 */
		protected $_childEntities = array();
		
		public function __construct($data = null) {
			if (static::$isPersistent) {
				$className = get_called_class();

				if (array_key_exists($className, self::$_persistentInstances)) {
					throw new \BadMethodCallException("Tried to create $className instance, but an active instance was already found.");
				} else {
					self::$_persistentInstances[$className] = $this;
				}

				register_shutdown_function(array($this, 'persist'));
			}

			$this->_constructed = true;

			$this->_initDb();

			if ($data === Db\Db::RESULT_READ_ONLY) {
				$this->_read_only = true;
			}

			if(!is_array($data)) {
				$data = $this->_pendingData;
			}

			if (!empty($data)) {
				$this->_initData($data);
				$this->_pendingData = array();
			}
			
			if (!$this->_read_only) {
				$this->checkRequired();
				$this->_handleErrors();
			}

			$this->init();
		}

		protected function _initDb() {
			$this->_db = Db\Db::getInstance();
		}

		public function setMany($data, $checkRequired = true){
			$this->_initData($data);
			if($checkRequired === true){
				$this->checkRequired();
			}
			$this->_handleErrors();
			
			return $this;
		}

		public function attach(SplObserver $observer) {
			$key = spl_object_hash($observer);

			if (!array_key_exists($key, $this->_observers)) {
				$this->_observers[$key] = $observer;
				return true;
			} else {
				return false;
			}
		}

		public function detach(SplObserver $observer) {
			$key = spl_object_hash($observer);

			if (array_key_exists($key, $this->_observers)) {
				unset($this->_observers[$key]);
				return true;
			} else {
				return false;
			}
		}

		public function notify() {
			foreach($this->_observers as $observer) {
				$observer->update($this);
			}

			return $this;
		}

		public function checkRequired($requiredFields = null){
			foreach(!empty($requiredFields) ? $requiredFields : $this->_requiredFields as $fieldName) {
				if (is_array($fieldName)) {
					list($fieldName, $message) = $fieldName;
				} else {
					$message = null;
				}
				// Call fieldName to make sure we trigger magic gets
				if ((array_key_exists($fieldName, $this->_data) && is_string($this->_data[$fieldName]) && !strlen($this->_data[$fieldName])) || ((!array_key_exists($fieldName, $this->_data) && is_null($this->$fieldName)) && !array_key_exists($fieldName, $this->_initErrors))) {
					$this->_initErrors[$fieldName] = new Exception\ValueEmptyException($message);
				}
			}
		}

		protected function _handleErrors(){
			if (!empty($this->_initErrors)) {
				foreach($this->_initErrors as $k => $e) {
					switch(true) {
						case ($e instanceof Exception\ValueEmptyException):
							$message = $e->getMessage() ?: "$k required";
							break;
							
						case ($e instanceof Exception\ValueInvalidException):
							$message = $e->getMessage() ?: "$k invalid";
							break;
						
						case ($e instanceof Exception\DataErrorsException):
                            $message = $e->getErrors();
                            break;
						
						default:
							$message = false;
							break;
					}
					
					$this->_initErrors[$k] = $message ?: $e->getMessage();
				}
				
				throw new Exception\DataErrorsException(null, 0, $this->_initErrors, $this->_lastData);
			}
		}
		
		protected function _initData($data) {
			if (is_null($this->_lastData)) {
				$this->_lastData = $data;
			}

			if ($this->_read_only) {
				$this->_data = $data;
			} else {
				try {
					foreach($data as $k => $v) {
						unset($data[$k]);
						$this->$k = $v;
					}
				} catch(\Exception $e) {
					$this->_initErrors[$k] = $e;
					if (!empty($data)) {
						$this->_initData($data);
					}
				}
			}
		}

		protected function init() { }
		
		/**
		 * __set
		 *
		 * Setter method. Overrides any previous set value.
		 *
		 * @param string $key   Fieldname to set
		 * @param mixed  $value Value to set
		 */
		public function __set($key, $value) {
			if (!$this->_constructed) {
				$this->_pendingData[$key] = $value;
				return;
			}

			// Normalize unix newlines
			$value = $this->_unixNewlines($value);

			if(is_string($value)) {
				$value = trim($value);
			}

			// Search for magic set method
			$methodKey  = str_replace(' ','',ucwords(str_replace('_',' ',$key)));
			$methodName = '_set'.$methodKey;

			if(method_exists($this, $methodName)){
				$value = $this->{$methodName}($value);
			}
			
			$this->_data[$key] = $value;
		}
		
		/**
		 * __get
		 *
		 * Getter method. Returns NULL if array key doesn't
		 * exists.
		 *
		 * @param string $key Fieldname to get
		 */
		public function __get($key) {
			if (!array_key_exists($key, $this->_data) || is_null($this->_data[$key])) {
				// Search for magic get method
				$methodKey  = str_replace(' ','',ucwords(str_replace('_',' ',$key)));
				$methodName = '_get'.$methodKey;

				if(method_exists($this,$methodName)){
					$value = $this->{$methodName}();
				}else{
					$value = null;
				}
			} else {
				$value = $this->_data[$key];
			}
			
			return $value;
		}
		
		public function __unset($key) {
			if (array_key_exists($key, $this->_data)) {
				unset($this->_data[$key]);
			}
		}
		
		public function __isset($key) {
			return array_key_exists($key, $this->_data);
		}
		
		/**
		 * save
		 *
		 * Saves the entity to the database, creating a new row
		 * if it's a new entity and updating an existing row if
		 * it's an existing entity.
		 *
		 */
		public function save() {
			if (empty($this->_table)) {
				return false;
			}

			if ($this->_read_only) {
				throw new BadMethodCallException('Entity is read-only');
			}
		
			$data = array();
			
			foreach($this->_dbFields as $field) {
				if (is_null($this->$field) && !array_key_exists($field, $this->_data)) {
					continue;
				}
				
				$value = $this->$field;
				
				switch(true) {
					case ($value instanceof AbstractEntity):
						$value->save();
						$data[$field] = $value->id;
						break;
						
					case ($value instanceof Value\AbstractValue):
						$data[$field] = $value->toStorage();
						break;
						
					default:
						$data[$field] = $value;
						break;
				}
			}
			
			$data = $this->preSave($data);
			
			if (empty($data)) {
				return false;
			} else {
				if (empty($this->id)) {
					$result = $this->_insertEntity($this->preInsert($data));
					$this->postInsert();
				} else {
					$this->_updateEntity($this->preUpdate($data));
					$this->postUpdate();

					$result = null;
				}
			}

			$this->postSave();

			return $result;
		}
		
		protected function preInsert($data){
			return $data;
		}

		protected function preUpdate($data) {
			return $data;
		}

		protected function preSave($data) {
			$this->notify();

			return $data;
		}
		
		protected function postSave(){
			$this->_lastData = $this->_data;
			$this->notify();
		}

		protected function postInsert() { }

		protected function postUpdate() { }

		public function __call($method, $params){
			// Magic check methods
			if(stristr($method, 'check', true) === ''){
				$checkKey    = substr($method, 5);
				$checkKey[0] = strtolower($checkKey[0]);
				if(!empty($this->_checks[$checkKey])){
					$this->_initErrors = array();
					$this->checkRequired($this->_checks[$checkKey]);
					$this->_handleErrors();
				}
			}
		}
		
		/**
		 * delete
		 *
		 * Deletes the entity from the database. After this method has been executed,
		 * the current object should be disposed from.
		 *
		 */
		public function delete() {
			if (empty($this->_table)) {
				return false;
			} else {
				return (empty($this->_data['id'])) ? false : $this->_deleteEntity();
			}
		}

		/**
		 * getLastData
		 * 
		 * @return array Array of last known data
		 */
		public function getLastData($field = null) {
			return is_null($field) ? $this->_lastData : $this->_lastData[$field];
		}
		
		protected function _insertEntity(array $data) {
			$this->_data['id'] = $this->_db->insert($this->_table, $data);

			return $this->_data['id'];
		}
		
		protected function _updateEntity(array $data) {	
			$where = "
				`id`='%d'
			";
			
			$where = sprintf($where, $this->_data['id']);

			//Update row
			$this->_db->update($this->_table, $data, $where);
			
			return true;
		}
		
		protected function _deleteEntity() {
			//Determine ID
			$id = (int) $this->_data['id'];
			
			if (empty($id)) {
				return false;
			}
			
			$where = "
				`id`='%d'
			";
			
			$where = sprintf($where, $id);
			
			//Run delete query
			$this->_db->delete($this->_table, $where);
			
			//Empty array
			$this->_data = array();
			
			return true;
		}
		
		protected function route($controller = null, $action = null, array $params = array(), $routeName = null) {
			return Application::getInstance()->getView()->route($controller, $action, $params, $routeName);
		}
		
		protected function factory($factoryName) {
			return Factory\AbstractFactory::getInstanceByName($factoryName);
		}

		protected function decorator($decoratorName, Traversable $data) {
			return \PXL\Core\Model\Decorator\AbstractDecorator::getInstanceByName($decoratorName, $data);
		}

		protected function debug($message) {
			\PXL\Core\Tools\Logger::debug($message);
		}
		
		protected function _unixNewlines($data) {
			if(is_string($data)){
				return str_replace(array("\r\n", "\r"), "\n", $data);
			}
			
			return $data;
		}
		
		/**
		 * Iterator methods
		 */
		
		public function current() {
			return array_peek($this->_data, $this->_position);
		}
		
		public function key() {
			$fieldNames = array_keys($this->_data);
			
			return array_peek($fieldNames, $this->_position);
		}
		
		public function next() {
			$this->_position++;
		}
		
		public function rewind() {
			$this->_position = 0;
		}
		
		public function valid() {
			$values = array_values($this->_data);
			
			return (($this->_position + 1) <= count($values));
		}
		
		public function toArray() {
			$data = $this->_data;
			
			foreach($data as &$v) {
				switch(true) {
					case ($v instanceof Value\AbstractValue):
						$v = $v->jsonSerialize();
						break;
						
					case ($v instanceof self):
						$v = $v->toArray();
						break;

					case ($v instanceof DateTime):
						$v = $v->format(DateTime::ISO8601);
						break;
						
					default:
						break;
				}
			}
			unset($v);
			
			return $data;
		}
		
		/**
		 * Serializable methods
		 */
		public function serialize() {
			return serialize(array($this->_data, $this->_meta)); //Serialize and return data
		}
		
		public function unserialize($data) {
			if (($data = unserialize($data)) && count($data) === 2) {
				list($this->_data, $this->_meta) = $data;
			}
			
			$this->_db                       = Db\Db::getInstance();   //Re-open connection
			$this->_constructed              = true;
		}

		public function jsonSerialize() {
			return $this->toArray();
		}

		public function simulatePersistency() {
			$this->unpersist();

			return static::_rebuild(new static(), static::_prepareForSession($this));
		}

		protected static function _prepareForSession($instance) {
			$data = array();
			
			foreach(static::$_persistentFields as $field) {
				$data[$field] = $instance->$field;
			}
			
			return $data;
		}

		protected static function _rebuild($instance, $data) {
			foreach(static::$_persistentFields as $field) {
				if (is_null($data[$field])) continue;
				$instance->$field = $data[$field];
			}

			return $instance;
		}

		public static function retrieve() {
			if (!static::$isPersistent) {
				throw new \BadMethodCallException('AbstractEntity::retrieve() is only available on persistent entities');
			}

			$className = get_called_class();

			if (!array_key_exists($className, self::$_persistentInstances)) {
				if(!is_null($sessionData = Session::get(self::_getSessionKey()))) {
					// Rebuild instance from session
					self::$_persistentInstances[$className] = static::_rebuild(new static(), $sessionData);
				} else {
					// Create a fresh instance
					self::$_persistentInstances[$className] = new static();
				}
			}
			
			return self::$_persistentInstances[$className];
		}

		public static function unpersist() {
			if (!static::$isPersistent) {
				throw new \BadMethodCallException('AbstractEntity::unpersist() is only available on persistent entities');
			}

			unset(self::$_persistentInstances[get_called_class()]);
			Session::delete(self::_getSessionKey());
		}

		protected static function _getSessionKey() {
			return static::$_sessionKey ?: '_entity_persistent_' . get_called_class();
		}

		public function persist() {
			if (!static::$isPersistent) {
				throw new \BadMethodCallException('AbstractEntity::persist() is only available on persistent entities');
			}

			$className = get_called_class();
			
			if (array_key_exists($className, self::$_persistentInstances)) {
				Session::set(self::_getSessionKey(), static::_prepareForSession(self::$_persistentInstances[$className]));
				unset(self::$_persistentInstances[$className]);
			}
		}
	}