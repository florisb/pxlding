<?php
	namespace PXL\Hornet\Scaffolding;
	
	use PXL\Hornet\Application\Application;
	
	abstract class Scaffolding {
		
		public static function controller($controllerName, $actionName) {
			if (empty($_GET['_scaffold'])) {
				// Override default view logic to show a prompt instead of normal view
				Application::getInstance()->getView()->overrideView(path(realpath(dirname(__FILE__)).'/Templates/ControllerPrompt.phtml'))
													 									 ->set('controllerName', $controllerName)
													 									 ->set('actionName',     $actionName)
													 									 ->render();
													 
				return false;
			} else {
				self::_createController($controllerName, $actionName);
				
				return true;
			}
		}
		
		protected static function _createController($controllerName, $actionName) {
			if(is_file(path("Controller/$controllerName.php"))) {
				return;
			}
			
			$view = Application::getInstance()->getView();
		
			$templatePath       = path(dirname(__FILE__) . '/Templates');
			$controllerTemplate = $view->renderView("$templatePath/Controller.php", array('controllerName' => ucfirst($controllerName), 'actionName' => strtolower($actionName)));
			$viewTemplate       = $view->renderView("$templatePath/View.phtml", array('controllerName' => ucfirst($controllerName), 'actionName' => strtolower($actionName)));
			
			$controllerViewDir = APPLICATION_PATH . path("Views/$controllerName");
			if (!is_dir($controllerViewDir)) {
				mkdir($controllerViewDir, 0777, true);
			}
			
			$controllerName = ucfirst($controllerName);
			file_put_contents(APPLICATION_PATH . path("Controller/$controllerName.php"), $controllerTemplate);
			file_put_contents(path("$controllerViewDir/$actionName.phtml"), $viewTemplate);
		}
	}