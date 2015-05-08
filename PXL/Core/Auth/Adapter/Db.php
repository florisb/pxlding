<?php
	namespace PXL\Core\Auth\Adapter;

	use PXL\Core\Config;
	use BadMethodCallException;
	use PXL\Core\Db as Database;
	
	class Db implements iAdapter {
		
		protected $_configuration;
		
		public function __construct() {
			$adapterConfig = Config::getAsObject()->auth->adapter->db;
			
			if (empty($adapterConfig)) {
				throw new BadMethodCallException('Missing configuration data for component auth.adapter');
			}
			
			foreach(array('table', 'identitycolumn', 'salt', 'credentialscolumn', 'algorithm') as $key) {
				if (empty($adapterConfig->$key)) {
					throw new BadMethodCallException('Missing configuration data for component auth.adapter.db.' . $key);
				}
			}
			
			$this->_configuration = $adapterConfig;
		}
		
		public function fetchIdentityData($identity, $credentials) {
			$db = Database\Db::getInstance();
		
			$q = "
				SELECT
					*
				FROM
					`%s`
				WHERE
					`%s`=?
				AND
					`%s`=?
				LIMIT
					0,1
			";
			
			$q = sprintf($q,
				$db->escape($this->_configuration->table),
				$db->escape($this->_configuration->identitycolumn),
				$db->escape($this->_configuration->credentialscolumn)
			);
			
			$stmt = new Database\Statement($q, array($identity, $this->_treatedCredentials($credentials)));
		
			return $db->row($stmt);
		}
		
		protected function _treatedCredentials($credentials) {
			return hash($this->_configuration->algorithm, $this->_configuration->salt . $credentials);
		}
	}