<?php
	namespace PXL\Hornet\Routing\Route;
	
	use PXL\Hornet\Request as R;
	
	abstract class AbstractRoute implements iRoute {
		
		protected $_uriStr;
		protected $_routingParams      = array();
		protected $_paramDefaultValues = array();
		protected $_paramRegexValues   = array();
		protected $_name;
		
		public function __construct($uriStr, array $paramDefaultValues = array(), array $paramRegexRequirements = array(), $name) {
			$this->_uriStr = $uriStr;
			$this->_name   = $name;
			
			$this->_setParamDefaultValues($paramDefaultValues);
			$this->_setParamRegexValues($paramRegexRequirements);
			
			$this->init();
		}
		
		public function getName() {
			return $this->_name;
		}
		
		protected function init() { }
		
		protected function _setParamRegexValues(array $data = array()) {
			foreach($data as $k => $v) {
				switch(strtolower($v)) {
					// ISO Standard 2-letter country and language codes
					case 'iso-3166-1':
					case 'iso-639-1':
						$this->_paramRegexValues[$k] = '[a-z]{2}';
						break;
						
					// ISO Standard YYYY-MM-DD date format
					case 'iso-8601':
						$this->_paramRegexValues[$k] = '(?:\d{4})(?:(?:\-\d{2})?(?:\-\d{2})|(?:\d{2})?(?:\d{2}))?';
						break;
						
					// Standardized format for slugs (alphanumeric with dashes as delimiters)
					case 'slug':
						$this->_paramRegexValues[$k] = '[A-z\d-]+';
						break;
					
					// Any other custom format
					default:
						$this->_paramRegexValues[$k] = $v;
						break;
				}
			}
		}
		
		protected function _setParamDefaultValues(array $data = array()) {
			foreach($data as $k => $v) {
				switch(strtolower($v)) {
					case '%current-iso-date':
						$this->_paramDefaultValues[$k] = date('Y-m-d');
						break;
						
					case '%current-iso-time':
						$this->_paramDefaultValues[$k] = date('H:i:s');
						break;
						
					case '%current-unix-timestamp':
						$this->_paramDefaultValues[$k] = time();
						break;
						
					default:
						$this->_paramDefaultValues[$k] = $v;
						break;
				}
			}
		}
	}