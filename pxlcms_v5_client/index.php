<?php
	$_SERVER['AJAX_CALL'] = false;
	include "meta.php";

	// use output buffering to allow in-process-redirection
	ob_start();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en" id='html'>
	<head>
		<title>CMS: <?= $CMS_ENV['pxlcms_client'] ?></title>
		<meta http-equiv="X-UA-Compatible" content="IE=EmulateIE8" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<meta http-equiv="Pragma" content="no-cache"/>
		<meta http-equiv="Cache-Control" content="no-cache"/>
		<meta name="description" content="">
		<meta name="keywords" content="">
		<meta name="robot" content="noindex,nofollow">
		<meta name="author" content="Pixelindustries">
		<meta name="language" content="nl-nl">

		<?php
			if (file_exists('config/favicon.png')) {
				echo "<link rel='shortcut icon' href='config/favicon.png' />";
			} else if (file_exists('config/favicon.ico')) {
				echo "<link rel='shortcut icon' href='config/favicon.ico' />";
			}
		?>


		<link rel="stylesheet" type="text/css" href="includes/default.css"/>
		<!--[if lt IE 7]>
			<link rel='stylesheet' title='IE_Only' href='includes/ie6_fixes.css' />
		<![endif]-->

		<script type="text/javascript" src="includes/scriptaculous/prototype.js"></script>
		<script type="text/javascript" src="includes/default.js"></script>
		<script type="text/javascript" src="includes/dragresize.js"></script>
		<script type="text/javascript" src="includes/AC_RunActiveContent.js"></script>
		<script type="text/javascript" src="includes/scriptaculous/effects.js"></script>
		<script type="text/javascript" src="includes/scriptaculous/scriptaculous.js"></script>
		<script type="text/javascript" src="includes/datepicker/datepicker.js"></script>
		<script type="text/javascript" src="includes/swfupload/swfupload.js"></script>
		<script type="text/javascript" src="includes/swfupload/swfupload.queue.js"></script>
		<script type="text/javascript" src="includes/swfupload/upload_handler.js"></script>
		<script type="text/javascript" src="includes/slick.js"></script>
		<script type="text/javascript" src="includes/slider.js"></script>

		<script type="text/javascript" src="includes/pxlcms.js"></script>
		<script type="text/javascript" src="includes/pxlcms.locationfield.js"></script>

		<script type="text/javascript" src="//maps.googleapis.com/maps/api/js?key=<?php echo($CMS_EXTRA['google_api_key']); ?>&sensor=false&language=nl"></script>
		<script type="text/javascript" src="//www.google.com/jsapi"></script>

		<?php
			// find and load custom module JS files
			$CMS = new CMS();
			$custom_modules = $CMS->getCustomModules();
			$loaded_js = array();
			foreach ($custom_modules as $module) {
				$m = explode("?", $module['custom_path']);
				$js = 'modules/'.str_replace(".php", ".js", $m[0]);
				if ($loaded_js[$js]) continue;
				if (file_exists($js)) {
					$loaded_js[$js] = true;
					echo "<script type='text/javascript' src='".$js."'></script>";
				}
			}
		?>

	</head>

	<body style='<?php
			if ($_SERVER['HTTP_HOST'] == 'www2.pixelindustries.com' && file_exists('config/header_local.jpg')) {
				echo "background-image: url(config/header_local.jpg);";
			} else if (file_exists('config/header.jpg')) {
				echo "background-image: url(config/header.jpg);";
			}
		?>' onload="keep_alive_timer();" onunload="">
		<div id='topanchor' style='position: absolute;'></div>

		<div id='help' style='display: none;'></div>
		<div id='dark' style='display: none;'></div>

		<!-- MAIN CONTENTS -->
		<div id='loading_indicator' style='display: none;'>Loading</div>

		<div id='topmenu'>
			<a href='login/logout.php' onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" style='background: url(img/topmenu/logout.gif); width: 43px;'></a>
			<?php
				// echo "<img style='cursor: pointer;' src='img/topmenu/help.gif' onclick=\"show_help();\" onmouseover=\"new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });\" onmouseout=\"new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });\" />";

				if ($CMS_SESSION['super_admin']) {
					?>
						<img style='cursor: pointer;' src='img/topmenu/logs.gif' onclick="process_setting({special_page: 'logs'});" onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" />
						<img style='cursor: pointer;' src='img/topmenu/resizing.gif' onclick="process_setting({special_page: 'resizing'});" onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" />
						<a href='update.php' onclick="return confirm('Are you sure you want to update this CMS?\n\n(Updating may result in an unstable CMS)');" onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" style='background: url(img/topmenu/update.gif); width: 39px;'></a>
						<a href='frontend' id='top_frontend' onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" style='background: url(img/topmenu/xml.gif); width: 33px;' target='_blank'></a>
						<img src='img/topmenu/database.gif' onclick="process_setting({ special_page: 'database' });" onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" />
					<?php
				}

				if ($CMS_SESSION['user']['user_manager']) {
					?>
						<img style='cursor: pointer;' src='img/topmenu/users.gif' onclick="process_setting({special_page: 'users'});" onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" />
					<?php
				}

				if (isset($CMS_DB_PUBLISHED['db_name'])) {
					?>
						<img style='cursor: pointer;' src='img/topmenu/publish.gif' onclick="process_setting({special_page: 'publish'});" onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" />
					<?php
				}

				if (isset($CMS_EXTRA['pxl_news_enabled']) && $CMS_EXTRA['pxl_news_enabled']) {
					?>
						<img style='cursor: pointer;' src='img/topmenu/news.gif' onclick="process_setting({special_page: 'news'});" onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" />
					<?php
				}

				if (isset($CMS_EXTRA['backups_enabled']) && $CMS_EXTRA['backups_enabled']) {
					?>
						<img style='cursor: pointer;' src='img/topmenu/database.gif' onclick="process_setting({special_page: 'backups'});" onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" />
					<?php
				}

				$CMS = new CMS();
				$menu = $CMS->getMenu();
				foreach ($menu as $m) {
					if (!isset($CMS_SESSION['menu_id']) || !$CMS_SESSION['menu_id']) {
						$CMS_SESSION['menu_id'] = $m['id'];
					}
				}
				$menu = array_reverse($menu);

				foreach ($menu as $m) {
					?>
					<img style='cursor: pointer;' src='img/topmenu/<?= $m['icon'] ?>' onclick="process_setting({cms_state: 'welcome', menu_id: <?= $m['id'] ?>});" onmouseover="new Effect.Opacity(this, { from: 0.25, to: 0.9, duration: 0.1 });" onmouseout="new Effect.Opacity(this, { from: 0.9, to: 0.25, duration: 0.3 });" />
				<?php
				}
			?>
		</div>

		<div id="mainframe">
			<table border='0' cellspacing='0' cellpadding='0' style='width: 100%;'>
				<tr valign='top'>

					<!-- LEFT COLUMN -->
					<td style='width: <?= isset($CMS_EXTRA['menu_width']) ? $CMS_EXTRA['menu_width'] : 200 ?>px;' id='navigation'>
						<?php
							$CMS = new CMS();
							$groups = $CMS->getStructure();

							// filter groups based on menu
							$g = array();
							foreach ($groups as $group) {
								if ($group['menu_id'] != $CMS_SESSION['menu_id']) continue;
								$g[] = $group;
							}
							$groups = $g;

							if (!isset($CMS_SESSION['group_id']) || !$CMS_SESSION['group_id']) {
								$CMS_SESSION['group_id'] = array_peek($groups);
								$CMS_SESSION['group_id'] = $CMS_SESSION['group_id']['id'];
							}

							if (count($groups) > 1) {
								$widget = new Widget();
								$widget->height = '10px';
								$widget->title = 'Menu';
								$widget->start();
								echo "<div style='' id='group_selection'>";
								include "core/navigation_group.php";
								echo "</div>";
								$widget->stop();
							}

							$widget = new Widget();
							$widget->height = '200px';

							foreach ($groups as $group) {
								if ($group['id'] == $CMS_SESSION['group_id']) {
									$widget->title = "<span id='group_nav_title'>".$group['name']."</span>";
								}
							}

							$widget->start();
							echo "<div style='' id='content_navigation'>";
							include "core/navigation.php";
							echo "</div>";
							$widget->stop();

							$languages = $CMS->my_languages($CMS_SESSION['user']['id']);
							if (count($languages) > 1)
							{
								$widget = new Widget();
								$widget->title = 'Your Language';
								$widget->start();
								echo "<select style='width: 100%' onkeyup=\"refresh('set_language='+this.value); this.blur();\" onchange=\"refresh('set_language='+this.value); this.blur();\">";
								foreach ($languages as $language) {
									echo "<option ".($language['id'] == $CMS_SESSION['language']['id'] ? 'selected' : '')." value='".$language['id']."'>".$language['language']."</option>";
								}
								echo "</select>";
								$widget->stop();
							}
						?>
					</td>

					<!-- spacer -->
					<td style='width: 30px;'>&nbsp;</td>

					<!-- RIGHT COLUMN -->
					<td>
						<?php
							echo "<div id='mainbody'>";
							include "core/body.php";
							echo "</div>";
						?>
					</td>
				</tr>
			</table>
		</div>

		<!-- FOOTER -->
		<div id='footer'>
			<?php
				echo "&copy; 2007-".date('Y')." Pixelindustries";
				echo "<br/><span id='keep_alive'>";
				include "core/_keep_alive.php";
				echo "</span>";
			?>
		</div>

	<?php
		// echo "<div style='color: #fff;'>";pr($CMS_SESSION);echo "</div>";
	?>

	</body>
</html>
