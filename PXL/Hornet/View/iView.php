<?php
	namespace PXL\Hornet\View;

	/**
	 * iView interface.
	 *
	 */
	interface iView {
		
		/**
		 * render function.
		 * 
		 * Renders the view
		 *
		 * @access public
		 * @return void
		 */
		public function render();
		
		/**
		 * partial function.
		 * 
		 * Renders a partial view template. This method should be used within
		 * an existing view template as an alternative to include(), where the
		 * template is actually rendered in a protected scope.
		 *
		 * Templates are retrieved logically rather than physically, which are
		 * based on action/controller combinations. When $controller is not
		 * passed (default: null), the current controller is used.
		 *
		 * @access public
		 * @param mixed $action
		 * @param array $variables (default: array())
		 * @param mixed $controller (default: null)
		 * @return void
		 */
		public function partial($action, $variables = array(), $controller = null);
		
		/**
		 * partialLoop function.
		 * 
		 * Does the same as iView::partial, but is used in situations where
		 * the partial template needs to be rendered for each item in a collection
		 * of items. This collection should be either an array or traversable class
		 * that implements Iterator, since the the collection to be traversed over
		 * is looped over using a foreach() loop.
		 *
		 * $variables should be an associative array, where the keys "_element"
		 * and "_counter" are reserved keys which may be used in the partial
		 * template to access the element and index in the current loop state.
		 *
		 * @access public
		 * @param mixed $action
		 * @param Traversable $traversable
		 * @param array $variables (default: array())
		 * @param mixed $controller (default: null)
		 * @return void
		 */
		public function partialLoop($action, $traversable, $variables = array(), $controller = null);
		
		/**
		 * set function.
		 * 
		 * Stores a (named) variable in the view object.
		 *
		 * @access public
		 * @param mixed $key
		 * @param mixed $value
		 * @return void
		 */
		public function set($key, $value);
		
		/**
		 * get function.
		 * 
		 * Retrieves a previously stored variable from the
		 * view.
		 *
		 * @access public
		 * @param mixed $key
		 * @return void
		 */
		public function get($key);
		
		
		/**
		 * view function.
		 * 
		 * @access public
		 * @param mixed $action
		 * @param mixed $controller (default: null)
		 * @return void
		 */
		public function view($action, $controller = null);
		
		/**
		 * template function.
		 * 
		 * Changes the template that will be used for view rendering.
		 *
		 * @access public
		 * @param mixed $name
		 * @return void
		 */
		public function template($name);
	}