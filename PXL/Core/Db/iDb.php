<?php
	namespace PXL\Core\Db;

	use PXL\Core\Collection as C;

	interface iDb {
		
		/**
		 * row function.
		 * 
		 * @access public
		 * @param Statement $query
		 * @param mixed $class (default: null)
		 * @return void
		 */
		public function row(Statement $query, $class = null);
		
		/**
		 * matrix function.
		 * 
		 * @access public
		 * @param Statement $query
		 * @param mixed $class (default: null)
		 * @param mixed $key (default: null)
		 * @param mixed $value (default: null)
		 * @return Map|List Map when keys are used, List when no key is used
		 */
		public function matrix(Statement $query, $class = null, $key = null, $value = null);
		
		/**
		 * query function.
		 * 
		 * @access public
		 * @param Statement $q
		 * @return mixed
		 */
		public function query(Statement $q);
		
		/**
		 * getLastAffectedRows function.
		 * 
		 * Returns the number of affected rows as
		 * a result from the most recent query.
		 *
		 * @access public
		 * @return int
		 */
		public function getLastAffectedRows();
		
		public function insert($table, $data);
		
		public function multi_insert($table, $data);
		
		public function update($table, $data, $where);
		
		public function delete($table, $where);
		
		public function truncate($table);
		
		/**
		 * begin function.
		 * 
		 * Initiates a transaction.
		 *
		 * @access public
		 * @return void
		 */
		public function begin();
		
		/**
		 * commit function.
		 * 
		 * Commits a transaction.
		 *
		 * @access public
		 * @return void
		 */
		public function commit();
		
		/**
		 * rollback function.
		 * 
		 * Ends and rollback a transaction.
		 *
		 * @access public
		 * @return void
		 */
		public function rollback();
	}