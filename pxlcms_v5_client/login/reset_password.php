<?php
	include "meta.php";
	
	$emailaddress = str_replace("'", "", $_POST['user']);
	
	$CMS = new CMS();
	$new_password = $CMS->resetPassword($emailaddress);
	
	if ($new_password === false) {
		$CMS_SESSION['error'] = 'This email-address has no user associated with it.';
		header("Location: .?forgot_password=1");
	} else {
		$user         = $new_password['user'];
		$new_password = $new_password['new_password'];
		
		include "../core/classes/HtmlEmailer.php";
		$mailer = new HtmlEmailer();
		$mailer->addressee = $emailaddress;
		$mailer->subject   = 'A new CMS password';
		$mailer->sender    = 'info@pixelindustries.com';
		
		$message = "Hi ".$user['fullname'].",\n\na new password was requested for your CMS: ".$CMS_ENV['pxl_cms_url']." - your new credentials are listed below.\n\nusername: ".$user['username']."\npassword: ".$new_password."\n\nGood luck!\n\n(This is a computer-generated email, replying is of no use.)";
		$mailer->set_plain_body($message);
		$mailer->send();
		
		
		$mailer->addressee = 'info@pixelindustries.com';
		$mailer->subject   = 'CMS password reset: '.$CMS_ENV['pxlcms_client'];
		$mailer->sender    = 'info@pixelindustries.com';
		$message = "A new password was requested for the ".$CMS_ENV['pxlcms_client']." CMS: ".$CMS_ENV['pxl_cms_url']." - the new credentials are listed below for your information:\n\nusername: ".$user['username']."\npassword: ".$new_password."\n\n\n(This is a computer-generated email, replying is of no use.)";
		$mailer->set_plain_body($message);
		$mailer->send();
		
		
		$CMS_SESSION['error'] = 'A new password was emailed to you.';
		header("Location: .");
	}
?>