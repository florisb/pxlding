<?php
	namespace Model\Factory;
	
	use PXL\Core\Collection;
	
	class Content extends BaseFactory {
		
		public function getSettingsForPage($page) {
			// if (!($content = $this->_buffer->get($page))) {
				$q = "
					%s
					WHERE
						`c`.`type_of_page`=:page
					ORDER BY
						`c`.`e_position` ASC
					LIMIT
						0,1
				";
		
				$q          = sprintf($q, self::_getSql());
				$languageId = self::session('_language_id');
				$stmt       = self::stmt($q, array(':language' => array($languageId, 'i'), ':page' => $page));
				
				$content = self::db()->row($stmt, 'Model\Entity\Content');
				
				if (is_null($content)) {
					$content = (object) null;
				}

				// if (!empty($content->id)) {
				// 	$this->_buffer->put($content->id, $content);
				// }

			// 	$this->_buffer->put($content->type_of_page, $content);
			// };

			return $content;
		}
		
		public function getAll() {
			$q = "
				SELECT
					`id`
				FROM
					`cms_m4_content`
				WHERE
					`e_active`=1
				ORDER BY
					`e_position` ASC
			";
			
			$stmt = self::stmt($q);
			$ids  = self::db()->matrix($stmt, null, 'id');
			
			return $this->getByIds($ids->keySet()->toArray());
		}
		
		public function getRoutingInformation($id) {
			$q = "
				SELECT
					`type_of_page`
				FROM
					`cms_m4_content`
				WHERE
					`id`=?
				AND
					`e_active`=1
				ORDER BY
					`e_position` ASC
				LIMIT
					0,1
			";
			
			$stmt = self::stmt($q, array(array($id, 'i')));
			
			return self::db()->row($stmt);
		}
		
		public function getBySlug($slug) {

			$q = "
				%s
				INNER JOIN
					`cms_m3_slugs` `s`
				ON
					(`s`.`ref_module_id`='6' AND `s`.`entry_id`=`c`.`id` AND `s`.`language_id`=:language)
				WHERE
					`s`.`slug`=:slug
				AND
					`c`.`e_active`=1
				ORDER BY
					`c`.`e_position` DESC
				LIMIT
					0,1
			";

			$q           = sprintf($q, self::_getSql());
			$languageId  = self::session('_language_id');
			$stmt        = self::stmt($q, array(':language' => array($languageId, 'i'), ':slug' => array($slug, 's')));
			
			return self::db()->row($stmt, 'Model\Entity\Content');
		}

		public function getSectionsByPageSlug($slug) {

			$q = "
				SELECT 
					*
				FROM 
					`cms_m4_content` `c`
				INNER JOIN
					`cms_m3_slugs` `s`
				ON
					(`s`.`ref_module_id`='6' AND `s`.`entry_id`=`c`.`id` AND `s`.`language_id` = :language)
				LEFT JOIN
					`cms_m_references` `r`
				ON 
					(`r`.`from_entry_id` = `c`.`id` AND `r`.`from_field_id` = '25')
				LEFT JOIN `cms_m4_section` `p`
				ON
					(`r`.`to_entry_id` = `p`.`id`)
				LEFT JOIN
					`cms_m4_section_ml` `p_ml`
				ON
					(`p_ml`.`entry_id`=`p`.`id` AND `p_ml`.`language_id` = :language)
				WHERE 
					`s`.`slug` = :slug
				AND	
					`c`.`e_active`=1
				ORDER BY
					`r`.`position` ASC
			";

			$languageId  = self::session('_language_id');
			$stmt        = self::stmt($q, array(':language' => array($languageId, 'i'), ':slug' => array($slug, 's')));

			return self::db()->matrix($stmt, 'Model\Entity\Content');

		}

		public function getSectionImagesByPageId($id, $sectionStructure) {

			$q = '';

			switch ($sectionStructure) {
				case 'structure1':
					$q = "
						SELECT 
							`i1`.`file` AS `s1_step1_image`,
							`i2`.`file` AS `s1_step2_image`,
							`i3`.`file` AS `s1_step3_image`,
							`i4`.`file` AS `s1_step4_image`
						FROM
							`cms_m4_section` `c`
						LEFT JOIN
							`cms_m_images` `i1`
						ON
							(`i1`.`entry_id`=`c`.`id` AND `i1`.`field_id`='30')
						LEFT JOIN
							`cms_m_images` `i2`
						ON
							(`i2`.`entry_id`=`c`.`id` AND `i2`.`field_id`='34') 
						LEFT JOIN
							`cms_m_images` `i3`
						ON
							(`i3`.`entry_id`=`c`.`id` AND `i3`.`field_id`='38') 
						LEFT JOIN
							`cms_m_images` `i4`
						ON
							(`i4`.`entry_id`=`c`.`id` AND `i4`.`field_id`='42') 
						WHERE
							`c`.`id` = :id
					";
					break;
				case 'structure2':
					$q = "
						SELECT 
							`i1`.`file` AS `s2_background_image`,
							`i2`.`file` AS `s2_box1_image`,
							`i3`.`file` AS `s2_box2_image`,
							`i4`.`file` AS `s2_box3_image`,
							`i5`.`file` AS `s2_box4_image`
						FROM
							`cms_m4_section` `c`
						LEFT JOIN
							`cms_m_images` `i1`
						ON
							(`i1`.`entry_id`=`c`.`id` AND `i1`.`field_id`='46')
						LEFT JOIN
							`cms_m_images` `i2`
						ON
							(`i2`.`entry_id`=`c`.`id` AND `i2`.`field_id`='49') 
						LEFT JOIN
							`cms_m_images` `i3`
						ON
							(`i3`.`entry_id`=`c`.`id` AND `i3`.`field_id`='55') 
						LEFT JOIN
							`cms_m_images` `i4`
						ON
							(`i4`.`entry_id`=`c`.`id` AND `i4`.`field_id`='52') 
						LEFT JOIN
							`cms_m_images` `i5`
						ON
							(`i5`.`entry_id`=`c`.`id` AND `i5`.`field_id`='58') 
						WHERE
							`c`.`id` = :id
					";
					break;
				case 'structure3':
					$q = "
						SELECT 
							`i1`.`file` AS `s3_prices_image`
						FROM
							`cms_m4_section` `c`
						LEFT JOIN
							`cms_m_images` `i1`
						ON
							(`i1`.`entry_id`=`c`.`id` AND `i1`.`field_id`='73')
						WHERE
							`c`.`id` = :id
					";
					break;
				case 'structure4':
					$q = "
						SELECT 
							`i1`.`file` AS `s4_background_image`
						FROM
							`cms_m4_section` `c`
						LEFT JOIN
							`cms_m_images` `i1`
						ON
							(`i1`.`entry_id`=`c`.`id` AND `i1`.`field_id`='77')
						WHERE
							`c`.`id` = :id
					";
					break;
				case 'structure5':
					$q = "
						SELECT 
							`i1`.`file` AS `s5_step1_image`,
							`i2`.`file` AS `s5_step2_image`,
							`i3`.`file` AS `s5_step3_image`,
							`i4`.`file` AS `s5_step4_image`
						FROM
							`cms_m4_section` `c`
						LEFT JOIN
							`cms_m_images` `i1`
						ON
							(`i1`.`entry_id`=`c`.`id` AND `i1`.`field_id`='101')
						LEFT JOIN
							`cms_m_images` `i2`
						ON
							(`i2`.`entry_id`=`c`.`id` AND `i2`.`field_id`='104') 
						LEFT JOIN
							`cms_m_images` `i3`
						ON
							(`i3`.`entry_id`=`c`.`id` AND `i3`.`field_id`='107') 
						LEFT JOIN
							`cms_m_images` `i4`
						ON
							(`i4`.`entry_id`=`c`.`id` AND `i4`.`field_id`='110') 
						WHERE
							`c`.`id` = :id
					";
					break;
			}

			if(empty($q)){
				return null;
			}

			$stmt = self::stmt($q, array(':id' => array($id, 'i')));

			// echo $stmt;

			return self::db()->row($stmt, 'Model\Entity\Content');
			
		}
		
		public function getById($id) {
			$id = (int) $id;
			
			if (!($content = $this->_buffer->get($id))) {
				$q = "
					%s
					WHERE
						`c`.`id`=:id
					AND
					`c`.`e_active`=1
				";
			
				$q = sprintf($q, self::_getSql());
			
				$stmt = self::stmt($q, array(
					':language' => array(self::session('_language_id'), 'i'),
					':id'       => array($id, 'i')
				));
				
				$content = self::db()->row($stmt, 'Model\Entity\Content');
				$this->_buffer->put($content->id,           $content);
				$this->_buffer->put($content->type_of_page, $content);
			}
		
			return $content;
		}
		
		public function getByIds(array $ids) {
			$ids       = array_map('intval', $ids);
			$result    = new Collection\SimpleMap();
			$storedIds = array_filter($this->_buffer->keySet()->toArray(), function($key) { return is_numeric($key); });
			$fetchIds  = array_diff($ids, $storedIds);
			
			if (count($fetchIds)) {
				$q = "
					%s
					WHERE
						`c`.`id` IN (:ids)
					AND
						`c`.`e_active`=1
					ORDER BY
						FIND_IN_SET(`c`.`id`, :idset)
				";
			
				$q = sprintf($q, self::_getSql());
				$stmt = self::stmt($q, array(
					':language' => array(self::session('_language_id'), 'i'),
					':ids'      => array(Collection\SimpleList::createFromArray($fetchIds), 'i'),
					':idset'    => implode(',', $fetchIds)
				));

				$contents = self::db()->matrix($stmt, 'Model\Entity\Content');
			
				foreach($contents as $content) {
					$this->_buffer->put((int) $content->id, $content);
					$this->_buffer->put($content->type_of_page, $content);
				}
			}

			foreach($ids as $id) {
				if ($content = $this->_buffer->get($id)) {
					$result->put($id, $content);
				}
			}
			
			return $result;
		}
		
		protected function _getSql() {
			$q = "
				SELECT DISTINCT
					`c_ml`.*,
					`c`.*,
					`i`.`file` AS `header_image`
				FROM
					`cms_m4_content` `c`
				LEFT JOIN
					`cms_m4_content_ml` `c_ml`
				ON
					(`c_ml`.`entry_id`=`c`.`id` AND `c_ml`.`language_id`=:language)
				LEFT JOIN
					`cms_m_images` `i`
				ON
					(`i`.`entry_id`=`c`.`id` AND `i`.`field_id`='21')

			";

			return $q;
		}
	}