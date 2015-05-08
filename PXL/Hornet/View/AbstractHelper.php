<?php
	namespace PXL\Hornet\View;
	
	abstract class AbstractHelper {
		
		protected $view = null;
		
		public function __construct(iView $view) {
			$this->view = $view;
		}
	}