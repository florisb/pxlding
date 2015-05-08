<?php
	include "meta.php";
	if (!$CMS_SESSION['super_admin']) die("Not allowed.");
?>
<html>
<head>
	<title>PXL.CMS Updater</title>
</head>
<body style='font-family: Calibri, Verdana, sans-serif; font-size: 11px;'>
<?php
	
	// ensure enough time
	set_time_limit(0);
	// and ensure the process will not be cancelled
	ignore_user_abort(true);
	
	echo "<h2>Updating CMS ".$CMS_ENV['version']."<span style='font-size: 12px'>/".$CMS_ENV['version_number']."</span></h2>";
	
	function move_folder($from, $to, $skip_folders = array()) {
		if ($dir = opendir($from)) {
			while ($file = readdir($dir)) {
				if ($file == '.' || $file == '..' || $file == 'Thumbs.db') continue;
				if (is_dir($from.$file)) {
					if (in_array($file, $skip_folders)) continue;
					// process dir
					if (!file_exists($to.$file)) mkdir($to.$file);
					move_folder($from.$file.'/', $to.$file.'/');
				} else {
					// process file
					if (!file_exists($to.$file) || (file_exists($to.$file) && md5(file_get($to.$file)) != md5(file_get($from.$file)))) {
						echo "Updating: ".$to.$file.'<br/>';
						copy($from.$file, $to.$file);
					}
				}
			}
			closedir($dir);
		}
	}
	
	function full_rmdir($dirname) {
		if ($dirHandle = opendir($dirname)) {
			$old_cwd = getcwd();
			chdir($dirname);
			while ($file = readdir($dirHandle)) {
				if ($file == '.' || $file == '..') continue;
				if (is_dir($file)) {
					if (!full_rmdir($file)) return false;
                } else {
					if (!unlink($file)) return false;
                }
            }
	        closedir($dirHandle);
	        chdir($old_cwd);
	        if (!rmdir($dirname)) return false;
			return true;
        } else {
			return false;
        }
    }
	
	
	// 1 - download the latest version
	$tmp    = '_upgrade/';
	$update = 'update.zip';
	$source = "http://www1.pixelindustries.com/pxlcms_v5_updates/latest.zip";
	@file_put_contents($update, file_get($source));
	
	// 2 - extract the update
	$zip = new ZipArchive;
	if ($zip->open($update) !== true) {
		unlink($update);
		exit("Can't load update from ".$source);
	}
    $zip->extractTo($tmp.'/');
    $zip->close();
	unlink($update);
	
	
	// 3 - verify the update?
	// todo?
	
	// 4 - install the update
	$skip_folders = array('config', 'uploads', 'watermarks', 'logs');
	foreach ($skip_folders as $folder) {
		if (!file_exists($folder)) mkdir($folder);
	}
	echo "<h4>Updating files...</h4>";
	move_folder($tmp, './', $skip_folders);
	
	// 5 - remove the update tmp
	full_rmdir($tmp);
	
	
	// output
	if ($_GET['force_db']) {
		$CMS_ENV['version_number'] = 1;
	}
	echo "<h4>Updating database...</h4>";
	include "includes/db_update.php";
	
	echo "<h2>Done!</h2>";
	
	echo "<a href='.' style='color: #000; margin-top: 30px; font-size: 13px;'>&raquo; Return to the updated CMS</a>";
	
	// logout to force logging-in again and a refresh...?
	// $CMS_SESSION = array();
?>
</body>
</html>