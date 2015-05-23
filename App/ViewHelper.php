<?php namespace App;

class ViewHelper {

	/**
	 * Simple urlencode DRY
	 * @param  string $string
	 * @return string
	 */
	public static function urlEncode($string)
	{
		return rawurlencode($string);
	}

	/**
	 * Formats bullets for the Services page asides, so placeholders are
	 * set accordingly
	 *
	 * @param  string $content
	 * @return string
	 */
	public static function formatServiceAsideBullets($content)
	{
		return static::replaceContentPlaceHolders($content);
	}

	/**
	 * Formats content for blog detail page
	 *
	 * @param  string $content
	 * @param  array  $images 	assoc array for image in correct order: keys:  src, alt
	 * @return string
	 */
	public static function formatBlogContent($content, $images = array())
	{

		// replace image placeholders
		$content = preg_replace_callback(
			'#\[\[img:?(\d+)(\|([^\]]+))?\]\]#i',
			function($matches) use ($images) {

				// 1 = img no
				// 3 = img class, if any
				$imgNum = $matches[1] - 1;

				if ( ! isset($images[ $imgNum ])) {
					return '<strong style="color: red;"><em>ERR: IMAGE NOT FOUND: "' . $imgNum . '"</em></strong>';
				}

				$img      = $images[ $imgNum ];
				$imgClass = 'default';

				if ( ! empty($matches[3])) {
					$imgClass .= ' ' . strtolower(trim($matches[3]));
				}

				return '<img src="' . $img['src'] . '" class="' . $imgClass . '" alt="' . $img['alt'] . '">';

			}, $content);


		return static::replaceContentPlaceHolders($content);
	}


	/**
	 * Replaces standard placeholders in CKEditor content text
	 *
	 * @param  string $content
	 * @return string
	 */
	public static function replaceContentPlaceHolders($content)
	{
		$content = preg_replace('#\[\[mark\]\]#', '<span class="content-checkmark"></span>', $content);

		// add h# classes to header tags
		$content = preg_replace('#<h(\d+)#', '<h\\1 class="h\\1"', $content);

		return $content;
	}


	/**
	 * Determines whether field is (really) empty. Could be that there's just
	 * your typical '&nbsp;' in there from FCK.
	 *
	 * @param  string $text
	 * @return boolean
	 */
	public static function textFieldEmpty($text)
	{
		$text = trim($text);

		if (empty($text)) return true;

		return preg_match('#^\s*(&nbsp;)*\s*$#i', $text);
	}

	/**
	 * Special case for case detail header: long titles will not fit.
	 * Should scale down based on how long the title is. Has several
	 * stages of scaling down. This returns css class names for it.
	 *
	 * @param  string $title
	 * @return string
	 */
	public static function getScaleClassForHugeTitleByLength($title)
	{
		$length = strlen( trim($title) );

		if ($length > 32) {
			return 'gargantuan';
		}

		if ($length > 26) {
			return 'huge';
		}

		if ($length > 20) {
			return 'large';
		}

		if ($length > 16) {
			return 'medium';
		}

		if ($length > 12) {
			return 'small';
		}

		return '';
	}

	/**
	 * Splits captions into a text and the phone/contact info, for
	 * better view rendering.
	 *
	 * @param  string $caption
	 * @return array() 		0 => paragraph, 1 => contact details
	 */
	public static function splitContactPathCaption($caption)
	{
		$caption = trim($caption);

		if (empty($caption)) return $caption;

		$return = array('', '');

		if ( ! preg_match('#^(.*)<br\s*/>\s*<br\s*/>(.*)$#i', $caption, $m)) {
			$return[0] = $caption;
			return $return;

		} else {
			// $return[0] = trim(trim($m[1]), '<br/>');
			// $return[1] = trim(trim($m[2]), '<br/>');
			$return[0] = preg_replace('#(\s*<br\s*/>\s*)*$#i', '', $m[1]);
			$return[1] = preg_replace('#(\s*<br\s*/>\s*)*$#i', '', $m[2]);
		}


		return $return;
	}
}