<?php
	namespace Model\Factory;

	class Contact extends BaseFactory {

		const ADDRESS_IMAGE_FIELD_ID = 111;



		public function getFirst() {

			$q = "
				%s
                WHERE
                    `c`.`e_active` = 1
                ORDER BY
	                '`c`.`e_position` ASC'
	            LIMIT
	            	0, 1
			";

			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
			));

			return self::db()->row($stmt, 'Model\Entity\Contact');
		}


		protected function _getSql() {
			$q = "
				SELECT
					*,
					`c`.`id`      AS `id`,
					`i`.`file`    AS `file`,
					`i`.`caption` AS `caption`
				FROM
					`cms_m16_contact` `c`
				INNER JOIN
					`cms_m16_contact_ml` `c_ml`
				ON
					(`c`.`id` = `c_ml`.`entry_id` AND `c_ml`.`language_id` = :language)
				LEFT JOIN
					`cms_m_images` `i`
				ON
					(`c`.`id` = `i`.`entry_id` AND `i`.`field_id` = '" . self::ADDRESS_IMAGE_FIELD_ID . "')
			";

			return $q;
		}

	}