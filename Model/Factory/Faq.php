<?php	
	namespace Model\Factory;

	class Faq extends BaseFactory {

		public function getAll() {

			$q = "
				SELECT
					`f_ml`.*,
					`f`.*
				FROM
					`cms_m10_faq` `f`
				LEFT JOIN
					`cms_m10_faq_ml` `f_ml`
				ON
					(`f_ml`.`entry_id`=`f`.`id` AND `f_ml`.`language_id`=:language)
			";

			$q = sprintf($q);
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\Content');
		}	

	}