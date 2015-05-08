<?php
	namespace PXL\Hornet\Routing\Route;
	
	use PXL\Hornet\Request as R;
	
	/**
	 * iRoute interface.
	 *
	 * Defines a route to be used by iRouter.
	 */
	interface iRoute {
	
		const CONTROLLER_PARAM = ':controller';
		const ACTION_PARAM     = ':action';
		const WILDCARD_PARAM   = '*';

		/**
		 * match function.
		 * 
		 * Matches the request with the current route,
		 * changing the request object if a match is
		 * actually found.
		 *
		 * This method should return true if a match was
		 * found (thus changing the request object accordingly)
		 * and false if no match was found. In the latter,
		 * the request is simply passed to the next route
		 * that exists in the chain.
		 *
		 * @access public
		 * @param iRequest $request
		 * @return boolean
		 */
		public function match(R\iRequest $request);
		
		/**
		 * adjustRequest function.
		 * 
		 * @access public
		 * @param iRequest $request
		 * @return void
		 */
		public function adjustRequest(R\iRequest $request);
	}