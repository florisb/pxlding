<?php
	namespace Model\Factory\Settings;
	
	use Model\Factory\BaseFactory;
	
	class GlobalSettings extends BaseFactory {
	
		public function getSettings() {
			$q = "
				SELECT
					`s_ml`.*,
					`s`.*
				FROM
					`cms_m5_global_settings` `s`
				LEFT JOIN
					`cms_m5_global_settings_ml` `s_ml`
				ON
					(`s_ml`.`entry_id`=`s`.`id` AND `s_ml`.`language_id`=?)
				WHERE
					`s`.`e_active`=1
				ORDER BY
					`s`.`e_position` ASC
				LIMIT
					0,1
			";
			
			$stmt = $this->stmt($q, array(array($this->session('_language_id'), 'i')));
			
			return $this->db()->row($stmt, 'Model\Entity\Settings\GlobalSettings');
		}
	}