<?php
	namespace PXL\Hornet\Request;

	/**
	 * iRequest interface.
	 *
	 * 
	 */
	interface iRequest {
		
		/**
		 * isGet function.
		 * 
		 * Returns true or false based on whether
		 * the current request is done through HTTP-GET.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isGet();
		
		/**
		 * isPost function.
		 *
		 * Returns true or false based on whether
		 * the current request is done through HTTP-POST.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isPost();
		
		/**
		 * isPut function.
		 *
		 * Returns true or false based on whether
		 * the current request is done through HTTP-PUT.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isPut();
		
		/**
		 * isDelete function.
		 *
		 * Returns true or false based on whether
		 * the current request is done through HTTP-DELETE.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isDelete();
		
		/**
		 * isHead function.
		 * 
		 * Returns true or false based on whether
		 * the current request is done through HTTP-HEAD.
		 *
		 * @access public
		 * @return void
		 */
		public function isHead();
		
		/**
		 * isOptions function.
		 * 
		 * Returns true or false based on whether
		 * the current request is done through HTTP-OPTIONS.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isOptions();
		
		/**
		 * isXhr function.
		 * 
		 * Returns true or false based on whether
		 * the current request is done asynchronously via
		 * xmlHttpRequest.
		 *
		 * @access public
		 * @return boolean
		 */
		public function isXhr();
		
		/**
		 * getControllerName function.
		 * 
		 * Returns the name of the controller currently
		 * stored in the request object.
		 *
		 * @access public
		 * @return string
		 */
		public function getControllerName();
		
		/**
		 * setControllerName function.
		 * 
		 * Sets a new controller name, overriding
		 * any existing controller name currently
		 * stored in the request object.
		 *
		 * @access public
		 * @return void
		 */
		public function setControllerName($value);
		
		/**
		 * getActionName function.
		 * 
		 * Returns the name of the action currently
		 * stored in the request object.
		 *
		 * @access public
		 * @return string
		 */
		public function getActionName();
		
		/**
		 * setActionName function.
		 * 
		 * Sets a new action name, overriding
		 * any existing action name currently
		 * stored in the request object.
		 *
		 * @access public
		 * @return void
		 */
		public function setActionName($value);	 
		
		/**
		 * getParam function.
		 * 
		 * Returns the value of a parameter, where $key
		 * indicates the position of the parameter (starting at 1).
		 *
		 * When performing additional routing through the
		 * use of routes, parameter values may also be
		 * retrieved via the names they are given during
		 * routing.
		 * 
		 *
		 * @access public
		 * @param mixed $key
		 * @return void
		 */
		public function getParam($key);
		
		/**
		 * setParam function.
		 * 
		 * Sets the value of a parameter. The argument
		 * $key may be either an integer (indicating the position
		 * of the parameter) or an string (indicating a named parameter).
		 *
		 * @access public
     * @param  mixed $key
		 * @return void
		 */
		public function setParam($key, $value);
		
		/**
		 * setParams function.
		 * 
		 * Sets multiple parameters at once.
		 *
		 * @access public
		 * @param array $params
		 * @return void
		 */
		public function setParams(array $params);
		
		/**
		 * setDispatched function.
		 * 
		 * @access public
		 * @param bool $dispatched (default: true)
		 * @return void
		 */
		public function setDispatched($dispatched = true);
		
		/**
		 * isDispatched function.
		 * 
		 * @access public
		 * @return void
		 */
		public function isDispatched();
		
		/**
		 * getBaseUrl function.
		 * 
		 * Returns the base url of the application. The
		 * base url should be automatically determined in
		 * the request constructor method.
		 *
		 * @access public
		 * @return void
		 */
		public function getBaseUrl();
		
		/**
		 * setBaseUrl function.
		 * 
		 * Overrides the base url with a new one.
		 *
		 * @access public
		 * @return void
		 */
		public function setBaseUrl($value);
		
		public function isSecure();
	}