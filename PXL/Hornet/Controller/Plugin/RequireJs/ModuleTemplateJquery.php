<?php
echo <<<EOF
/**
 * $action.js
 *
 * Module file for AMD-module "$controller/$action".
 *
 * @author [Auto-generated]
 * @date   $date
 */
define(function() {
	return function() {
		this.initialize = function() {
			// Constructor body
		};

		this.initialize();
	};
});
EOF;
?>