<?php
	namespace PXL\Core\Auth\Adapter;

	use PXL\Core\Db;
	use PXL\Core\Config;
	use BadMethodCallException;
	
	class SecureDb implements iAdapter {
		
		protected $_configuration = null;
		
		public function __construct() {
			$adapterConfig = Config::getAsObject()->auth->adapter->securedb;
			
			if (empty($adapterConfig)) {
				throw new BadMethodCallException('Missing configuration data for component auth.adapter.securedb');
			}
			
			foreach(array('table', 'identitycolumn', 'credentialscolumn') as $key) {
				if (empty($adapterConfig->$key)) {
					throw new BadMethodCallException('Missing configuration data for component auth.adapter.securedb.' . $key);
				}
			}
			
			$this->_configuration = $adapterConfig;
		}
		
		public function fetchIdentityData($identity, $credentials) {
			$db = Db\Db::getInstance();
		
			$q = "
				SELECT
					*
				FROM
					`%s`
				WHERE
					`%s`=?
				LIMIT
					0,1
			";
			
			$q = sprintf($q,
				$db->escape($this->_configuration->table),
				$db->escape($this->_configuration->identitycolumn)
			);
			
			$stmt   = new Db\Statement($q, array($identity));
			$result = $db->row($stmt);
			
			if (empty($result) || !password_verify($credentials, $result->get($this->_configuration->credentialscolumn))) {
				return null;
			} else {
				$result->remove($this->_configuration->credentialscolumn);
				return $result;
			}
		}
	}