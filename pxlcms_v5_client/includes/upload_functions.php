<?php
	function imglog($s) {
		echo date("d-m-Y @ H.i.s")." - ".$s."\r\n";
		// file_put_contents("../logs/last_upload.log", ob_get_contents());
	}
	
	function writelog() {
		file_put_contents("../logs/last_upload.log", ob_get_contents());
	}
	
	function find_file_target($target_path, $fname, $fext) {
		$target = Tools::clean_filename($fname).".".$fext;
		for ($i = 2; is_file($target_path . $target); $i++) {
			$target = Tools::clean_filename($fname, FALSE)."_v".$i.".".$fext;
		}
		return $target;
	}
?>