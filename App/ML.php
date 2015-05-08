<?php
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'PXL', 'Core', 'Db', 'Db.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'PXL', 'Core', 'Db', 'Statement.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'PXL', 'Core', 'Session', 'Session.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'PXL', 'Hornet', 'View', 'View.php')));
	require_once(dirname(__FILE__) . DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array('..', 'PXL', 'Hornet', 'Application', 'Application.php')));

	use PXL\Core\Db\Db;
	use PXL\Hornet\View\View;
	use PXL\Core\Db\Statement;
	use PXL\Core\Session\Session;
	use PXL\Hornet\Application\Application;

	class ML {
	
		protected static $_labels = null;
		
		public static function label($label, $allow_html = true) {
			$languageId = Session::get('_language_id');
			
			if (is_null($languageId)) {
				throw new \BadMethodCallException('Invalid language set');
			}
			
			if (is_null(self::$_labels)) {
				self::_getLabels($languageId);
			}
			
			if (self::$_labels->containsKey($label)) {
				$label = self::$_labels->get($label);
				$label = str_replace('<br type="_moz" />', '',  $label);
				$label = str_replace('&nbsp;',             ' ', $label);
				
				if (!$allow_html) {
					return strip_tags(trim($label));
				}
				
				return trim($label);
			} else {
				return '{' . $label . '}';
			}
		}
		
		protected static function _getLabels($languageId) {
			// Try and fetch labels from cache if possible
			if (Application::getInstance()->hasCache()) {
				$cache   = Application::getInstance()->getCache();
				$cacheId = "__ml_labels_language_id_$languageId";
				
				if ((self::$_labels = $cache->load($cacheId)) === false) {
					self::$_labels = self::_retrieveFromDb($languageId);
					$cache->save(self::$_labels, $cacheId, array($cacheId));
				}
			} else {
				self::$_labels = self::_retrieveFromDb($languageId);
			}
		}
		
		protected static function _retrieveFromDb($languageId) {
			$q = "
				SELECT
					`l`.`label`,
					`l_ml`.`translation`
				FROM
					`cms_m1_multilingual_labels` `l`
				INNER JOIN
					`cms_m1_multilingual_labels_ml` `l_ml`
				ON
					(`l_ml`.`entry_id`=`l`.`id` AND `l_ml`.`language_id`=?)
			";
				
			$stmt = new Statement($q, array(array($languageId, 'i')));
			return Db::getInstance()->matrix($stmt, null, 'label', 'translation');
		}
	}