<?php
	// ensure that Safari AJAX calls are forced UTF-8 since they have no default encoding?
	header("Content-type:text/html; charset=utf-8");
	
	// document used for ajax calls
	// ----------------------------
	// provides same includes and relative path structure as with
	// normal requests
	
	$_SERVER['AJAX_CALL'] = true;
	include "meta.php";
	$p = (isset($_POST['page']) ? $_POST['page'] : $_GET['page']);
	if ($p != '') include 'core/'.$p;
?>