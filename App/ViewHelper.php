<?php namespace App;

class ViewHelper {

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
}