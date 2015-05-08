<?php	
	namespace Model\Factory;

	class Whatwedo extends BaseFactory {

		public function getAll() {

			$q = "
				SELECT
					`w_ml`.*,
					`w`.*,
					`i`.`file` as `file`
				FROM
					`cms_m11_what_we_do` `w`
				LEFT JOIN
					`cms_m11_what_we_do_ml` `w_ml`
				ON
					(`w_ml`.`entry_id`=`w`.`id` AND `w_ml`.`language_id`=:language)
				LEFT JOIN
					`cms_m_images` `i`
				ON
					(`i`.`entry_id`=`w`.`id` AND `i`.`field_id`='48')
			";

			$q = sprintf($q);
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\Content');
		}	

	}