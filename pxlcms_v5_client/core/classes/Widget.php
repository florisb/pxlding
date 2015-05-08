<?php

	class Widget {
		
		var $title  = 'Widget';
		var $width  = '100%';
		var $height = 'auto';
		var $mode   = 'frame';
		var $id     = '';
		var $css    = '';
		
		function start() {
			switch ($this->mode)
			{
				case 'frame':
					$this->frame_start();
					break;
				case 'box':
					$this->box_start();
					break;
				default:
					die("Invalid Widget-&gt;mode");
					break;
			}
		}
		
		function stop() {
			switch ($this->mode)
			{
				case 'frame':
					$this->frame_stop();
					break;
				case 'box':
					$this->box_stop();
					break;
				default:
					die("Invalid Widget-&gt;mode");
					break;
			}
		}
	
		function frame_start() {
			$this->new_id();
			echo "<table id='".$this->id."' border='0' cellspacing='0' cellpadding='0' style='width: ".$this->width."; margin-bottom: 25px; ".$this->css."'>";
			echo "<tr style='height: 36px; background: url(img/frame/t.jpg) repeat-x #1f2022;'>";
			echo "<td>";
			echo "<img src='img/frame/tl.jpg' style='float: left;' />";
			echo "<img src='img/frame/tr.jpg' style='float: right;' />";
			echo "<div class='widget_title'>".$this->title."</div>";
			echo "</td>";
			echo "</tr>";
			echo "<tr>";
			echo "<td>";
			echo "<table border='0' cellspacing='0' cellpadding='0' style='height: ".$this->height."; width: 100%;'>";
			echo "<tr valign='top'>";
			echo "<td style='background: url(img/frame/l.jpg) repeat-y left top #fff; padding-left: 29px; padding-top: 25px;'>";
		}
		
		function frame_stop() {
			echo "</td>";
			echo "<td style='width: 50px; background: url(img/frame/r.jpg);'>&nbsp;</td>";
			echo "</tr>";
			echo "</table>";
			echo "</td>";
			echo "</tr>";
			echo "<tr style='height: 36px; background: url(img/frame/b.jpg);'>";
			echo "<td>";
			echo "<img src='img/frame/bl.jpg' style='float: left;' />";
			echo "<img src='img/frame/br.jpg' style='float: right;' />";
			echo "&nbsp;";
			echo "</td>";
			echo "</tr>";
			echo "</table>";
		}
		
		function box_start() {
			$this->new_id();
			echo "<table id='".$this->id."' border='0' cellspacing='0' cellpadding='0' style='width: ".$this->width."; margin: 0 0 15px 15px; ".$this->css."'>";
			echo "<tr valign='top' style='height: 58px; background: url(img/box/t.jpg) repeat-x;'>";
			echo "<td width='22' style='background: url(img/box/tl.jpg) no-repeat;'>&nbsp;</td>";
			echo "<td>";
		}
		
		function box_stop() {
			echo "</td>";
			echo "<td width='22' style='background: url(img/box/tr.jpg) no-repeat;'>&nbsp;</td>";
			echo "</tr>";
			
			echo "<tr style='height: 31px; background: url(img/box/b.jpg);'>";
			echo "<td width='22' style='background: url(img/box/bl.jpg) no-repeat;'>&nbsp;</td>";
			echo "<td>&nbsp;</td>";
			echo "<td width='22' style='background: url(img/box/br.jpg) no-repeat;'>&nbsp;</td>";
			echo "</tr>";
			
			echo "</table>";
		}
		
		function new_id() {
			$this->id = 'widget_'.mt_rand();
		}
	}
	
?>