<?php $message = 'Caution: This data is recorded by an amateur-run personal weather station - its accuracy and reliability cannot be guaranteed.';
if($file == 5 || $file == 2 || $file == 6) {
$message = 'Caution: Never base important decisions on this information.';
}

if($date_year < 2000): $date_year = date("Y"); endif;
?>

<div id="footer">
	<div class="doNotPrint">
		<a href="#header">Top</a> |
		<a href="contact.php" title="E-mail me">Contact</a> |
		<a href="./" title="Browse to homepage">Home</a> |
		<a href="/iwdl/" title="Mobile-optimised site version">Mobile</a>
	</div>
	<div>
		&copy; 2010-<?php echo $date_year; ?>, B. Lee-Rodgers<span class="doNotPrint"> |
		<a href="http://www.weather-display.com" title="Powered by Weather Display">Weather Display v<?php echo $wdversion, ' Build ', $wdbuild; ?></a></span>
	</div>
	<div>
	<!--
		<span> I have developed the Live Weather Compare Europe app for Android.
			<a href="https://play.google.com/store/apps/details?id=co.uk.wxApp" title="Download from Google Play">You may want to try it out.</a></span>
		 <br /> 
	-->
		<?php echo $message; ?>
		
	</div>
	<div>
		<a href="http://www.getfirefox.net/" title="Download now">nw3weather recommends Firefox</a><br />
		<span style="font-size:85%"><a href="http://validator.w3.org/check?uri=referer" title="check the W3C validity of this page">XHTML and CSS valid</a> |
		<?php $phpload = microtime(get_as_float) - $scriptbeg;
		echo 'PHP executed <acronym title="Session count: ', $_SESSION['count'][$file], '">in</acronym> ', round($phpload,3), 's'; ?></span>
	</div>
</div>

<?php
$browser = $_SERVER['HTTP_USER_AGENT'];
$find_bot = array('bot', 'crawl', 'wise', 'search', 'validator', 'lipperhey', 'spider', 'http');
for($i = 0; $i < count($find_bot); $i++) {
	if( strpos( strtolower($browser), $find_bot[$i] ) !== false ) {
		$is_bot = true;
		break;
	}
}

if(!$is_bot && !$me) {
	log_eventsV2("siteV2Access.txt", substr($phpload, 0, 3));
}

function str_subpad($string, $length) {
	$padding = (strlen($string) > $length) ? "...  " : " ";
	return substr( str_pad($string, $length), 0, $length - strlen($padding) ) . $padding;
}

function log_eventsV2($txtname, $content = "") {
	if(strlen($content) > 0) {
		$content .= "\t";
	}

	$file_1 = fopen($GLOBALS['root'].'Logs/'.$txtname,"a");
	fwrite( $file_1, date("H:i:s d/m/Y") . "\t" . $content .
		str_subpad( $_SERVER['REQUEST_URI'], 50 ) .
		str_pad($_SERVER['REMOTE_ADDR'], 16) .
		str_replace("Mozilla/5.0 ","",$_SERVER['HTTP_USER_AGENT']) . "\r\n" );
	fclose($file_1);
}


// Mail/logging alerts
	/*
if($phpload < 100 && intval($file) >= 1 && $file != 30 && $file != 111 && strpos($_SERVER['HTTP_USER_AGENT'],'wise') == 0 && strpos($_SERVER['HTTP_USER_AGENT'],'bot') == 0  && strpos($_SERVER['HTTP_USER_AGENT'],'search') == 0) {

	if($_SESSION['count'][$file] == 10 && floor($file) != 71 && $file != 871 && $me != 1) {
		log_events("session_counts.txt", $_SESSION['count'][$file]);
	}
	
	$report = $root.date("FY", mktime(0,0,0,intval(date('m')),date('j')-1)).'.htm';
	if(mktime()-filemtime($report) > 24.05*3600 && date('H') < 8) {
		mail("alerts@nw3weather.co.uk","Old Report","Warning! Latest report not uploaded! Act now! (detected by action in file ".$file. ' by '.
			$_SERVER['REMOTE_ADDR']. ' '.$_SERVER['HTTP_USER_AGENT']. ")", "From: server");
	}
	
	$graph = $root.date("Ymd", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'.gif';
	if(date('i') > 15 && !file_exists($graph) && $baro > 10 && date('H') < 8) {
		mail("alerts@nw3weather.co.uk","Old Graph","Warning! Latest graph not uploaded! Act now! (detected by action in file ".$file. ' by '.
			$_SERVER['REMOTE_ADDR']. ' '.$_SERVER['HTTP_USER_AGENT']. ")", "From: server");
	}
	
	$wcimg = $root.date("Y/Ymd", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'dailywebcam.jpg';
	if(date('i') > 35 && !file_exists($wcimg) && $baro > 10 && date('H') < 8) {
		mail("alerts@nw3weather.co.uk","Old WC image","Warning! Latest webcam image summary not uploaded! Act now! (detected by action in file ".$file. ' by '.
			$_SERVER['REMOTE_ADDR']. ' '.$_SERVER['HTTP_USER_AGENT']. ")", "From: server");
	}

	if($phpload > 2) { log_events("process_times.txt", round($phpload,1)); }
	
}

//Carry out mailing requests from previous files

if(strpos($_SERVER['HTTP_USER_AGENT'],'bot') == 0 && strpos($_SERVER['HTTP_USER_AGENT'],'search') == 0  && strpos($_SERVER['HTTP_USER_AGENT'],'wise') == 0) {
	if($wcnt2 > 0) { //webcam mid-load
		$wfilsiz = $wcnt2*0.25 . "\t"; $wfilsiz .= round($wsizen[0]/1000) . ' ' . round($wsizen[$wcnt2]/1000);
		log_events("sleep_webcam.txt", $wfilsiz);
	}
	if($mail1 == 1) { //phptags mid-load
		$phptags_sleep_content = $sleep . " ". $psize . " " . $psize2 . " " . $psize3;
		log_events("sleep_phptags.txt", $phptags_sleep_content);
	}
	if($mail2 == 1) { //main_tags mid-load
		$maintags_sleep_content = $msize . " ". $msize2;
		log_events("sleep_maintags.txt", $maintags_sleep_content);
	}
	if($mail3 == 1) { //main_tags corruption
		mail("alerts@nw3weather.co.uk","Corrupted main_tags Data","Warning! Source file corrupted! Note Well! (detected by action in file ".$file. ' by '.
			$_SERVER['REMOTE_ADDR']. ' '.$_SERVER['HTTP_USER_AGENT']. ")", "From: server");
	}
	if($mail4 == 1) { //phptags corruption
		mail("alerts@nw3weather.co.uk","Corrupted phptags Data","Warning! Source file corrupted! Note Well! (detected by action in file ".$file. ' by '.
			$_SERVER['REMOTE_ADDR']. ' '.$_SERVER['HTTP_USER_AGENT']. ")", "From: server");
	}
}

*/
// if($mail5 == 1) { //old data
	// mail("alerts@nw3weather.co.uk","Old Data","Warning! Updates not being carried out! Act now! (detected by action in file ".$file. ' by '.
		// $_SERVER['REMOTE_ADDR']. ' '.$_SERVER['HTTP_USER_AGENT']. ")", "From: server");
// }
// if($mail6 == 1) { //phptags missed upload
	// log_events("missed_phptags.txt", "");
// }
// if($mail7 == 1) { //old data on file 1
	// mail("alerts@nw3weather.co.uk","Old main_tags Data","Alert! Updates not being carried out! Act now! (detected by action in file ".$file. ' by '.
		// $_SERVER['REMOTE_ADDR']. ' '.$_SERVER['HTTP_USER_AGENT']. ")", "From: server");
// }
// if($mail8 == 1) { //old clientraw data
	// mail("alerts@nw3weather.co.uk","Old clientraw Data","Alert! Updates not being carried out! Act now! Delay: " . $craw_lag . " s
	// (detected by action in file ".$file. ' by '. $_SERVER['REMOTE_ADDR']. ' '.$_SERVER['HTTP_USER_AGENT']. ")", "From: server");
// }
?>