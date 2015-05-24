<?php
	namespace Controller;

	use Model\Factory;

	class Contact extends BaseController {

		public function indexAction() {

			$contact = Factory\Contact::getFirst();

			$this->set('contact', $contact);

			// set this for the 'we are just X km away' hipster thingy
	        $this->set('pxlLocation', (object) array(
	            'latitude'  => 52.38828,
	            'longitude' => 4.64306
	        ));
		}

	}