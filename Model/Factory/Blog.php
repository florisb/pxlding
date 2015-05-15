<?php
	namespace Model\Factory;

	class Blog extends BaseFactory {

		public function getAll() {

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `blg`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 15)
                WHERE
                    `blg`.`e_active` = 1
                ORDER BY
	                `blg`.`e_position` ASC
			";


			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\Blog');
		}

		public function getLatest($amount = 3) {

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `blg`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 15)
                WHERE
                    `blg`.`e_active` = 1
                ORDER BY
                	`blg`.`date` DESC,
	                `blg`.`e_position` ASC
	            LIMIT
	            	0, :latest
			";


			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
				':latest'   => array($amount, 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\Blog');
		}

		public function getBySlug($slug) {

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `blg`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 15)
                WHERE
                    `blg`.`e_active` = 1
                AND
                    `s`.`slug` = :slug
			";

			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
				':slug'     => array($slug, 's')
			));

			return self::db()->row($stmt, 'Model\Entity\Blog');
		}

		protected function _getSql() {
			$q = "
				SELECT
					*,
					`blg`.`id` AS `cid`,
					`i`.`file` AS `file`,
					`i2`.`file` AS `banner`
				FROM
					`cms_m15_blog` `blg`
				INNER JOIN
					`cms_m15_blog_ml` `blg_ml`
				ON
					(`blg`.`id` = `blg_ml`.`entry_id` AND `blg_ml`.`language_id` = :language)
				LEFT JOIN
					`cms_m_images` `i`
				ON
					(`blg`.`id` = `i`.`entry_id` AND `i`.`field_id` = '73')
				LEFT JOIN
					`cms_m_images` `i2`
				ON
					(`blg`.`id` = `i2`.`entry_id` AND `i2`.`field_id` = '72')
			";

			return $q;
		}

	}