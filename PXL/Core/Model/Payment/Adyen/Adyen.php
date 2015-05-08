<?php
	namespace PXL\Core\Model\Payment;

	use DateTime;
	use PXL\Core\Config;
	use PXL\Core\Model\Value;
	
	class Adyen extends AbstractPayment {
		
		protected $_config = null;
		
		protected function _configure() {
			Config::addFile(path(dirname(__FILE__) . '/config_adyen.ini'), APPLICATION_ENV);
			
			$this->_config = Config::getAsObject()->adyen;
		}
		
		protected function init() {
			$this->skinCode        = $this->_config->skin_code;
			$this->merchantAccount = $this->_config->merchantAccount;
			$this->paymentUrl      = $this->_config->payment_url;
		}
		
		protected function _getMerchantSig() {
			$hmacData = implode('', array(
				$this->paymentAmount,
				$this->currencyCode,
				$this->shipBeforeDate,
				$this->merchantReference,
				$this->skinCode,
				$this->merchantAccount,
				$this->sessionValidity,
				$this->shopperEmail,
				$this->shopperReference
			));
			
			return base64_encode(hash_hmac('sha1', $hmacData, $this->_config->hmac_key, true));
		}
		
		protected function _setPaymentAmount(Value\Price $price) {
			return floor($price->value * 100);
		}
		
		protected function _setShipBeforeDate(DateTime $date) {
			return $date->format('Y-m-d');
		}
		
		protected function _setSessionValidity(DateTime $date) {
			return $date->format(DATE_ATOM);
		}
		
		public function getUrl() {
			return $this->paymentUrl . '?' . http_build_query(array(
				'paymentAmount'     => $this->paymentAmount,
				'shipBeforeDate'    => $this->shipBeforeDate,	
				'currencyCode'      => $this->currencyCode,
				'skinCode'          => $this->skinCode,
				'merchantAccount'   => $this->merchantAccount,
				'merchantReference' => $this->merchantReference,
				'sessionValidity'   => $this->sessionValidity,
				'shopperEmail'      => $this->shopperEmail,
				'shopperReference'  => $this->shopperReference,
				'merchantSig'       => $this->merchantSig,
				'resURL'            => $this->resURL
			), '', '&amp;');
		}
	}