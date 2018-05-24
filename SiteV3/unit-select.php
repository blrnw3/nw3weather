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
	//     $DATT = unserialize( file_get_contents($root.'serialised_datt.txt') );
	$DATM = unserialize(file_get_contents($root . 'serialised_datm.txt'));
	$DATX = datx();
}

$expTime = 3600 * 24 * 100; // cookie lifespan - 100 days

//CSS setting getter/saver
if (isset($_GET['css'])) {
	if ($_GET['css'] == 'wide') {
		$wideormain = 'wide';
		setcookie("css", "wide", time() + $expTime);
	} else {
		$wideormain = 'main';
		setcookie("css", "main", time() + $expTime);
	}
} elseif (isset($_COOKIE['css'])) {
	if ($_COOKIE['css'] == 'wide') {
		$wideormain = 'wide';
	} else {
		$wideormain = 'main';
	}
} else {
	$wideormain = 'main';
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
	$_SESSION['year'] = ($syr >= 2009 && $syr <= $dyear) ? $syr : $dyear;
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


/**
 * Produces DATX array
 * @global type $DATA
 * @global type $DATM
 */
function datx() {
	global $DATA, $DATM;
	$DATX = array();
	for($i = 0; $i < 4; $i++) { // $types_derived
		foreach ($DATA[0] as $year => $arr1) { // Convenience for iterating through YMD
			foreach ($arr1 as $month => $arr2) {
				foreach ($arr2 as $day => $val) {
					if($i < 3) {
						$DATX[$i][$year][$month][$day] =
							$DATA[$i*3+1][$year][$month][$day] - $DATA[$i*3][$year][$month][$day];
					}
					else { // rain rate
						$val = ($DATM[1][$year][$month][$day] > 0.4 && $DATA[13][$year][$month][$day] > 0.2) ?
							$DATA[13][$year][$month][$day] / $DATM[1][$year][$month][$day] : '';
						$DATX[$i][$year][$month][$day] = $val;

					}
				}
			}
		}
	}
	return $DATX;
}

function datAnom() {
	global $types_anom, $types_all, $lta, $vars, $sumq_all;
	$ltaRefDaily = ['tmina' => 0, 'tmaxa' => 1, 'tmeana' => 2, 'sunhrp' => 4];
	$ltaRefMonthly = ['raina' => 4, 'wmeana' => 6, 'wethra' => 11, 'sunhra' => 12];

	$res = array();
	foreach($types_anom as $i => $varName) {
		$originalVarNum = $types_all[substr($varName, 0, strlen($varName)-1)];  // e.g. tmina -> tmin
		$originalSummable = $sumq_all[$originalVarNum];
		$anomType = substr($varName, strlen($varName)-1, 1);  // a or p
		$arr = varNumToDatArray($originalVarNum);

		foreach ($arr as $year => $arr1) {
			foreach ($arr1 as $month => $arr2) {
				$daysInMonth = get_days_in_month($month, $year);
				foreach ($arr2 as $day => $v) {
					if(array_key_exists($varName, $ltaRefDaily)) {
						$climVal = $lta[$ltaRefDaily[$varName]][date('z', mkdate($month, $day, $year))];
					} elseif(array_key_exists($varName, $ltaRefMonthly)) {
						$divisor = $originalSummable ? $daysInMonth : 1;
						$climVal = $vars[$ltaRefMonthly[$varName]][$month-1] / $divisor;
					} else {
						$climVal = 24;
					}
					if($climVal == 0) {
						error_log("YIKES! $climVal $year $month $day $varName");
					}
					if($originalSummable) {
						$val = $arr[$year][$month][$day] / $climVal * 100;
					} else {
						$val = $arr[$year][$month][$day] - $climVal;
					}
					if($val > 100 && $anomType === 'p') {
						$val = 100;
					}
					$res[$varName][$year][$month][$day] = $val;
				}
			}
		}
	}
	return $res;
}
?>