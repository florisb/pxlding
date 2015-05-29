<?php
	namespace Model\Factory;

	class Cases extends BaseFactory {

		const SHOWCASED_REF_FIELD = 63;
		const SERVICES_REF_FIELD  = 62;


		public function getAll($shuffle = false, $offline = false) {

			$order   = $shuffle ? 'RAND()' : '`c`.`e_position` ASC';
			$offline = $offline ? 1 : 0;

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `c`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 6)
                WHERE
                    `c`.`e_active` = 1
                AND
                	`c`.`offline` = ".$offline."
                ORDER BY
	                ".$order."
			";

			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\PCase');
		}

		public function getRandom() {

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `c`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 6)
                WHERE
                	`c`.`e_active` = 1
                AND
                	`c`.`offline` = 0
                ORDER BY
                	 RAND()
				LIMIT 3
			";

			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\PCase');

		}

		public function getBySlug($slug) {

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `c`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 6)
                WHERE
                    `c`.`e_active` = 1
                AND
                    `s`.`slug` = :slug
			";

			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
				':slug'     => array($slug, 's')
			));

			return self::db()->row($stmt, 'Model\Entity\PCase');
		}

		public function getGalleryById($id) {
			$q = "
				SELECT
					`i`.`file`,
					`i`.`caption`
				FROM
					`cms_m6_cases` `c`
				LEFT JOIN
					`cms_m_images` `i`
				ON
					(`c`.`id` = `i`.`entry_id` AND `i`.`field_id` = '20')
				WHERE
					`c`.`id` = :id
				ORDER BY
					`i`.`position` ASC
			";

			$stmt = self::stmt($q, array(
				':id'     => array($id, 'i')
			));

			return self::db()->matrix($stmt, 'Model\Entity\PCase');
		}

		public function getShowCased() {

			// self::SHOWCASED_REF_FIELD

			$extra = "
				`shc`.`text_dark`,
				`c_ml`.`name` AS `name`,
				`shc`.`name`  AS `showcase_name`
			";

			$q = "
				%s
                INNER JOIN
                    `cms_m14_cases_showcase` `shc`
                ON
                    `shc`.`case` = `c`.`id`
				INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `c`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 6)
                WHERE
                    `shc`.`e_active` = 1
                ORDER BY
                	`shc`.`e_position`
                LIMIT
                	0, 4
			";

			$q = sprintf($q, self::_getSql($extra));
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i')
			));

			return self::db()->matrix($stmt, 'Model\Entity\PCase');
		}

		/**
		 * Get related cases for service
		 */
		public function getByService($service) {

			if ($service instanceof \Model\Entity\PCase) {
				$serviceId = $service->id;
			} else {
				$serviceId = (int) $service;
			}

			$q = "
				%s
                INNER JOIN
                    `cms_m_references` `ref`
                ON
                    (`ref`.`to_entry_id` = `c`.`id` AND `ref`.`from_field_id` = " . self::SERVICES_REF_FIELD . ")
				INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `c`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 6)
                WHERE
                    `c`.`e_active` = 1
                AND
                	`ref`.`from_entry_id` = :serviceid
                ORDER BY
                    `ref`.`position`
			";

			$q = sprintf($q, self::_getSql());

			$stmt = self::stmt($q, array(
				':language'  => array(self::session('_language_id'), 'i'),
				':serviceid' => array($serviceId, 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\PCase');
		}



		protected function _getSql($extra = '') {

			$q = "
				SELECT
					*,
					`c`.`id` AS `cid`,
					`i`.`file` AS `file`,
					`i2`.`file` AS `blur`,
					`i3`.`file` AS `overview`
					%s
				FROM
					`cms_m6_cases` `c`
				INNER JOIN
					`cms_m6_cases_ml` `c_ml`
				ON
					(`c`.`id` = `c_ml`.`entry_id` AND `c_ml`.`language_id` = :language)
				LEFT JOIN
					`cms_m_images` `i`
				ON
					(`c`.`id` = `i`.`entry_id` AND `i`.`field_id` = '19')
				LEFT JOIN
					`cms_m_images` `i2`
				ON
					(`c`.`id` = `i2`.`entry_id` AND `i2`.`field_id` = '22')
				LEFT JOIN
                                        `cms_m_images` `i3`
                                ON
                                        (`c`.`id` = `i3`.`entry_id` AND `i3`.`field_id` = '119')
			";

			if ( ! empty($extra)) $extra = ',' . $extra;

			$q = sprintf($q, $extra);

			return $q;
		}

	}
