<?php
	readfile('pxl_config.js');
	
	$custom = '../../config/fck.js';
	
	if (file_exists($custom))
	{
		readfile($custom);
	}