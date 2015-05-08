<?php
	namespace PXL\Core\Tools;
	
	use PXL\Core\Session\Session;
	
	/**
	 * Abstract Number class.
	 * 
	 * Generic tools for number-type data.
	 *
	 * @abstract
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	abstract class Number {
	
		protected static $_powerNames = array(
			116 => array(
				1  => 'tien',
				2  => 'honderd',
				3  => 'duizend',
				6  => 'miljoen',
				9  => 'miljard',
				12 => 'biljard'
			),
			38 => array(
				1  => 'ten',
				2  => 'hundred',
				3  => 'thousand',
				6  => 'million',
				9  => 'billion',
				12 => 'trillion'
			),
			33 => array(
				1  => 'zehn',
				2  => 'hundert',
				3  => 'tausend',
				6  => 'Million',
				9  => 'Milliarde',
				12 => 'Billion'
			)
		);
	
		protected static $_numberNames = array(
			116 => array(
				1 => 'een',
				2 => 'twee',
				3 => 'drie',
				4 => 'vier',
				5 => 'vijf',
				6 => 'zes',
				7 => 'zeven',
				8 => 'acht',
				9 => 'negen'
			),
			38 => array(
				1 => 'one',
				2 => 'two',
				3 => 'three',
				4 => 'four',
				5 => 'five',
				6 => 'six',
				7 => 'seven',
				8 => 'eight',
				9 => 'nine'
			),
			33 => array(
				1 => 'eins',
				2 => 'zwei',
				3 => 'drei',
				4 => 'vier',
				5 => 'fünf',
				6 => 'sechs',
				7 => 'sieben',
				8 => 'acht',
				9 => 'neun'
			)
		);
	
		protected static $_decimalNames = array(
			116 => array(
				1 => 'tien',
				2 => 'twintig',
				3 => 'dertig',
				4 => 'veertig',
				5 => 'vijftig',
				6 => 'zestig',
				7 => 'zeventig',
				8 => 'tachtig',
				9 => 'negentig'
			),
			38 => array(
				1 => 'teen',
				2 => 'twenty',
				3 => 'thirty',
				4 => 'fourty',
				5 => 'fifty',
				6 => 'sixty',
				7 => 'seventy',
				8 => 'eighty',
				9 => 'ninety'
			),
			33 => array(
				1 => 'zehn',
				2 => 'zwanzig',
				3 => 'dreißig',
				4 => 'vierzig',
				5 => 'fünfzig',
				6 => 'sechzig',
				7 => 'siebzig',
				8 => 'achtzig',
				9 => 'neunzig'
			)
		);
	
		protected static $_exceptions = array(
			116 => array(
				11 => 'elf',
				12 => 'twaalf',
				13 => 'dertien',
				14 => 'veertien'
			),
			38  => array(
				11 => 'eleven',
				12 => 'twelve'	
			),
			33 => array(
				11 => 'elf',
				12 => 'zwölf'
			)
		);
	
		const LANGUAGE_NL = 116;
		const LANGUAGE_EN = 38;
		const LANGUAGE_DE = 33;
		
		/**
		 * toText function.
		 *
		 * Converts a number into a numeral. Supports some Germanic
		 * languages, but could be extended with additional routines
		 * to support more complex (Romanic) languages as well.
		 *
		 * @access public
		 * @static
		 * @param mixed $number
		 * @param boolean $uppercase (default: false)
		 * @param mixed $language (default: null)
		 * @author Max van der Stam <max@pixelindustries.com>
		 * @return string $text
		 */
		public static function toText($number, $uppercase = false, $language = null) {
			$supportedLanguages = array(116, 38, 33);
		
			// Try and fetch current language from Session
			if (is_null($language)) {
				try {
					$language = Session::get('_language_id');
				} catch(\Exception $e) {
					// Continue gracefully
					$language = null;
				}
			}
			
			// Make sure only valid languages are used
			if (!in_array($language, $supportedLanguages)) {
				$language = self::LANGUAGE_EN;
			}
			
			$text            = array();
			$valueString     = (string) floor($number);
			$priceComponents = str_split($valueString, 1);
			$priceDecimals   = count($priceComponents);
			$chunks          = array_map('array_reverse', array_reverse(array_chunk(array_reverse($priceComponents), 3)));
		
			foreach($chunks as $chunk) {
				$chunkPosition = array_search($chunk, $chunks);
				$chunkSize     = count($chunk);
		
				switch(true) {
					case ($chunkSize === 3):
						if ($chunk[0] !== '0') {
							switch($language) {
								case self::LANGUAGE_NL:
								case self::LANGUAGE_DE:
									if ($chunk[0] !== '1') {
										$text[] = self::$_numberNames[$language][$chunk[0]];
									}
									
									$text[] = self::$_powerNames[$language][2];
									break;
									
								case self::LANGUAGE_EN:
									$text[] = self::$_numberNames[$language][$chunk[0]];
									$text[] = ' ' . self::$_powerNames[$language][2] . ' ';
									break;
									
								default:
									break;
							}	
						}
					
						self::_parseBiDecimalNumber($chunk, $language, $chunks, $priceDecimals, $chunkPosition, $text);
						break;
					
					case ($chunkSize === 2):
						self::_parseBiDecimalNumber($chunk, $language, $chunks, $priceDecimals, $chunkPosition, $text);
						break;
					
					case ($chunkSize === 1):
						$pow    = $priceDecimals - ($chunkPosition + 1);
						switch($language) {
							case self::LANGUAGE_NL:
							case self::LANGUAGE_DE:
								switch(true) {
									case ($pow === 3):
										if ($chunk[0] !== '1') {
											$text[] = self::$_numberNames[$language][$chunk[0]];
										}
										$text[] = self::$_powerNames[$language][$pow];
										$text[] = ' ';
										break;
										
									case ($pow >= 6):
										$text[] = self::$_numberNames[$language][$chunk[0]];
										$text[] = ' ';
										$text[] = self::$_powerNames[$language][$pow];
										$text[] = ' ';
										break;
										
									default:
										$text[] = self::$_numberNames[$language][$chunk[0]];
										$text[] = self::$_powerNames[$language][$pow];
										break;
								}
								break;
								
							case self::LANGUAGE_EN:
								$text[] = self::$_numberNames[$language][$chunk[0]];
								$text[] = ' ';
								$text[] = self::$_powerNames[$language][$pow];
								$text[] = ' ';
								break;
								
							default:
								break;
						}
						break;
					
					default:
						break;
				}
			}
			
			$text = implode('', $text);
			
			if ($uppercase) {
				$text = str_replace(array('ë'), array('Ë'), strtoupper($text));
			}
			
			return $text;
		}
		
		protected static function _parseBiDecimalNumber($chunk, $language, $chunks = array(), $priceDecimals, $chunkPosition, &$text) {
			switch(true) {
				case (count($chunk) === 3):
					$chunk = array($chunk[1], $chunk[2]);
					break;
					
				case (count($chunk) === 2):
					break;
					
				default:
					throw new \BadMethodCallException('Invalid chunk length to parse decimal number');
					break;
			}
			
			$decimal = (int) ($chunk[0].$chunk[1]);
		
			if (array_key_exists($decimal, self::$_exceptions[$language])) {
				$text[] = self::$_exceptions[$language][$decimal];
			} else {
				switch($language) {
					case self::LANGUAGE_NL:
					case self::LANGUAGE_DE:
						switch(true) {
							case ($chunk[0] === '1'):
								if ($chunk[1] === '0') {
									$text[] = self::$_powerNames[$language][$chunk[0]];
								} else {
									$text[] = self::$_numberNames[$language][$chunk[1]];
									$text[] = self::$_decimalNames[$language][$chunk[0]];
								}
								break;
								
							case ($chunk[0] === '0'):
								$text[]   = self::$_numberNames[$language][$chunk[1]];
								break;
								
							default:
								$text[]   = self::$_numberNames[$language][$chunk[1]];
								
								$lastText = end($text);
								if ($lastText) {
									switch($language) {
										case self::LANGUAGE_NL:
											if ($lastText{strlen($lastText)-1} === 'e') {
												$text[] = 'ën';
											} else {
												$text[] = 'en';
											}
											break;
											
										case self::LANGUAGE_DE:
											$text[] = 'und';
											break;
											
										default:
											break;
									}
								}
								
								$text[] = self::$_decimalNames[$language][$chunk[0]];
								break;
						}
						break;
							
					case self::LANGUAGE_EN:
						switch(true) {
							case ($chunk[0] === '0' && $chunk[1] !== '0'):
								$text[] = self::$_numberNames[$language][$chunk[1]];
								break;
							
							case ($chunk[0] === '1'):
								if ($chunk[1] === '0') {
									$text[] = self::$_powerNames[$language][$chunk[0]];
								} else {
									$text[] = self::$_numberNames[$language][$chunk[1]];
									$text[] = self::$_decimalNames[$language][$chunk[0]];
								}
								break;
							
							default:
								$text[] = self::$_decimalNames[$language][$chunk[0]];
							
								if ($chunk[1] !== '0') {
									$text[] = '-';
									$text[] = self::$_numberNames[$language][$chunk[1]];
								}
								break;
						}
						break;
					
					default:
						break;
				}
			}
		
			if ($chunks && $chunk !== end($chunks)) {
				$count = 0;
				foreach($chunks as $key => $_chunk) {
					$count += count($_chunk);
					if ($key === $chunkPosition) {
						break;
					}
				}
				
				$pow = $priceDecimals - $count;
				
				switch($language) {
					case self::LANGUAGE_NL:
					case self::LANGUAGE_DE:
						switch(true) {
							case ($pow === 3):
								$text[] = self::$_powerNames[$language][$pow] . ' ';
								
								break;
								
							case ($pow >= 6):
								$text[] = ' ' . self::$_powerNames[$language][$pow] . ' ';
								break;
								
							default:
								break;
						}
						break;
						
					case self::LANGUAGE_EN:
						$text[] = ' ' . self::$_powerNames[$language][$pow] . ' ';
						break;
						
					default:
						break;
				}
			}
		}
	}