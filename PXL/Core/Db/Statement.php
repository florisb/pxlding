<?php
	namespace PXL\Core\Db;
	
	require_once('Result.php');
	require_once('iStatement.php');
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'Collection', 'SimpleMap.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'Collection', 'SimpleList.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'Exception', 'DbQueryFailureException.php')));
	
	use PXL\Core\Exception  as E;
	use PXL\Core\Db\Result  as Result;
	use PXL\Core\Collection as C;
	
	/**
	 * Statement class.
	 * 
	 * @author     Max van der Stam <max@pixelindustries.com>
	 * @implements iStatement
	 */
	class Statement implements iStatement {
		
		protected $_statement  = null;
		protected $_values     = array();
		protected $_valueTypes = array();
		protected $_sql        = null;
		protected $_parsedSql  = null;
		protected $_conn       = null;
		
		protected $_last_result = null;
		
		public function __construct($sql, $variables = null) {
			$this->_sql  = $this->_parsedSql = $sql;
			
			if (!is_null($variables)) {
				$this->_initParameters($variables);
			}
		}
		
		public function __get($key) {
			switch($key) {
				case 'statement':
					$result = $this->_statement;
					break;
					
				default:
					throw new \BadMethodCallException("Property $key does not exist.");
					break;
			}
			
			return $result;
		}
		
		public function getAffectedRows() {
			try {
				return $this->_statement->affected_rows;
			} catch(\Exception $e) {
				return null;
			}
		}
		
		public function getNumRows() {
			try {
				return $this->_statement->num_rows;
			} catch(\Exception $e) {
				return null;
			}
		}

		public function setConnection(Db $conn) {
			$this->_conn = $conn->getConnection();
		}
		
		public function getInsertId() {
			return $this->_conn->insert_id;
		}
		
		public function execute($newValues = null, Db $conn = null) {
			if (!is_null($newValues)) {
				$this->_initParameters($newValues);
			}

			if (is_null($conn)) {
				if (is_null($this->_conn)) $this->_conn = Db::getInstance()->getConnection(); //only use default connection if no previous connection has been set
			} else {
				$this->_conn = $conn->getConnection();
			}
			
			if (empty($this->_statement)) {
				$this->_createStatement();
			}
		
			// Execute statement
			$this->_statement->execute();
			
			// Store result set in advance
			if (!method_exists($this->_statement, 'get_result')) {
				$this->_statement->store_result();
			}
			
			// If errors occurred, throw an exception instead of failing silently
			if ($this->_statement->errno) {
				throw new E\DbQueryFailureException("Query failed: (" . $this->_statement->errno . ") " . $this->_statement->error . " Query: " . (string) $this);
			}
			
			return $this;
		}
		
		public function get_result() {			
			$this->_last_result = new Result($this);
			
			return $this->_last_result;
		}

		public function close() {
			if (!empty($this->_statement)) {
				$this->_statement->close();
			}
		}
		
		public function show_sql() {
			$variables = $this->_values;
			$types     = $this->_valueTypes;
			$i         = 0;
			
			// Build query using the values and value types in the current statement object
			$sql = preg_replace_callback('#(?<!\?)\?(?!\?)#', function($matches) use ($variables, $types, &$i) {
				$i++;
				
				$escapedValue = pxl_db_safe(array_peek($variables, $i));
				
				switch(array_peek($types, $i - 1)) {
					case 'd':
						return (double) $escapedValue;
						break;
						
					case 'i':
						return (int) $escapedValue;
						break;
						
					default:
						return "'$escapedValue'";
						break;
				}
				
			}, $this->_parsedSql);
			$sql = str_replace("??", "?", $sql);
			
			return $sql;
		}

		public function __toString() {
			return $this->show_sql();
		}
		
		protected function _initParameters($values) {
			if (!is_array($values)) {
				if ($values instanceof C\Map) {
					$assoc = true;
				}	elseif ($values instanceof C\Enumerated) {
					$assoc = false;
				} else {
					throw new \InvalidArgumentException('Statement parameters must be passed as an array, Collection\Map instance or Collection\Enumerated instance.');
				}
			} else {
				$assoc = (bool)count(array_filter(array_keys($values), 'is_string'));
			}
			
			if ($assoc) {
				$newValues = $this->_parseNamedParameters($this->_sql, $values);
			} else {
				$newValues = $this->_parseUnnamedParameters($this->_sql, $values);
			}
				
			foreach($newValues as $k => $v) {
				$this->_values[$k] = $v;
			}
		}
		
		protected function _parseNamedParameters($sql, $variables) {
			// Replace named parameters with ?-characters since MySQLi doesn't support named parameters out of the box
			$values     = array();
			$valueTypes = array();

			$sql = preg_replace_callback('#(:[a-z_]{1}[a-z_0-9]*)#i', function($matches) use (&$values, &$valueTypes, $variables) {
				if (is_array($variables)) {
					$value = array_key_exists($matches[0], $variables) ? $variables[$matches[0]] : '';
				} else {
					$value = $variables->containsKey($matches[0]) ? $variables->get($matches[0]) : '';
				}
				
				if (is_array($value)) {
					list($value, $type) = $value;
				} else {
					$type = 's'; // Assume default type string
				}
				
				if ($value instanceof C\SimpleList) {
					$values     = array_merge($values, $value->toArray());
					$valueTypes = array_merge($valueTypes, array_fill(0, count($value), $type));
					
					return implode(',', array_fill(0, count($value), '?'));
				} else {
					$values[]     = $value;
					$valueTypes[] = $type;
					
					return '?';
				}
			}, $sql);
			
			$this->_valueTypes = $valueTypes;
		
			$valueTypes = implode('', $valueTypes);
			array_unshift($values, $valueTypes);
			
			$this->_parsedSql = $sql;
			
			return $values;
		}
		
		protected function _parseUnnamedParameters($sql, $variables) {
			$values     = array();
			$valueTypes = array();
			$i          = 0;
			
			$sql = preg_replace_callback('#(?<!\?)\?(?!\?)#', function($matches) use (&$values, &$valueTypes, $variables, &$i) {
				$value = array_peek($variables, $i);
				$i++;
				
				if (is_array($value)) {
					list($value, $type) = $value;
				} else {
					$type = 's'; // Assume default type string
				}
				
				if ($value instanceof C\SimpleList) {
					$values     = array_merge($values, $value->toArray());
					$valueTypes = array_merge($valueTypes, array_fill(0, count($value), $type));
					
					return implode(',', array_fill(0, count($value), '?'));
				} else {
					$values[]     = $value;
					$valueTypes[] = $type;
					
					return '?';
				}
			}, $sql);
			$sql = str_replace("??", "?", $sql);
			
			$this->_valueTypes = $valueTypes;
			
			$valueTypes = implode('', $valueTypes);
			array_unshift($values, $valueTypes);
			
			$this->_parsedSql  = $sql;
			
			return $values;
		}
		
		protected function _createStatement() {
			// Prepare SQL statement
			$this->_parsedSql = str_replace("??", "?", $this->_parsedSql);
			$this->_statement = $this->_conn->prepare($this->_parsedSql);
			
			if ($this->_statement === false) {
				throw new E\DbQueryFailureException("Prepare failed: (" . $this->_conn->errno . ") " . $this->_conn->error . " Query: " . (string) $this);
			}
			
			if (!empty($this->_values)) {
				$values = array();
				foreach($this->_values as $k => $v) {
					$values[$k] = &$this->_values[$k];
				}
			
				// Make sure we can catch thrown errors
				$stmt = $this->_statement;
				set_error_handler(function($errno, $errString) use ($stmt) {
					throw new E\DbQueryFailureException("Binding parameters failed: (" . $stmt->errno . ") " . $stmt->error . " Query: " . (string) $this);
				}, E_WARNING);
			
				// Bind parameters to placeholders
				call_user_func_array(array($this->_statement, 'bind_param'), $values);
				
				// Restore error handler
				restore_error_handler();
			}
		}
	}