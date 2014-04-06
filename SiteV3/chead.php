<?php
//Bot dealings
$browser = $_SERVER['HTTP_USER_AGENT'];
$find_bot = array('bot', 'crawl', 'wise', 'search', 'validator', 'lipperhey', 'spider', 'http', 'java', 'www');

for($i = 0; $i < count($find_bot); $i++) {
	if( strpos( strtolower($browser), $find_bot[$i] ) !== false ) {
		$is_bot = true;
		break;
	}
}
$is_bot |= (strlen($browser) === 0);
if(strpos($browser, 'Ezooms') !== false) die('bad bot');

//Session track and update dealings
$_SESSION['count'][$file]++;
$metaRefreshable = in_array($file, array(3,4,10,12,13,14,15)) && !$subfile;

if($auto && $metaRefreshable && !$is_bot) {
	if($_SESSION['count'][$file] < 50) {
		$reftime = 302 - ( time() - filemtime($root.'serialised_datNow.txt') );
		if($reftime < 10) { $reftime = 30; }
		echo '<meta http-equiv="refresh" content="', $reftime, '" />';
	} else {
		//log_events("autoRefeshLimitReached", $_SESSION['count'][$file] .'file: '. $file);
	}
}
?>

<meta name="keywords" content="weather, london, nw3, data, records, statistics, weather station" />
<meta http-equiv="content-type" content="application/xhtml+xml; charset=utf-8" />
<meta http-equiv="content-language" content="en-GB" />	<?php if(isset($_GET['mob'])) { echo '<!--'; $skip_css = true; }
if($needValcolStyle) { echo '
					<link rel="stylesheet" type="text/css" href="/valcolstyle.css" media="screen" title="screen" />'; } ?>
<link rel="stylesheet" type="text/css" href="/<?php echo $wideormain; ?>style.css" media="screen" title="screen" /> <?php if($skip_css) { echo '-->'; } ?>
<!-- <?php // print_r($_SESSION); ?> -->

<script type="text/javascript">
	//<![CDATA[
	var timePHP = <?php echo time(); ?>;
	var dateJS = new Date(timePHP*1000);
	var cntJS = 0;
	function updateTime() {
		dateJS = new Date(timePHP*1000);
		timePHP++;
		setTimeout("updateTime()", 1000);
	}
	updateTime();
	//]]>
</script>

<?php
if($file <= 2) {
	$camImg = '/currcam';
	$camImg .= ($file === 1) ? '_small.jpg' : '.jpg';
	echo '
<script type="text/javascript">
//<![CDATA[
	image = "'.$camImg .'"; //name of the image
	function camRefesh() {
		if(dateJS.getSeconds() < 15 && dateJS.getSeconds() >= 10) {
			document.images["refresh"].src = image+"?"+timePHP;
			//console.log("sec 10-15");
		}
		setTimeout("camRefesh()", 5000);
		//console.log("camrefresh call at second " + dateJS.getSeconds());
	}
	//]]>
</script>';
}
?>


