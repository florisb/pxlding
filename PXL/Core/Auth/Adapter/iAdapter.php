<?php
	namespace PXL\Core\Auth\Adapter;
	
	/**
	 * iAdapter interface.
	 *
	 * Describes an adapter used by PXL\Core\Auth\iAuth to authenticate and retrieve
	 * identity *data* from.
	 *
	 * @interface
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	interface iAdapter {
		
		/**
		 * fetchIdentityData function.
		 * 
		 * Returns an SimpleMap object or NULL
		 * based on whether an matching data entry was
		 * found using the supplied identity and credentials
		 * parameter.
		 *
		 * @access public
		 * @param mixed $identity
		 * @param mixed $credentials
		 * @return PXL\Core\Collection\SimpleMap | NULL
		 */
		public function fetchIdentityData($identity, $credentials);
	}