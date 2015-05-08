<?php
	date_default_timezone_set('Europe/Amsterdam');
	error_reporting(E_ALL ^ E_NOTICE);
	if (!isset($_GET['month']) || $_GET['month'] == 0) $_GET['month'] = date('m');
	if (!isset($_GET['year']) || $_GET['year'] == 0) $_GET['year'] = date('Y');
	
	$years_allowed = array();
	for ($yrs = 1900; $yrs < date('Y') + 10; $yrs++) {
		$years_allowed[sizeof($years_allowed)] = $yrs;
	}
	
	if (!in_array($_GET['year'], $years_allowed)) $_GET['year'] = date('Y');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <title>PXL.DatePicker</title>
	<style type="text/css" media='screen'>
	<!--
	  body { margin: 0px; padding: 4px; background-color: white; }
	  select { width: 49%; }
	  select.month { }
	  select.year { float: right; }
	  table.date { padding: 0px; margin: 0px; }
	  table.date tr { height: 24px; }
	  table.date td { text-align: center; font-family: Verdana, sans-serif; font-size: 11px; width: 24px; cursor: pointer; }
	  table.date td.header { background-color: #0161b6; color: white; font-weight: bold; cursor: auto; }
	  table.date td.header:hover { }
	  table.date td.otherMonth { background-color: #f3f3f3; }
	  table.date td.thisMonth { background-color: #c7e5ff; }
	  table.date td.today { background-color: #4eb0ff; color: white; font-weight: bold; }
	  table.date td:hover { background-color: #015eb2; color: white; }
	-->
	</style>
	<script type="text/javascript" src="../scriptaculous/prototype.js"></script>
	<script language='Javascript'>
	
	function flz(n) {
		return n < 10 ? '0'+n : n;
	}
	
	function setValue(v) {
		v += (parseInt(document.getElementById('dp_hours').value) * 3600) + (parseInt(document.getElementById('dp_minutes').value) * 60);
		var date = new Date(v * 1000);
		parent.document.getElementById('<?= $_GET['target'] ?>').value = v;
		parent.document.getElementById('<?= $_GET['target'] ?>_formatted').value = date.getDate()+' / '+(date.getMonth()+1)+' / '+((""+date.getYear()).length < 4 ? date.getYear() + 1900 : date.getYear()) <?php if (isset($_GET['hours'])) { echo "+ ' at ' +flz(date.getHours())+':'+flz(date.getMinutes())";} ?>;
		parent.pxl_datepicker_hide();
	}
	
	function setTime() {
		var date = new Date(1000 * parent.document.getElementById('<?= $_GET['target'] ?>').value);
		date.setHours(document.getElementById('dp_hours').value);
		date.setMinutes(document.getElementById('dp_minutes').value);
		v = date.getTime() / 1000;
		$(parent.document.getElementById('<?= $_GET['target'] ?>')).writeAttribute('value', v);
		parent.document.getElementById('<?= $_GET['target'] ?>_formatted').value = date.getDate()+' / '+(date.getMonth()+1)+' / '+((""+date.getYear()).length < 4 ? date.getYear() + 1900 : date.getYear()) + ' at ' +flz(date.getHours())+':'+flz(date.getMinutes());
		parent.pxl_datepicker_hide();
	}
	
	function move() {
		window.location.href = '<?= $_SERVER['PHP_SELF'] ?>?target=<?= $_GET['target'] ?>&day=<?= $_GET['day'] ?>&month='+document.getElementById('dp_m').value+'&year='+document.getElementById('dp_y').value<?php if (isset($_GET['hours'])) { echo "+'&hours='+document.getElementById('dp_hours').value+'&minutes='+document.getElementById('dp_minutes').value"; } ?>;
	}
	</script>
  </head>
  <body>
  
  <?php
	$day   = 24 * 60 * 60;
	$today = date('dmY');
	
	$day_names   = array('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday');
	$month_names = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

	echo "<div style='padding: 3px;'>";
	
	echo "<select id='dp_y' class='year' onchange='move();'>";
	for ($i = $years_allowed[sizeof($years_allowed)-1]; $i >= $years_allowed[0]; $i--) {
		echo "<option value='".$i."' ".($i == $_GET['year'] ? 'selected' : '').">".$i."</option>";
	}
	echo "</select>";
	
	echo "<select id='dp_m' class='month' onchange='move();'>";
	for ($i = 0; $i < sizeof($month_names); $i++) {
		echo "<option value='".($i+1)."' ".(($i+1) == $_GET['month'] ? 'selected' : '').">".$month_names[$i]."</option>";
	}
	echo "</select>";

	echo "</div>";
	
	echo "<table class='date' border='0' cellspacing='4' cellpadding='0'>";
	
	
	// determine calendar start & end (this month)
	$cal_start = mktime(0,0,0,$_GET['month'], 1, $_GET['year']);
	$cal_end   = mktime(0,0,-1,$_GET['month']+1, 1, $_GET['year']);
	
	// extend calendar start & end to fill exactly from a monday - sunday
	while (date('w', $cal_start) != 1) { $cal_start -= $day; }
	while (date('w', $cal_end) != 0) { $cal_end += $day; }
	
	echo "<tr>";
	foreach ($day_names as $day_name) {
		echo "<td class='header'>".substr($day_name, 0, 2)."</td>";
	}
	echo "</tr><tr>";
	$rows = 0;
	for ($d = $cal_start; $d <= $cal_end; $d += $day) {
		
		// ensure every day is only processed once (keeps us away from borderline cases)
		if ($processed_date[date('dm', $d)]) continue;
		$processed_date[date('dm', $d)] = true;
		
		$dag = date('w', $d);
		if ($dag == 0) $dag = 7;
		
		if ($dag == 1 && $d != $cal_start) {
			echo "</tr><tr>";
			$rows++;
		}
		
		if (date('dmY', $d) == $today) {
			$class = 'today';
		} else if (date('m', $d) == $_GET['month']) {
			$class = 'thisMonth';
		} else {
			$class = 'otherMonth';
		}
		
		echo "<td class='".$class."' onclick='setValue(".$d.");' width='14%'>".date('j', $d)."</td>";
	}
	
	if ($rows == 4) {
		echo "</tr><tr>";
		for ($i = 1; $i <=7; $i++) {
			$d += $day;
			echo "<td class='otherMonth' onclick='setValue(".$d.");' width='14%'>".date('j', $d)."</td>";
		}
	}
	echo "</tr></table>";
	
	if (isset($_GET['hours'])) {
		echo "<div style='text-align: center; margin-top: 2px; font-family: Verdana, sans-serif; font-size: 12px;'>";
		echo "<input type='text' maxlength='2' style='width: 20px;' id='dp_hours' value='".$_GET['hours']."' />:";
		echo "<input type='text' maxlength='2' style='width: 20px;' id='dp_minutes' value='".$_GET['minutes']."' />";
		echo " <input type='submit' style='height: 22px;' value='set time' onclick=\"setTime();\" />";
		echo "</div>";
	} else {
		echo "<input type='hidden' id='dp_hours' value='0' />";
		echo "<input type='hidden' id='dp_minutes' value='0' />";
	}
	
  ?>
  </body>
</html>
