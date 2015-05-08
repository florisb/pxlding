<?php
	namespace PXL\Hornet\Application;

	use PXL\Hornet\Request as R;

	/**
	 * iApplication interface.
	 *
	 * Application class that handles initialization
	 * of various components (Routing, Configuration,
	 * etcetera) and finally dispatches the request
	 * to the correct controller and action.
	 *
	 * Because of the singular nature of this class,
	 * the application is used as a singleton.
	 */
	interface iApplication extends \SplSubject {
		
		/**
		 * getInstance function.
		 * 
		 * Returns the current application instance,
		 * creating a new instance if necessary.
		 *
		 * @access public
		 * @return void
		 */
		public static function getInstance();
		
		/**
		 * initRoutes function.
		 * 
		 * Initializes logic concerning routes.
		 *
		 * @access public
		 * @return void
		 */
		public function initRoutes();
		
		/**
		 * dispatch function.
		 * 
		 * Dispatches a request to the correct
		 * controller and action.
		 *
		 * @access public
		 * @param iRequest $oRequest
		 * @return void
		 */
		public function dispatch();
		
		/**
		 * getRequest function.
		 * 
		 * Returns the request object that was originally
		 * created in the current application lifetime.
		 *
		 * @access public
		 * @return iRequest
		 */
		public function getRequest();
	}