<?php
	date_default_timezone_set ('Europe/London' );
	$root = $_SERVER['DOCUMENT_ROOT'].'/';
	$absRoot = $_SERVER['DOCUMENT_ROOT'].'/';
	$scriptbeg = microtime(get_as_float);
	$firstday = (date('j') == 1);
	
	if(!isset( $_SESSION ) ) {
		session_start();
	}
	$unitT = 'C'; $unitR = 'mm'; $unitW = 'mph'; $unitP = 'hPa';
	$expTime = 3600*24*100;
	if (isset($_GET['unit'])) {
		if($_GET['unit'] == 'US') { $unitT = 'F'; $unitR = 'in'; $unitW = 'mph'; $unitP = 'inHg'; setcookie("SetUnits", "US", time()+$expTime); }
		elseif($_GET['unit'] == 'UK') { $unitT = 'C'; $unitR = 'mm'; $unitW = 'mph'; $unitP = 'hPa'; setcookie("SetUnits", "UK", time()+$expTime); }
		else { $unitT = 'C'; $unitR = 'mm'; $unitW = 'km/h'; $unitP = 'mb'; setcookie("SetUnits", "EU", time()+$expTime); }
	}
	elseif(isset($_COOKIE['SetUnits'])) {
		if($_COOKIE['SetUnits'] == 'US') { $unitT = 'F'; $unitR = 'in'; $unitW = 'mph'; $unitP = 'inHg'; }
		if($_COOKIE['SetUnits'] == 'UK') { $unitT = 'C'; $unitR = 'mm'; $unitW = 'mph'; $unitP = 'hPa'; }
		if($_COOKIE['SetUnits'] == 'EU') { $unitT = 'C'; $unitR = 'mm'; $unitW = 'km/h'; $unitP = 'mb'; }
	}
	
	$auto = 'on';
	if (isset($_GET['update'])) {
		if($_GET['update'] == 'on') { $auto = 'on'; setcookie("SetUpdate", "on", time()+$expTime); }
		else { $auto = 'off'; setcookie("SetUpdate", "off", time()+$expTime); }
	}
	elseif(isset($_COOKIE['SetUpdate'])) {
		if($_COOKIE['SetUpdate'] == 'on') { $auto = 'on'; }
		if($_COOKIE['SetUpdate'] == 'off') { $auto = 'off'; }
	}
	
	$size = 66350; $sleep = 0;
	// if(filesize($root.'phptags.php') < $size && filesize($root.'phptags.php') > 0) {
		// $psize = filesize('phptags.php')/1000; sleep(1); $sleep = 1;
		// clearstatcache(); $psize2 = filesize($root.'phptags.php')/1000;
		// if(filesize('phptags.php') < $size) { sleep(1); $sleep = 2; clearstatcache(); $psize3 = filesize($root.'phptags.php')/1000; }
		// $scriptbeg = microtime(get_as_float);
	// }
	$size2 = 8400;
	// if(filesize($root.'main_tags.php') < $size2) {
		// $msize = filesize($root.'main_tags.php')/1000;
		// sleep(1);
		// clearstatcache(); $msize2 = filesize($root.'main_tags.php')/1000;
		// $scriptbeg = microtime(get_as_float); 
	// }
		
	if($_COOKIE['me'] == 1 || $_SERVER['REMOTE_ADDR'] == '131.111.131.12') { $me = 1; }
	if(isset($_GET['blr'])) { $me = 1; setcookie("me", 1, time()+$expTime); }
	if(isset($_GET['noblr'])) { $me = 0; setcookie("me", 0, time()+$expTime); }
	
	if(isset($_GET['year'])) { setcookie("year", intval($_GET['year']), time()+$expTime/400); }
	if(isset($_GET['length'])) { setcookie("length", $_GET['length'], time()+$expTime/10); }
	if(isset($_GET['order'])) { setcookie("order", $_GET['order'], time()+$expTime/10); }
	if(isset($_GET['lengthM'])) { setcookie("lengthM", $_GET['lengthM'], time()+$expTime/10); }
	if(isset($_GET['rank_type'])) { setcookie("rank_type", $_GET['rank_type'], time()+$expTime/10); }
?>