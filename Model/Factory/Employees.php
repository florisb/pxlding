<?php
	namespace Model\Factory;

	class Employees extends BaseFactory {

		public function getAll($shuffle = false) {

			$order = $shuffle ? 'RAND()' : '`e`.`e_position` ASC';

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

		/**
		 * Get colleagues for a job
		 */
		public function getColleaguesById($jobId, $shuffle = true) {

			$jobId = (int) $jobId;
			$order = $shuffle ? 'RAND()' : '`e`.`e_position` ASC';

			$q = "
				%s
				INNER JOIN
					`cms_m_references` `ref`
						ON `ref`.`to_entry_id` = `e`.`id`
                WHERE
                    `e`.`e_active` = 1
				AND
					`ref`.`from_field_id` = 86
				AND
					`ref`.`from_entry_id` = :job
                ORDER BY
	                " . $order . "
			";

			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':job'      => array($jobId, 'i'),
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