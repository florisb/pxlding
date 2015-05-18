<?php


	/**
	 * Hooks.php
	 *
	 * Handles various routines that need to run when a certain
	 * CRUD action happends CMS-wise.
	 *
	 * @author Max van der Stam <max@pixelindustries.com>
	 */

	function toAscii($str, $replace=array(), $delimiter='-') {
			setlocale(LC_ALL, 'en_US.UTF8');
			if( !empty($replace) ) {
				$str = str_replace((array)$replace, ' ', $str);
			}

			$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
			$clean = preg_replace("/[^a-zA-Z0-9\/_|+ -]/", '', $clean);
			$clean = strtolower(trim($clean, '-'));
			$clean = preg_replace("/[\/_|+ -]+/", $delimiter, $clean);

			return $clean;
		}

	/**
	 * updateSlugs function.
	 *
	 * Updates the slugs table accordingly, by using a field
	 * named "name" as data to generate the slug.
	 *
	 * @access public
	 * @param mixed $data
	 * @param mixed $ref_module_id
	 * @return void
	 */
	function updateSlugs($entry_id) {
		//Determine current module ID and language
		$language_id   = $_SESSION['cms_v5_session']['language']['id'];
		$ref_module_id = $_SESSION['cms_v5_session']['module_id'];

		//Fetch ML table name
		$sMLModule = getModuleNameML($ref_module_id);

		// use different field than name?
		$nameField = 'name';
		switch ($ref_module_id) {

			case 13: // services
			case 15: // blog
				$nameField = 'slug';
				break;
		}

		if (!$sMLModule) {
			//Try and fetch regular table name
			$sModule = getModuleName($ref_module_id);

			if (!$sModule) {
				return;
			}

			$sName       = getNameValue($sModule, $entry_id, $nameField);
			$language_id = 116;
		} else {
			$sName       = getNameMLValue($sMLModule, $entry_id, $language_id, $nameField);

			if (!$sName) {
				//Try and fetch regular table name
				$sModule = getModuleName($ref_module_id);
				$sName   = getNameValue($sModule, $entry_id, $nameField);
				$language_id = 116;
			}
		}

		if (!$sName) {
			return; //No name value found
		}

		//Determine slug
		$sSlug = toAscii(trim($sName));

		//Fetch latest slug that exists for this entry, module and language
		$sCurrentSlug = getCurrentSlug($ref_module_id, $language_id, $entry_id);

		//If the current slug differs from the one we've just generated, insert it into the Slugs table as the newest entry
		if ($sSlug !== $sCurrentSlug || empty($sCurrentSlug)) {
			insertSlug($ref_module_id, $language_id, $entry_id, $sSlug);
		}
	}

	/**
	 * cleanupSlugs function.
	 *
	 * Cleans up entries in the Slugs table after an entry has been
	 * removed from the database.
	 *
	 * @access public
	 * @param mixed $entry_id
	 * @return void
	 */
	function cleanupSlugs($entry_id) {
		//Determine current module ID
		$ref_module_id = $_SESSION['cms_v5_session']['module_id'];

		$q = <<<SQL
			DELETE FROM
				`cms_m3_slugs`
			WHERE
				`ref_module_id`='%d'
			AND
				`entry_id`='%d'
SQL;

		$q = sprintf($q, $ref_module_id, $entry_id);
		mysql_query($q);
	}

	function getModuleName($ref_module_id) {
		global $CMS_DB;

		$q = <<<SQL
			SHOW TABLES
				WHERE
					`Tables_in_%1\$s` REGEXP '^cms_m%2\$d_(.*)$'
				AND
					`Tables_in_%1\$s` NOT REGEXP '^cms_m%2\$d_(.*)_ml$';
SQL;

		$q        = sprintf($q, pxl_db_safe($CMS_DB['db_name']), $ref_module_id);
		$result   = mysql_query($q);
		$rows     = mysql_num_rows($result);

		if (!$result) {
			return false;
		}

		if ($rows === 0) {
			return null;
		} else {
			$row = mysql_fetch_row($result);
			return $row[0];
		}
	}

	/**
	 * getModuleNameML function.
	 *
	 * Fetches the ML module (table) name of a
	 * certain module ID.
	 *
	 * @access public
	 * @param mixed $ref_module_id
	 * @return void
	 */
	function getModuleNameML($ref_module_id) {
	 	 $q = <<<SQL
		 	 SHOW TABLES LIKE 'cms_m%d\_%%\_ml'
SQL;

		 $q      = sprintf($q, $ref_module_id);
		 $result = mysql_query($q);
		 $rows   = mysql_num_rows($result);

		 if (!$result) {
			 return false; //This shouldn't run
		 }

		 if ($rows === 0) {
			 return null;
		 } else {
			 $row = mysql_fetch_row($result);
			 return $row[0];
		 }
	}

	function getNameMLValue($sMLModule, $entry_id, $language_id, $field = 'name') {
		$q = <<<SQL
			SELECT
				`%s`
			FROM
				`%s`
			WHERE
				`entry_id`='%d'
			AND
				`language_id`='%d'
			LIMIT
				0,1
SQL;

		$q      = sprintf($q, $field, $sMLModule, $entry_id, $language_id);
		$result = mysql_query($q);

		if (!$result) {
			return false; //This will happen if there isn't a column named "name"
		}

		$row = mysql_fetch_assoc($result);

		if ($row) {
			return $row[$field];
		} else {
			return null;
		}
	}

	function getNameValue($sModule, $entry_id, $field = 'name') {
		$q = <<<SQL
			SELECT
				`%s`
			FROM
				`%s`
			WHERE
				`id`='%d'
			LIMIT
				0,1
SQL;

		$q        = sprintf($q, $field, $sModule, $entry_id);
		$result   = mysql_query($q);

		if (!$result) {
			return false;
		}

		$row = mysql_fetch_assoc($result);

		if ($row) {
			return $row[$field];
		} else {
			return null;
		}
	}

	function getCurrentSlug($ref_module_id, $language_id, $entry_id) {
		$q = <<<SQL
			SELECT
				`slug`
			FROM
				`cms_m3_slugs`
			WHERE
				`ref_module_id`='%d'
			AND
				`language_id`='%d'
			AND
				`entry_id`='%d'
			ORDER BY
				`e_position` DESC
			LIMIT
				0,1
SQL;

		$q      = sprintf($q, $ref_module_id, $language_id, $entry_id);
		$result = mysql_query($q);

		$row = mysql_fetch_assoc($result);

		if ($row) {
			return $row['slug'];
		} else {
			return null;
		}
	}

	function insertSlug($ref_module_id, $language_id, $entry_id, $slug) {
		//Determine new position ID
		$q = <<<SQL
			SELECT
				MAX(`e_position`) AS `position`
			FROM
				`cms_m3_slugs`
SQL;

		$result = mysql_query($q);
		$row    = mysql_fetch_assoc($result);

		if (!$row['position']) {
			$position = 1;
		} else {
			$position = ((int) $row['position']) + 1;
		}

		//Check if there if there already exists a slug with this name for the current combination of ID's
		$slug = checkDuplicateSlug($language_id, $slug);

		//Generate INSERT SQL and run query
		$q = <<<SQL
			INSERT INTO `cms_m3_slugs`
				(`ref_module_id`, `language_id`, `entry_id`, `slug`, `e_position`)
			VALUES
				('%d', '%d', '%d', '%s', '%d')
SQL;

		$q = sprintf($q, $ref_module_id, $language_id, $entry_id, $slug, $position);

		mysql_query($q);
	}

	/**
	 * checkDuplicateSlug function.
	 *
	 * @access public
	 * @param mixed $ref_module_id
	 * @param mixed $language_id
	 * @param mixed $entry_id
	 * @param mixed $slug
	 * @return void
	 */
	function checkDuplicateSlug($language_id, $slug) {
		$q = <<<SQL
			SELECT
				`slug`
			FROM
				`cms_m3_slugs`
			WHERE
				`slug` REGEXP '^%s(\-[0-9]+)?$'
SQL;

		$q      = sprintf($q, pxl_db_safe($slug));
		$result = mysql_query($q);

		$aSlugs = array();
		while($row = mysql_fetch_assoc($result)) {
			$aSlugs[] = $row['slug'];
		}

		//Append an integer to the slug to indicate a duplicate value
		$duplicateCounter = 1;
		$_slug            = $slug;
		while(in_array($_slug, $aSlugs)) {
			$_slug = $slug . '-' . $duplicateCounter;
			$duplicateCounter++;
		}

		return $_slug;
	}


	// Register postSave events to the updateSlugs routine for modules containing a (ML or non-ML) field called "name"
	foreach(array(
		4, 6, 7,
		9,  // jobs
		13, // services
		15, // blog
	) as $moduleId) {
		Event::register('updateSlugs',  'postSave',   $moduleId);
		Event::register('cleanupSlugs', 'postDelete', $moduleId);
	}


	// Run cache-related hooks
	include_once(dirname(__FILE__) . '/HornetCache/HornetCache.php');
	$hornetCache = HornetCache::getInstance();

	if ($hornetCache->hasCache()) {
		$cache = $hornetCache->getCache();

		foreach(array(3, 7, 45) as $moduleId) {
			Event::register(function($entryId) use ($cache, $moduleId) {
				$languageId = $_SESSION['cms_v5_session']['language']['id'];

				// Generic clean
				$cache->clean(PXL\Core\Cache\Cache::CLEAN_MODE_MATCHING_TAG, array("_module_id_$moduleId", "_entry_id_$entryId"));

				switch($moduleId) {
					case 7:
						$cache->clean(PXL\Core\Cache\Cache::CLEAN_MODE_MATCHING_TAG, array('__navigation'));
						break;

					case 45:
						$cache->clean(PXL\Core\Cache\Cache::CLEAN_MODE_MATCHING_TAG, array('__global_settings'));
						break;

					case 3:
						$cache->clean(PXL\Core\Cache\Cache::CLEAN_MODE_MATCHING_TAG, array("__ml_labels_language_id_$languageId"));
						break;

					default:
						break;
				}

			}, 'postSave', $moduleId);
		}
	}