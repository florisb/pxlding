<?php
	namespace Model\Entity;
	
	use Model\Factory;
	
	class Slug extends BaseEntity {
		
		protected function _setAlternativeSlugs($data) {
			return empty($data) ? array() : Factory\Slugs::getInstance()->getAlternativeSlugs(explode(',', $data), $this->ref_module_id, $this->entry_id);
		}
		
		public function isOld() {
			return ($this->slug !== $this->newest_slug);
		}
	}