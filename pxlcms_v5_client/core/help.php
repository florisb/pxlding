<?php

	if (isset($CMS_SESSION['special_page']))
	{
		switch ($CMS_SESSION['special_page'])
		{
			case 'users':
				$contents = "users.php";
				$title    = 'User management';
				break;
			
			case 'news':
				$contents = "news.php";
				$title    = 'Pixelindustries news';
				break;
			
			case 'database':
				$contents = "database.php";
				$title    = 'Run SQL';
				break;
		}
	}
	else if ($CMS_SESSION['module_id'])
	{
		switch ($CMS_SESSION['last_state'])
		{
			case 'add':
				$contents = "entry_add.php";
				$title    = 'Add entry';
				break;
				
			case 'edit':
				$contents = "entry_edit.php";
				$title    = 'Edit entry';
				break;
				
			case 'category_edit':
				$contents = "category_edit.php";
				$title    = 'Edit category';
				break;
			
			case 'category_add':
				$contents = "category_add.php";
				$title    = 'Add category';
				break;
				
			case 'overview':
				$contents = "module_overview.php";
				$title    = 'Module overview';
				break;
		}
	}
	else
	{
		$contents = 'welcome.php';
		$title    = 'CMS';
	}
?>

<div style='position: absolute; left: 25px; top: 16px;'>
	<h1 style='color: #fff;'>Help: <?= $title ?></h1>
</div>

<div style='color: #fff; position: absolute; right: 25px; top: 20px; cursor: pointer;' onclick="hide_help();">close [x]</div>

<div style='height: 100%; width: 100%; overflow: auto;'>

	<?php
		$f = 'core/help/'.$contents;
		if (is_file($f)) {
			echo "<br/>";
			include $f;
		} else {
			echo "<br/>Help does not yet exist on this subject. To create this help section, please use the file:<br/><br/><span style='font-family: Courier New, sans-serif;'>".$f."</span>";
		}
	?>

</div>