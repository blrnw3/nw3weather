<?php
require_once('basics.php');

// Block excessive curler
$curlers = array("35.176.125.39", "92.237.11.251", "71.187.230.231");
if(in_array($_SERVER['REMOTE_ADDR'], $curlers) && strpos($_SERVER['HTTP_USER_AGENT'], "urllib") !== false) {
	http_response_code(429);
	die("Too many requests. IP temporarily blocked. Please reduce the request rate to under 6 per hour. Email blr@nw3weather.co.uk to appeal. Thank you");
}

class DateConsts {
	public static $months3 = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	public static $startYear = 2009;
}

class GenConsts {
	public static $lhm = array('Low','High','Mean');
}

if (!isset($_SESSION)) {
	session_start();
	if (count($_SESSION['count']) == 0) {
		$_SESSION['count'] = array();
	}
}

//Make data available
$NOW = unserialize(file_get_contents($root . 'serialised_datNow.txt'));
$HR24 = unserialize(file_get_contents($root . 'serialised_datHr24.txt'));
if ($allDataNeeded) {
	$DATA = unserialize(file_get_contents($root . 'serialised_dat.txt'));
	$DATM = unserialize(file_get_contents($root . 'serialised_datm.txt'));
}

$expTime = 3600 * 24 * 100; // cookie lifespan - 100 days

//CSS setting getter/saver
if (isset($_GET['css'])) {
	$STYLE_SHEET = $_GET['css'];
	setcookie("css", $STYLE_SHEET, time() + $expTime);
} elseif (isset($_COOKIE['css'])) {
	$STYLE_SHEET = $_COOKIE['css'];
} else {
	$STYLE_SHEET = 'mainstyle';
}

//Unit setting getter/saver
if (isset($_GET['unit'])) {
	if ($_GET['unit'] == 'US') {
		$unitT = 'F';		$unitR = 'in';		$unitW = 'mph';		$unitP = 'inHg';
		$unitL = 'ft';		$unitS = 'in.';		$unitA = 'ozft<sup>-3</sup>';
		setcookie("SetUnits", "US", time() + $expTime);
		$imperial = true;
	} elseif ($_GET['unit'] == 'UK') {
		$unitT = 'C';		$unitR = 'mm';		$unitW = 'mph';		$unitP = 'hPa';
		$unitS = 'cm';		$unitL = 'm';		$unitA = 'gm<sup>-3</sup>';
		setcookie("SetUnits", "UK", time() + $expTime);
	} else { //unit is EU
		$unitT = 'C';		$unitR = 'mm';		$unitW = 'kph';		$unitP = 'mb';
		$unitS = 'cm';		$unitL = 'm';		$unitA = 'gm<sup>-3</sup>';
		setcookie("SetUnits", "EU", time() + $expTime);
		$metric = true;
	}
} elseif (isset($_COOKIE['SetUnits'])) {
	if ($_COOKIE['SetUnits'] == 'US') {
		$imperial = true;
		$unitT = 'F';		$unitR = 'in';		$unitW = 'mph';		$unitP = 'inHg';
		$unitS = 'in.';		$unitL = 'ft.';		$unitA = 'ozft<sup>-3</sup>';
	}
	if ($_COOKIE['SetUnits'] == 'UK') {
		$unitT = 'C';		$unitR = 'mm';		$unitW = 'mph';		$unitP = 'hPa';
		$unitS = 'cm';		$unitL = 'm';		$unitA = 'gm<sup>-3</sup>';
	}
	if ($_COOKIE['SetUnits'] == 'EU') {
		$metric = true;
		$unitT = 'C';		$unitR = 'mm';		$unitW = 'kph';		$unitP = 'mb';
		$unitS = 'cm';		$unitL = 'm';		$unitA = 'gm<sup>-3</sup>';
	}
} else { //No cookies or GET - use default (UK) units
	$unitT = 'C';	$unitR = 'mm';	$unitW = 'mph';	$unitP = 'hPa';
	$unitS = 'cm';	$unitL = 'm';	$unitA = 'gm<sup>-3</sup>';
}
$ukUnits = !$imperial && !$metric;

//Globally-agreed units
$unitD = 'degrees';		$unitH = '%';	$unitRR = $unitR . '/h';

//For data tables and reports
$std_units = array($unitT, $unitR, $unitW, $unitP, $unitH, $unitD, $unitRR, 'hrs', 'views', 'days', 'shorthand', $unitS);
$conv_units = array('', ' &deg;' . $unitT, ' ' . $unitR, ' ' . $unitP, ' ' . $unitW, $unitH, ' ' . $unitS, ' ' . $unitL, ' day', ' hrs', ' ' . $unitA, ' degrees');

//Auto-update setting getter/saver
$auto = false;
if (isset($_GET['update'])) {
	if ($_GET['update'] == 'on') {
		$auto = true;
		setcookie("SetUpdate", "on", time() + $expTime);
	} else {
		$auto = false;
		setcookie("SetUpdate", "off", time() + $expTime);
	}
} elseif (isset($_COOKIE['SetUpdate'])) {
	if ($_COOKIE['SetUpdate'] == 'on') {
		$auto = true;
	}
	if ($_COOKIE['SetUpdate'] == 'off') {
		$auto = false;
	}
}

//Me setting getter/saver (stops analytics and provides more debugging)
if (isset($_GET['blr'])) {
	$me = true;
	setcookie("me", true, time() + $expTime * 10);
} elseif(isset($_COOKIE['me']) && $_COOKIE['me'] == true) {
	$me = true;
}
if (isset($_GET['noblr'])) {
	$me = false;
	setcookie("me", false, time() + $expTime);
}

$nw3 = ($_SERVER['REMOTE_ADDR'] == '217.155.197.157');

//if(!$nw3 && $_SERVER['REMOTE_ADDR'] != '77.72.205.160' && strlen($_SERVER['REMOTE_ADDR']) > 4) {
//	error_log('Died here'. $_SERVER['REMOTE_ADDR']);
//	die('Maintenance. Back Soon.');
//}

//Session setters
if (isset($_GET['year'])) {
	$syr = (int)$_GET['year'];
	$_SESSION['year'] = ($syr >= 1871 && $syr <= $dyear) ? $syr : $dyear;
}
if (isset($_GET['month'])) {
	$smo = (int)$_GET['month'];
	$_SESSION['month'] = ($smo >= 0 && $smo <= 12) ? $smo : 0;
}
if (isset($_GET['vartype'])) {
	$_SESSION['vartype'] = $_GET['vartype'];
}
if (isset($_GET['rankLimit'])) {
	$_SESSION['rankLimit'] = (int)$_GET['rankLimit'];
}
if (isset($_GET['start_year_rep'])) {
	$_SESSION['start_year_rep'] = (int)$_GET['start_year_rep'];
}

?>