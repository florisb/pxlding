<?php
	namespace PXL\Core\Db;
	
	/**
	 * iDbConnectionInfo interface.
	 */
	interface iDbConnectionInfo {
	
		/**
		 * __construct function.
		 *
		 * The DSN should have the following structure:
		 *
		 * <driver>://<username>:<password>@<host>:<port>/<database>
		 *
		 * For example, a valid DSN is:
		 *
		 * mysql://administrator:supersecretpassword@localhost:3306/mydatabase
		 *
		 * @access public
		 * @param string $dsn
		 * @return void
		 */
		public function __construct($dsn);
	
		/**
		 * getDsn function.
		 * 
		 * Returns the full DSN (Data Source Name) from the current
		 * object.
		 *
		 * @access public
		 * @return void
		 */
		public function getDsn();
		
		/**
		 * getUsername function.
		 * 
		 * Returns the username for the 
		 *
		 * @access public
		 * @return void
		 */
		public function getUsername();
		
		/**
		 * getPassword function.
		 * 
		 * @access public
		 * @return void
		 */
		public function getPassword();
		
		/**
		 * getHost function.
		 * 
		 * @access public
		 * @return void
		 */
		public function getHost();
		
		/**
		 * getPort function.
		 * 
		 * @access public
		 * @return void
		 */
		public function getPort();
		
		/**
		 * getDatabase function.
		 * 
		 * @access public
		 * @return void
		 */
		public function getDatabase();
		
		/**
		 * getDriver function.
		 * 
		 * @access public
		 * @return void
		 */
		public function getDriver();
		
		/**
		 * getIdentifier function.
		 * 
		 * Returns an 32-length string that
		 * may be used to identify this iDbConnectionInfo
		 * instance, and other instances that were constructed
		 * using the same dsn.
		 *
		 * @access public
		 * @return void
		 */
		public function getIdentifier();
	}