<?php
	$CMS_SESSION['keep_alive']['last'] = time();
	
	function flz($n) {
		return $n < 10 ? '0'.$n : $n;
	}
	
	if ($CMS_SESSION['keep_alive']['last'] - $CMS_SESSION['keep_alive']['start'] >= $CMS_EXTRA['lifetime']) {
		$CMS_SESSION = array();
		session_destroy();
		echo "<script type='text/javascript'>window.location.reload();</script>";
	} else {
		
		$seconds = $CMS_EXTRA['lifetime'] - ($CMS_SESSION['keep_alive']['last'] - $CMS_SESSION['keep_alive']['start']);
		$togo = floor($seconds / 3600).':'.flz(floor(($seconds % 3600) / 60)); // .':'.floor($togo%60); // seconds
		
		// togo
		echo 'Automatic sign-out in '.$togo;
		
		// at time
		// echo 'Automatic sign-out at '.date('H:i', time() + $seconds);
	}
?>