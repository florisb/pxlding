<?php
	namespace Controller;

	use Model\Factory;

	class Contact extends BaseController {

		public function indexAction() {

			$contact = Factory\Contact::getFirst();

			$this->set('contact', $contact);
		}

	}