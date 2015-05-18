<?php
	namespace Model\Factory;

	class Blog extends BaseFactory {

		const BANNER_IMAGE_FIELD_ID   = 72;
		const IMAGES_FIELD_ID         = 73;
		const OVERVIEW_IMAGE_FIELD_ID = 78;

		public function getAll($page = null, $pageSize = 6, $allToCurrent = false) {

			$usePages = ( ! empty($page) && $pageSize > 0);

			$page = max( $page - 1, 0);

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `blg`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 15)
                WHERE
                    `blg`.`e_active` = 1
                ORDER BY
	                `blg`.`e_position` ASC
				%s
			";

			$binds = array(
				':language' => array(self::session('_language_id'), 'i'),
			);

			$limit = '';

			if ($usePages) {

				$limit = "
					LIMIT
						:page, :pagesize
				";

				if ($allToCurrent) {
					// get all content up to and including that for
					// this page
					$binds[':page']     = array(0 , 'i');
					$binds[':pagesize'] = array(($page + 1) * $pageSize, 'i');
				} else {

					$binds[':page']     = array($page * $pageSize , 'i');
					$binds[':pagesize'] = array($pageSize, 'i');
				}
			}


			$q = sprintf($q,
				self::_getSql(),
				$limit
			);
			$stmt = self::stmt($q, $binds);

			return self::db()->matrix($stmt, 'Model\Entity\Blog');
		}

		public function getCount() {

			$q = "
				SELECT
					COUNT(*) AS `count`
				FROM
					`cms_m15_blog` AS `blg`
				WHERE
					`blg`.`e_active` = 1
			";

			$stmt = self::stmt($q);

			$result = self::db()->row($stmt);

			return $result->count;
		}


		public function getFiltered($search = null, $page = null, $pageSize = 6, $allToCurrent = false) {

			$usePages = ( ! empty($page) && $pageSize > 0);

			$page = max( $page - 1, 0);

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `blg`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 15)
                WHERE
                    `blg`.`e_active` = 1
                AND
                	(
                			`blg_ml`.`title`   LIKE :search
                		OR	`blg_ml`.`content` LIKE :search
                		OR	`blg`.`author`     LIKE :search
                	)
                ORDER BY
	                `blg`.`e_position` ASC
				%s
			";

			$binds = array(
				':language' => array(self::session('_language_id'), 'i'),
				':search'   => array('%' . $search .'%', 's'),
			);

			$limit = '';

			if ($usePages) {

				$limit = "
					LIMIT
						:page, :pagesize
				";

				if ($allToCurrent) {
					// get all content up to and including that for
					// this page
					$binds[':page']     = array(0 , 'i');
					$binds[':pagesize'] = array(($page + 1) * $pageSize, 'i');
				} else {

					$binds[':page']     = array($page * $pageSize , 'i');
					$binds[':pagesize'] = array($pageSize, 'i');
				}
			}


			$q = sprintf($q,
				self::_getSql(),
				$limit
			);

			$stmt = self::stmt($q, $binds);

			return self::db()->matrix($stmt, 'Model\Entity\Blog');
		}


		public function getLatest($amount = 3) {

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `blg`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 15)
                WHERE
                    `blg`.`e_active` = 1
                ORDER BY
                	`blg`.`date` DESC,
	                `blg`.`e_position` ASC
	            LIMIT
	            	0, :latest
			";


			$q = sprintf($q, self::_getSql());
			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
				':latest'   => array($amount, 'i'),
			));

			return self::db()->matrix($stmt, 'Model\Entity\Blog');
		}

		public function getBySlug($slug) {

			$q = "
				%s
                INNER JOIN
                    `cms_m3_slugs` `s`
                ON
                    (`s`.`entry_id` = `blg`.`id` AND `s`.`language_id` = :language AND `s`.`ref_module_id` = 15)
                WHERE
                    `blg`.`e_active` = 1
                AND
                    `s`.`slug` = :slug
			";

			$q = sprintf($q, self::_getSql());

			$stmt = self::stmt($q, array(
				':language' => array(self::session('_language_id'), 'i'),
				':slug'     => array($slug, 's')
			));

			return self::db()->row($stmt, 'Model\Entity\Blog');
		}

		public function getGalleryById($id) {

			$q = "
				SELECT
					`i`.`file`,
					`i`.`caption`
				FROM
					`cms_m15_blog` `blg`
				LEFT JOIN
					`cms_m_images` `i`
				ON
					(`blg`.`id` = `i`.`entry_id` AND `i`.`field_id` = " . (int) self::IMAGES_FIELD_ID . ")
				WHERE
					`blg`.`id` = :id
				ORDER BY
					`i`.`position` ASC
			";

			$stmt = self::stmt($q, array(
				':id'     => array($id, 'i')
			));

			return self::db()->matrix($stmt, 'Model\Entity\PCase');
		}

		protected function _getSql() {
			$q = "
				SELECT
					*,
					`blg`.`id` AS `bid`,
					`i`.`file` AS `file`,
					`i2`.`file` AS `banner`
				FROM
					`cms_m15_blog` `blg`
				INNER JOIN
					`cms_m15_blog_ml` `blg_ml`
				ON
					(`blg`.`id` = `blg_ml`.`entry_id` AND `blg_ml`.`language_id` = :language)
				LEFT JOIN
					`cms_m_images` `i`
				ON
					(`blg`.`id` = `i`.`entry_id` AND `i`.`field_id` = '" . (int) self::OVERVIEW_IMAGE_FIELD_ID . "')
				LEFT JOIN
					`cms_m_images` `i2`
				ON
					(`blg`.`id` = `i2`.`entry_id` AND `i2`.`field_id` = '" . (int) self::BANNER_IMAGE_FIELD_ID . "')
			";

			return $q;
		}

	}