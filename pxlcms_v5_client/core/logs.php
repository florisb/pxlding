<?php
	$rep  = opendir('logs/');
	$logs = array();
			
	while ($file = readdir($rep)){
		if($file != '..' && $file !='.' && $file !='' && $file[0] != '_' && $file[0] != "." && $file != 'wiki' && !is_dir($file) && $file != 'index.php') { 
			$logs[] = $file;
		}
	}
	
	rsort($logs);
	
	if (isset($_GET['l'])) {
		echo "<div style='background: #eee; border: 1px solid #ccc; margin-bottom: 20px; padding: 10px;'><pre>".file_get("logs/".$logs[$_GET['l']])."</pre></div>";
	}
	
	foreach ($logs as $index => $log) {
		echo "<div style='background: url(img/icons/file.gif) 3px 3px no-repeat #eee; padding: 3px 0 3px 20px; font-family: Courier New, Courier, sans-serif; margin-bottom: 3px;'><a href='?l=".$index."'>".$log."</a></div>";
	}
?>