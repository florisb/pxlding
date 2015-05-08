<?php
	$CMS = new CMS();
	
	$editable = array('username' => 'Username', 'fullname' => 'Full name', 'email' => 'Email', 'password' => 'Password');
	
	$form = new Form();
	$form->start(array('onsubmit' => "refresh('form_processing=create_user&'+Form.serialize(this)); return false;"));
	$p = array('style' => 'width: 290px; margin-bottom: 20px;');
	foreach ($editable as $e => $description) {
		echo $description."<br/>";
		echo $form->text($e, $user[$e], $p);
	}
	echo $form->hidden('id', $user['id']);
	
	echo $form->submit('<< back', array('onclick' => "process_setting({special_page: 'users'}); return false;", 'style' => 'margin-right: 69px;'));
	echo $form->submit('Create user');
	
	$form->stop();
	

?>