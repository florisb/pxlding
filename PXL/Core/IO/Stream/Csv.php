<?php
	namespace PXL\Core\IO\Stream;
	
	/**
	 * Csv class.
	 *
	 * Simple to use, object oriented class to generate CSV (Comma Seperated Values) files.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	class Csv {
		
		protected $_handle = null;
		
		public function __construct($fields = null) {
			$this->_handle = tmpfile();
			
			if (!is_null($fields)) {
			
				// Check fields type
				switch(true) {
					case (is_array($fields)):
						$fields = array_values($fields);
						break;
					
					case (is_object($fields) && in_array('PXL\Core\Collection\Collection', class_implements($fields))):	
					case (is_object($fields) && in_array('PXL\Core\Collection\Map', class_implements($fields))):
						$fields = $fields->toArray();
						break;
				
					default:
						throw new \InvalidArgumentException('Invalid type for argument "fields". Expected array, Collection or Map');
						break;
				}
				
				$this->_putFieldnames($fields);
			}
		}
		
		public function put(array $data) {
			fputcsv($this->_handle, $data);
			
			return $this;
		}
		
		public function putMany($data) {
			switch(true) {
				case (is_array($data)):
					array_walk($data, array($this, 'put'));
					break;
					
				case (is_object($data) && in_array('PXL\Core\Collection\Collection', class_implements($data))):
				case (is_object($data) && in_array('PXL\Core\Collection\Map', class_implements($data))):
					foreach($data as $d) {
						switch(true) {
							case (is_array($d)):
								$d = array_values($d);
								break;
								
							case (is_object($d) && in_array('PXL\Core\Collection\Collection', class_implements($d))):
							case (is_object($d) && in_array('PXL\Core\Collection\Map', class_implements($d))):
								$e = array();
								foreach($d->toArray() as $_d) {
									$e[] = $_d->getValue();
								}
								$d = $e;
								break;
								
							default:
								$d = (array) $d;
								break;
						}
						
						$this->put($d);
					}
					break;
					
				default:
					throw new \InvalidArgumentException('Invalid argument type. Expected array, Collection or Map');
					break;
			}
			
			return $this;
		}
		
		public function __toString() {
			return $this->getCsv(true);
		}
		
		public function getCsv($returnData = false, $filename = 'export.csv') {
			rewind($this->_handle);
			$csvString = stream_get_contents($this->_handle);
			
			if (!$returnData) {
				// Output correct headers
				header('Content-Encoding: UTF-8');
				header('Content-type: text/csv; charset=UTF-8');
				header("Content-Disposition: attachment; filename=$filename");
				echo "\xEF\xBB\xBF"; // UTF-8 BOM
				echo $csvString;
			} else {
				return $csvString;
			}
		}
		
		public function __destruct() {
			fclose($this->_handle);
		}
		
		protected function _putFieldnames(array $fields) {
			fputcsv($this->_handle, $fields);
		}
	}