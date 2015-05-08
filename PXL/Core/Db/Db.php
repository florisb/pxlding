<?php
	namespace PXL\Core\Db; 

	require_once('iDb.php');
	require_once('DbConnectionInfo.php');
	require_once('Statement.php');
	require_once('ResultMap.php');

	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'Config.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'Log', 'Logger.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'Exception', 'DbConnectionErrorException.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'Exception', 'DbQueryFailureException.php')));

	use PXL\Core\Log;
	use PXL\Core\Config;
	use PXL\Core\Exception;
	use PXL\Core\Collection as C;

	class Db implements iDb {

		const RESULT_READ_ONLY = '__PXL_READ_ONLY__';

		protected $_conn   = null;
		protected $_logger = null;

		protected $_last_affected_rows = null;
		protected $_last_num_rows      = null;

		public static $debug                     = false;
		protected static $_instances             = array();
		protected static $_defaultConnectionInfo = null;

		protected static $_resultMapClassName  = 'PXL\Core\Db\ResultMap';
		protected static $_resultListClassName = 'PXL\Core\Collection\SimpleList';

		protected function __construct(iDbConnectionInfo $dbConnectionInfo) {
			if (Config::has('db.log')) {
				self::$debug = true;
				Log\Logger::$enable_logging = true;
			}

			$this->_logger = Log\Logger::getInstance();

			// Create a new connection whilst supressing errors as we're handling them ourselves using exceptions
			$this->_conn = @new \mysqli(
				$dbConnectionInfo->getHost(),
				$dbConnectionInfo->getUsername(),
				$dbConnectionInfo->getPassword(),
				$dbConnectionInfo->getDatabase(),
				$dbConnectionInfo->getPort()
			);

			if ($this->_conn->connect_errno) {
				throw new Exception\DbConnectionErrorException("Failed to connect to MySQL: ({$this->_conn->connect_errno}) {$this->_conn->connect_error}");
			}

			// Set charset to UTF-8
			$this->_conn->set_charset('utf8');
		}

		protected function __clone() { }

		public static function getInstance($dbConnectionInfo = null) {
			if (empty(self::$_instances) && is_null($dbConnectionInfo)) {
				throw new Exception\DbConnectionErrorException('No active DB connection found');
			}

			if ($dbConnectionInfo) {
				if (!(self::$_instances instanceof C\Map)) self::$_instances = new C\SimpleMap();
				$connectionKey = $dbConnectionInfo->getIdentifier();
				if (!self::$_instances->containsKey($connectionKey)) {
					self::$_instances->put($connectionKey, new static($dbConnectionInfo));
				}

				if (self::$_instances->get($connectionKey) instanceof iDbConnectionInfo) {
					self::$_instances->put($connectionKey, new static($dbConnectionInfo));
				}

				return self::$_instances->get($connectionKey);
			} else {
				if (!count(self::$_instances)) {
					throw new Exception\DbConnectionErrorException('No active DB connection found');
				}

				if (is_null(self::$_defaultConnectionInfo)) {
					self::$_defaultConnectionInfo = array_peek(self::$_instances);
				}

				$identifier = self::$_defaultConnectionInfo->getIdentifier();
				if (!($connectionValue = self::$_instances->get($identifier))) {
					throw new Exception\DbConnectionInfo('Tried to fetch instance of non-existing connection');
				}
				if (!($connectionValue instanceof static)) {
					self::$_instances->put($identifier, new static($connectionValue));
				}

				return self::$_instances->get($identifier);
			}
		}

		public static function storeConnectionInfo(iDbConnectionInfo $dbConnectionInfo) {
			if (!(self::$_instances instanceof C\Map)) self::$_instances = new C\SimpleMap();

			self::$_instances->put($dbConnectionInfo->getIdentifier(), $dbConnectionInfo);
		}

		public static function setDefaultConnectionInfo(iDbConnectionInfo $dbConnectionInfo) {
			$identifier = $dbConnectionInfo->getIdentifier();

			if (!self::$_instances->containsKey($identifier)) {
				throw new Exception\DbConnectionErrorException('Tried to set non-existing default connection');
			}

			self::$_defaultConnectionInfo = $dbConnectionInfo;
		}

		public function getLastAffectedRows() {
			return $this->_last_affected_rows;
		}

		public function getLastNumRows() {
			return $this->_last_num_rows;
		}

		public function getConnection() {
			return $this->_conn;
		}

		public function row(Statement $statement, $class = null, $readOnly = false, $customParam = null) {
			$statement->setConnection($this);

			$query_start          = microtime(true);
			$result               = $statement->execute()->get_result();
			$this->_last_num_rows = $statement->getNumRows();

			if (self::$debug) {
				$query_time = microtime(true) - $query_start;
				$this->_logger->log_query((string) $statement, (boolean) $result, '', $statement);
				$this->_logger->log('> '.($query_time * 1000).' ms', 'green');
			}

			if (is_null($class) && ($resultData = $result->fetch_assoc())) {
				$resultMapClass = static::$_resultMapClassName;
				$resultData     = $resultMapClass::createFromArray($resultData);
			} else {
				$resultData = $result->fetch_object($class, $readOnly, $customParam);

				if (is_array($resultData) && $resultData === $result->getLastErroneousData()) {
					$resultData = null;
				}
			}

			$result->free();

			return $resultData;
		}

		public function matrix(Statement $statement, $class = null, $key = null, $value = null, $readOnly = false, $customParam = null) {
			$statement->setConnection($this);

			$query_start          = microtime(true);
			$result               = $statement->execute()->get_result();
			$this->_last_num_rows = $statement->getNumRows();

			if (self::$debug) {
				$query_time = microtime(true) - $query_start;
				$this->_logger->log_query($statement->show_sql(), (boolean) $result, '', $statement);
				$this->_logger->log('> '.($query_time * 1000).' ms', 'green');
			}

			$retClass = empty($key) ? new static::$_resultListClassName() : new static::$_resultMapClassName();
			$ret      = array();

			while($r = (is_null($class) ? $result->fetch_assoc() : $result->fetch_object($class, $readOnly, $customParam))) {
				if (is_array($r)) {
					// Check if the result couldn't be fetched as a valid object. If so, don't store the result in the definitive result set.
					if ($r === $result->getLastErroneousData()) {
						continue;
					}
					$r = C\FastMap::createFromArray($r);
				}

				if (!empty($key)) {
					$ret[$r->$key] = (empty($value) ? $r : $r->$value);
				} else {
					$ret[] = (empty($value) ? $r : $r->$value);
				}
			}

			$result->free();

			return $retClass::createFromArray($ret);
		}

		public function query(Statement $statement, $close_statement = false) {
			$statement->setConnection($this);

			$query_start = microtime(true);
			$success     = $statement->execute();

			if (self::$debug) {
				$query_time = microtime(true) - $query_start;
				$this->_logger->log_query($statement->show_sql(), (boolean) $success, '', $statement);
				$this->_logger->log('> '.($query_time * 1000).' ms', 'green');
			}

			$this->_last_num_rows      = $statement->getNumRows();
			$this->_last_affected_rows = $statement->getAffectedRows();

			if ($close_statement) {
				$statement->close();
			}

			return $success;
		}

		public function insert($table, $data, $debug = false) {
			$q = "
				INSERT INTO `%s`
					(%s)
				VALUES
					(:%s)
			";

			// Check data parameter type
			if (!is_array($data)) {
				if (!($data instanceof C\Map)) {
					throw new \InvalidArgumentException('Data to insert has to be an array or Collection\Map instance');
				}

				$fields = array_map(array($this, 'escape'), $data->keySet()->toArray());

				foreach($data as $k => $v) {
					$data->remove($k);
					$data->put(":$k", $v);
				}

			} else {
				$fields = array_map(array($this, 'escape'), array_keys($data));

				foreach($data as $k => $v) {
					$data[":$k"] = $v;
					unset($data[$k]);
				}
			}

			$q    = sprintf($q, $this->escape($table), '`'.implode('`,`', $fields).'`', implode(',:', $fields));
			$stmt = new Statement($q, $data);
			$stmt->setConnection($this);
			$query_start = microtime(true);
			$result = $stmt->execute();

			if (self::$debug) {
				$query_time = microtime(true) - $query_start;
				$this->_logger->log_query($stmt->show_sql(), (boolean) $result, '', $stmt);
				$this->_logger->log('> '.($query_time * 1000).' ms', 'green');
			}

			$this->_last_affected_rows = $stmt->getAffectedRows();
			$this->_last_insert_id     = $stmt->getInsertId();

			$stmt->close();

			return $this->_last_insert_id;
		}

		public function multi_insert($table, $data) {
			$q = "
				INSERT INTO `%s`
					(%s)
				VALUES
					(:%s)
			";

			// Check data parameter type
			if (!is_array($data)) {
				if (!($data instanceof C\Enumerated)) {
					throw new \InvalidArgumentException('Data to multi_insert has to be an array or Collection\Enumerated instance');
				}

				// Fetch the first entry in the $data array -- this entry is used to determine fieldnames
				$i = $data->getIterator();
				$i->rewind();
				$fields = $i->current();
				$fields = array_map(array($this, 'escape'), $fields->keySet()->toArray());
			} else {
				$keys   = array_keys($data);
				$fields = $data[$keys[0]];
				$fields = array_map(array($this, 'escape'), array_keys($fields));
			}


			$q = sprintf($q, $this->escape($table), '`'.implode('`,`', $fields).'`', implode(',:', $fields));

			// Initialize statement
			$stmt = new Statement($q);
			$stmt->setConnection($this);
			$this->_last_affected_rows = 0;
			foreach($data as $_data) {
				foreach($_data as $k => $v) {
					if (!is_array($data)) {
						$_data->remove($k);
						$_data->put(":$k", $v);
					} else {
						$_data[":$k"] = $v;
						unset($_data[$k]);
					}
				}

				$stmt->execute($_data);
				$this->_last_affected_rows += $stmt->getAffectedRows();
			}

			$stmt->close();

			return $this->_last_affected_rows;
		}

		public function truncate($table) {
			$q = "
				TRUNCATE TABLE %s
			";

			$q    = sprintf($q, $this->escape($table));
			$stmt = new Statement($q);
			$stmt->setConnection($this);
			$stmt->execute()
					 ->close();
		}

		public function update($table, $data, $where) {
			$q = "
				UPDATE `%s`
				SET %s
				WHERE %s
			";

			$updateStr = array();

			if (!is_array($data)) {
				if (!($data instanceof C\Map)) {
					throw new \InvalidArgumentException('Data to update has to be an array or Collection\Map instance');
				}

				foreach($data as $k => $v) {
					$k              = $this->escape($k);
					$updateStr[]    = "`$k`=:$k";
					$data->remove($k);
					$data->put(":$k", $v);
				}
			} else {
				foreach($data as $k => $v) {
					$k              = $this->escape($k);
					$updateStr[]    = "`$k`=:$k";
					$data[":$k"] = $v;
					unset($data[$k]);
				}
			}

			$q    = sprintf($q, $this->escape($table), implode(',', $updateStr), $where);
			$stmt = new Statement($q, $data);

			return $this->query($stmt, true);
		}

		public function delete($table, $where) {
			$q = "
				DELETE FROM `%s`
				WHERE %s
			";

			$q = sprintf($q, $this->escape($table), $where);

			return $this->query(new Statement($q), true);
		}

		public function listTables() {
			$q = "
				SHOW TABLES
			";

			$stmt = new Statement($q);
			$stmt->setConnection($this);
			$result = $stmt->execute()->get_result();

			$tables = new C\SimpleList();
			while($resultData = $result->fetch_assoc()) {
				$tables->add(array_peek(array_values($resultData)));
			}

			return $tables;
		}

		public function tableExists($table) {
			return ($this->listTables()->indexOf((string) $table) !== -1);
		}

		public function listFields($table) {
			if (!$this->tableExists($table)) {
				throw new \BadMethodCallException("Cannot list fields of table $table. Table does not exist.");
			}

			$q = "
				SHOW COLUMNS FROM `%s`
			";

			$q    = sprintf($q, $this->escape($table));
			$stmt = new Statement($q);
			$stmt->setConnection($this);

			$result = $stmt->execute()->get_result();
			$fields = new C\SimpleList();
			while($resultData = $result->fetch_assoc()) {
				$fields->add(array_peek(array_values($resultData)));
			}

			return $fields;
		}

		public function fieldExists($fieldName, $table) {
			return ($this->listFields($table)->indexOf($fieldName) !== -1);
		}

		public function begin() {
			$this->_conn->autocommit(false);
		}

		public function commit() {
			$this->_conn->commit();
			$this->_conn->autocommit(true);
		}

		public function rollback() {
			$this->_conn->rollback();
			$this->_conn->autocommit(true);
		}

		public function clear() {
			do {
				if ($result = $this->getConnection()->store_result()) {
					$result->free();
				}
			} while($this->getConnection()->next_result());

			return $this;
		}

		/**
		 * escape function.
		 *
		 * Alias of real_escape_string
		 *
		 * @access public
		 * @param mixed $str
		 * @return void
		 */
		public function escape($str) {
			return $this->getConnection()->real_escape_string($str);
		}
	}
