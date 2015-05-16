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
	 * Replaces standard placeholders in CKEditor content text
	 *
	 * @param  string $content
	 * @return string
	 */
	public static function replaceContentPlaceHolders($content)
	{
		$content = preg_replace('#\[\[mark\]\]#', '<span class="content-checkmark"></span>', $content);

		return $content;
	}
}