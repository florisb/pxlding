<?php
	namespace PXL\Hornet\Routing;
	
	use PXL\Hornet\Request as R;
	
	class Router implements iRouter {
		
		protected static $_instance = null;
		
		protected $_matchedRoute = null;
		protected $_routes       = array();
		
		public static function getInstance() {
			if (!self::$_instance) {
				self::$_instance = new Router();
			}
			
			return self::$_instance;
		}
		
		public function addRoute(Route\iRoute $route, $name = null) {
			if (is_null($name)) {
				$this->_routes[] = $route;
			} else {
				$this->_routes[$name] = $route;
			}
			
			return $this;
		}
		
		public function getRoute($name) {
			if (is_numeric($name)) {
				return array_peek($this->_routes, $name);
			} elseif (array_key_exists($name, $this->_routes)) {
				return $this->_routes[$name];
			} else {
				return null;
			}
		}
		
		public function getMatchedRouteName() {
			return array_search($this->_matchedRoute, $this->_routes);
		}
		
		public function getMatchedRoute() {
			return $this->_matchedRoute;
		}
		
		public function route(R\iRequest $request) {
			foreach($this->_routes as $route) {
				if ($route->match($request)) {
				
					$this->_matchedRoute = $route;

					$route->adjustRequest($request);
					break;
				}
			}
		}
	}