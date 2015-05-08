<?php
	namespace PXL\Core\Auth;
	
	/**
	 * iAuth interface.
	 *
	 * Describes a interface for authentication purposes. Classes
	 * that implement this interface should follow the singleton
	 * design pattern.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	interface iAuth {
		
		public static function getInstance();
		
		/**
		 * authenticate function.
		 * 
		 * Attempts to authenticate using identity and
		 * credentials information.
		 *
		 * If authentication was succesful, an instance of
		 * the configured identityclass is created and returned.
		 * If authentication was *not* successful, a
		 * boolean FALSE is returned.
		 *
		 * @access public
		 * @static
		 * @param mixed $identity
		 * @param mixed $credentials
		 * @return void
		 */
		public function authenticate($identity, $credentials);
		
		/**
		 * hasIdentity function.
		 * 
		 * Returns true or false based on wheter an identity exists
		 * in the system.
		 * 
		 * @access public
		 * @static
		 * @return void
		 */
		public function hasIdentity();
		
		/**
		 * clearIdentity function.
		 * 
		 * Clears any existing identity from system.
		 *
		 * @access public
		 * @static
		 * @return void
		 */
		public function clearIdentity();
		
		/**
		 * getIdentity function.
		 * 
		 * Returns the current identity, or NULL
		 * if no identity exists.
		 *
		 * @access public
		 * @return void
		 */
		public function getIdentity();
	}