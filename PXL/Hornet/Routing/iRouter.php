<?php
	namespace PXL\Hornet\Routing;

	use PXL\Hornet\Request as R;

	interface iRouter {
		
		/**
		 * getInstance function.
		 * 
		 * Returns the current router instance,
		 * creating a new instance if necessary.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		public static function getInstance();
		
		/**
		 * addRoute function.
		 * 
		 * Adds a route to the router, which is eventually
		 * used by iRouter::route() when the definite route
		 * is determined.
		 *
		 * @access public
		 * @param iRoute $route
		 * @return void
		 */
		public function addRoute(Route\iRoute $route);
		
		/**
		 * route function.
		 *
		 * Loops through all routes that were added through
		 * iRouter::addRoute() and tries to match the current
		 * request with each of those routes, starting with
		 * the first route that was added. If a match is found
		 * for a route, the routing stops and the request is
		 * returned to the application.
		 * 
		 * @access public
		 * @param iRequest $request
		 * @return void
		 */
		public function route(R\iRequest $request);
	}