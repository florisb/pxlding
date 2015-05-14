<?php namespace Controller;

use PXL\Hornet\Controller\Controller;
use App;

class BaseController extends Controller
{

	public function preAction()
	{
		$detect = new App\Mobile_Detect;

		$this->set('isMobileTablet', $detect->isMobile() || $detect->isTablet(), true);
		$this->set('isMobile', $detect->isMobile() && !$detect->isTablet(), true);
		$this->set('isTablet', $detect->isTablet(), true);

		$this->set('controllerName', $this->getControllerName(), true);
	}

	public function postAction()
	{

		// header content
		$headerMenu = array(
			(object) array(
				'title'  => 'Pixelindustries',
				'url'    => $this->route(''),
				'active' => ($this->getControllerName() == 'home'),
			),
			(object) array(
				'title'  => 'Our mission',
				'url'    => $this->route('mission'),
				'active' => ($this->getControllerName() == 'mission'),
			),
			(object) array(
				'title'  => 'Services',
				'url'    => $this->route('services'),
				'active' => ($this->getControllerName() == 'services'),
			),
			(object) array(
				'title'  => 'Cases',
				'url'    => $this->route('cases'),
				'active' => ($this->getControllerName() == 'cases'),
			),
			(object) array(
				'title'  => 'Blog',
				'url'    => $this->route('blog'),
				'active' => ($this->getControllerName() == 'blog'),
			),
			(object) array(
				'title'  => 'Careers',
				'url'    => $this->route('jobs'),
				'active' => ($this->getControllerName() == 'jobs'),
			),
			(object) array(
				'title'  => 'Contact',
				'url'    => $this->route('contact'),
				'active' => ($this->getControllerName() == 'contact'),
			),
		);

		// footer content
		$footerMenu = $headerMenu;

		$this->set('headerMenu', $headerMenu, true);
		$this->set('footerMenu', $footerMenu, true);
	}

}