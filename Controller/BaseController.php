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


	}

}