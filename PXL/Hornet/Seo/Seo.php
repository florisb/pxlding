<?php
	namespace PXL\Hornet\Seo;

	use PXL\Core\Tools;	
	use PXL\Hornet\Application\Application;
	
	/**
	 * Seo class.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */
	abstract class Seo {
		
		protected static $_titleParts  = array();
		protected static $_keywords    = array();
		protected static $_description = '';
		protected static $_robots      = 'index, follow';
		
		protected static $_keywordsGlue = ', ';
		protected static $_titleGlue    = ' | ';
		
		protected static $_canonical = null;
		protected static $_next      = null;
		protected static $_prev      = null;

		protected static $breadCrumbs = array();		
		
		public static function setKeywordsGlue($glue) {
			self::$_keywordsGlue = $glue;
		}
		
		public static function getKeywordsGlue() {
			return self::$_keywordsGlue;
		}
		
		public static function setTitleGlue($glue) {
			self::$_titleGlue = $glue;
		}
		
		public static function getTitleGlue() {
			return self::$_titleGlue;
		}
		
		public static function addTitle($title) {
			self::$_titleParts[] = str_replace(array("\t", "\r", "\n"), '', strip_tags($title));
		}
		
		public static function getTitle() {
			return implode(self::$_titleGlue, array_reverse(self::$_titleParts));
		}
		
		public static function addTitles($titles) {
			self::$_titleParts += array_reverse($titles);
		}
		
		public static function emptyTitle() {
			self::$_titleParts = array();
		}
		
		public static function addKeywords(array $keywords) {
			self::$_keywords += $keywords;
		}
		
		public static function addKeyword($keyword) {
			if (!in_array($keywords, self::$_keywords)) {
				self::$_keywords[] = $keyword;
			}
		}
		
		public static function getKeywords() {
			return implode(self::$_keywordsGlue, self::$_keywords);
		}
		
		public static function getDescription() {
			return self::$_description;
		}
		
		public static function setDescription($description) {
			self::$_description = str_replace(array("\t", "\r", "\n"), '', strip_tags($description));
		}
		
		public static function setRobots($value) {
			self::$_robots = $value;
		}
		
		public static function getRobots() {
			return self::$_robots;
		}
		
		public static function setCanonical($url, $prependBaseUrl = true) {
			self::$_canonical = ($prependBaseUrl ? Application::getInstance()->getRequest()->getBaseUrl() : '') . trim($url, '/') . '/';
		}
		
		public static function getCanonical() {
			return self::$_canonical;
		}
		
		public static function setNext($url, $prependBaseUrl = true) {
			self::$_next = ($prependBaseUrl ? Application::getInstance()->getRequest()->getBaseUrl() : '') . trim($url, '/') . '/';
		}
		
		public static function getNext() {
			return self::$_next;
		}
		
		
		public static function setPrev($url, $prependBaseUrl = true) {
			self::$_prev = ($prependBaseUrl ? Application::getInstance()->getRequest()->getBaseUrl() : '') . trim($url, '/') . '/';
		}
		
		public static function getPrev() {
			return self::$_prev;
		}
		
		public static function getDataAsArray() {
			return array(
				'keywords'    => self::getKeyWords(),
				'title'       => self::getTitle(),
				'description' => self::getDescription(),
				'robots'      => self::getRobots(),
				'canonical'   => self::getCanonical(),
				'next'        => self::getNext(),
				'prev'        => self::getPrev()
			);
		}
		
		public static function slug($s) {
			return Tools\String::toAscii((string) $s);
		}

		public static function addBreadCrumb($url, $name) {
			$crumb = array(
				'url'  => $url,
				'name' => $name
			);

			if (!in_array($crumb, self::$breadCrumbs)) {
				self::$breadCrumbs[] = $crumb;
			}
		}

		public static function getBreadCrumbs() {
			return self::$breadCrumbs;
		}
	}