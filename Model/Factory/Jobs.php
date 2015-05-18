<?php
	namespace Model\Factory;

	class Jobs extends BaseFactory {

		const IMAGE_FIELD_ID       = 87;
		const ICON_LEFT_FIELD_ID   = 80;
		const ICON_MIDDLE_FIELD_ID = 82;
		const ICON_RIGHT_FIELD_ID  = 84;


		public function getAll($shuffle = false) {

			$order = $shuffle ? 'RAND()' : '`j`.`e_position` ASC';

			$q = "
				%s
				INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `j`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 9)
                WHERE
                    `j`.`e_active` = 1
                ORDER BY
	                ".$order."
			";

			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\Job');
		}

		public function getBySlug($slug) {

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `j`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 9)
                WHERE
                    `j`.`e_active` = 1
                AND
                    `s`.`slug` = :slug
			";

			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
				':slug'     => array($slug, 's')
			));

			return self::db()->row($stmt, 'Model\Entity\Job');
		}


		/**
		 * Not a real gallery, just the icons
		 */
		public function getIconsById($id) {
			$q = "
				SELECT
					`il`.`file`    AS `left_file`,
					`il`.`caption` AS `left_caption`,
					`im`.`file`    AS `middle_file`,
					`im`.`caption` AS `middle_caption`,
					`ir`.`file`    AS `right_file`,
					`ir`.`caption` AS `right_caption`
				FROM
					`cms_m9_jobs` `j`
				LEFT JOIN
					`cms_m_images` `il`
				ON
					(`j`.`id` = `il`.`entry_id` AND `il`.`field_id` = '" . self::ICON_LEFT_FIELD_ID . "')
				LEFT JOIN
					`cms_m_images` `im`
				ON
					(`j`.`id` = `im`.`entry_id` AND `im`.`field_id` = '" . self::ICON_MIDDLE_FIELD_ID . "')
				LEFT JOIN
					`cms_m_images` `ir`
				ON
					(`j`.`id` = `ir`.`entry_id` AND `ir`.`field_id` = '" . self::ICON_RIGHT_FIELD_ID . "')
				WHERE
					`j`.`id` = :id
			";

			$stmt = self::stmt($q, array(
				':id' => array($id, 'i')
			));

			return self::db()->row($stmt);
		}

		protected function _getSql() {
			$q = "
				SELECT
					*,
					`j`.`id`      AS `id`,
					`i`.`file`    AS `file`,
					`i`.`caption` AS `caption`
				FROM
					`cms_m9_jobs` `j`
				INNER JOIN
					`cms_m9_jobs_ml` `j_ml`
				ON
					(`j`.`id` = `j_ml`.`entry_id` AND `j_ml`.`language_id` = :language)
				LEFT JOIN
					`cms_m_images` `i`
				ON
					(`j`.`id` = `i`.`entry_id` AND `i`.`field_id` = '" . self::IMAGE_FIELD_ID . "')
			";

			return $q;
		}

	}