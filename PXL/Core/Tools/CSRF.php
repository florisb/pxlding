<?php
	namespace PXL\Core\Tools;

	/**
	 * CSRF
	 *
	 * A class used to generate and validate tokens which then
	 * are utilized to prevent CSRF (Cross Site Request Forgery attacks).
	 */
	abstract class CSRF {

		const CSRF_NAME   = '_PXL_CSRF';
		const CSRF_SECRET = 'ĺ4,ź*æ₣&4¢©^8+]ž;4-Ķ~*3Ҳ+;73674ŗĆ≈√å®';

		/**
		 * getToken
		 * 
		 * @return string base64-encoded token
		 */
		public static function getToken() {
			$data  = implode('', array(session_id(), $_SERVER['HTTP_USER_AGENT']));
			$token = base64_encode(hash_hmac('sha512', $data, self::CSRF_SECRET, true));

			return $token;
		}

		public static function validateToken($token) {
			return ($token === self::getToken());
		}
	}