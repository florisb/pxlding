<?php
	include "meta.php";
	
	if ($CMS_IP['frontend_localhost_only'] && $_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR']) {
		header("HTTP/1.1 404 Not Found");
		header("Status: 404 Not Found");
		exit;
	}
?>
<html>
	<head>
		<title>PXL.CMS v5: XML</title>
		<meta http-equiv='Content-Type' content='text/html; charset=utf-8' />
	</head>
	<body style='background: url(theme.jpg) top right no-repeat #f4f4f4; overflow: hidden; color: #001; font-family: Calibri, Verdana, sans-serif;'>
		<h2>CMS v5: choose an XML feed</h2>
		<?php
			$CMS = new CMS();
			
			echo "<form action='index.php' method='post'>";

			echo "<a href='".$CMS_ENV['pxl_cms_url']."frontend/languages_xml.php' style='display: block;'>Languages XML</a>";
			
			echo "<select id='module_id' name='module_id'>";
			$groups = $CMS->getStructure();
			foreach ($groups as $group) {
				foreach ($group['sections'] as $section) {
					$modules = $CMS->getModules($section['id']);
					foreach ($modules as $module) {
						if ($module['xml_access'] == 0) continue;
						echo "<option ".($_POST['module_id'] == $module['id'] ? 'selected' : '')." value='".$module['id']."'>".$group['name'].' &gt; '.$section['name'].": ".$module['name']."</option>";
					}
				}
			}
			echo "</select>";
			
			echo "&nbsp;";
			
			echo "<select id='xml' name='xml'>";
			echo "<option value='entries_xml.php'>Entries</option>";
			echo "<option value='categories_xml.php' ".($_POST['xml'] == 'categories_xml.php' ? 'selected' : '').">Categories</option>";
			echo "</select>";
			
			echo "&nbsp;";
			
			echo "<input type='submit' name='show' value='XML' />";
			
			echo "</form>";
			
			if (isset($_POST['show']))
			{
				$xml = $CMS_ENV['pxl_cms_url'].'frontend/'.$_POST['xml'].'?module_id='.$_POST['module_id'];
				
				echo "<a style='display: block; color: #000; margin: 30px 0 30px 0;' href='".$xml."' target='_blank'>".$xml."</a>";
				echo "<iframe style='border: 1px solid #444;' allowtransparency frameborder='0' src='".$xml."' style='width: 100%; height: 70%;'></iframe>";
				
			}
		?>
	</body>
</html>