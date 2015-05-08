<?php
	namespace PXL\Core\Log;
	
	class Logger {
		
		protected $LOG = '';
		protected $query_counter = 0;
		
		protected static $_duplicateQueryBuffer = array();
		protected static $_similarQueryBuffer   = array();
		
		protected static $_instance   = null;
		public static $enable_logging = false;
		
		public static function getInstance() {
			if (is_null(self::$_instance)) {
				self::$_instance = new static();
			}
			
			return self::$_instance;
		}
		
		protected function __construct() { }
		protected function __clone() { }
		
		public function __destruct() {
			if (self::$enable_logging) {
				if (getenv('SERVER_TYPE') == 'JSONRPC') {
					file_put_contents(APPLICATION_PATH . 'debug.log', html_entity_decode(strip_tags(str_replace("<div style='clear: both;", "\n<div style='clear: both;", $this->show_log()))) . "\n-----------------------\n", FILE_APPEND);
				} else {
					if (!(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest')) {
						echo "<div style='background: #fff;'>";
						echo $this->show_log();
						echo "</div>";
					}
				}
			}
		}

		public function log_query($q, $s, $note = '', $statement) {
			$this->query_counter++;
			//$q      = preg_replace("#(?:[0-9 ,]{100,})#", " ... ", $q);
			$syntax = preg_split('#[\s]+#', trim($q));
			
			$select_query = strtolower($syntax[0]) == 'select' || strtolower($syntax[0]) == 'show';
			$delete_query = strtolower($syntax[0]) == 'delete';
			$insert_query = strtolower($syntax[0]) == 'insert';
			
			$syntax = array_map(array($this, 'color_sql'), $syntax);
			$syntax = implode(' ', $syntax);
			
			
			// Check duplicates
			$queryHash = md5($q);
			if (in_array($queryHash, self::$_duplicateQueryBuffer)) {
				$note .= '<span style="font-weight: bold; color: red;">DUPLICATE</span>';
			} else {
				self::$_duplicateQueryBuffer[] = $queryHash;
			}
			
			$this->log($syntax.(!empty($note) ? " <span style='color: #aaa;'>(".$note.")</span>" : ""), 'black', false);
			
			/*
			$stacks = debug_backtrace();
			$last = null;
			$stacklogs = array();
			
			foreach ($stacks as $stack) {
				$file = explode('/', $stack['file']);
				$file = implode('/', array_slice($file, -2));
				
				if ($stack['class'] == 'Logger' || $stack['class'] == 'Database') {
					$last = $stack;
					continue;
				}
				if ($file == 'framework/Templater.php' || $file == '') {
					break;
				}
				if (isset($last)) {
					$stacklogs[] = $last;
					unset($last);
				}
				$stacklogs[] = $stack;
			}
			
			foreach ($stacklogs as $stack) {
				$file = explode('/', $stack['file']);
				$file = implode('/', array_slice($file, -2));
				$this->log("> in ".$file.' ('.$stack['line'].') calling '.($stack['class'] != '' ? $stack['class'].'::' : '').$stack['function'].'()', '#bbb');
			}*/
			
			if ($s) {
				if ($select_query) {
					$this->log("> OK (".$statement->getNumRows()." row(s) returned)", 'green');
				} else if ($delete_query) {
					$this->log("> OK (".$statement->getAffectedRows()." row(s) deleted)", 'green');
				} else if ($insert_query) {
					$this->log("> OK (".$statement->getAffectedRows()." row(s) inserted)", 'green');
				} else {
					$this->log("> OK", 'green');
				}
			} else {
				$this->log("> FAILED", 'red');
				$this->log($statement->error, 'red');
			}
		}
		
		public function title($t) {
			if ($t != '') $this->_title($t);
			$this->_title($this->query_counter." queries logged");
		}
		
		protected function _title($t) {
			$this->LOG = $this->timestamp()."<font style='font-weight: bold; font-size: 120%;'>".$t."</font><hr/>".$this->LOG;
		}
		
		public function timestamp() {
			return "<font style='float: left; color: #aaa; margin-right: 10px;'>".date('H:i:s')."</font> ";
		}
		
		public function log($q, $color = 'black', $escape = true) {
			$this->LOG .= "<div style='clear: both; border-bottom: 1px solid #eee;'>";
				$this->LOG .= "<div style='float: left; color: ".$color."'>";
					$this->LOG .= $this->timestamp();
					$this->LOG .= ($escape ? htmlentities($q) : $q);
				$this->LOG .= "</div>";
			$this->LOG .= "</div>";
		}
		
		public function show_log() {
			return "<div style='font-family: Courier New, Courier, sans-serif; font-size: 11px;'>".$this->LOG."</div>";
		}
		
		public function color_sql($w) {
			$sql_words = array('AS', 'IS', 'LIKE', 'GROUP', 'DELETE', 'COLUMNS', 'ON', 'TABLES', 'DUPLICATE', 'ASC', 'INDEX', 'FULLTEXT', 'FULL', 'JOIN', 'LEFT', 'RIGHT', 'INNER', 'DESC', 'LIMIT', 'UPDATE', 'TRUNCATE', 'INSERT', 'INTO', 'VALUES', 'SHOW', 'ALTER', 'CREATE', 'TABLE', 'FROM', 'AND', 'ORDER', 'BY', 'WHERE', 'SELECT', '(', ')', 'ADD', 'DROP', 'AFTER', '=', 'IF', 'NOT', 'EXISTS', 'INT', 'UNSIGNED', 'NULL', 'AUTO_INCREMENT', 'PRIMARY', 'KEY', 'UNIQUE', 'TINYTEXT', 'CHARACTER', 'SET', 'COLLATE', ',', 'DEFAULT', 'MEDIUMINT', 'TINYINT(1)', 'INT(11)', 'SMALLINT', 'IN', 'HAVING');
			return in_array(strtoupper($w), $sql_words) ? "<font style='color: blue; font-weight: bold;'>".$w."</font>" : $w;
		}
		
		public function merge($logger) {
			$this->query_counter += $logger->query_counter;
			$this->LOG .= "<ul style='margin: 15px 15px 15px 40px;'>".$logger->LOG."</ul>";
		}
	}