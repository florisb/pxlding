<?php
	namespace PXL\Core\Db;
	
	interface iStatement {
		
		public function execute($newValues = null);
		
		public function close();
		
		public function get_result();
		
		public function getAffectedRows();
	}