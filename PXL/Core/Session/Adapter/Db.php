<?php
	namespace PXL\Core\Session\Adapter;
	
	use PXL\Core\Db\Db as Database;
	use PXL\Core\Config;
	use PXL\Core\Db\Statement;
	
	class Db implements iAdapter {
		
		protected $_data;
		protected $_table;
		
		public function __construct() {
			$this->_table = Config::read('session.db.table');
			Database::getInstance();
		}
		
		public function __destruct() {
			session_write_close();
		}
		
		public function open($savePath, $sessionName) {
			return true;
		}
		
		public function close() {
			return true;
		}
		
		public function read($sessionId) {
			$db = Database::getInstance();
			
			$q = "
				SELECT
					`data`
				FROM
					`%s`
				WHERE
					`id`=?
			";
			
			$q   = sprintf($q, $this->_table);
			$row = $db->row(new Statement($q, array($sessionId)));
			
			if (!empty($row)) {
				return $row->get('data');
			} else {
				return '';
			}
		}
		
		public function write($sessionId, $data) {
			$db = Database::getInstance();
		
			$q = "
				REPLACE INTO `%s`
					(`id`, `data`)
				VALUES
					(?,?)
			";
			
			$q    = sprintf($q, $this->_table);
			$stmt = new Statement($q, array($sessionId, $data));
			
			$db->query($stmt);
		}
		
		public function destroy($sessionId) {
			$where = "
				`id`='%s'
			";
			
			$where = sprintf($where, pxl_db_safe($sessionId));
			Database::getInstance()->delete($this->_table, $where);
			
			return true;
		}
		
		public function gc($lifetime) {
			$where = "
				`last_modified` <= DATE_SUB(NOW(), INTERVAL %d SECOND)
			";
			
			$where = sprintf($where, $lifetime);
			Database::getInstance()->delete($this->_table, $where);
			
			return true;
		}
	}