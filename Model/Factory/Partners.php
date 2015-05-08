<?php	
	namespace Model\Factory;

	class Partners extends BaseFactory {

		public function getAll($shuffle = false) {

			$order = $shuffle ? 'RAND()' : '`c`.`e_position` ASC';

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `c`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 7)
                WHERE
                    `c`.`e_active` = 1
                ORDER BY
	                ".$order."
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
                    (`s`.`entry_id` = `c`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 7)
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
	
		protected function _getSql() {
			$q = "
				SELECT 
					*,
					`i`.`file` AS `file`,
					`i2`.`file` AS `logo`
				FROM 
					`cms_m7_partners` `c`
				INNER JOIN
					`cms_m7_partners_ml` `c_ml`
				ON
					(`c`.`id` = `c_ml`.`entry_id` AND `c_ml`.`language_id` = :language)
				LEFT JOIN
					`cms_m_images` `i` 
				ON
					(`c`.`id` = `i`.`entry_id` AND `i`.`field_id` = '28')
				LEFT JOIN
					`cms_m_images` `i2` 
				ON
					(`c`.`id` = `i2`.`entry_id` AND `i2`.`field_id` = '31')
			";

			return $q;
		}		

	}