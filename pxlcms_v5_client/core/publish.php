<?php
	if (!isset($_POST['publish']))
	{
		echo "Please double check your content before publishing to the LIVE environment.";
		echo "<br/><br/>";
		echo "<input type='submit' class='button' value='Publish' onclick=\"this.disabled = true; $('content_being_published').style.display = ''; refresh('publish=true'); return false;\" />";
		echo "<br/>";
		echo "<div style='display: none;' id='content_being_published'>Content is being published...</div>";
	}
	else
	{
		include "core/classes/DBSync.php";
		
		ignore_user_abort();
		set_time_limit(500);
		
		$sync = new DBSync();
		
		$from = db_connect($CMS_DB);
		$to   = db_connect($CMS_DB_PUBLISHED);
		
		$tables = array();
		$ts = CMS_DB::mysql_query("SHOW TABLES", $from);
		while ($t = mysql_fetch_row($ts)) {
			$tables[] = array_pop($t);
		}
		
		$sync->synchronize_data($from, $to, $tables, true, true);
		
		echo "The content was published to the LIVE environment.";
	}