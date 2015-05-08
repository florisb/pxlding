<?php
	namespace PXL\Core\Tools;
	
	use Imagick;
	use Exception;
	use ImagickException;
	use InvalidArgumentException;
	
	abstract class Image {
		
		/**
		 * Get the average pixel colour from the given file using Image Magick
		 * 
		 * @param string $filename
		 * @param bool $as_hex      Set to true, the function will return the 6 character HEX value of the colour.    
		 *                          If false, an array will be returned with r, g, b components.
		 */
		public static function get_average_colour($filename, $as_hex_string = true) {
			try {
				// Read image file with Image Magick
				$image = new Imagick();
				$image->readImageBlob($filename);
				// Scale down to 1x1 pixel to make Imagick do the average
				$image->scaleimage(1, 1);
				/** @var ImagickPixel $pixel */
				if(!$pixels = $image->getimagehistogram()) {
					return null;
				}
			} catch(ImagickException $e) {
				// Image Magick Error!
				return null;
			} catch(Exception $e) {
				// Unknown Error!
				return null;
			}
 
			$pixel = reset($pixels);
			$rgb = $pixel->getcolor();
 
			if($as_hex_string) {
				return sprintf('%02X%02X%02X', $rgb['r'], $rgb['g'], $rgb['b']);
			}
 
			return $rgb;
		}
		
		public static function rgbToHsl( $r, $g, $b ) {
			$oldR = $r;
			$oldG = $g;
			$oldB = $b;

			$r /= 255;
			$g /= 255;
			$b /= 255;

			$max = max( $r, $g, $b );
			$min = min( $r, $g, $b );

			$h;
			$s;
			$l = ( $max + $min ) / 2;
			$d = $max - $min;

    	if( $d == 0 ){
				$h = $s = 0; // achromatic
    	} else {
        $s = $d / ( 1 - abs( 2 * $l - 1 ) );

				switch( $max ){
					case $r:
						$h = 60 * fmod( ( ( $g - $b ) / $d ), 6 ); 
						if ($b > $g) {
							$h += 360;
						}
						break;

					case $g: 
						$h = 60 * ( ( $b - $r ) / $d + 2 ); 
						break;

					case $b: 
						$h = 60 * ( ( $r - $g ) / $d + 4 ); 
						break;
				}		        	        
			}

			return array( round( $h, 2 ), round( $s, 2 ), round( $l, 2 ) );
		}
		
		/**
		 * determineBestImageFontColor function.
		 * 
		 * Determines the optimal font color for a specific region
		 * of an image. The image is cropped using the Imagick::cropImage
		 * method, and the logic is performed on that cropped image.
		 *
		 * This method returns either "black" or "white", to indicate
		 * what font color is used best for the image.
		 *
		 * @access public
		 * @static
		 * @param mixed $filename
		 * @param mixed $width
		 * @param mixed $height
		 * @param mixed $x
		 * @param mixed $y
		 * @return black|white
		 */
		public static function determineBestImageFontColor($filename, $width, $height, $x, $y) {
			if (!is_file($filename)) {
				throw new InvalidArgumentException("Cannot determine optimal font color for image \"$filename\". File not found.");
			}
			
			$tmpHandle = fopen('php://temp', 'w+');
			$img       = new Imagick($filename);
			
			$img->cropImage($width, $height, $x, $y);
			$img->writeImageFile($tmpHandle);
			
			rewind($tmpHandle);
			list($r, $g, $b, $a) = array_values(static::get_average_colour(stream_get_contents($tmpHandle), false));
			list($h, $s, $l)     = static::rgbToHsl($r, $g, $b);
			fclose($tmpHandle);
						
			return ($l >= 0.5) ? 'black' : 'white';
		}
	}