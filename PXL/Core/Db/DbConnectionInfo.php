<?php
	namespace PXL\Core\Db;
	
	require_once('iDbConnectionInfo.php');
	
	/**
	 * DbConnectionInfo class.
	 * 
	 * @implements iDbConnectionInfo
	 * @author     Max van der Stam <max@pixelindustries.com>
	 */
	class DbConnectionInfo implements iDbConnectionInfo {
		
		protected $_dsn      = null;
		protected $_username = null;
		protected $_password = null;
		protected $_host     = null;
		protected $_port     = null;
		protected $_database = null;
		protected $_driver   = null;
		
		public function __construct($dsn) {
			$this->_dsn = $dsn;
			
			// Parse DSN into the variables we need
			$vars = parse_url($this->_dsn);
			
			$this->_username = $vars['user'];
			$this->_password = $vars['pass'];
			$this->_driver   = $vars['scheme'];
			$this->_host     = $vars['host'];
			$this->_port     = $vars['port'];
			$this->_database = trim($vars['path'], '/');
		}
		
		public function getDsn() {
			return $this->_dsn;
		}
		
		public function getUsername() {
			return $this->_username;
		}
		
		public function getPassword() {
			return $this->_password;
		}
		
		public function getHost() {
			return $this->_host;
		}
		
		public function getPort() {
			return $this->_port;
		}
		
		public function getDatabase() {
			return $this->_database;
		}
		
		public function getDriver() {
			return $this->_driver;
		}
		
		public function getIdentifier() {
			return md5(serialize(array(
				$this->_username,
				$this->_password,
				$this->_driver,
				$this->_host,
				$this->_port,
				$this->_database
			)));
		}
	}