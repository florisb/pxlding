<?php
/**************************************************************************
 * CLASS clsImage [PHP5] v1.0 09.12.2004      
 *
 * http://www.zutz.nl/phpclasses
 *
 * this is an image manipulation class for PHP5 with the Zend engine
 * based on the GD Library. supported imagetypes: [ jpg | gif | png ]
 * 
 * LICENSE
 * Public domain
 *
 * MODERATOR
 * Ronald Zötsch - ZUTZ Automatisering
 **************************************************************************/

include ("class.image.config.php");
include ("class.image.interface.php");

class clsImage implements interfaceImage {
/* constants */
  const IMAGEDIRSEPARATOR = IMAGEDIRSEPARATOR;
  const IMAGEBASEURL = IMAGEBASEURL;
  const IMAGEBASEPATH = IMAGEBASEPATH;
  const IMAGEFONTDIR = IMAGEFONTDIR;
	const IMAGEINTERLACE = IMAGEINTERLACE;
	const IMAGEJPEGQUALITY = IMAGEJPEGQUALITY;
	const IMAGEDEBUG = IMAGEDEBUG;	
	
/* properties */
  private $ImageStream;
  private $aProperties;
	protected $sFileLocation;
  protected $sImageURL;
		
	public $interlace;
	public $jpegquality;	

/* default methods */
  function __construct() {
	/* constructor */
	  $this->aProperties = array();
		$this->jpegquality = clsImage::IMAGEJPEGQUALITY;
		
		/* set interlace boolean */
		if (clsImage::IMAGEINTERLACE != 0) {
		  $this->interlace = true;
		}
		else {
		  $this->interlace = false;		
		}		
	}
	
  function __destruct() {
	/* destructor */
    unset($this->ImageStream);
    unset($this->aProperties);
	}
	
  public function __get($sPropertyName) {
	/* get properties */
		if (isset($this->aProperties[$sPropertyName])) {
		  $sPropertyValue = $this->aProperties[$sPropertyName];
		}
		else {	
      $sPropertyValue = NULL;
		}
		
    return($sPropertyValue);
  }
	
  public function __set($sPropertyName, $sPropertyValue) {
	/* set properties */
		if (!isset($this->aProperties)) {
		  $this->aProperties = array();
		}

    $this->aProperties[$sPropertyName] = $sPropertyValue;			
  }		
	
/* private methods */
	private function printError($sMessage, $sMethod = __METHOD__, $sLine = __LINE__) {
	/* echo errormessage to client and terminate script run */
	  if(clsImage::IMAGEDEBUG == 1) {
		  echo $sMethod . "(" . $sLine . ") " . $sMessage;
    }

	  if(clsImage::IMAGEDEBUG == 2) {
			header("Location: class.image.debug.code.php?line={$sLine}#{$sLine}");
    }


		exit;	
	}
	
  private function loadImage() {
	/* load a image from file */
	switch($this->type) {
		  case 1: 
			  $this->ImageStream = imagecreatefromgif($this->sFileLocation);
			  break;
			case 2:
			  $this->ImageStream = imagecreatefromjpeg($this->sFileLocation);			
			  break;
			case 3: 
			  $this->ImageStream = imagecreatefrompng($this->sFileLocation);			
			  break;
			default: 
			  $this->printError('invalid imagetype',__METHOD__,__LINE__); 
		}	
		
    if (!$this->ImageStream) {
		  $this->printError('image not loaded',__METHOD__,__LINE__); 
    }

	}
	
  private function saveImage() {
	/* store a memoryimage to file */
    if (!$this->ImageStream) {
		  $this->printError('image not loaded',__METHOD__,__LINE__); 
    }
		
	  switch($this->type) {
		  case 1: 
			  /* store a interlaced gif image */
			  if ($this->interlace === true) {
   			  imageinterlace($this->ImageStream, 1);
				}
				
			  imagegif($this->ImageStream,$this->sFileLocation);
			  break;
			case 2:
			  /* store a progressive jpeg image (with default quality value)*/
			  if ($this->interlace === true) {
   			  imageinterlace($this->ImageStream, 1);
				}
			  imagejpeg($this->ImageStream,$this->sFileLocation,$this->jpegquality);							
			  break;
			case 3: 
			  /* store a png image */
			  imagepng($this->ImageStream,$this->sFileLocation);			
			  break;
			default: 
			  $this->printError('invalid imagetype',__METHOD__,__LINE__); 
				
			if (!file_exists($this->sFileLocation)) {
				$this->printError('file not stored',__METHOD__,__LINE__); 		
			}				
		}			
	}	
	
  private function showImage() {
	/* show a memoryimage to screen */
    if (!$this->ImageStream) {
		  $this->printError('image not loaded',__METHOD__,__LINE__); 
    }
		
	  switch($this->type) {
		  case 1: 
			  imagegif($this->ImageStream);
			  break;
			case 2:
			  imagejpeg($this->ImageStream);			
			  break;
			case 3: 
			  imagepng($this->ImageStream);			
			  break;
			default: 
			  $this->printError('invalid imagetype',__METHOD__,__LINE__); 				
		}			
	}	
	
  private function setFilenameExtension() {
		/* set the image type and mimetype */
		if (!in_array(strtolower(Tools::file_extension($this->filename)), array('jpg', 'jpeg', 'gif', 'png'))) {
			$this->printError('invalid filename extension: '.$sOldFilenameExtension." (".$this->filename.")",__METHOD__,__LINE__);
		}
		
	  switch($this->type) {
		  case 1: 
			  $this->filename = substr($this->filename,0,strlen($this->filename) - 4) . '.gif';
			  break;
			case 2:
			  $this->filename = substr($this->filename,0,strlen($this->filename) - 4) . '.jpg';			
			  break;
			case 3: 
			  $this->filename = substr($this->filename,0,strlen($this->filename) - 4) . '.png';			
			  break;
			default: 
			  $this->printError('invalid imagetype',__METHOD__,__LINE__); 
		}			
	}

  private function setImageType($iType) {
	/* set the imahe type and mimetype */
	  switch($iType) {
		  case 1: 
			  $this->type = $iType;
			  $this->mimetype = 'image/gif';
				$this->setFilenameExtension();
			  break;
			case 2:
			  $this->type = $iType;
			  $this->mimetype = 'image/jpeg';
				$this->setFilenameExtension();							
			  break;
			case 3: 
			  $this->type = $iType;
			  $this->mimetype = 'image/png';
				$this->setFilenameExtension();							
			  break;
			default: 
			  $this->printError('invalid imagetype',__METHOD__,__LINE__); 
		}			
	}

	private function setLocations($sFileName) {
  /* set the photo url */
	  $this->filename = $sFileName;
    $this->sFileLocation = clsImage::IMAGEBASEPATH . clsImage::IMAGEDIRSEPARATOR . $this->filename;
    $this->sImageURL = clsImage::IMAGEBASEURL . '/' . $this->filename;		
	}
	
	private function initializeImageProperties() {
	/* get imagesize from file and set imagesize array */
	  list($this->width, $this->height, $iType, $this->htmlattributes) = getimagesize($this->sFileLocation);

		if (($this->width < 1) || ($this->height < 1)) {
		  $this->printError('invalid imagesize',__METHOD__,__LINE__); 
		}			 
		
		$this->setImageOrientation();
		$this->setImageType($iType);
	}

	private function setImageOrientation() {
	/* get image-orientation based on imagesize
	   options: [ portrait | landscape | square ] */
		 
		if ($this->width < $this->height) {
		  $this->orientation = 'portrait';
		}
		
		if ($this->width > $this->height) {
		  $this->orientation = 'landscape';
		}
		
		if ($this->width == $this->height) {
		  $this->orientation = 'square';
		}							
	}	
		
/* public methods */
	public function loadfile($sFileName) {
	/* load an image from file into memory */
		
	 $this->setLocations($sFileName);
		
		if (file_exists($this->sFileLocation)) {
			$this->initializeImageProperties();
			$this->loadImage();
		}
		else {
		  $this->printError('file not found: "'.$this->sFileLocation.'"',__METHOD__,__LINE__); 
		}

	}
	
	public function savefile($sFileName = NULL) {
  /* store memory image to file */
	  if ((isset($sFileName)) && ($sFileName != '')) {
      $this->setLocations($sFileName);
		}
	 
    $this->saveImage();
	}	
	
	public function preview() {
  /* print memory image to screen */
		header("Content-type: {$this->mimetype}");
    $this->showImage();	
	}	

	public function showhtml($sAltText = NULL, $sClassName = NULL) {
  /* print image as htmltag */
		if (file_exists($this->sFileLocation)) {
		  /* set html alt attribute */
		  if ((isset($sAltText)) && ($sAltText != '')) {
			  $htmlAlt = " alt=\"".$sAltText."\"";
			}
			else {
			  $htmlAlt = "";
			}
			
		  /* set html class attribute */
		  if ((isset($sClassName)) && ($sClassName != '')) {
			  $htmlClass = " class=\"".$sClassName."\"";
			}
			else {
			  $htmlClass = " border=\"0\"";
			}			
			
	    $sHTMLOutput = '<img src="'.$this->sImageURL.'"'.$htmlClass.' width="'.$this->width.'" height="'.$this->height.'"'.$htmlAlt.'>';	
			print $sHTMLOutput;
		}
		else {
		  $this->printError('file not found',__METHOD__,__LINE__); 
		}	
	}
	
	public function resize($iNewWidth, $iNewHeight) {
		/* resize the memoryimage do not keep ratio */
		
	    if (!$this->ImageStream) {
			  $this->printError('image not loaded',__METHOD__,__LINE__); 
	    }    
				
		if(function_exists("imagecopyresampled")){
			$ResizedImageStream = imagecreatetruecolor($iNewWidth, $iNewHeight);
			
			// alpha?
			if ($this->type == 1 || $this->type == 3) {
				imagealphablending($ResizedImageStream, false);
				imagesavealpha($ResizedImageStream,true);
				$transparent = imagecolorallocatealpha($ResizedImageStream, 255, 255, 255, 127);
				imagefilledrectangle($ResizedImageStream, 0, 0, $iNewWidth, $iNewHeight, $transparent);
			}

			imagecopyresampled($ResizedImageStream, $this->ImageStream, 0, 0, 0, 0, $iNewWidth, $iNewHeight, $this->width, $this->height);
		}
		else{
			$ResizedImageStream = imagecreate($iNewWidth, $iNewHeight);
				imagecopyresized($ResizedImageStream, $this->ImageStream, 0, 0, 0, 0, $iNewWidth, $iNewHeight, $this->width, $this->height);
		}		
		
		$this->ImageStream = $ResizedImageStream;
		$this->width = $iNewWidth;
		$this->height = $iNewHeight;
		$this->setImageOrientation();		
	}	
	
	public function resizetowidth($iNewWidth) {
  /* resize image to given width (keep ratio) */
		$iNewHeight = ($iNewWidth / $this->width) * $this->height;
		$this->resize($iNewWidth,$iNewHeight); 		
	}	
	
	public function resizetoheight($iNewHeight) {
  /* resize image to given height (keep ratio) */
		$iNewWidth = ($iNewHeight / $this->height) * $this->width;
		$this->resize($iNewWidth,$iNewHeight); 		
	}	
	
	public function resizetopercentage($iPercentage) {
  /* resize image to given percentage (keep ratio) */
		$iPercentageMultiplier = $iPercentage / 100;
		$iNewWidth = $this->width * $iPercentageMultiplier;
		$iNewHeight = $this->height * $iPercentageMultiplier;		
		
    $this->resize($iNewWidth,$iNewHeight);		
	}	
	
	public function crop($iNewWidth, $iNewHeight, $iResize = 0) {
  /* crop image (first resize with keep ratio) */
    if (!$this->ImageStream) {
		  $this->printError('image not loaded',__METHOD__,__LINE__); 
    }   
		
	/* resize imageobject in memory if resize percentage is set */
	if ($iResize > 0) {
	  $this->resizetopercentage($iResize);
	}		 
		
		/* constrain width and height values */
    if (($iNewWidth > $this->width) || ($iNewWidth < 0)) {
		  $this->printError('width out of range',__METHOD__,__LINE__); 
    } 
    if (($iNewHeight > $this->height) || ($iNewHeight < 0)) {
		  $this->printError('height out of range',__METHOD__,__LINE__); 
    }	
	  
		/* create blank image with new sizes */
		$CroppedImageStream = ImageCreateTrueColor($iNewWidth,$iNewHeight);
		
		/* calculate size-ratio */
		$iWidthRatio = $this->width / $iNewWidth;
		$iHeightRatio = $this->height / $iNewHeight;
		$iHalfNewHeight = $iNewHeight / 2;
		$iHalfNewWidth = $iNewWidth / 2;
		
		/* if the image orientation is landscape */
		if($this->orientation == 'landscape') {
			/* calculate resize width parameters */
			$iResizeWidth = $this->width / $iHeightRatio;
			$iHalfWidth = $iResizeWidth / 2;
			$iDiffWidth = $iHalfWidth - $iHalfNewWidth;
			
			if(function_exists("imagecopyresampled")){
				imagecopyresampled($CroppedImageStream,$this->ImageStream,-$iDiffWidth,0,0,0,$iResizeWidth,$iNewHeight,$this->width,$this->height);
			}
			else {
				imagecopyresized($CroppedImageStream,$this->ImageStream,-$iDiffWidth,0,0,0,$iResizeWidth,$iNewHeight,$this->width,$this->height);
			}		
		}
		/* if the image orientation is portrait or square */
		elseif(($this->orientation == 'portrait') || ($this->orientation == 'square')) {
		  /* calculate resize height parameters */		
			$iResizeHeight = $this->height / $iWidthRatio;
			$iHalfHeight = $iResizeHeight / 2;
			$iDiffHeight = $iHalfHeight - $iHalfNewHeight;
			
			$force_no_top_crop = false;
			
			if(function_exists("imagecopyresampled")){
				imagecopyresampled($CroppedImageStream,$this->ImageStream,0, ($force_no_top_crop ? 0 : -$iDiffHeight),0,0,$iNewWidth,$iResizeHeight,$this->width,$this->height);
			}
			else {
				imagecopyresized($CroppedImageStream,$this->ImageStream,0, ($force_no_top_crop ? 0 : -$iDiffHeight),0,0,$iNewWidth,$iResizeHeight,$this->width,$this->height);
			}	
		}
		else { 
			if(function_exists("imagecopyresampled")){
				imagecopyresampled($CroppedImageStream,$this->ImageStream,0,0,0,0,$iNewWidth,$iNewHeight,$this->width,$this->height);
			}
			else {
				imagecopyresized($CroppedImageStream,$this->ImageStream,0,0,0,0,$iNewWidth,$iNewHeight,$this->width,$this->height);
			}	
		}		

		$this->ImageStream = $CroppedImageStream;
		$this->width = $iNewWidth;
		$this->height = $iNewHeight;
		$this->setImageOrientation();											
	}

public function make_grayscale() {
	// by johan

	if (!$this->ImageStream) {
		  $this->printError('image not loaded',__METHOD__,__LINE__); 
    }
	
	// create new image with gray palette
	$im = imageCreate($this->width,$this->height);
	for ($c = 0; $c < 256; $c++) {
	    ImageColorAllocate($im, $c,$c,$c);
	} 
	
	// copy into grayscale image
	ImageCopyMerge($im,$this->ImageStream,0,0,0,0, $this->width, $this->height, 100);

	$this->ImageStream = $im;
}
	
public function resizeAspectCrop($iNewWidth, $iNewHeight, $forcePosition=array('center','middle'))
// by johan
{
	/* crop image (first resize with keep ratio) */
    if (!$this->ImageStream) {
		  $this->printError('image not loaded',__METHOD__,__LINE__); 
    }
	
	if ($iNewHeight == 1) {
		$iNewHeight = round(($iNewWidth / $this->width) * $this->height);
	}
	else if ($iNewWidth == 1) {
		$iNewWidth = round(($iNewHeight / $this->height) * $this->width);
	}
		
	/* constrain width and height values */
	/*
    if (($iNewWidth > $this->width) || ($iNewWidth < 0)) {
		  return $this->resize($iNewWidth, $iNewHeight);
    } 
    if (($iNewHeight > $this->height) || ($iNewHeight < 0)) {
		  return $this->resize($iNewWidth, $iNewHeight);
    }
	*/
	 
	/* create blank image with new sizes */
	$CroppedImageStream = ImageCreateTrueColor($iNewWidth,$iNewHeight);
	
	// support alpha
	if ($this->type == 1 || $this->type == 3) {
		imagealphablending($CroppedImageStream, false);
		imagesavealpha($CroppedImageStream,true);
		$transparent = imagecolorallocatealpha($CroppedImageStream, 255, 255, 255, 127);
		imagefilledrectangle($CroppedImageStream, 0, 0, $iNewWidth, $iNewHeight, $transparent);
	}
	
	$R1 = $this->width /  $this->height;
	$R2 = $iNewWidth   /  $iNewHeight;
	
	if ($R1 > $R2)
	{
		$this->resizetoheight($iNewHeight);
		
		if (in_array('center', $forcePosition))
			$cutX = ($this->width - $iNewWidth)/2;
		else if (in_array('left', $forcePosition))
			$cutX = 0;
		else if (in_array('right', $forcePosition))
			$cutX = $this->width - $iNewWidth;
		
		imagecopyresampled($CroppedImageStream, $this->ImageStream, 0, 0, $cutX, 0, $iNewWidth, $iNewHeight, $iNewWidth, $iNewHeight);
		$this->ImageStream = $CroppedImageStream;
	}
	else if ($R1 < $R2)
	{
		$this->resizetowidth($iNewWidth);
		
		if (in_array('middle', $forcePosition))
			$cutY = ($this->height - $iNewHeight)/2;
		else if (in_array('top', $forcePosition))
			$cutY = 0;
		else if (in_array('bottom', $forcePosition))
			$cutY = $this->height - $iNewHeight;
		

		imagecopyresampled($CroppedImageStream, $this->ImageStream, 0, 0, 0, $cutY, $iNewWidth, $iNewHeight, $iNewWidth, $iNewHeight);
		$this->ImageStream = $CroppedImageStream;
	}
	else // R1 == R2
	{
		$this->resizetowidth($iNewWidth); // doesnt matter, aspect ratio's are equal
	}
	
	$this->width = $iNewWidth;
	$this->height = $iNewHeight;
	$this->setImageOrientation();
}		
	
	public function writetext($sText, $iFontSize = 10, $sTextColor = '0,0,0', $sFontFilename = 'arial.ttf', $iXPos = 5, $iYPos = 15, $iTextAngle = 0) {
	    /* write text on image */
	    if (!$this->ImageStream) {
			  $this->printError('image not loaded',__METHOD__,__LINE__); 
	    } 
			
	    if (($iXPos > $this->width) || ($iXPos < 0)) {
			  $this->printError('x-pos out of range',__METHOD__,__LINE__); 
	    } 
			
	    if (($iYPos > $this->height) || ($iYPos < 0)) {
			  $this->printError('y-pos out of range',__METHOD__,__LINE__); 
	    } 				
			
		$sFont = clsImage::IMAGEFONTDIR . clsImage::IMAGEDIRSEPARATOR . $sFontFilename;
		$aTextColor = explode(',',$sTextColor,3);
		$ImageColor = imagecolorallocate($this->ImageStream,$aTextColor[0],$aTextColor[1],$aTextColor[2]);
		$iLineWidth = imagettfbbox($iFontSize, $iTextAngle, $sFont, $sText);
		imagettftext($this->ImageStream, $iFontSize, $iTextAngle, $iXPos, $iYPos, $ImageColor, $sFont, $sText);
	}		
	
	public function convert($sTargetType) {
	  
		/* convert image to given type [ jpg | gif | png ] */
	    if (!$this->ImageStream) {
			  $this->printError('image not loaded',__METHOD__,__LINE__); 
	    } 

		switch($sTargetType) {
		  case 'gif': 
			  $this->setImageType(1);
			  break;
			case 'jpg': 
			  $this->setImageType(2);
			  break;
			case 'png': 
			  $this->setImageType(3);
			  break;
		  default: $this->printError('invalid imagetype',__METHOD__,__LINE__); 
		}	  
	}

	public function watermark($mark, $position = array(50, 50)) {
		// by johan
		// -------
		// position parameter is an array (horizontal, vertical) describing the position in % from the image
		
		imagealphablending($this->ImageStream, TRUE);
		
		$watermark = imagecreatefrompng($mark);
		$watermark_width = imagesx($watermark);
		$watermark_height = imagesy($watermark);
		
		// check if we need to scale the watermark down
		$resizefactor = min($this->width / $watermark_width, $this->height / $watermark_height, 1);
		
		$dst_x = (($position[0] / 100) * $this->width)  - (($position[0] / 100) * $watermark_width * $resizefactor);
		$dst_y = (($position[1] / 100) * $this->height) - (($position[1] / 100) * $watermark_height * $resizefactor);
		
		imagecopyresampled($this->ImageStream,
					$watermark,
					$dst_x , // dst x
					$dst_y , // dst y
					0, // src x
					0, // src y
					$watermark_width * $resizefactor,  // dst w
					$watermark_height * $resizefactor, // dst h
					$watermark_width,  // src w
					$watermark_height  // src h
					);
				
		imagedestroy($watermark);
	}
	
	public function corners($name) {
		// by floris
		
		//north
		if(file_exists($name."_n.png")) {
			$corner = imagecreatefrompng($name."_n.png");
			$corner_width = imagesx($corner);
			$corner_height = imagesy($corner);
			$dst_x = 0;
			$dst_y = 0;
			while($dst_x < $this->width) {
				imagecopy($this->ImageStream,
						$corner,
						$dst_x,
						$dst_y,
						0,
						0,
						$corner_width,
						$corner_height
				);
				$dst_x += $corner_width;
			}
			imagedestroy($corner);
		}
		
		//south
		if(file_exists($name."_s.png")) {
			$corner = imagecreatefrompng($name."_s.png");
			$corner_width = imagesx($corner);
			$corner_height = imagesy($corner);
			$dst_x = 0;
			$dst_y = $this->height - $corner_height;
			while($dst_x < $this->width) {
				imagecopy($this->ImageStream,
						$corner,
						$dst_x,
						$dst_y,
						0,
						0,
						$corner_width,
						$corner_height
				);
				$dst_x += $corner_width;
			}
			imagedestroy($corner);
		}

		//west
		if(file_exists($name."_w.png")) {
			$corner = imagecreatefrompng($name."_w.png");
			$corner_width = imagesx($corner);
			$corner_height = imagesy($corner);
			$dst_x = 0;
			$dst_y = 0;
			while($dst_y < $this->height) {
				imagecopy($this->ImageStream,
						$corner,
						$dst_x,
						$dst_y,
						0,
						0,
						$corner_width,
						$corner_height
				);
				$dst_y += $corner_height;
			}
			imagedestroy($corner);
		}

		//east
		if(file_exists($name."_e.png")) {
			$corner = imagecreatefrompng($name."_e.png");
			$corner_width = imagesx($corner);
			$corner_height = imagesy($corner);
			$dst_x = $this->width - $corner_width;
			$dst_y = 0;
			while($dst_y < $this->height) {
				imagecopy($this->ImageStream,
						$corner,
						$dst_x,
						$dst_y,
						0,
						0,
						$corner_width,
						$corner_height
				);
				$dst_y += $corner_height;
			}
			imagedestroy($corner);
		}

		//northwest corner
		if(file_exists($name."_nw.png")) {
			$corner = imagecreatefrompng($name."_nw.png");
			$corner_width = imagesx($corner);
			$corner_height = imagesy($corner);
			$dst_x = 0;
			$dst_y = 0;
			imagecopy($this->ImageStream,
					$corner,
					$dst_x,
					$dst_y,
					0,
					0,
					$corner_width,
					$corner_height
			);
			imagedestroy($corner);
		}
		
		//northeastcorner
		if(file_exists($name."_ne.png")) {
			$corner = imagecreatefrompng($name."_ne.png");
			$corner_width = imagesx($corner);
			$corner_height = imagesy($corner);
			$dst_x = $this->width - $corner_width;
			$dst_y = 0;
			imagecopy($this->ImageStream,
					$corner,
					$dst_x,
					$dst_y,
					0,
					0,
					$corner_width,
					$corner_height
			);
			imagedestroy($corner);
		}
		
		//southwestcorner
		if(file_exists($name."_sw.png")) {
			$corner = imagecreatefrompng($name."_sw.png");
			$corner_width = imagesx($corner);
			$corner_height = imagesy($corner);
			$dst_x = 0;
			$dst_y = $this->height - $corner_height;
			imagecopy($this->ImageStream,
					$corner,
					$dst_x,
					$dst_y,
					0,
					0,
					$corner_width,
					$corner_height
			);
			imagedestroy($corner);
		}

		//southeastcorner
		if(file_exists($name."_se.png")) {
			$corner = imagecreatefrompng($name."_se.png");
			$corner_width = imagesx($corner);
			$corner_height = imagesy($corner);
			$dst_x = $this->width - $corner_width;
			$dst_y = $this->height - $corner_height;
			imagecopy($this->ImageStream,
					$corner,
					$dst_x,
					$dst_y,
					0,
					0,
					$corner_width,
					$corner_height
			);
			imagedestroy($corner);
		}
		
	}
	
	public function resizeAspect($iNewWidth, $iNewHeight, $color = array(255,255,255))
	// by floris
	{
		
		/* crop image (first resize with keep ratio) */
		if (!$this->ImageStream) {
			$this->printError('image not loaded',__METHOD__,__LINE__); 
		}
		
		if ($iNewHeight == 1) {
			$iNewHeight = round(($iNewWidth / $this->width) * $this->height);
		}
		else if ($iNewWidth == 1) {
			$iNewWidth = round(($iNewHeight / $this->height) * $this->width);
		}
			
		/* constrain width and height values */
		if (($iNewWidth > $this->width) || ($iNewWidth < 0)) {
			//return $this->resize($iNewWidth, $iNewHeight);
		} 
		if (($iNewHeight > $this->height) || ($iNewHeight < 0)) {
			//return $this->resize($iNewWidth, $iNewHeight);
		}	
		 
		/* create blank image with new sizes */
		$CroppedImageStream = ImageCreateTrueColor($iNewWidth,$iNewHeight);
		
		if (is_array($color)) {
			$background = imagecolorallocate($CroppedImageStream, $color[0], $color[1], $color[2]);
			imagefill($CroppedImageStream, 0, 0, $background);
		}
		
		// support alpha
		if ($this->type == 1 || $this->type == 3) {
			imagealphablending($CroppedImageStream, false);
			imagesavealpha($CroppedImageStream,true);
			$transparent = imagecolorallocatealpha($CroppedImageStream, $color[0], $color[1], $color[2], 127);
			imagefilledrectangle($CroppedImageStream, 0, 0, $iNewWidth, $iNewHeight, $transparent);
		}
		
		$R1 = $this->width /  $this->height;
		$R2 = $iNewWidth   /  $iNewHeight;
		
		if ($R1 > $R2)
		{
			$this->resizetowidth($iNewWidth);
			
			$newY = round($iNewHeight / 2) - ($this->height / 2);
			
			imagecopyresampled($CroppedImageStream, $this->ImageStream, 0, $newY, 0, 0, $this->width, $this->height, $this->width, $this->height);
			$this->ImageStream = $CroppedImageStream;
		}
		else if ($R1 < $R2)
		{
			$this->resizetoheight($iNewHeight);
			
			$newX = round($iNewWidth / 2) - ($this->width / 2);
			
			imagecopyresampled($CroppedImageStream, $this->ImageStream, $newX, 0, 0, 0, $this->width, $this->height, $this->width, $this->height);
			$this->ImageStream = $CroppedImageStream;
		}
		else // R1 == R2
		{
			$this->resizetowidth($iNewWidth); // doesnt matter, aspect ratio's are equal
		}
		
		$this->width = $iNewWidth;
		$this->height = $iNewHeight;
		$this->setImageOrientation();
	}
	
	
	function trim_trans() {
		// by johan
		
		// transparent
		$bg = imagecolorallocatealpha($this->ImageStream,0,0,0,127);
		
		// Get the image width and height.
		$imw = imagesx($this->ImageStream);
		$imh = imagesy($this->ImageStream);
		
		// Set the X variables.
		$xmin = $imw;
		$xmax = 0;
		
		// Start scanning for the edges.
		for ($iy=0; $iy<$imh; $iy++) {
			$first = true;
			for ($ix=0; $ix<$imw; $ix++) {
				$ndx = imagecolorat($this->ImageStream, $ix, $iy);
				if ($ndx % 255 != 127) {
					if ($xmin > $ix) { $xmin = $ix; }
					if ($xmax < $ix) { $xmax = $ix; }
					if (!isset($ymin)) { $ymin = $iy; }
					$ymax = $iy;
					if ($first) { $ix = $xmax; $first = false; }
				}
			}
		}
		
		// The new width and height of the image
		$imw = 1 + $xmax - $xmin; // Image width in pixels
		$imh = 1 + $ymax - $ymin; // Image height in pixels
		
		// Make another image to place the trimmed version in.
		$im2 = imagecreatetruecolor($imw, $imh);
		imagealphablending($im2, false);
		imagesavealpha($im2,true);
		
		// Make the background of the new image the same as the background of the old one.
		$bg2 = imagecolorallocate($im2, ($bg >> 16) & 0xFF, ($bg >> 8) & 0xFF, $bg & 0xFF);
		imagefill($im2, 0, 0, $bg2);
		
		// Copy it over to the new image.
		imagecopy($im2, $this->ImageStream, 0, 0, $xmin, $ymin, $imw, $imh);
		
		// To finish up, we replace the old image.
		
		$this->ImageStream = $im2;
		$this->width = imagesx($this->ImageStream);
		$this->height = imagesy($this->ImageStream);
		$this->setImageOrientation();
	}

	
	function rotate($orientation) {
		//by floris
		$width = imagesx($this->ImageStream);
		$height = imagesy($this->ImageStream);
		$im2 = imagecreatetruecolor($height,$width);

		if(function_exists("imagerotate")) { //bundled version of gd
			if($orientation == "right") {
				$im2 = imagerotate($this->ImageStream, 270, imagecolorallocatealpha($this->ImageStream, $color[0], $color[1], $color[2], 127));
			} else {
				$im2 = imagerotate($this->ImageStream, 90, imagecolorallocatealpha($this->ImageStream, $color[0], $color[1], $color[2], 127));
			}			
		} else {
			for($i = 0;$i < $width; $i++) {
				for($j = 0;$j < $height; $j++) {
					$ref = imagecolorat($this->ImageStream,$i,$j);
					if($orientation == "right") {
						imagesetpixel($im2,$height - $j,$i,$ref);
					} else {
						imagesetpixel($im2,$j, $width - $i,$ref);
					}
				}
			}
		}
		$this->ImageStream = $im2;
		$this->width = imagesx($this->ImageStream);
		$this->height = imagesy($this->ImageStream);
	}
}


?>