<?php
	namespace PXL\Hornet\Routing\Route;
	
	use PXL\Hornet\Request\iRequest;
	use PXL\Hornet\Application\Application;
	
	/**
	 * Route class.
	 * 
	 * Default routing class which is used to route requests
	 * dynamically. Routes should be defined in the routes.ini.
	 *
	 * @author  Max van der Stam <max@pixelindustries.com>
	 * @extends AbstractRoute
	 */
	class Route extends AbstractRoute {
		
		protected $_matchedValues = array();
		protected $_components    = null;
		protected $_delimiters    = null;
		
		protected function init() {
			// Retrieve components and delimiters
			$this->_components = $this->_retrieveComponents();
			$this->_delimiters = $this->_retrieveDelimiters();
		}
		
		public function match(iRequest $request) {
			if (!$this->_components) {
				return false;
			}
			
			// Assemble regular expression and check if request matches
			$regExp = $this->assemble($this->_components, array_map('preg_quote', $this->_delimiters));
			$match  = preg_match($regExp, $request->getRequestUri(), $matches);
			
			if ($match === 0) {
				return false;
			}
			
			if (array_key_exists('params', $matches)) {
				$matches['params'] = explode('/', $matches['params']);
			} else {
				unset($matches['params']);
			}
			
			// Store matched values in array
			foreach($matches as $k => $match) {
				if (is_string($k) && !empty($match)) {
					$this->_matchedValues[$k] = $match;
				}
			}
			
			// Set default values where neccessary
			if ($this->_paramDefaultValues) {
				foreach($this->_paramDefaultValues as $key => $value) {
					if (!array_key_exists($key, $this->_matchedValues)) {
						$this->_matchedValues[$key] = $value;
					}
				}
			}
			
			return true;
		}
		
		public function adjustRequest(iRequest $request) {
			// Retrieve controller, action and params from matched values
			$controller = $this->_matchedValues['controller'];
			$action     = $this->_matchedValues['action'];
			$params     = empty($this->_matchedValues['params']) ? array() : $this->_matchedValues['params'];
			
			// Unset those values, so only additional (named) parameters are left
			unset($this->_matchedValues['controller']);
			unset($this->_matchedValues['action']);
			unset($this->_matchedValues['params']);
			
			// Remove dashes in controller and action names
			$controller = str_replace('-', '', $controller);
			$action     = str_replace('-', '', $action);
		
			// Set controller and action name
			$request->setControllerName($controller)
							->setActionName($action);

			// Set named parameters							
			if ($this->_matchedValues) {
				$request->setParams($this->_matchedValues);
			}
			
			// Set unnamed parameters
			if ($params) {
				$request->setParams($params);
			}
		}
		
		public function reverseAssemble($components) {
			$matchedValues = array();
			$delimiters    = array();
			$request       = Application::getInstance()->getRequest();
			
			reset($this->_delimiters);
			
			foreach($this->_components as $component) {
				switch($component) {
					case self::CONTROLLER_PARAM:
					case self::ACTION_PARAM:
					default:
						if(strpos($component, ':') === false) {
							$matchedComponent = $component;
						} else {
							$component = trim($component, ':');
							
							if (empty($components[$component])) {
								if (array_key_exists($component, $this->_paramDefaultValues)) {
									// This parameter is optional, so skip it
									next($this->_delimiters);
									continue;
								} elseif (!($matchedComponent = $request->getParam($component))) {
									throw new \InvalidArgumentException("Missing required parameter $component to reverse-assemble route");
								}
							} else {
								$matchedComponent = $components[$component];
							}
						}
						
						if (!empty($matchedComponent)) {
							$matchedValues[] = $matchedComponent;
							$delimiters[]    = current($this->_delimiters);
							next($this->_delimiters);
						}
						break;
					
					case self::WILDCARD_PARAM:
						foreach($components as $k => $v) {
							if (is_numeric($k)) {
								$matchedValues[] = $v;
								$delimiters[]    = '/';
							}
						}
						break;
				}
			}
			
			reset($delimiters);

			$url = '';
			foreach($matchedValues as $val) {
				$url .= rawurlencode($val) . current($delimiters);
				next($delimiters);
			}
			
			$url = rtrim($url, '/') . '/';
			
			return $url;
		}
		
		protected function assemble($components, $delimiters) {
			$pathComponents = array();
			
			foreach($components as $key => $component) {
				if ($key > 0) {
					$delimiter = &$delimiters[$key - 1];
				} else {
					$delimiter = '';
				}
			
				switch($component) {
					case self::CONTROLLER_PARAM:
					case self::ACTION_PARAM:
						$component = trim($component, ':');
						$pathRegex = "(?P<$component>[a-z]{1}[a-z_0-9\-]*)";
						
						// If this parameter has default values -- make it optional
						if (array_key_exists($component, $this->_paramDefaultValues)) {
							if (":$component" === reset($components)) {
								$pathRegex = "(?:$pathRegex)?";
							} else {
								$pathRegex = "(?:{$delimiter}{$pathRegex})?";
								$delimiter = '';
							}
						}

						$pathComponents[] = $pathRegex;
						break;

					case self::WILDCARD_PARAM:
						if ($component === reset($components)) {
							$pathRegex = '(?:(?P<params>.+))?';
						} else {
							$pathRegex = "(?:{$delimiter}(?P<params>.+))?";
							$delimiter = '';
						}

						$pathComponents[] = $pathRegex;
						break;

					default:
						// Check if this component is a static value or a parameter
						if (strpos($component, ':') === 0) {
							$component = trim($component, ':');
							
							// If this parameter needs a specific form, override the default behaviour
							if (array_key_exists($component, $this->_paramRegexValues)) {
								$pathRegex = $this->_paramRegexValues[$component];
							} else {
								$pathRegex = '[^\.]+';
							}

							$pathRegex = "(?P<$component>$pathRegex)";

							// If this parameter has default values -- make it optional
							if (array_key_exists($component, $this->_paramDefaultValues)) {
								if (":$component" === reset($components)) {
									$pathRegex = "(?:$pathRegex)?";
								} else {
									$pathRegex = "(?:{$delimiter}{$pathRegex})?";
									$delimiter = '';
								}
							}
						} else {
							$pathRegex = preg_quote($component);
						}
						
						$pathComponents[] = $pathRegex;
						break;
				}

				unset($delimiter);
			}

			$regExp = '';
			reset($delimiters);

			foreach($pathComponents as $component) {
				$regExp .= $component . current($delimiters);
				next($delimiters);
			}

			//Return expression with optional trailing slash
			return "#^$regExp/?$#i";
		}
		
		protected function _retrieveComponents() {
			// Retrieve components
			$result = preg_match_all('#(:?[A-z]{1}[A-z_0-9]*|\*)+#', $this->_uriStr, $matches);
			
			if ($result === 0) {
				return false;
			}
			
			return $matches[1];
		}
		
		protected function _retrieveDelimiters() {
			// Retrieve delimiters
			preg_match_all('#(/|-|\.)#', $this->_uriStr, $delimiters);
			
			return $delimiters[0];
		}
	}