<?php
	
	function kill_magic_quotes($value) {
		return get_magic_quotes_gpc() ? stripslashes($value) : $value;
	}

	if (isset($_POST['sql'])) {
		$sql = kill_magic_quotes($_POST['sql']);
		$sql = explode("\n", $_POST['sql']);
		foreach ($sql as $line) {
			echo "<div style='padding: 5px; border: 1px solid #aaa; margin-bottom: 30px;'>";
			echo $line;
			$s = CMS_DB::mysql_query($line);
			echo "<hr/>";
			echo $s ? '<span style="color: green;">OK</span>' : '<span style="color: red;">FAIL</span>';
			if (!$s) {
				echo "<hr/>";
				echo mysql_error();
			}
			echo "</div>";
		}
	}

?>

<h2>Run SQL</h2>
<form action='.' method='post'>
<textarea name='sql' style='width: 95%; height: 90px;'></textarea>
<input type='submit' value='Run' />
</form>