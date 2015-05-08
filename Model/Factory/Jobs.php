<?php	
	namespace Model\Factory;

	class Jobs extends BaseFactory {

		public function getAll($shuffle = false) {

			$order = $shuffle ? 'RAND()' : '`j`.`e_position` ASC';

			$q = "
				%s
                WHERE
                    `j`.`e_active` = 1
                ORDER BY
	                ".$order."
			";

			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\Employee');
		}

		protected function _getSql() {
			$q = "
				SELECT 
					*
				FROM 
					`cms_m9_jobs` `j`
				INNER JOIN
					`cms_m9_jobs_ml` `j_ml`
				ON
					(`j`.`id` = `j_ml`.`entry_id` AND `j_ml`.`language_id` = :language)
			";

			return $q;
		}		

	}