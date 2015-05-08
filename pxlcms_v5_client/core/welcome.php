Welcome to your CMS, <?= $CMS_SESSION['user']['fullname'] ?>.<br/><br/>

<div style='color: #ccc;'>
	<table border='0' cellspacing='0' cellpadding='0'>
	<?php
		echo "<tr><td>Version</td><td width='20'>&nbsp;</td><td>v".$CMS_ENV['pxlcms_version']."</td></tr>";
		echo "<tr><td>Released</td><td width='20'>&nbsp;</td><td>".$CMS_ENV['version_release']."</td></tr>";
	?>
	</table>
</div>