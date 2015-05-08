<?php
	
	$CMS = new CMS();
	$category = $CMS->getCategory($CMS_SESSION['category_id']);
	
	$form = new Form();
	$form->start();
	
		echo "<table border='0' cellspacing='0' cellpadding='0' class='cell_right_spaced'>";
		
			echo "<tr><td>Name:</td><td>".$form->text('name', $category['name'])."</td></tr>";
			echo "<tr><td>Description:</td><td>".$form->textarea('description', $category['description'], array('style' => 'width: 270px; height: 70px;'))."</td></tr>";
			echo "<tr><td colspan='2' align='center'><br/>".$form->submit('Save changes')."</td></tr>";
		
		echo "</table>";
		
		echo "<input type='hidden' name='form_processing' value='category_edit' />";
		echo "<input type='hidden' name='category_id' value='".$CMS_SESSION['category_id']."' />";

	$form->stop();
	
?>