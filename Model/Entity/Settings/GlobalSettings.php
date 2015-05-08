<?php
	namespace Model\Entity\Settings;
	
	use Model\Entity\BaseEntity;
	
	class GlobalSettings extends BaseEntity {
		
		protected function init() {
			if (!empty($this->_data['page_not_found_text'])) {
				$this->_data['page_not_found_text'] = nl2br($this->_data['page_not_found_text']);
			}
		}
	}