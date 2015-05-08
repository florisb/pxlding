<?php
	namespace PXL\Hornet\Controller;
	
	/**
	 * iController interface.
	 */
	interface iController {
		
		/**
		 * preAction function.
		 * 
		 * Method that is automatically called
		 * prior to running the action.
		 *
		 * @access public
		 * @return void
		 */
		public function preAction();
		
		/**
		 * postAction function.
		 * 
		 * Method that is automatically called
		 * after running the action.
		 *
		 * @access public
		 * @return void
		 */
		public function postAction();
	}