<?php
	class DBSync {
	
		public static function synchronize_data($from_connection, $to_connection, $tables = array(), $synchronize_first = false, $truncate = false) {
		
			$existing_tables = CMS_DB::mysql_query("SHOW TABLES");
			$et = array();
			while ($existing_table = mysql_fetch_row($existing_tables)) {
				$et[] = $existing_table[0];
			}
			
			foreach ($tables as $table) {
			
				if (!in_array($table, $et)) {
					continue;
				}
				
				// pre-work
				if ($synchronize_first) {
					DBSync::synchronize_structure($from_connection, $to_connection, array($table));
				}
				if ($truncate) {
					DBSync::mysql_query("TRUNCATE TABLE `".$table."`", $to_connection);
				}
				
				// synchronization:
				$fields    = array();
				$old_entries = CMS_DB::mysql_query("SELECT * FROM `".$table."`", $from_connection);
				$first     = true;
				
				while ($old_entry = mysql_fetch_assoc($old_entries)) {
					$entry = array();
					foreach ($old_entry as $field => $value) {
						if ($value === null) {
							$value = 'NULL';
						} else {
							$value = "'".str_replace("'", "''", $value)."'";
						}
						$entry[] = $value;
						if ($first) $fields[] = $field;
					}
					$first = false;
					DBSync::mysql_query("INSERT IGNORE INTO `".$table."` (`".implode('`, `', $fields)."`) VALUES (".implode(',', $entry).")", $to_connection);
				}
			}
		}
		
		public static function mysql_query($q, $c) {
			// fast, no debug
			return CMS_DB::mysql_query($q, $c);
			
			
			$s = CMS_DB::mysql_query($q, $c);
			if ($s === false) {
				$q = "<div style='padding: 4px; border: 4px solid red;'>".$q."</div>";
			}
			echo $q."<br/>";
			return $s;
		}
		
		public static function synchronize_structure($from_connection, $to_connection, $tables = null) {
			$data  = array('from' => DBSync::read_structure($from_connection, $tables), 'to' => DBSync::read_structure($to_connection, $tables));
			$dummy = "delete_me_please_912983182918294";
			
			foreach ($data['from'] as $table => $table_data) {
				if (is_array($tables) && !in_array($table, $tables)) continue;
				
				$remove_dummy = false;
				
				// SYNCHRONIZE TABLE EXISTENCE
				if (!isset($data['to'][$table])) {
					$data['to'][$table] = array('fields' => array(), 'keys' => array());
					DBSync::mysql_query("CREATE TABLE `".$table."` ( `".$dummy."` tinyint(1) ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci", $to_connection);
					$remove_dummy = true;
				}
				
				// SYNCHRONIZE FIELDS IN TABLE
				$fields_updated = false;
				// remove obsolete fields
				$alter_queries = array();
				
				foreach ($data['to'][$table]['fields'] as $field => $sql) {
					if (!in_array($field, array_keys($table_data['fields']))) {
						$alter_queries[] = "DROP `".$field."`";
					}
				}
				// create missing fields
				foreach ($table_data['fields'] as $field => $sql) {
					if (!isset($data['to'][$table]['fields'][$field])) {
						$fields_updated = true;
						$alter_queries[] = $sql;
					}
				}
				if ($remove_dummy) {
					$alter_queries[] = "DROP `".$dummy."`";
				}
				
				// SYNCHRONIZE KEYS
				if ($fields_updated)
				{
					// remove old indexes
					if (isset($data['to'][$table]['keys']['INDEX'])) {
						foreach ($data['to'][$table]['keys']['INDEX'] as $index) {
							$alter_queries[] = "DROP INDEX `".$index."`";
						}
					}
					// (re)create keys/indexes
					if (is_array($data['from'][$table]['keys']))
					{
						foreach ($data['from'][$table]['keys'] as $type => $fields) {
							switch ($type)
							{
								case 'INDEX':
										foreach ($fields as $index) {
											$alter_queries[] = "ADD INDEX (`".$index."`)";
										}
										break;
										
								case 'PRIMARY KEY':
										DBSync::mysql_query("ALTER TABLE `".$table."` DROP PRIMARY KEY", $to_connection);
										$alter_queries[] = "ADD PRIMARY KEY (`".implode('`, `', $fields)."`)";
										break;
							}
						}
					}
				}
				
				if (count($alter_queries)) {
					DBSync::mysql_query("ALTER TABLE `".$table."` ".implode(', ', $alter_queries), $to_connection);
				}
			}
			
			// remove obsolete tables
			if (!is_array($tables)) {
				foreach ($data['to'] as $table => $table_data) {
					if (isset($data['from'][$table])) continue;
					DBSync::mysql_query("DROP TABLE `".$table."`", $to_connection);
				}
			}
		}
		
		public static function read_structure($connection, $tables) {
			$key_legend = array('PRI' => 'PRIMARY KEY', 'MUL' => 'INDEX');
			$r = array();
			$t = DBSync::mysql_query("SHOW TABLES", $connection);
			while ($table = mysql_fetch_row($t)) {
				
				if (is_array($tables) && !in_array($table[0], $tables)) continue;
				
				$has_auto_inc = false;
				$c            = DBSync::mysql_query("SHOW FULL COLUMNS FROM `".$table[0]."`", $connection);
				$columns      = array();
				$keys         = array();
				$last_field   = null;
				
				while ($column = mysql_fetch_object($c)) {
					$has_auto_inc = $has_auto_inc || strtolower($column->Extra) == 'auto_increment';
					
					$column->Extra = str_replace("auto_increment", "PRIMARY KEY auto_increment", $column->Extra);
					
					$q  = "ADD `".$column->Field."` ".$column->Type;
					$q .= ($column->Collation != '' ? ' COLLATE '.$column->Collation : '');
					$q .= ($column->Null == 'NO' ? ' NOT NULL' : '');
					$q .= ($column->Default != '' ? " DEFAULT '".$column->Default."'" : '');
					$q .= ($column->Extra != '' ? ' '.$column->Extra : '');
					$q .= ($last_field == null ? ' FIRST' : ' AFTER `'.$last_field.'`');
					
					$columns[$column->Field] = $q;
					
					if ($column->Key != '') {
						$keys[$key_legend[$column->Key]][] = $column->Field;
					}
					
					$last_field = $column->Field;
				}
				
				if ($has_auto_inc) unset($keys['PRIMARY KEY']);
				$r[$table[0]] = array('fields' => $columns, 'keys' => $keys);
			}
			return $r;
		}
	}
?>