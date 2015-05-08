<?php

/*
	===============
	== chkiplist ==
	===============
	originally by: palitoy-ga
	commented and signature change by johan kohne
	=> signature change: instead of reading $lines from file, it's included as parameter
	
	description
	---------
	ultra-uitgebreide ip check functie, met alle range and wildcard support die maar kan wensen
	zie ook: http://answers.google.com/answers/threadview?id=379235
	
	usage
	---------
	chkiplist($ip, $lines)
	
	$ip    - the ip that will be matched against:
	$lines - an array of ip masks. masks may contain wildcards/ranges, using: "*", "?", "-"
	
	eg:
	
	chkiplist("24.25.26.27", array("24.25.255.*", "24.25.25-29.1-100))   -> matches (on 2nd mask)
	chkiplist("24.25.255.7", array("24.25.255.*", "24.25.25-29.1-100))   -> matches (on 1st mask)
	chkiplist("24.25.26.119", array("24.25.255.*", "24.25.25-29.1-100))   -> no match
	chkiplist("24.25.26.119", array("24.25.255.*", "24.25.25-29.1-100))   -> no match

	it also supports masks of the format:
	
	10.125.1.1 - 10.125.1.255
	192.168.* - 192.169.*

*/


function chkiplist($ip, $lines) {
	# set a variable as false
	$found = false;
	# convert ip address into a number
	$split_it = explode(".",$ip);
	$ip = "1" . sprintf("%03d",$split_it[0]) . sprintf("%03d",$split_it[1]) . sprintf("%03d",$split_it[2]) . sprintf("%03d",$split_it[3]);
	# loop through the ip address file
	foreach ($lines as $line) {
		# set a maximum and minimum value
		$max = $line;
		$min = $line;
		# replace * with a 3 digit number
		if ( strpos($line,"*",0) <> "" ) {
			$max = str_replace("*","999",$line);
			$min = str_replace("*","000",$line);
		}
		# replace ? with a single digit
		if ( strpos($line,"?",0) <> "" ) {
			$max = str_replace("?","9",$line);
			$min = str_replace("?","0",$line);
		}
		# if the line is invalid go to the next line
		if ( $max == "" ) { continue; };
		# check for a range
		if ( strpos($max," - ",0) <> "" ) {
			$split_it = explode(" - ",$max);
			# if the second part does not match an ip address
			if ( !preg_match("|\d{1,3}\.|",$split_it[1]) ) {
				$max = $split_it[0];
			}
			else { 
				$max = $split_it[1];
			};
		}
		if ( strpos($min," - ",0) <> "" ) {
			$split_it = explode(" - ",$min);
			$min = $split_it[0];
		}
		# make $max into a number
		$split_it = explode(".",$max);
		for ( $i=0;$i<4;$i++ ) {
			if ( $i == 0 ) { $max = 1; };
			if ( strpos($split_it[$i],"-",0) <> "" ) {
				$another_split = explode("-",$split_it[$i]);
				$split_it[$i] = $another_split[1];
			} 
		$max .= sprintf("%03d",$split_it[$i]);
		}
		# make $min into a number
		$split_it = explode(".",$min);
		for ( $i=0;$i<4;$i++ ) {
			if ( $i == 0 ) { $min = 1; };
			if ( strpos($split_it[$i],"-",0) <> "" ) {
				$another_split = explode("-",$split_it[$i]);
				$split_it[$i] = $another_split[0];
			} 
		$min .= sprintf("%03d",$split_it[$i]);
		}
		# check for a match
		if ( ($ip <= $max) && ($ip >= $min) ) {
			$found = true;
			break;
		};
	}
	return $found;
}; # end function

