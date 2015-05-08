<?php
	namespace PXL\Core\Tools;

	/* ---------------------------------------------
	 * Emailer class
	 * ---------------------------------------------
	 * @author johank (johan@pixelindustries.com)
	 * ---------------------------------------------
	 * NB: all content passed to this class must be
	 *     encoded according to the input encoding
	 *     passed into the constructor:
	 *       - subjects
	 *       - plaintext body
	 *       - html bodies
	 *     default encoding is UTF-8; to use
	 *     something else, try for example:
	 *
	 *     new Emailer('ISO-8859-1');
	 *
	 * 
	 *
	 * HEADERS
	 * ---------------------------------------------
	 * $email = new Emailer();
	 * $email->addressee('johan@pixelindustries.com');
	 * $email->sender('johan@pixelindustries.com');
	 * $email->cc('rick@pixelindustries.com');
	 * $email->bcc('rick@pixelindustries.com');
	 * $email->subject('Johan Köhne');
	 *
	 *
	 * BODY
	 * ---------------------------------------------
	 * Support for plaintext and html messages
	 * ---------------------------------------------
	 * $email->message($plaintext);
	 * $email->messageHtml($html);
	 *
	 * a call to either function will overwrite a
	 * previously configured message, the last call
	 * defines the actual message
	 * 
	 *
	 * ---------------------------------------------
	 * Layout html-emails using a template
	 * ---------------------------------------------
	 * Extend the Emailer class with your own class,
	 * which implements the htmlHeader() and
	 * htmlFooter() functions. The returned html is
	 * pre-/appended to the regular html body, which
	 * was set through messageHtml().
	 * 
	 *
	 * ATTACHMENTS
	 * ---------------------------------------------
	 * Support for attachments by location or stream
	 * ---------------------------------------------
	 * $email->attachmentByLocation('test.pdf', 'downloads/av_pxl_25mrt2010.pdf');
	 * $email->attachmentByLocation('test.pdf', 'downloads/av_pxl_25mrt2010.pdf', 'application/pdf');
	 *
	 * $email->attachmentByStream('test.txt', 'Try this test-file contents', 'text/plain');
	 *
	 *
	 *
	 * SENDING
	 * ---------------------------------------------
	 * $email->send();
	 *
	 * will do all the other magic :)
	 * ---------------------------------------------
	 */

	class Emailer {
		
		protected $addressee = null;
		protected $sender    = null;
		protected $replyto	 = null;
		protected $cc        = null;
		protected $bcc       = null;
		protected $subject   = null;
		protected $body      = null;
		protected $base64    = false;

		private $inputEncoding  = '';
		private $hasAttachments = false;
		private $attachments    = '';
		private $mime_boundary  = '';
		private $eol            = "\n";
		
		public function __construct($encoding = 'UTF-8') {
			$this->inputEncoding = $encoding;
			$this->mime_boundary = '==Multipart_Boundary_x'.md5(time()).'x'; 
		}

		public function base64($toggle = true){
			$this->base64 = $toggle;
		}
		
		public function addressee($a) {
			// common mistake:
			// use semicolon instead comma (blame Outlook)
			// replace them for the user
			$this->addressee = str_replace(';', ',', $a);
		}
		
		public function sender($a) {
			$this->sender = $a;
		}
		
		public function replyto($a) {
			$this->replyto = (string) $a;
		}
		
		public function cc($a) {
			// common mistake:
			// use semicolon instead comma (blame Outlook)
			// replace them for the user
			$this->cc = str_replace(';', ',', $a);
		}
		
		public function bcc($a) {
			// common mistake:
			// use semicolon instead comma (blame Outlook)
			// replace them for the user
			$this->bcc = str_replace(';', ',', $a);
		}
		
		public function subject($a) {
			if($this->inputEncoding != "UTF-8") {
				$a = mb_convert_encoding($a, 'ISO-8859-1', $this->inputEncoding);
				$this->subject = mb_encode_mimeheader($a, "ISO-8859-1", "Q");
			} else {
				$this->subject = '=?UTF-8?B?'.base64_encode($a).'?=';
			}
		}
		
		public function message($a) {
			$this->bodyMime = 'text/plain';
			$this->body = $a;
		}
		
		// for html templates: header/footer
		// overload in your own class extension
		public function htmlHeader() { return ''; }
		public function htmlFooter() { return ''; }
		
		public function messageHtml($a) {
			$this->bodyMime = 'text/html';
			$this->body = $this->htmlHeader() . $a . $this->htmlFooter();
		}
		
		public function attachmentByLocation($file_name, $file_path, $file_type = "application/octet-stream") {
			$this->addAttachment($file_name, $file_path, null, $file_type);
		}
		
		public function attachmentByStream($file_name, $file_data, $file_type = "application/octet-stream") {
			$this->addAttachment($file_name, '', $file_data, $file_type);
		}
		
		protected function addAttachment($file_name, $file_path, $file_data = null, $file_type = "application/octet-stream") {
			$this->hasAttachments = true;
			
			if (is_null($file_data)) {
				// file_get is a PXL.Framework function with support for http:// file-loading
				if (function_exists('file_get')) {
					$file_data = file_get($file_path);
				} else {
					$file_data = file_get_contents($file_path);
				}
			}
			
			$file_data = chunk_split(base64_encode($file_data));
			
			$this->attachments .= '--' . $this->mime_boundary . $this->eol .
								  'Content-Type: ' . $file_type . '; name="' . $file_name . '"' . $this->eol .
								  'Content-Disposition: attachment; filename="' . $file_name . '"' . $this->eol .
								  'Content-Transfer-Encoding: base64' . $this->eol . $this->eol .
								  $file_data . $this->eol;
		}
		
		public function send() {
			$checkProperties = array('addressee', 'sender', 'subject', 'body');
			
			foreach ($checkProperties as $check) {
				if (is_null($this->{$check})) {
					trigger_error($check." not specified to Emailer class", E_USER_ERROR);
				}
			}
			
			if ($this->hasAttachments) {
				$sendbody = 'This is a multi-part message in MIME format.' . $this->eol .
							  '--' . $this->mime_boundary . $this->eol .
							  'Content-Type: '.$this->bodyMime.'; charset="' . $this->inputEncoding .'"' . $this->eol .
							  'Content-Transfer-Encoding: 7bit' . $this->eol .
							  $this->eol .
							  $this->body .
							  $this->eol .
							  $this->attachments .
							  '--' . $this->mime_boundary . '--';
			} else {
				if($this->base64){
					$sendbody = rtrim(chunk_split(base64_encode($this->body)));
				}else{
					$sendbody = $this->body;
				}
			}

			preg_match_all('/\b[A-Z0-9._%+-]+@(?:[A-Z0-9-]+\.)+[A-Z]{2,6}\b/i', $this->sender, $result, PREG_PATTERN_ORDER);
			$fSender = $result[0][0];

			return mail(
				$this->addressee,
				$this->subject,
				$sendbody,
				$this->_headers(),
				"-f".$fSender
			);
		}
		
		protected function _headers() {
			$headers = array();
			
			$headers[] = "MIME-Version: 1.0";
			if ($this->hasAttachments) {
				$headers[] = 'Content-Type: multipart/mixed; boundary="'.$this->mime_boundary.'"'; 
			} else {
				$headers[] = "Content-Type: ".$this->bodyMime."; charset=".$this->inputEncoding;
			}
			
			$headers[] = "From: ".$this->sender;
			
			if (!empty($this->cc)) {
				$headers[] = "Cc: ".$this->cc;
			}
			
			if (!empty($this->bcc)) {
				$headers[] = "Bcc: ".$this->bcc;
			}
			if (is_null($this->replyto) || strlen($this->replyto) == 0) {
				$headers[] = "Reply-To: ".$this->sender;
			}
			else {
				$headers[] = "Reply-To: ".$this->replyto;
			}
			$headers[] = "Return-Path: ".$this->sender;
			$headers[] = "X-Mailer: PHP v".phpversion();
			
			// no support for base64 encoded transfers with attachments yet
			// todo!
			if (!$this->hasAttachments && $this->base64) {
				$headers[] = 'Content-Transfer-Encoding: base64';
			}
			
			return implode($this->eol, $headers).$this->eol;
		}
	}
