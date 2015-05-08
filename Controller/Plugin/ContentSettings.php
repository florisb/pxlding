<?php
	namespace Controller\Plugin;
	
	use PXl\Core\Db;
	use PXL\Core\Config;
	use PXL\Hornet\Seo\Seo;
	use PXL\Hornet\View\View;
	use Model\Factory\Content;
	use PXL\Core\Session\Session;
	use PXL\Hornet\Application\Application;
	use Model\Factory\Settings\GlobalSettings;
	use PXL\Hornet\Controller\Plugin\AbstractPlugin;
	
	class ContentSettings extends AbstractPlugin {
		
		protected $_application;
		protected $_languageId;
		
		protected $_contentFactory = null;
		
		public function preDispatch(Application $application) {
			// Store reference of application
			$this->_application           = $application;
			$this->_languageId            = Session::get('_language_id');
			$this->_contentFactory        = Content::getInstance();
			$this->_globalSettingsFactory = GlobalSettings::getInstance();
		
			$globalSettings = $this->_getGlobalSettings();
			$content        = $this->_getContent();
			$navigation     = $this->_getNavigation();
			$enActive       = $this->_determineEnActive();
		
			$controllerName = $application->getRequest()->getControllerName();
			$settingsFactoryClassName = 'Model\Factory\Settings\\' . ucfirst($controllerName);
			
			if (class_exists($settingsFactoryClassName) && in_array('Model\Factory\Settings\iSettingsFactory', class_implements($settingsFactoryClassName))) {
				$settings = call_user_func(array($settingsFactoryClassName, 'getInstance'))->getSettings();
			}
			
			if (empty($settings)) {
				$settings = (object) null;
			}
			
			$imgurl = Config::has('imgurl') ? Config::read('imgurl') : '';

			View::getInstance()->set('_content',        $content,        true)
                               ->set('_settings',       $settings,       true)
                               ->set('_globalSettings', $globalSettings, true)
                               ->set('_navigation',     $navigation,     true)
                               ->set('_imgurl',         $imgurl,         true)
                               ->set('_enActive',       $enActive,       true)
                               ->set('_show_ga',        ($application->getEnvironment() === 'production'));
		}
		
		protected function _getContent() {
			$controllerName = $this->_application->getRequest()->getControllerName();
		
			// Fetch content data for this page from cache if possible
			if ($this->_application->hasCache()) {
				$cache   = $this->_application->getCache();
				$cacheId = "__content_{$controllerName}_language_id_{$this->_languageId}";
				
				if (($data = $cache->load($cacheId)) === false) { // Cache hit
					$data = $this->_contentFactory->getSettingsForPage(strtolower($controllerName));

					if (!empty($data)) { // Cache miss
						$cache->save($data, $cacheId, array('_module_id_6', "_entry_id_{$data->id}"));
					}
				}
 			} else {
				$data = $this->_contentFactory->getSettingsForPage(strtolower($controllerName));
 			}
 			
 			if (!($data instanceof \StdClass && !count((array) $data))) {
 				if ($data->seo_title) {
 					Seo::addTitle($data->seo_title);
 				} elseif ($data->name) {
 					Seo::addTitle($data->name);
 				}
 				
 				if ($data->seo_description) {
	 				Seo::setDescription($data->seo_description);
 				}
 				
 				if ($data->seo_keywords) {
	 				Seo::addKeywords(array_map('trim', explode(',', $data->seo_keywords)));
 				}
 			}
	
 			return $data;
		}
		
		protected function _getNavigation() {
			// Fetch navigation data from cache if possible
			if ($this->_application->hasCache()) {
				$cache   = $this->_application->getCache();
				$cacheId = "__navigation__language_id_{$this->_languageId}";
				
				if (($data = $cache->load($cacheId)) === false) {
					$data = $this->_contentFactory->getAll();
					$cache->save($data, $cacheId, array('__navigation'));
				}
			} else {
				$data = $this->_contentFactory->getAll();
			}
			
			return $data;
		}

		protected function _getSections() {
			// Fetch section data from cache if possible
			if ($this->_application->hasCache()) {
				$cache   = $this->_application->getCache();
				$cacheId = "__section__language_id_{$this->_languageId}";
				
				if (($data = $cache->load($cacheId)) === false) {
					$data = $this->_contentFactory->getAll();
					$cache->save($data, $cacheId, array('__section'));
				}
			} else {
				$data = $this->_contentFactory->getAll();
			}
			
			return $data;
		}
		
		protected function _getGlobalSettings() {
			// Fetch global settings from cache if possible
			if ($this->_application->hasCache()) {
				$cache   = $this->_application->getCache();
				$cacheId = "__global_settings__language_id_{$this->_languageId}";
				
				if (($data = $cache->load($cacheId)) === false) {
					$data = $this->_globalSettingsFactory->getSettings();
					$cache->save($data, $cacheId, array('__global_settings'));
				}
			} else {
				$data = $this->_globalSettingsFactory->getSettings();
			}
			
			if ($data) {
				if ($data->main_title) {
					Seo::addTitle($data->main_title);
				}
			}
			
			return $data;
		}
		
		protected function _determineEnActive() {
			$q = "
				SELECT
					`id`
				FROM
					`cms_m2_languages`
				WHERE
					`code`='en'
				AND
					`e_active`=1
				ORDER BY
					`e_position` ASC
				LIMIT
					0,1
			";
			
			$stmt = new Db\Statement($q);
			$db   = Db\Db::getInstance();
			
			return !is_null($db->row($stmt));
		}
	}