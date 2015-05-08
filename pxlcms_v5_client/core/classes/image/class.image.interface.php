<?php
/**************************************************************************
 * INTERFACE interfaceImage (filename: class.image.interface.php)
 *
 * This is the interface definition and documentation for 
 * the class clsImage.
 */
 
interface interfaceImage {
/**
 * Load an imagefile into memory.
 * @constant GLOBAL string IMAGEBASEPATH  
 * @param string $sFileName - the filename of the imagefile
 */
  public function loadfile($sFileName);
	
/**
 * Store the memoryimage to file.
 * @constant GLOBAL string IMAGEBASEPATH   
 * @param string $sFileName [default: clsImage->filename] - the new filename of the memoryimage
 */
  public function savefile($sFileName = NULL); 
	
/**
 * Send the memoryimage to the client browser with mimetype header.
 * The script terminates after sending the preview to the client, a
 * new header will be send.
 */	 
  public function preview();
	
/**
 * Echo the imagefile as a html imagetag to the client browser. The imagefile is used not
 * the memoryimage, store the memoryimage to file first.
 * @constant GLOBAL string IMAGEBASEURL    
 * @param string $sAltText [default: no alt text] - the alt text for the html imagetag
 * @param string $sClassName [default: no class] - the css classname for the html imagetag 
 */
	public function showhtml($sAltText = NULL, $sClassName = NULL);
	
/**
 * Resize the memoryimage to a given width and height, do not keep the aspect ratio.
 * @param integer $iNewWidth - the new width of the memoryimage in pixels
 * @param integer $iNewHeight - the new height of the memoryimage in pixels 
 */	
	public function resize($iNewWidth, $iNewHeight);

/**
 * Convert the memoryimage, change the imagetype and mimetype. Also the
 * filename will be changed to the corresponding filetype extension.
 * @param string $sTargetType [options: jpg | gif | png ] - the new imagetype
 */		
  public function convert($sTargetType);

/**
 * Resize the memoryimage to a given width, keep the aspect ratio. The height
 * of the memoryimage will be changed according to the aspect ratio.
 * @param integer $iNewWidth - the new width of the memoryimage in pixels
 */	
	public function resizetowidth($iNewWidth);
	
/**
 * Resize the memoryimage to a given height, keep the aspect ratio. The width
 * of the memoryimage will be changed according to the aspect ratio.
 * @param integer $iNewHeight - the new height of the memoryimage in pixels
 */		
	public function resizetoheight($iNewHeight);

/**
 * Resize the memoryimage to a given percentage, keep the aspect ratio. The width
 * of the memoryimage will be changed according to the aspect ratio.
 * @param integer $iPercentage - the resize percentage
 */
	public function resizetopercentage($iPercentage); 
	
/**
 * Resize and crop the memoryimage to a given height and width, keep the aspect ratio.
 * Before cropping a resize will be executed, after the resize the crop is executed.
 * @param integer $iNewWidth - the new width of the memoryimage in pixels
 * @param integer $iNewHeight - the new height of the memoryimage in pixels 
 * @param integer $iResize [default: 0]- the resize value in percentage (%)  
 */	
	public function crop($iNewWidth, $iNewHeight, $iResize = 0);
	
/** 
 * Write text on the memoryimage, use several parameters to define the text-layout. 
 * @constant GLOBAL string IMAGEFONTDIR 
 * @param string $sText - the text
 * @param integer $iFontSize - the text fontsize in pixels 
 * @param string $sTextColor [default: 0,0,0] [range: 0..255,0..255,0..255] - the text color in comma seperated RGB values
 * @param string $sFontname [default: arial.ttf] - the truetype font filename 
 * @param integer $iXPos [default: 5] [range: 0..clsImage->width] - the text start X-pixel-position  
 * @param integer $iYPos [default: 15] [range: 0..clsImage->height] - the text start Y-pixel-position 
 * @param integer $iTextAngle [default: 0] [range: 0..360] - the text rotation-angle     
 */	
  public function writetext($sText, $iFontSize = 10, $sTextColor = '0,0,0', $sFontname = 'arial.ttf', $iXPos = 5, $iYPos = 15, $iTextAngle = 0);
}
?>