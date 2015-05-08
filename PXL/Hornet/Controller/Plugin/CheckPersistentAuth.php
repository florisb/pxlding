<?php
	namespace PXL\Hornet\Controller\Plugin;

	use PXL\Core\Db;
	use PXL\Core\Config;
	use PXL\Core\Session;
	use PXL\Core\Auth\Auth;
	use PXL\Hornet\Application\Application;
	use PXL\Hornet\Controller\Plugin\AbstractPlugin;

	class CheckPersistentAuth extends AbstractPlugin {

		public function preDispatch(Application $application) {
			$auth = Auth::getInstance();

			if ($auth->hasIdentity()) {
				return;
			}

			if ($loginHash = $_COOKIE[Auth::AUTH_PERSISTENT_COOKIE_KEY]) {
				if (!($config = Config::getAsObject()->auth->adapter->securedb)) {
					return;
				}

				$identityClass = Config::read('auth.identityclass');
				$db            = Db\Db::getInstance();

				$q = "
					SELECT
						*
					FROM
						`%s`
					WHERE
						MD5(CONCAT(`id`,`%s`))=?
					LIMIT
						0,1
				";
				
				$q = sprintf($q, $db->escape($config->table), $db->escape($config->identitycolumn));

				if (!($result = $db->row(new Db\Statement($q, array($loginHash))))) {
					return;
				}

				$result->remove($config->credentialscolumn);
				$result->put(Auth::AUTH_FIELD_CHECK, true);

				$auth->setIdentity(new $identityClass($result->toAssocArray()));
			}
		}
	}