<?php
	include "meta.php";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
	<head>
		<title>Login</title>
	</head>
	<body onload="document.getElementById('user').focus();" style='background: #000817; font-family: Arial, sans-serif; font-size: 11px; color: #222;'>
		<?php
			if ($_GET['forgot_password']) {
			?>
			
				<form method='post' action='reset_password.php' style='display: block; background: url(../img/login_box.jpg) center center; no-repeat; width: 765px; height: 262px; position: absolute; left: 50%; top: 50%; margin-left: -382px; margin-top: -131px;'>
					<div style='position: absolute; background: #fff; width: 225px; height: 120px; top: 100px; left: 271px; text-align: center;'>
						<?php
							if (isset($CMS_SESSION['error'])) {
								echo "<div style='margin: 10px 0 17px 0; font-weight: bold;'>".$CMS_SESSION['error']."</div>";
								unset($CMS_SESSION['error']);
							} else {
								echo "<div style='margin: 10px 0 17px 0; font-weight: bold;'>Forgot your password?</div>";
							}
						?>
						Please enter your email-address, a new password will be mailed to you:<br/><br/>
						<input name='user' id='user' value='' type='text' style='font-size: 11px; display: inline; width: 120px;' />
						<input type='submit' value='New password' style='font-size: 11px; display: inline; width: 90px;' />
					</div>
				</form>
			
			<?php
				} else {
			?>
			
				<form method='post' action='login.php' style='display: block; background: url(../img/login_box.jpg) center center; no-repeat; width: 765px; height: 262px; position: absolute; left: 50%; top: 50%; margin-left: -382px; margin-top: -131px;'>
					<input name='user' id='user' value='' type='text' style='position: absolute; left: 358px; top: 121px; border: 0px; background: transparent; width: 118px; font-size: 11px;' />
					<input name='pass' value='' type='password' style='position: absolute; left: 358px; top: 154px; border: 0px; background: transparent; width: 120px; font-size: 11px;' />
					<input type='image' style='position: absolute; left: 367px; top: 200px;' src='../img/login_button.gif'>
					<div style='position: absolute; left: 267px; top: 180px; font-size: 11px; width: 217px; text-align: right; font-style: italic; color: #444;'>
					<?php
						if (isset($CMS_SESSION['error'])) {
							echo $CMS_SESSION['error'];
							unset($CMS_SESSION['error']);
						}
					?>
					</div>
					<a href='.?forgot_password=1' style='position: absolute; text-align: center; display: block; left: 233px; top: 260px; color: #404b5c; width: 300px;'>Forgot your password? Click here!</a>
				</form>
				
			<?php
			}
			?>
	</body>
</html>