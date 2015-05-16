<?php
	namespace Model\Factory;

	class Services extends BaseFactory {

		const SERVICE_BANNER_FIELD = 57;

		public function getAll() {

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `svc`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 13)
                WHERE
                    `svc`.`e_active` = 1
                ORDER BY
	                `svc`.`e_position` ASC
			";


			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\Service');
		}

		public function getBySlug($slug) {

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `svc`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 13)
                WHERE
                    `svc`.`e_active` = 1
                AND
                    `s`.`slug` = :slug
			";

			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
				':slug'     => array($slug, 's')
			));

			return self::db()->row($stmt, 'Model\Entity\Service');
		}

		protected function _getSql() {
			$q = "
				SELECT
					*,
					`svc`.`id` AS `cid`,
					`i`.`file` AS `banner`,
					`i`.`caption` AS `banner_caption`
				FROM
					`cms_m13_services` `svc`
				INNER JOIN
					`cms_m13_services_ml` `svc_ml`
				ON
					(`svc`.`id` = `svc_ml`.`entry_id` AND `svc_ml`.`language_id` = :language)
				LEFT JOIN
					`cms_m_images` `i`
				ON
					(`svc`.`id` = `i`.`entry_id` AND `i`.`field_id` = '" . self::SERVICE_BANNER_FIELD . "')
			";

			return $q;
		}

	}