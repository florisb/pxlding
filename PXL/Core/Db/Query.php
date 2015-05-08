<?php
	namespace PXL\Core\Db;

	use PXL\Core\Collection;
	
	/**
	 * Query class.
	 *
	 * Object-oriented method of creating queries.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	class Query {
		
		// Collection-type variables
		protected $_select    = null;
		protected $_leftJoin  = null;
		protected $_innerJoin = null;
		protected $_whereAnd  = null;
		protected $_whereOr   = null;
		
		// String-type variables
		protected $_from      = '';
		protected $_limit     = '';
		
		public function __construct($from = null) {
			if (!is_null($from)) {
				$this->_from = $from;
			}
		
			$this->_select    = new Collection\SimpleList();
			$this->_leftJoin  = new Collection\SimpleList();
			$this->_innerJoin = new Collection\SimpleList();
			$this->_where     = new Collection\SimpleList();
			$this->_orderBy   = new Collection\SimpleList();
		}
		
		protected function _buildSql() {
		}
		
		public function select($columns) {
			return $this;
		}
		
		public function from($table) {
			$this->_from = Db::getInstance()->escape($table);
			
			return $this;
		}
		
		public function leftJoin($table, $alias) {
			return $this;
		}
		
		public function innerJoin($table, $alias) {
			return $this;
		}
		
		public function where($conditions) {
			foreach($conditions as $condition) {
				
			}
			
			return $this;
		}
		
		public function whereOr($conditions) {
			return $this;
		}
		
		public function orderBy() {
			return $this;
		}
		
		public function limit($start, $length = -1) {
			$this->_limit = sprintf("LIMIT %d, %d", $start, $length);
			
			return $this;
		}
		
		public function __toString() {
			echo $this->_buildSql();
		}
	}