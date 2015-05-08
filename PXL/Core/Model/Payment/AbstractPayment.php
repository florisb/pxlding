<?php
	namespace PXL\Core\Model\Payment;
	
	use PXL\Core\Model\Entity;

	/**
	 * Payment_Abstract
	 *
	 * A class that describes a common interface for
	 * objects representing a (single) payment within
	 * a payment provider. This ensures that all payment
	 * provider objects are usable within a certain
	 * interface and increases code maintainability.
	 *
	 * @author  Max van der Stam <max@pixelindustries.com>
	 * @extends Entity\AbstractEntity
	 * @abstract
	 */
	abstract class AbstractPayment extends Entity\AbstractEntity {
		
		/**
		 * __construct
		 *
		 * Constructor method. The constructor method
		 * is finalized in it's own implentation where.
		 * At object construction, the Order object
		 * is stored after which the payment class
		 * is configured with Payment_Abstract::_configure().
		 * The final method executed is Payment_Abstract::_generateEncodedData().
		 * This method generated the encoded data neccessary for authentication, etc.
		 *
		 * @param  Entity_Order $order Instance of class "Entity_Order"
		 * @final
		 * @access public
		 */
		final public function __construct($data = null) {
			$this->_configure();
			
			parent::__construct($data);
		}
		
		/**
		 * _configure
		 *
		 * Configures the Payment object, storing data
		 * and such.
		 *
		 * @abstract
		 * @returns  void
		 * @access   protected
		 */
		abstract protected function _configure();
		
		/**
		 * __toString
		 *
		 * Should be implemented by extended classes.
		 */
		public function __toString() {
			return;
		}
	}