<?php
	$CMS = new CMS();
	
	$editable = array('username' => 'Username', 'fullname' => 'Full name', 'email' => 'Email', 'password' => 'New password (only when changing password)');
	
	$user = $CMS->getUser($CMS_SESSION['edit_user_id']);
	$user['password'] = '';
	
	$form = new Form();
	$form->start(array('onsubmit' => "refresh('form_processing=save_user&'+Form.serialize(this)); return false;"));
	$p = array('style' => 'width: 290px; margin-bottom: 20px;');
	foreach ($editable as $e => $description) {
		echo $description."<br/>";
		echo $form->text($e, $user[$e], $p);
	}
	echo $form->hidden('id', $user['id']);
	
	
	// super admin?
	if ($CMS_SESSION['user']['id'] == 0) {
		$p = array();
		$p['style'] = 'margin: 0px; padding: 0px; display: inline;';
		if ($user['user_manager']) $p['checked'] = 'checked';
		
		echo $form->checkbox('user_manager', 1, $p);
		echo 'User manager<br/><br/>';
	}
	
	
	echo $form->submit('<< back', array('onclick' => "process_setting({special_page: 'users'}); return false;", 'style' => 'margin-right: 69px;'));
	
	echo $form->submit('Save');
	$form->stop();
	

?>