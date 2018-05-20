<?php
$message = in_array($file, array(5,2,6)) ? 'Caution: Information provided carries no guarantee.' :
	'Caution: This data is recorded by an amateur-run personal weather station - its accuracy and reliability cannot be guaranteed.';

$phpload = myround(microtime(get_as_float) - $scriptbeg, 3);
if($dyear < 2000) { //some sort of non-inclusion
	$dyear = date("Y");
	$phpload = 0;
}
?>
<br />
<div id="footer">
	<div>
		<a href="#header">Top</a> |
		<a href="contact.php" title="E-mail me">Contact</a> |
		<a href="mob.php" title="Very basic mobile browsing">Mobile</a> |
		<a href="http://nw3weather.co.uk" title="Browse to homepage">Home</a>
	</div>
	<div>
		&copy; 2010-<?php echo $dyear; ?>, Ben Lee-Rodgers<span> | Site version 3</span>
	</div>
	<div>
		<?php echo $message; ?>
	</div>
	<div>
		<span style="font-size:85%">
			<a href="http://validator.w3.org/check?uri=referer" title="check the W3C validity of this page">XHTML and CSS valid</a> |
			<?php
			echo 'PHP executed '. acronym('Session count: '. $_SESSION['count'][$file], 'in ') . $phpload .'s';
			if($me) {
				$mem_usage = round(memory_get_usage() / 1024 / 1024, 1);
				$mem_peak = round(memory_get_peak_usage() / 1024 / 1024, 1);
				echo "<br />Mem: $mem_usage, Peak mem: $mem_peak";
			}	?>
		</span>
	</div>
</div>

</div> <!-- //end page div -->
</div>

<?php
echo "<!-- ";
if($me) print_r($_SESSION);
echo "-->";

if(!$me && !$is_bot) {
	$unitLand = $imperial ? 'US' : ($metric ? 'EU' : 'UK');
	log_events("siteV3Access.txt", $phpload .' '. $unitLand .' '. makeBool($auto));
}
if(spam_hack_request()) {
	log_events("spam_hack_requests.txt", $phpload);
}
// Mail/logging alerts
$wcimg = $root.date("Y/Ymd", mkday($dday-1,$dyear)).'dailywebcam.jpg';
if( !file_exists($wcimg) && date('H') < 1 ) {
	mail("alerts@nw3weather.co.uk","Old WC image","Warning! Latest webcam image summary not created! Act now!");
}
if($_SESSION['count'][$file] == 20 && $phpload < 100 && !$is_bot && !$me) {
	log_events("session_counts.txt", "");
}

if($phpload > 1 && !$is_bot && !$me) {
	log_events("process_times.txt", $phpload);
}

if($mailBufferCount > 0) {
	foreach($mailBuffer as $email) {
		server_mail($email['file'], $email['content']);
	}
}

function spam_hack_request() {
	//Empty user-agent string
	$no_uas = (strlen($_SERVER['HTTP_USER_AGENT']) === 0);
	if($no_uas) {
		return true;
	}
	//Hacky request
	$bad_words = array('register', 'login', 'editor', 'admin', 'session', 'forum', 'board', 'join', 'config');
	foreach($bad_words as $badword) {
		if(strpos($_SERVER['REQUEST_URI'], $badword) !== false) {
			return true;
		}
	}
	return false;
}
?>