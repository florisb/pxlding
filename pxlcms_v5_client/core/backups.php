<?php
	echo "<input type='submit' value='Create new backup' style='margin:0 0 15px 5px;' onclick=\"refresh('form_processing=make_backup&name='+$('name').getValue());\" />";
	echo "Insert name for new backup: <input type='text' value='' id='name' name='name' />";
	echo "<table border='0' cellspacing='0' cellpadding='0' id='user_rights'>";
	
	echo "<tr>";
	echo "<td class='nopad' style='height: 17px; width: 17px; background: url(img/blue/tl.gif);'></td>";
	echo "<td class='nopad' style='background: #d5d7da' colspan='2'>&nbsp;</td>";
	echo "<td class='nopad' style='background: #d5d7da' colspan='1'>&nbsp;</td>";
	echo "<td class='nopad' style='height: 17px; width: 17px; background: url(img/blue/tr.gif);'></td>";
	echo "</tr>";
	
	// languages (header)
	echo "<tr style='background: #6e7278'>";
		echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
		echo "<td style='border-right: 1px solid #fff; color: #fff; font-weight: bold; border-top: 1px solid #fff;'>Name</td>";
		echo "<td style='border-right: 1px solid #fff; color: #fff; font-weight: bold; border-top: 1px solid #fff;'>Backups</td>";
		echo "<td style='color: #fff; font-weight: bold; border-top: 1px solid #fff;padding-left:10px;'>Action</td>";
		echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
	echo "</tr>";
	
	$backups = array();
	if ($handle = opendir('backups')) {
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, array(".", "..", ".svn"))) {
				$s = str_replace(".gz", "", $file);
				$s = explode('-', $s);
				$d['timestamp'] = array_pop($s);
				$d['name'] =implode('-',$s);
				$d['file'] = $file;
				$backups[$d['timestamp']] = $d; 
			}
		}
		closedir($handle);
	}
	krsort($backups);
	foreach ($backups as $backup) {
		//$datearr = explode('-', $backup);
		//$timestamp = array_pop($datearr);
		//$name = implode('-',$datearr);
		$date = date("d-m-Y H:i", $backup['timestamp']);
		
		echo "<tr style='background: #d5d7da'>";
			echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
			echo "<td style='border-top: 1px solid #fff; border-right: 1px solid #fff;'>".$backup['name']."</td>";
			echo "<td style='border-top: 1px solid #fff;border-right: 1px solid #fff;'>".$date."</td>";
			echo "<td style='border-top: 1px solid #fff;'>";
			echo "<img alt='Give all rights' title='Restore backup' src='img/icons/activate.gif' onclick=\"if (confirm('Really restore this backup?')) { refresh('form_processing=restore_backup&file=".$backup['file']."'); } return false;\" style='cursor: pointer; margin:0 10px 0 10px;' />";
			echo "<img alt='Delete user' title='Delete backup' src='img/icons/delete.gif' onclick=\"if (confirm('Really delete this backup?')) { refresh('form_processing=delete_backup&file=".$backup['file']."'); } return false;\" style='cursor: pointer;' />";
			echo "</td>";
			echo "<td style='border-top: 1px solid #fff;'>&nbsp;</td>";
		echo "</tr>";
	}
	echo "<tr>";
	echo "<td class='nopad' style='border-top: 1px solid #fff;height: 17px; width: 17px; background: url(img/blue/bl.gif);'></td>";
	echo "<td class='nopad' style='border-top: 1px solid #fff;background: #d5d7da' colspan='2'>&nbsp;</td>";
	echo "<td class='nopad' style='border-top: 1px solid #fff;background: #d5d7da' colspan='1'>&nbsp;</td>";
	echo "<td class='nopad' style='border-top: 1px solid #fff;height: 17px; width: 17px; background: url(img/blue/br.gif);'></td>";
	echo "</tr>";
	
	echo "</table>";	
?>