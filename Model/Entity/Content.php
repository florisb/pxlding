<?php
	namespace Model\Entity;
	
	class Content extends BaseEntity {
		
		public function getUrl() {
			if (empty($this->_data['slug']) || $this->_data['type_of_page'] === 'home') {
				return $this->route(null, null, array(), 'defaultml');
			} else {
				return $this->route(null, null, array('slug' => $this->_data['slug']), 'slugs');
			}
		}
	}