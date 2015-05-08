<?php
	
	class Logger {
		
		var $LOG = '';
		var $query_counter = 0;
		
		function log_query($q, $s, $note = '') {
			$this->query_counter++;
			$syntax = explode(' ', $q);
			$select_query = strtolower($syntax[0]) == 'select' || strtolower($syntax[0]) == 'show';
			$delete_query = strtolower($syntax[0]) == 'delete';
			$insert_query = strtolower($syntax[0]) == 'insert';
			$syntax = array_map(array(&$this, 'color_sql'), $syntax);
			$syntax = implode(' ', $syntax);
			$this->log($syntax.($note != '' ? " <span style='color: #aaa;'>(".$note.")</span>" : ""), 'black', false);
			if ($s) {
				if ($select_query) {
					$this->log("> OK (".mysql_num_rows($s)." row(s) returned)", 'green');
				} else if ($delete_query) {
					$this->log("> OK (".mysql_affected_rows()." row(s) deleted)", 'green');
				} else if ($insert_query) {
					$this->log("> OK (".mysql_affected_rows()." row(s) inserted)", 'green');
				} else {
					$this->log("> OK", 'green');
				}
			} else {
				$this->log("> FAILED", 'red');
				$this->log(mysql_error(), 'red');
			}
		}
		
		function title($t) {
			if ($t != '') $this->_title($t);
			$this->_title($this->query_counter." queries logged");
		}
		
		private function _title($t) {
			$this->LOG = $this->timestamp()."<font style='font-weight: bold; font-size: 120%;'>".$t."</font><hr/>".$this->LOG;
		}
		
		function timestamp() {
			return "<font style='float: left; color: #aaa; margin-right: 10px;'>".date('H:i:s')."</font> ";
		}
		
		function log($q, $color = 'black', $escape = true) {
			$this->LOG .= "<div style='clear: both;'>".$this->timestamp()."<font style='float: left; color: ".$color."'>".($escape ? htmlentities($q) : $q)."</font>&nbsp;</div>";
		}
		
		function show_log() {
			return "<div style='font-family: Courier New, Courier, sans-serif; font-size: 11px;'>".$this->LOG."</div>";
		}
		
		function color_sql($w) {
			$sql_words = array('AS', 'GROUP', 'DELETE', 'COLUMNS', 'ON', 'DUPLICATE', 'ASC', 'DESC', 'LIMIT', 'UPDATE', 'TRUNCATE', 'INSERT', 'INTO', 'VALUES', 'SHOW', 'ALTER', 'CREATE', 'TABLE', 'FROM', 'AND', 'ORDER', 'BY', 'WHERE', 'SELECT', '(', ')', 'ADD', 'DROP', 'AFTER', '=', 'IF', 'NOT', 'EXISTS', 'INT', 'UNSIGNED', 'NULL', 'AUTO_INCREMENT', 'PRIMARY', 'KEY', 'UNIQUE', 'TINYTEXT', 'CHARACTER', 'SET', 'COLLATE', ',', 'DEFAULT', 'MEDIUMINT', 'TINYINT(1)', 'INT(11)', 'SMALLINT', 'IN', 'HAVING');
			return in_array(strtoupper($w), $sql_words) ? "<font style='color: blue; font-weight: bold;'>".$w."</font>" : $w;
		}
		
		function merge($logger) {
			$this->query_counter += $logger->query_counter;
			$this->LOG .= "<ul style='margin: 15px 15px 15px 40px;'>".$logger->LOG."</ul>";
		}
	}
	
?>