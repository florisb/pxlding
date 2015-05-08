<?php

	class HtmlEmailer {
		public $addressee = null;
		public $subject   = 'HtmlEmailer/default';
		public $sender    = 'info@pixelindustries.com';
		public $cc    = '';
		public $bcc 	= '';
		public $reply    = 'info@pixelindustries.com';
		public $body      = '<html><body>HtmlEmailer-test.</body></html>';
		
		private function _headers() {
			$eol = "\r\n";
			$headers  = "MIME-Version: 1.0".$eol;
			$headers .= "Content-type: text/html; charset=utf-8".$eol;
			$headers .= "From: {%%SENDER%%}".$eol;
			
			if(!empty($this->cc)) 
				$headers .= "Cc: {%%CC%%}".$eol;
			if(!empty($this->bcc)) 
				$headers .= "Bcc: {%%BCC%%}".$eol;
				
			$headers .= "Reply-To: {%%REPLY%%}".$eol;
			$headers .= "Return-Path: {%%SENDER%%}".$eol;
			$headers .= "X-Mailer: PHP v".phpversion().$eol;

			$headers = str_replace("{%%SENDER%%}", $this->sender, $headers);
			if(!empty($this->cc)) $headers = str_replace("{%%CC%%}", $this->cc, $headers);
			if(!empty($this->bcc)) $headers = str_replace("{%%BCC%%}", $this->bcc, $headers);
			$headers = str_replace("{%%REPLY%%}", $this->reply, $headers);
			return $headers;
		}
		
		public function set_plain_body($txt) {
			$b = nl2br(htmlentities($txt));
			$b = preg_replace("/[-]{5,}/", "<hr/>", $b);
			$this->body = "<html><body style='font-family: Courier New, sans-serif;'>".$b.'</body></html>';
		}
		
		public function send() {
			if ($this->addressee == null) die("HtmlEmailer.send(): addressee not set");
			mail($this->addressee, $this->subject, $this->body, $this->_headers());
		}
	}
?>