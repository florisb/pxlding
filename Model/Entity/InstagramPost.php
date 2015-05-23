<?php
	namespace Model\Entity;

	use Model\Factory;

	class InstagramPost extends BaseEntity {

		protected $_table = 'cms_m17_instagram';

		protected $_dbFields = array(
			'post_id',
			'title',
			'image',
			'date',
			'likes',
			'comments',
		);

		protected $_requiredFields = array(
			'post_id',
		);

	}