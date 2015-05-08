<?php	
	namespace Model\Factory;

	class Employees extends BaseFactory {

		public function getAll($shuffle = false) {

			$order = $shuffle ? 'RAND()' : '`c`.`e_position` ASC';

			$q = "
				%s
                WHERE
                    `e`.`e_active` = 1
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
					`cms_m8_employees` `e`
				INNER JOIN
					`cms_m8_employees_ml` `e_ml`
				ON
					(`e`.`id` = `e_ml`.`entry_id` AND `e_ml`.`language_id` = :language)
				LEFT JOIN
					`cms_m_images` `i` 
				ON
					(`e`.`id` = `i`.`entry_id` AND `i`.`field_id` = '35')
			";

			return $q;
		}		

	}