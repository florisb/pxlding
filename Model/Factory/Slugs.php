<?php
	namespace Model\Factory;

	use PXL\Core\Collection;

	class Slugs extends BaseFactory {
		
		/**
		 * getBySlug function.
		 * 
		 * Fetches a slug from the database, also retrieving
		 * the newest slug for the combination of language ID,
		 * module ID and entry ID of that specific slug.
		 *
		 * @access public
		 * @static
		 * @param mixed $slug
		 * @param mixed $moduleId
		 * @return void
		 */
		public function getBySlug($slug) {
			$q = "
				SELECT
					`s`.*,
					(
						SELECT
							`slug`
						FROM
							`cms_m3_slugs`
						WHERE
							`ref_module_id`=`s`.`ref_module_id`
						AND
							(`language_id`=`s`.`language_id` OR (`language_id`='0' AND `ref_module_id` IN (:languagelessmodules)))
						AND
							`entry_id`=`s`.`entry_id`
						ORDER BY
							`e_position` DESC
						LIMIT
							0,1
					) AS `newest_slug`,
					(
						SELECT
							GROUP_CONCAT(DISTINCT `language_id`)
						FROM
							`cms_m3_slugs`
						WHERE
							`ref_module_id`=`s`.`ref_module_id`
						AND
							`entry_id`=`s`.`entry_id`
						AND
							`language_id`<>`s`.`language_id`
						GROUP BY
							`ref_module_id`
					) AS `alternative_slugs`
				FROM
					`cms_m3_slugs` `s`
				WHERE
					`s`.`slug`=:slug
				LIMIT
					0,1
			";
			
			// Array of module ID's that have a non-ML `name` field
			$languageLessModules = array(0);
			
			$stmt = $this->stmt($q, array(
				':languagelessmodules' => implode(',', $languageLessModules),
				':slug'                => $slug
			));
			
			return $this->db()->row($stmt, 'Model\Entity\Slug');
		}
		
		public function getAlternativeSlugs(array $languageIds, $moduleId, $entryId) {
			$q = "
				SELECT
					`s`.*,
					`l`.`code` AS `language_code`
				FROM
					`cms_m3_slugs` `s`
				INNER JOIN
					`cms_languages` `l`
				ON
					(`l`.`id`=`s`.`language_id`)
				WHERE
					`s`.`ref_module_id`=:ref_module_id
				AND
					`s`.`entry_id`=:entry_id
				AND
					`s`.`language_id` IN (:languageIds)
				ORDER BY
					`s`.`e_position` ASC
				LIMIT
					0,:limit
			";
			
			$stmt = $this->stmt($q, array(
				':ref_module_id' => array($moduleId, 'i'),
				':entry_id'      => array($entryId, 'i'),
				':languageIds'   => array(Collection\SimpleList::createFromArray($languageIds), 'i'),
				':limit'         => array(count($languageIds), 'i')
			));
			
			return $this->db()->matrix($stmt, 'Model\Entity\Slug');
		}
	}