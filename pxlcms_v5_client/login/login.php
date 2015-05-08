<?php
	if (!function_exists('version_compare') || version_compare( phpversion(), '5', '<' )) {
		echo "PXL.CMS requires PHP version > 5 (current version ".phpversion().")";
		exit;
	}
	
	include "meta.php";
	
	if ($CMS_ENV['license'] != md5($CMS_ENV['base_url'])) {
		$CMS_SESSION['error'] = 'CMS license expired.';
		header("Location: .");
		exit;
	}
	
	// allow auto-login 
	$PXL_LOCAL = false;
	if ($CMS_IP['pxl_local_nologin'] && chkiplist($_SERVER['REMOTE_ADDR'], $CMS_IP['pxl_ip_range'])) $PXL_LOCAL = true;
	// $PXL_LOCAL = true;
	
	if (isset($_POST['user']) || $PXL_LOCAL)
	{
		if (!$PXL_LOCAL) {
			// super admin?
			$pixelaccount = "http://www.pixelindustries.info/pixelcms/credentials_v5.dat";
			$accountdata = @file_get($pixelaccount) or die ("<h3>No internet connection!</h3>Internet connection required to validate login.");
			$accountdata = explode(" ", trim($accountdata));
		}
		
		if ($PXL_LOCAL
			|| (!isset($CMS_EXTRA['override_super_admin_pass']) && $_POST['user'] == $accountdata[0] && md5($_POST['pass']) == $accountdata[1])
			|| (isset($CMS_EXTRA['override_super_admin_pass']) && $_POST['user'] == $accountdata[0] && md5($_POST['pass']) == md5($CMS_EXTRA['override_super_admin_pass']))
		) {
			// override pass? store key!
			if (isset($CMS_EXTRA['override_super_admin_pass'])) {
				$CMS_SESSION['passoverride_key'] = md5($CMS_EXTRA['override_super_admin_pass']);
			}
			
			$CMS_SESSION['keep_alive']  = array('start' => time());
			$CMS_SESSION['logged_in']   = true;
			$CMS_SESSION['super_admin'] = true;
			$CMS_SESSION['user']        = array(
												'id'           => 0,
												'username'     => 'administrator',
												'fullname'     => 'PXL.Superadmin',
												'email'        => 'johan@pixelindustries.com',
												'user_manager' => 1,
												'created_by'   => 0,
												'last_login'   => time()
												);
			
			$CMS_SESSION['language'] = mysql_fetch_assoc(CMS_DB::mysql_query("SELECT * FROM `".$CMS_DB['prefix']."languages` ORDER BY `default` DESC, `available` DESC LIMIT 1"));
			
			header("Location: ../.");
			exit;
		}
		
		// common user
		else {
			$CMS = new CMS();
			
			$user = $CMS->loginUser($_POST['user'], $_POST['pass']);

			if ($user === false) {
			
				header("Location: .");
				exit;
				
			} else {
				
				$CMS_SESSION['keep_alive']  = array('start' => time());
				$CMS_SESSION['logged_in']   = true;
				$CMS_SESSION['user']        = $user;
				
				$languages = $CMS->my_languages($CMS_SESSION['user']['id']);
				$CMS_SESSION['language'] = array_shift($languages);
				
				header("Location: ../.");
				exit;
				
			}
		}
	}
	
	// default... show login form
	header("Location: .");
?>