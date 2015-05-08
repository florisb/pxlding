<?php
	namespace PXL\Core\Exception;
	
	use Exception;
		
	/**
	 * DataErrorsException class.
	 * 
	 * Exception thrown by AbstractEntity when
	 * errors in data validation occurred.
	 *
	 * @extends Exception
	 * @author  Max van der Stam <max@pixelindustries.com>
	 */
	class DataErrorsException extends Exception {
		
		protected $errors = null;
		protected $data   = null;
		
		public function __construct($message = null, $code = 0, array $errors, $data = null) {
			parent::__construct($message, $code);
			
			$this->errors = $errors;
			$this->data   = $data;
		}
		
		public function getErrors() {
			return $this->errors;
		}
		
		public function getData() {
			return $this->data;
		}
		
		public function __toString() {
			foreach(debug_backtrace() as $trace) {
				if ($trace['class'] === 'PXL\Core\Tools\Logger' && $trace['function'] === 'log') {
					return implode("\n", array(parent::__toString(), 'Data errors:', var_export($this->errors, true)));
				}
			}
			
			if (function_exists('xdebug_print_function_stack')) {
				xdebug_print_function_stack('Data errors:' . var_export($this->errors, true));
				return parent::__toString();
			} else {
				return implode("\n", array(parent::__toString(), 'Data errors:', var_export($this->errors, true)));
			}
		}
	}