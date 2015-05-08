<?php
	namespace PXL\Core\Model\Entity;

	interface iPersistentEntity {

		public static function retrieve();

		public static function unpersist();

		public function persist();
	}