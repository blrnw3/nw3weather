<?php

ini_set("memory_limit","128M");

$allDataNeeded = true;
$root = '/var/www/html/';
$t_start = microtime(get_as_float);
include_once($fullpath . 'unit-select.php');
include_once($fullpath . 'functions.php');
include_once($fullpath . 'climavs.php');

echo "START: ". date('r'). "\n";

$DATT = unserialize(file_get_contents($root . 'serialised_datt.txt'));

$viewable = '
if( isset($_GET["BLRdebugTags"]) ) {
	//--self downloader --
   $filenameReal = __FILE__;
   $download_size = filesize($filenameReal);
   header("Pragma: public");
   header("Cache-Control: private");
   header("Cache-Control: no-cache, must-revalidate");
   header("Content-type: text/plain");
   header("Accept-Ranges: bytes");
   header("Content-Length: $download_size");
   header("Connection: close");

   readfile($filenameReal);
   exit;
}

$TIMESTAMP = ' . var_export(date('H:i:s jS F Y'), true) . ';
';

class circularBuffer {

	private $size;
	public $items = null;
	private $pointer = 0;
	public $isFull = false;

	function __construct($size) {
		$this->items = array();
		$this->size = $size;
	}

	function add($item) {
		$pos = $this->pointer % $this->size;
		$this->items[$pos] = $item;
		$this->pointer++;
		if ($this->pointer === $this->size) {
			$this->isFull = true;
		}
	}

}

#######################  RAINFALL  ###########################################################
$rainav_curr = $rainav[$dmonth - 1];
$rainall = $rainallM = $rains = array();
for ($y = 2009; $y <= $dyear; $y++) { //Rain tags
	$rains[$y] = $DATA[13][$y];
	$rain2[$y] = MDtoZ($rains[$y]);
	$rain3[$y] = MDtoMsummary($rains[$y], true);
	$rainall = array_merge($rainall, $rain2[$y]);
	$rainallM = array_merge($rainallM, $rain3[$y]);
}
$rainallCnt = count($rainall);
if ($rainallCnt == 0) {
	error_log("Faulty unserialised data in crontags!");
	die();
}

//if($me) { echo _LINE_1, ':  ', myround(microtime(get_as_float) - $scriptbeg,3), ' &nbsp; '; }
$driest31 = 99;
$driest365 = 999;
$rndays_all = 0;
for ($i = 0; $i < $rainallCnt; $i++) {
	if ($rainall[$i] < 0.2) {
		$drylength += 1;
	} else {
		if ($drylength > $recdrylength) {
			$recdrylength = $drylength;
			$recdrylengths_end = $i - 1;
		}
		if ($drylength > $recdrylength_curr && date('n', daytotime($i - 1 - floor($drylength / 2))) == $dmonth) {
			$recdrylength_curr = $drylength;
			$recdrylengths_curr_end = $i - 1;
		}
		$drylength = 0;
	}
	if ($rainall[$i] >= 0.2) {
		$wetlength += 1;
		$rndays_all++;
	} else {
		if ($wetlength > $recwetlength) {
			$recwetlength = $wetlength;
			$recwetlengths_end = $i - 1;
		}
		if ($wetlength > $recwetlength_curr && date('n', daytotime($i - 1 - floor($wetlength / 2))) == $dmonth) {
			$recwetlength_curr = $wetlength;
			$recwetlengths_curr_end = $i - 1;
		}
		$wetlength = 0;
	}
	$cumrn31 += floatval($rainall[$i]);
	if ($i > 30) {
		$cumrn31 -= floatval($rainall[$i - 31]);
	}
	if ($cumrn31 > $wettest31) {
		$wettest31 = $cumrn31;
		$wettest31_end = $i;
	}
	if ($cumrn31 < $driest31 && $i > 30) {
		$driest31 = $cumrn31;
		$driest31_end = $i;
	}
	$cumrn365 += floatval($rainall[$i]);
	if ($i > 364) {
		$cumrn365 -= floatval($rainall[$i - 365]);
	}
	if ($cumrn365 > $wettest365) {
		$wettest365 = $cumrn365;
		$wettest365_end = $i;
	}
	if ($cumrn365 < $driest365 && $i > 364) {
		$driest365 = $cumrn365;
		$driest365_end = $i;
	}
	$cumrn7 += floatval($rainall[$i]);
	if ($i >= 7) {
		$cumrn7 -= floatval($rainall[$i - 7]);
	}
	if ($cumrn7 > $wettest7) {
		$wettest7 = $cumrn7;
		$wettest7_end = $i;
	}
	$cumrn3 += floatval($rainall[$i]);
	if ($i >= 3) {
		$cumrn3 -= floatval($rainall[$i - 3]);
	}
	if ($cumrn3 > $wettest3) {
		$wettest3 = $cumrn3;
		$wettest3_end = $i;
	}
}

//deal with final day (today) being a record-breaker - loop to set record would not be entered so need the lines below
if ($wetlength > $recwetlength) {
	$recwetlength = $wetlength;
	$recwetlengths_end = $i - 1;
}
if ($wetlength > $recwetlength_curr && date('n', daytotime($i - 1 - floor($wetlength / 2))) == $dmonth) {
	$recwetlength_curr = $wetlength;
	$recwetlengths_curr_end = $i - 1;
}
if ($wetlength > $recwetlength) {
	$recwetlength = $wetlength;
	$recwetlengths_end = $i - 1;
}
if ($wetlength > $recwetlength_curr && date('n', daytotime($i - 1 - floor($wetlength / 2))) == $dmonth) {
	$recwetlength_curr = $wetlength;
	$recwetlengths_curr_end = $i - 1;
}
$recdrylengthdate = date('jS M', daytotime($recdrylengths_end - $recdrylength + 1)) . ' - ' . today(true, true, true, true, daytotime($recdrylengths_end));
$recwetlengthdate = date('jS M', daytotime($recwetlengths_end - $recwetlength + 1)) . ' - ' . today(true, true, true, true, daytotime($recwetlengths_end));
$recdrylength_currdate = date('jS M', daytotime($recdrylengths_curr_end - $recdrylength_curr + 1)) . ' - ' . today(true, true, true, true, daytotime($recdrylengths_curr_end));
$recwetlength_currdate = date('jS M', daytotime($recwetlengths_curr_end - $recwetlength_curr + 1)) . ' - ' . today(true, true, true, true, daytotime($recwetlengths_curr_end));

$wettest3date = 'Ending ' . today(true, true, true, null, daytotime($wettest3_end));
$wettest7date = 'Ending ' . today(true, true, true, null, daytotime($wettest7_end));
$wettest31date = 'Ending ' . today(true, true, true, null, daytotime($wettest31_end));
$driest31date = 'Ending ' . today(true, true, true, null, daytotime($driest31_end));
$wettest365date = 'Ending ' . today(true, true, true, null, daytotime($wettest365_end));
$driest365date = 'Ending ' . today(true, true, true, null, daytotime($driest365_end));

$endday = $rainallCnt - date('z') - 1;
$drylength = $wetlength = 0;
for ($i = $rainallCnt - 1; $i >= $endday - 50; $i--) {
	if ($rainall[$i] < 0.2) {
		$drylength += 1;
	} else {
		if ($drylength > $yrecdrylength && ($i + $drylength + 1) > $endday) {
			$yrecdrylength = $drylength;
			$yrecdrylengths_beg = $i + 1;
		}
		$drylength = 0;
	}
	if ($rainall[$i] >= 0.2) {
		$wetlength += 1;
	} else {
		if ($wetlength > $yrecwetlength && ($i + $wetlength + 1) > $endday) {
			$yrecwetlength = $wetlength;
			$yrecwetlengths_beg = $i + 1;
		}
		$wetlength = 0;
	}
}

$yrecwetlengthdate = ($yrecwetlength < 1) ? 'n/a' : date('jS M', daytotime($yrecwetlengths_beg)) . ' - ' . today(null, true, true, true, daytotime($yrecwetlengths_beg + $yrecwetlength - 1));
$yrecdrylengthdate = ($yrecdrylength < 1) ? 'n/a' : date('jS M', daytotime($yrecdrylengths_beg)) . ' - ' . today(null, true, true, true, daytotime($yrecdrylengths_beg + $yrecdrylength - 1));

$endday = $rainallCnt - $dday;
$drylength = $wetlength = 0;
$daysnorain = $dayswithrain = -1;
for ($i = $rainallCnt - 1; $i >= $endday - 50; $i--) {
	if ($rainall[$i] < 0.2) {
		$drylength += 1;
	} else {
		if ($drylength > $mrecdrylength && ($i + $drylength + 1) > $endday) {
			$mrecdrylength = $drylength;
			$mrecdrylengths_beg = $i + 1;
		}
		if ($daysnorain < 0) {
			$daysnorain = $drylength;
		}
		$drylength = 0;
	}
	if ($rainall[$i] >= 0.2) {
		$wetlength += 1;
		if ($rainall[$rainallCnt - 1] == 0 && $i == $rainallCnt - 2) {
			$dayswithrain = -1;
		}
	} else {
		if ($wetlength > $mrecwetlength && ($i + $wetlength + 1) > $endday) {
			$mrecwetlength = $wetlength;
			$mrecwetlengths_beg = $i + 1;
		}
		if ($dayswithrain < 0) {
			$dayswithrain = $wetlength;
		}
		$wetlength = 0;
	}
}
$mrecwetlengthdate = ($mrecwetlength < 1) ? 'n/a' : date('jS M', daytotime($mrecwetlengths_beg)) . ' - ' . today(null, null, true, true, daytotime($mrecwetlengths_beg + $mrecwetlength - 1));
$mrecdrylengthdate = ($mrecdrylength < 1) ? 'n/a' : date('jS M', daytotime($mrecdrylengths_beg)) . ' - ' . today(null, null, true, true, daytotime($mrecdrylengths_beg + $mrecdrylength - 1));
if ($daysnorain > 0) {
	$drywet = 'Dry';
	$drywetdays = $daysnorain;
} else {
	$drywet = 'Wet';
	$drywetdays = $dayswithrain;
}

$rndays_all = acronym("Proportion of days: " . percent($rndays_all, $rainallCnt), $rndays_all, true);

$allrn = array_sum($rainall);
$rain365a = $rain2[$dyear] + $rain2[$dyear - 1];
//print_r($rain365a);
$raintots = array_merge($rain3[$dyear] + $rain3[$dyear - 1]);
$day_rain_last_year = $rains[$dyear - 1][$dmonth][$dday];
$monthrn = array_sum($rains[$dyear][$dmonth]);
$yearrn = array_sum($rain2[$dyear]);
$yestrn = $ystdrain = $rain2[$yr_yest][$dz_yest];

$rndays_week = 0;
for ($d = 1; $d <= 7; $d++) {
	$rainweekVals[$d] = $rain365a[date('z', mkday($dday - $d)) + 1];
	if ($rainweekVals[$d] >= 0.2) {
		$rndays_week++;
	}
}
$rndays_week = acronym("Proportion of days: " . percent($rndays_week, 7), $rndays_week, true);
$rainweek = array_sum($rainweekVals);
$rainweekMax = max($rainweekVals);
$rainweekDate = today(null, $display_mon, true, null, mkday($dday - array_search($rainweekMax, $rainweekVals) + 1));

for ($d = 1; $d <= 31; $d++) {
	$rain31 += $rain365a[date('z', mkday($dday - $d)) + 1];
}
$rain365 = array_sum($rain365a);
$raindays_month = $raindays_year = 0;
$timeMonthago = mkdate($dmonth - 1, 1, $dyear);
for ($d = 1; $d <= count($rains[$dyear][$dmonth]); $d++) {
	if ($rains[$dyear][$dmonth][$d] > 0.1) {
		$raindays_month += 1;
	}
}
for ($d = 0; $d < count($rain2[$dyear]); $d++) {
	if ($rain2[$dyear][$d] > 0.1) {
		$raindays_year += 1;
	}
}
for ($d = 1; $d <= count($rains[$dyear][$dmonth]); $d++) {
	$raintodmonthago += $rains[date('Y', $timeMonthago)][date('n', $timeMonthago)][$d];
}
for ($d = 0; $d < count($rain2[$dyear]); $d++) {
	$raintodayearago += $rain2[$dyear - 1][$d];
}

$raintots[$dmonth - 1] = $monthrn;
$s_offset = $season * 3 - 3;
for ($e = 0; $e < $sc; $e++) {
	$total += floatval($raintots[date('n', mkdate($e + $s_offset, 1)) - 1]);
}
for ($f = 0; $f < 3; $f++) {
	$s_avg += floatval($rainav[date('n', mkdate($f + $s_offset, 1)) - 1]);
}
for ($g = 1; $g < $sc; $g++) {
	$s_avg_r += floatval($rainav[date('n', mkdate($g + $s_offset, 1)) - 1]);
} $s_avg_r += $rainav_curr / date('t') * $dday;

$lymrain = $rain3[$dyear - 1][$dmonth];
$lymrain1 = $rain3[date('Y', mkdate($dmonth - 1, 1, $dyear - 1))][date('n', mkdate($dmonth - 1, 1, $dyear - 1))];
$lymrain2 = $rain3[date('Y', mkdate($dmonth - 2, 1, $dyear - 1))][date('n', mkdate($dmonth - 2, 1, $dyear - 1))];
//conv($monthrn, 2) . ' (' .
$monthrnF = ' (' . acronym('Of final expected: ' . round(100 * $monthrn / $rainav_curr) . '%', round(100 * $monthrn / ($rainav_curr / $dt * $dday)) . '%', true) . ')';
for ($m = 0; $m < $dmonth - 1; $m++) {
	$curryearrainav += $rainav[$m];
}//conv($yearrn, 2) . ' (' .
$yearrnF = ' (' . acronym('Of final expected: ' . round(100 * $yearrn / array_sum($rainav)) . '%', round(100 * $yearrn / ($rainav_curr / $dt * $dday + $curryearrainav)) . '%', true) . ')';
$seasonrnF = ' (' . acronym('Of final expected: ' . round(100 * $total / $s_avg) . '%', round(100 * $total / $s_avg_r) . '%', true) . ')'; //conv($total, 2) .
$raindays_monthF = acronym('Proportion of days: ' . round(100 * $raindays_month / ($dday)) . '%', $raindays_month, true);
$raindays_yearF = acronym('Proportion of days: ' . round(100 * $raindays_year / (date('z') + 1)) . '%', $raindays_year, true);

for ($y = 2009; $y <= $dyear; $y++) {
	if ($y != $dyear) {
		$wettestyra[$y] = array_sum($rain2[$y]);
	}
	$maxrainDa[$y] = $rains[$y][$dmonth][$dday];
	if (is_array($rains[$y][$dmonth])) {
		$maxrainMDa[$y] = max($rains[$y][$dmonth]);
		$maxrainMDdatea[$y] = array_search($maxrainMDa[$y], $rains[$y][$dmonth]);
	}
	$monthrn_max_curra[$y] = $rain3[$y][$dmonth];
	if ($y < $dyear) {
		$monthrn_min_curra[$y] = $rain3[$y][$dmonth];
	}
	$monthrn_mina[$y] = min($rain3[$y]);
	$monthrn_mindatea[$y] = array_search($monthrn_mina[$y], $rain3[$y]);
	$monthrn_maxa[$y] = max($rain3[$y]);
	$monthrn_maxdatea[$y] = array_search($monthrn_maxa[$y], $rain3[$y]);
	$recorddailyraina[$y] = max($rain2[$y]);
	$recorddailyraindatea[$y] = array_search($recorddailyraina[$y], $rain2[$y]);
	for ($m = 1; $m <= count($rains[$y]); $m++) {
		for ($d = 1; $d <= count($rains[$y][$m]); $d++) {
			if ($rains[$y][$m][$d] >= 0.2) {
				$rndays[$y][$m] += 1;
				$rndays2[$m][$y] += 1;
			}
		}
	}
	$rndays_maxa[$y] = max($rndays[$y]);
	$rndays_maxdatea[$y] = array_search($rndays_maxa[$y], $rndays[$y]);
	if (isset($rndays[$dyear][$dmonth])) {
		unset($rndays[$dyear][$dmonth]);
	} //ignore current month
	if (!($dmonth == 1 && $y == $dyear)) {
		$rndays_mina[$y] = min($rndays[$y]);
		$rndays_mindatea[$y] = array_search($rndays_mina[$y], $rndays[$y]);
	}
}

$wettestyr = max($wettestyra);
$wettestyrdate = array_search($wettestyr, $wettestyra);
$driestyr = min($wettestyra);
$driestyrdate = array_search($driestyr, $wettestyra);

$rndays_max = max($rndays_maxa);
$rndays_maxdate = today(array_search($rndays_max, $rndays_maxa), $rndays_maxdatea[array_search($rndays_max, $rndays_maxa)], null, true);
$rndays_min = min($rndays_mina);
$rndays_mindate = today(array_search($rndays_min, $rndays_mina), $rndays_mindatea[array_search($rndays_min, $rndays_mina)], null, true);

$yrndays_max = $rndays_maxa[$dyear];
$yrndays_maxdate = today(null, $rndays_maxdatea[$dyear], null, true);
$yrndays_min = $rndays_mina[$dyear];
$yrndays_mindate = today(null, $rndays_mindatea[$dyear], null, true);

$rndays_max_curr = max($rndays2[$dmonth]);
$rndays_max_currdate = today(array_search($rndays_max_curr, $rndays2[$dmonth]), null, null, true);
if ($too_early) {
	unset($rndays2[$dmonth][$dyear]);
}
$rndays_min_curr = min($rndays2[$dmonth]);
$rndays_min_currdate = today(array_search($rndays_min_curr, $rndays2[$dmonth]), null, null, true);

$maxrainD = max($maxrainDa);
$maxrainDdate = today(array_search($maxrainD, $maxrainDa));
$maxrainMD = max($maxrainMDa);
$maxrainMDdate = today(array_search($maxrainMD, $maxrainMDa), null, $maxrainMDdatea[array_search($maxrainMD, $maxrainMDa)]);

$monthrn_max_curr = max($monthrn_max_curra);
$monthrn_max_currdate = today(array_search($monthrn_max_curr, $monthrn_max_curra), null, null, true);
$monthrn_min_curr = min($monthrn_min_curra);
$monthrn_min_currdate = today(array_search($monthrn_min_curr, $monthrn_min_curra), null, null, true);

$monthrn_min = min($monthrn_mina);
$monthrn_mindate = today(array_search($monthrn_min, $monthrn_mina), $monthrn_mindatea[array_search($monthrn_min, $monthrn_mina)], null, true);
$monthrn_max = max($monthrn_maxa);
$monthrn_maxdate = today(array_search($monthrn_max, $monthrn_maxa), $monthrn_maxdatea[array_search($monthrn_max, $monthrn_maxa)], null, true);

$yrmonthrn_max = $monthrn_maxa[$dyear];
$yrmonthrn_maxdate = today(null, $monthrn_maxdatea[$dyear], null, true);
$yrmonthrn_min = $monthrn_mina[$dyear];
$yrmonthrn_mindate = today(null, $monthrn_mindatea[$dyear], null, true);

$recorddailyrain = max($recorddailyraina);
$tempyr = array_search($recorddailyrain, $recorddailyraina);
$tempz = $recorddailyraindatea[$tempyr];
$recorddailyraindate = today($tempyr, true_z($tempz, $tempyr), true_z($tempz, $tempyr, 'j'));
$yrecorddailyrain = $recorddailyraina[$dyear];
$yrecorddailyraindate = today(null, true_z($recorddailyraindatea[$dyear], $dyear), true_z($recorddailyraindatea[$dyear], $dyear, 'j'));
$mrecorddailyrain = $maxrainMDa[$dyear];
$mrecorddailyraindate = today(null, null, $maxrainMDdatea[$dyear]);

//Wettest Days
$rain_sort = $rainall;
sort($rain_sort);
$ranknum = 15;
for ($i = 1; $i <= $ranknum; $i++) {
	$wettest[$i] = $rain_sort[count($rain_sort) - $i];
	$dayRec = array_search($wettest[$i], $rainall);
	$wettest_day[$i] = today(true, true, true, null, daytotime($dayRec));
	$rainall[$dayRec] -= 0.01; //prevent duplicated dates
}
//Wettest & Driest Months
$rain_sortM = $rainallM;
sort($rain_sortM);
$ranknumM = 10;
for ($i = 1; $i <= $ranknumM; $i++) {
	$wettestM[$i] = $rain_sortM[count($rain_sortM) - $i];
	$wetMnum = array_search($wettestM[$i], $rainallM);
	$wettest_dayM[$i] = today(date('Y', mkdate(1 + $wetMnum, 1, 2009)), date('n', mkdate(1 + $wetMnum, 1, 2009)), false, true);
	$driestM[$i] = $rain_sortM[$i - 1];
	$dryMnum = array_search($driestM[$i], $rainallM);
	$driest_dayM[$i] = today(date('Y', mkdate(1 + $dryMnum, 1, 2009)), date('n', mkdate(1 + $dryMnum, 1, 2009)), false, true);
}

unset($rains, $rain2, $rain3, $rainall); //destroy variables

for ($y = 2009; $y <= $dyear; $y++) { //Rate tags
	$rate[$y] = $DATA[16][$y];
	$rate2[$y] = MDtoZ($rate[$y]);
	$maxrateDa[$y] = $rate[$y][$dmonth][$dday];
	if (is_array($rate[$y][$dmonth])) {
		$maxrateMDa[$y] = max($rate[$y][$dmonth]);
		$maxrateMDdatea[$y] = array_search($maxrateMDa[$y], $rate[$y][$dmonth]);
	}
	$recorddailyratea[$y] = max($rate2[$y]);
	$recorddailyratedatea[$y] = array_search($recorddailyratea[$y], $rate2[$y]);
}
//if($me) { echo _LINE3_, ':  ', myround(microtime(get_as_float) - $scriptbeg,3), ' &nbsp; '; }

$maxrateD = max($maxrateDa);
$maxrateDdate = today(array_search($maxrateD, $maxrateDa));
$maxrateMD = max($maxrateMDa);
$maxrateMDdate = today(array_search($maxrateMD, $maxrateMDa), null, $maxrateMDdatea[array_search($maxrateMD, $maxrateMDa)]);

$maxrainrateyest = rate_fix($rate2[$yr_yest][$dz_yest]);
$maxrainrateyesttime = $DATT[16][$yr_yest][$mon_yest][$day_yest];
$recorddailyrate = rate_fix(max($recorddailyratea));
$tempyr = array_search($recorddailyrate, $recorddailyratea);
$recorddailyratedate = today($tempyr, true_z($recorddailyratedatea[$tempyr], $tempyr), true_z($recorddailyratedatea[$tempyr], $tempyr, 'j'));
//$recorddailyratetime = $ratet[$tempyr][$recorddailyratedatea[$tempyr]];
$yrecorddailyrate = rate_fix($recorddailyratea[$dyear]);
$yrecorddailyratedate = today(null, true_z($recorddailyratedatea[$dyear], $dyear), true_z($recorddailyratedatea[$dyear], $dyear, 'j'));
//$yrecorddailyratetime = $ratet[$dyear][$recorddailyratedatea[$dyear]];
$mrecorddailyrate = rate_fix($maxrateMDa[$dyear]);
$mrecorddailyratedate = today(null, null, $maxrateMDdatea[$dyear]);
//$mrecorddailyratetime = $ratet[$dyear][date('z', mkday($maxrateMDdatea[$dyear])) + 1];

unset($rate, $rate2, $ratet); //destroy variables
//if($me) { echo _LINE4_, ':  ', myround(microtime(get_as_float) - $scriptbeg,3), ' &nbsp; '; }

for ($y = 2009; $y <= $dyear; $y++) { //hrmax tags
	$hrmax[$y] = $DATA[14][$y];
	$hrmax2[$y] = MDtoZ($hrmax[$y]);
	$maxhrmaxDa[$y] = $hrmax[$y][$dmonth][$dday];
	if (is_array($hrmax[$y][$dmonth])) {
		$maxhrmaxMDa[$y] = max($hrmax[$y][$dmonth]);
		$maxhrmaxMDdatea[$y] = array_search($maxhrmaxMDa[$y], $hrmax[$y][$dmonth]);
	}
	$recorddailyhrmaxa[$y] = max($hrmax2[$y]);
	$recorddailyhrmaxdatea[$y] = array_search($recorddailyhrmaxa[$y], $hrmax2[$y]);
}
//if($me) { echo _LINE5_, ':  ', myround(microtime(get_as_float) - $scriptbeg,3), ' &nbsp; '; }

$maxhrmaxD = max($maxhrmaxDa);
$maxhrmaxDdate = today(array_search($maxhrmaxD, $maxhrmaxDa));
$maxhrmaxMD = max($maxhrmaxMDa);
$maxhrmaxMDdate = today(array_search($maxhrmaxMD, $maxhrmaxMDa), null, $maxhrmaxMDdatea[array_search($maxhrmaxMD, $maxhrmaxMDa)]);

$maxhourrnyest = $hrmax2[$yr_yest][$dz_yest];
$maxhourrnyesttime = $DATT[14][$yr_yest][$mon_yest][$day_yest];
$recorddailyhrmax = max($recorddailyhrmaxa);
$tempyr = array_search($recorddailyhrmax, $recorddailyhrmaxa);
$recorddailyhrmaxdate = today($tempyr, true_z($recorddailyhrmaxdatea[$tempyr], $tempyr), true_z($recorddailyhrmaxdatea[$tempyr], $tempyr, 'j'));
//$recorddailyhrmaxtime = trim($hrmaxt[$tempyr][$recorddailyhrmaxdatea[$tempyr]]);
$yrecorddailyhrmax = $recorddailyhrmaxa[$dyear];
$yrecorddailyhrmaxdate = today(null, true_z($recorddailyhrmaxdatea[$dyear], $dyear), true_z($recorddailyhrmaxdatea[$dyear], $dyear, 'j'));
//$yrecorddailyhrmaxtime = $hrmaxt[$dyear][$recorddailyhrmaxdatea[$dyear]];
$mrecorddailyhrmax = $maxhrmaxMDa[$dyear];
$mrecorddailyhrmaxdate = today(null, null, $maxhrmaxMDdatea[$dyear]);
//$mrecorddailyhrmaxtime = $hrmaxt[$dyear][date('z', mkday($maxhrmaxMDdatea[$dyear])) + 1];

if ($monthrn < 0.3) {
	//set various stuff to n/a
}

unset($hrmax, $hrmax2); //destroy variables

$maxRateWeek = null;
$maxRateWeek_date = null;
for ($i = 0; $i < 7; $i++) {
	$mt = mkdate($dmonth, $dday - $i, $dyear);
	$y = date('Y', $mt);
	$m = date('n', $mt);
	$d = date('j', $mt);
	if ($DATA[16][$y][$m][$d] > $maxRateWeek) {
		$maxRateWeek = $DATA[16][$y][$m][$d];
		$maxRateWeek_date = today(null, null, true, null, $mt);
	}
}

$maxhourrn = $NOW['max']['rnhr'];
$maxhourrnt = $NOW['timeMax']['rnhr'];
$maxrainratehr = $NOW['max']['rate'];
$maxrainratetime = $NOW['timeMax']['rate'];

$outputRn = '
$rnallcount = \'' . $rainallCnt . '\';
$yestrn = \'' . $yestrn . '\';
$rainweek = \'' . $rainweek . '\';
$rndays_week = \'' . $rndays_week . '\';
$rain31 = \'' . $rain31 . '\';
$rain365 = \'' . $rain365 . '\';
$monthrn = \'' . $monthrn . '\';
$yearrn = \'' . $yearrn . '\';
$seasonrn = \'' . $total . '\';
$allrn = \'' . $allrn . '\';
$rndays_all = \'' . $rndays_all . '\';
$monthrnF = \'' . $monthrnF . '\';
$yearrnF = \'' . $yearrnF . '\';
$seasonrnF = \'' . $seasonrnF . '\';
$drywetdays = \'' . $drywetdays . '\';
$raindays_monthF = \'' . $raindays_monthF . '\';
$raindays_yearF = \'' . $raindays_yearF . '\';
$day_rain_last_year = \'' . $day_rain_last_year . '\';
$raintodmonthago = \'' . $raintodmonthago . '\';
$raintodayearago = \'' . $raintodayearago . '\';
$drywet = \'' . $drywet . '\';
$rainweekMax = \'' . $rainweekMax . '\';
$rainweekMaxDate = \'' . $rainweekMaxDate . '\';
$maxhourrn = \'' . $maxhourrn . '\';
$maxrainratehr = \'' . $maxrainratehr . '\';
$mrecorddailyrain = \'' . $mrecorddailyrain . '\';
$mrecorddailyhrmax = \'' . $mrecorddailyhrmax . '\';
$mrecorddailyrate = \'' . $mrecorddailyrate . '\';
$mrecwetlength = \'' . $mrecwetlength . '\';
$mrecdrylength = \'' . $mrecdrylength . '\';
$yrecorddailyrain = \'' . $yrecorddailyrain . '\';
$yrecorddailyhrmax = \'' . $yrecorddailyhrmax . '\';
$yrecorddailyrate = \'' . $yrecorddailyrate . '\';
$yrecwetlength = \'' . $yrecwetlength . '\';
$yrecdrylength = \'' . $yrecdrylength . '\';
$yrmonthrn_max = \'' . $yrmonthrn_max . '\';
$yrmonthrn_min = \'' . $yrmonthrn_min . '\';
$yrndays_max = \'' . $yrndays_max . '\';
$yrndays_min = \'' . $yrndays_min . '\';
$maxhourrnyest = \'' . $maxhourrnyest . '\';
$maxrainrateyest = \'' . $maxrainrateyest . '\';
$maxhourrnt = \'' . $maxhourrnt . '\';
$maxrainratetime = \'' . $maxrainratetime . '\';
$mrecorddailyraindate = \'' . $mrecorddailyraindate . '\';
$mrecorddailyhrmaxdate = \'' . $mrecorddailyhrmaxdate . '\';
$mrecorddailyratedate = \'' . $mrecorddailyratedate . '\';
$mrecwetlengthdate = \'' . $mrecwetlengthdate . '\';
$mrecdrylengthdate = \'' . $mrecdrylengthdate . '\';
$yrecorddailyraindate = \'' . $yrecorddailyraindate . '\';
$yrecorddailyhrmaxdate = \'' . $yrecorddailyhrmaxdate . '\';
$yrecorddailyratedate = \'' . $yrecorddailyratedate . '\';
$yrecwetlengthdate = \'' . $yrecwetlengthdate . '\';
$yrecdrylengthdate = \'' . $yrecdrylengthdate . '\';
$yrmonthrn_maxdate = \'' . $yrmonthrn_maxdate . '\';
$yrmonthrn_mindate = \'' . $yrmonthrn_mindate . '\';
$yrndays_maxdate = \'' . $yrndays_maxdate . '\';
$yrndays_mindate = \'' . $yrndays_mindate . '\';
$maxhourrnyesttime = \'' . $maxhourrnyesttime . '\';
$maxrainrateyesttime = \'' . $maxrainrateyesttime . '\';
$maxrainD = \'' . $maxrainD . '\';
$maxhrmaxD = \'' . $maxhrmaxD . '\';
$maxrateD = \'' . $maxrateD . '\';
$maxrainMD = \'' . $maxrainMD . '\';
$maxhrmaxMD = \'' . $maxhrmaxMD . '\';
$maxrateMD = \'' . $maxrateMD . '\';
$recwetlength_curr = \'' . $recwetlength_curr . '\';
$recdrylength_curr = \'' . $recdrylength_curr . '\';
$monthrn_max_curr = \'' . $monthrn_max_curr . '\';
$monthrn_min_curr = \'' . $monthrn_min_curr . '\';
$rndays_max_curr = \'' . $rndays_max_curr . '\';
$rndays_min_curr = \'' . $rndays_min_curr . '\';
$recorddailyrain = \'' . $recorddailyrain . '\';
$recorddailyhrmax = \'' . $recorddailyhrmax . '\';
$recorddailyrate = \'' . $recorddailyrate . '\';
$recwetlength = \'' . $recwetlength . '\';
$recdrylength = \'' . $recdrylength . '\';
$monthrn_max = \'' . $monthrn_max . '\';
$monthrn_min = \'' . $monthrn_min . '\';
$rndays_max = \'' . $rndays_max . '\';
$rndays_min = \'' . $rndays_min . '\';
$maxrainDdate = \'' . $maxrainDdate . '\';
$maxhrmaxDdate = \'' . $maxhrmaxDdate . '\';
$maxrateDdate = \'' . $maxrateDdate . '\';
$maxrainMDdate = \'' . $maxrainMDdate . '\';
$maxhrmaxMDdate = \'' . $maxhrmaxMDdate . '\';
$maxrateMDdate = \'' . $maxrateMDdate . '\';
$recwetlength_currdate = \'' . $recwetlength_currdate . '\';
$recdrylength_currdate = \'' . $recdrylength_currdate . '\';
$monthrn_max_currdate = \'' . $monthrn_max_currdate . '\';
$monthrn_min_currdate = \'' . $monthrn_min_currdate . '\';
$rndays_max_currdate = \'' . $rndays_max_currdate . '\';
$rndays_min_currdate = \'' . $rndays_min_currdate . '\';
$recorddailyraindate = \'' . $recorddailyraindate . '\';
$recorddailyhrmaxdate = \'' . $recorddailyhrmaxdate . '\';
$recorddailyratedate = \'' . $recorddailyratedate . '\';
$recwetlengthdate = \'' . $recwetlengthdate . '\';
$recdrylengthdate = \'' . $recdrylengthdate . '\';
$monthrn_maxdate = \'' . $monthrn_maxdate . '\';
$monthrn_mindate = \'' . $monthrn_mindate . '\';
$rndays_maxdate = \'' . $rndays_maxdate . '\';
$rndays_mindate = \'' . $rndays_mindate . '\';
$wettestyr = \'' . $wettestyr . '\';
$driestyr = \'' . $driestyr . '\';
$wettest3 = \'' . $wettest3 . '\';
$wettest7 = \'' . $wettest7 . '\';
$wettest31 = \'' . $wettest31 . '\';
$driest31 = \'' . $driest31 . '\';
$wettest365 = \'' . $wettest365 . '\';
$driest365 = \'' . $driest365 . '\';
$record24hrrain = \'' . $record24hrrain . '\';
$wettestyrdate = \'' . $wettestyrdate . '\';
$driestyrdate = \'' . $driestyrdate . '\';
$wettest3date = \'' . $wettest3date . '\';
$wettest7date = \'' . $wettest7date . '\';
$wettest31date = \'' . $wettest31date . '\';
$driest31date = \'' . $driest31date . '\';
$wettest365date = \'' . $wettest365date . '\';
$driest365date = \'' . $driest365date . '\';
$ranknum = \'' . $ranknum . '\';
$ranknumM = \'' . $ranknumM . '\';
$lymrain = \'' . $lymrain . '\';
$lymrain1 = \'' . $lymrain1 . '\';
$lymrain2 = \'' . $lymrain2 . '\';
$maxRateWeek = \'' . $maxRateWeek . '\';
$maxRateWeek_date = \'' . $maxRateWeek_date . '\';

$wettest_day = ' . var_export($wettest_day, true) . ';
$wettest = ' . var_export($wettest, true) . ';
$wettest_dayM = ' . var_export($wettest_dayM, true) . ';
$wettestM = ' . var_export($wettestM, true) . ';
$raintots = ' . var_export($raintots, true) . ';
$driestM = ' . var_export($driestM, true) . ';
$driest_dayM = ' . var_export($driest_dayM, true) . ';
';

file_put_contents($root . "RainTags.php", "<?php $viewable $outputRn ?>");

if (isset($_GET['debugRn']))
	echo $outputRn;


#######################  TEMPERATURE  ###########################################################
$hrsfrostmidnight = $NOW['misc']['frosthrs'];

$nighttimeMin = $NOW['min']['night'];
$daytimeMax = $NOW['max']['day'];
$nighttimeMinT = $NOW['timeMin']['night'];
$daytimeMaxT = $NOW['timeMax']['day'];

$daysTminL0C = $daysTminyearL0C = $mon = 0;
foreach ($DATA[20][$dyear] as $tminM) {
	$mon++;
	foreach ($tminM as $tmin) {
		if ($tmin < 0) {
			$daysTminyearL0C++;
			if ($mon == $dmonth) {
				$daysTminL0C++;
			}
		}
	}
}

function getDetailedData($varNum, $dataType = 'A', $debug = false) {
	global $fullpath, $NOW, $HR24, $types_original, $mappingsToDailyDataKey;
	include $fullpath . 'basics.php';

	$mapToDailyDataKey = $mappingsToDailyDataKey[substr($types_original[$varNum], 0, 1)];
//	echo "key for $varNum is $mapToDailyDataKey <br />";
	$avLast24 = $HR24['mean'][$mapToDailyDataKey];

	if ($varNum == 0 && $dataType == 'A') {
		global $lta, $tdatday, $tdatav;
		$getExtra = true;
	}

	$c = 0;
	$ranks = array();
	$totals = array();

	for ($x = $varNum; $x < $varNum + 3; $x++) { //min/max/mean loop
		$key = $dnm[$c];

		$tdatday[$c] = $NOW[$key][$mapToDailyDataKey];
		$tdatdaydate[$c] = $NOW['time'.$mmmr[$c]][$mapToDailyDataKey];
		$tdatyest[$key] = $GLOBALS['DATA'][$x][$yr_yest][$mon_yest][$day_yest];
		$tdatyestdate[$c] = $GLOBALS['DATT'][$x][$yr_yest][$mon_yest][$day_yest];

		$tdatall = $tdatallM = $tdatcurrMon = array();
		for ($y = $temp_styr; $y <= $dyear; $y++) { //Collect data
			$tdat1[$y] = $GLOBALS['DAT' . $dataType][$x][$y];
			$tdat2[$y] = MDtoZ($tdat1[$y]);
			$tdat3[$y] = MDtoMsummary($tdat1[$y]);
			$tdatall = array_merge($tdatall, $tdat2[$y]);
			$tdatallM = array_merge($tdatallM, $tdat3[$y]);
			$tdatcurrMon = array_merge($tdatcurrMon, $tdat1[$y][$dmonth]);
		}
		$datallCnt = count($tdatall);

		$tdatMMa = array_merge($tdat1[$dyear] + $tdat1[$dyear - 1]);
		$tdatMMa[$dmonth - 1] = $tdat1[$dyear - 1][$dmonth];

		//Start variable assignment
		$tdat[$key][2]['m'] = $tdat3[$dyear][$dmonth];
		$tdat[$key][2]['y'] = mean($tdat2[$dyear]);

		//### TOTALS ###// //sun, wet, rain only
		if( ($x == 13 && $dataType == 'A') || ($x <= 1 && $dataType == 'M') ) {
			if($dataType == 'A') {
				$totType = 'rain';
				$daysGone = $dday;
			} else {
				$totType = ($x == 0) ? 'sun' : 'wet';
				$daysGone = $dday - 1;
			}
			$totals[$c]['today'] = $tdat1[$dyear][$dmonth][$dday];
			$totals[$c]['yest'] = $tdat2[$yr_yest][$dz_yest];
			$totals[$c]['m'] = array_sum($tdat1[$dyear][$dmonth]);
			$totals[$c]['y'] = array_sum($tdat2[$dyear]);
			$totals[$c]['a'] = array_sum($tdatall);

			$daysof[$c]['m'] = sum_cond($tdat1[$dyear][$dmonth], true, 0.1);
			$daysof[$c]['y'] = sum_cond($tdat2[$dyear], true, 0.1);
			$daysof[$c]['a'] = sum_cond($tdatall, true, 0.1);

			$curryrLTAtotal = 0;
			for ($m = 0; $m < $dmonth - 1; $m++) {
				$curryrLTAtotal += $GLOBALS[$totType.'av'][$m];
			}
			$annualLTA = array_sum($GLOBALS[$totType.'av']);
			$currMonAv = $GLOBALS[$totType.'av'][$dmonth-1] * $daysGone / $dt;

			$totalsAnoms[$c]['m'] = percent($totals[$c]['m'], $currMonAv);
			$totalsAnoms[$c]['y'] = percent($totals[$c]['y'], $currMonAv + $curryrLTAtotal);
			$totalsAnoms[$c]['a'] = percent($totals[$c]['a'] / $datallCnt, $annualLTA / 365);

			foreach($pgather as $pName) {
				$totals[$c][$pName] = 0;
				$daysof[$c][$pName] = 0;
				$LTAcum = 0;
				for($i = 1; $i <= $pName; $i++) {
					$totals[$c][$pName] += $tdatall[$datallCnt-$i];
					$daysof[$c][$pName] += (int) ($tdatall[$datallCnt-$i] > 0.1);
					if($pName <= 31) {
						$mon = mkdate($dmonth, $dday-$i+1);
						$LTAcum += $GLOBALS[$totType.'av'][(int)date('n', $mon)-1] / date('t', $mon);
					}
				}
				if($pName > 31) {
					$LTAcum = $annualLTA;
				}
				$totalsAnoms[$c][$pName] = percent($totals[$c][$pName], $LTAcum);
			}
		}

		if ($getExtra) { //anomaly stuff (temperature only)
			$tdatyestanom[$key] = $tdatyest[$key] - $lta[$x][$dz_yest];
			$tdatdayanom[$key] = $tdatday[$c] - $lta[$x][$dz];
			$tdatav_curr = $tdatav[$key][$dmonth - 1];

			$tdat_mtdanom = $tdat_ytdanom = 0;

			for ($d = 1; $d <= $dday; $d++) {
				$tdat_mtdanom += $lta[$x][date('z', mkday($dday - $d + 1))] / $dday;
			}
			$tdat[$key][2]['manom'] = $tdat[$key][2]['m'] - $tdat_mtdanom;
			for ($d = 0; $d <= $dz; $d++) {
				$tdat_ytdanom += $lta[$x][date('z', mkday($dday - $d + 1))] / $dz;
			}
			$tdat[$key][2]['yanom'] = $tdat[$key][2]['y'] - $tdat_ytdanom;
		}

		for ($y = $temp_styr; $y <= $dyear; $y++) {
			if (is_array($tdat1[$y][$dmonth])) {
				$tdatD[$y] = $tdat1[$y][$dmonth][$dday];
			}
			$tdata[$y] = $tdat3[$y][$dmonth];
			if ($y > 2008) {
				$tdatYa[$y] = mean($tdat2[$y]);
			}
		}

		for ($i = 0; $i < 2; $i++) { // inner min,max loop
			$hol = ($i == 0) ? PHP_INT_MAX : -1 * PHP_INT_MAX; //initialise extremes
			//recent period averages
			for ($p = 0; $p < count($pgather); $p++) { //period length loop
				$tdat[$key][$i][$pgather[$p]] = $hol;
				for ($d = 1; $d <= $pgather[$p]; $d++) {
					if (opmom($tdatall[$datallCnt - $d], $tdat[$key][$i][$pgather[$p]], $i)) {
						$tdat[$key][$i][$pgather[$p]] = $tdatall[$datallCnt - $d];
						$tempdate = mkday($dday - $d + 1);
					}
				}
				if ($pgather[$p] > 31) {
					$display_mon = true;
				} else {
					$display_mon = false;
				}
				$tdat[$key][$i][$pgather[$p] . 'date'] = today(null, $display_mon, true, null, $tempdate);
				if ($getExtra) {
					$tdat[$key][$i][$pgather[$p] . 'anom'] = $tdat[$key][$i][$pgather[$p]] - $lta[$x][date('z', $tempdate)];
				}

				//record period averages
				$tdatcum = 0;
				$buff = new circularBuffer($pgather[$p]);
				$tdatcumfix = $hol; //cumulative
				for ($k = 0; $k < $datallCnt; $k++) {
					if (!isBlank($tdatall[$k])) {
						$buff->add(floatval($tdatall[$k]));
					}
					if ($buff->isFull) {
						$tdatcum = array_sum($buff->items);
						if (opmom($tdatcum, $tdatcumfix, $i)) {
							$tdatcumfix = $tdatcum;
							$cumend = $k;
						}
					}
				}
				$tdat[$key][$i][$pgather[$p] . 'cum'] = $tdatcumfix / $pgather[$p];
				$tdat[$key][$i][$pgather[$p] . 'cumdate'] = 'Ending ' . today(true, true, true, null, daytotime($cumend));
				//$tdat[$key][$i][$pgather[$p].'cumanom'] = $tdat[$key][$i][$pgather[$p].'cum'] - 0; pending time to code (complex)
			}

			for ($y = $temp_styr; $y <= $dyear; $y++) {
				$tdatMon[$y] = mom($tdat1[$y][$dmonth], $i);
				$tdatMondate[$y] = array_search($tdatMon[$y], $tdat1[$y][$dmonth]);
				$tdatYr[$y] = mom($tdat2[$y], $i);
				$tdatYdatea[$y] = array_search($tdatYr[$y], $tdat2[$y]);
				$tdatMMon[$y] = mom($tdat3[$y], $i);
				$tdatMMondate[$y] = array_search($tdatMMon[$y], $tdat3[$y]);
			}
			//Day records
			$tdat[$key][$i]['m'] = ( abs($tdatMon[$dyear]) == PHP_INT_MAX ) ? '-' : $tdatMon[$dyear];
			$tdat[$key][$i]['y'] = $tdatYr[$dyear];
			$tdat[$key][$i]['a'] = mom($tdatYr, $i);
			$tempyr1 = array_search($tdat[$key][$i]['a'], $tdatYr);
			$tdat[$key][$i]['mr'] = mom($tdatMon, $i);
			$tempyr2 = array_search($tdat[$key][$i]['mr'], $tdatMon);
			$tdat[$key][$i]['dr'] = mom($tdatD, $i);

			$tdat[$key][$i]['mdate'] = today(null, null, $tdatMondate[$dyear]);
			$tdat[$key][$i]['ydate'] = today(null, true, true, null, mkz($tdatYdatea[$dyear]));
			$tdat[$key][$i]['adate'] = today(true, true, true, null, mkz($tdatYdatea[$tempyr1], $tempyr1));
			$tdat[$key][$i]['mrdate'] = today($tempyr2, null, $tdatMondate[$tempyr2]);
			$tdat[$key][$i]['drdate'] = today(array_search($tdat[$key][$i]['dr'], $tdatD));

			if ($getExtra) {
				$tdat[$key][$i]['manom'] = $tdat[$key][$i]['m'] - $lta[$x][date('z', mkday($tdatMondate[$dyear]))];
				$tdat[$key][$i]['yanom'] = $tdat[$key][$i]['y'] - $lta[$x][$tdatYdatea[$dyear]];
				$tdat[$key][$i]['aanom'] = $tdat[$key][$i]['a'] - $lta[$x][$tdatYdatea[$tempyr1]];
				$tdat[$key][$i]['mranom'] = $tdat[$key][$i]['mr'] - $lta[$x][date('z', mkdate($dmonth, $tdatMondate[$tempyr2], $tempyr2))];
				$tdat[$key][$i]['dranom'] = $tdat[$key][$i]['dr'] - $lta[$x][$dz];
			}
			//Month records
			$tdat[$key][$i]['My'] = $tdatMMon[$dyear];
			$tdat[$key][$i]['Mmr'] = mom($tdata, $i);
			$tdat[$key][$i]['Ma'] = mom($tdatMMon, $i);
			$tempyr = array_search($tdat[$key][$i]['Ma'], $tdatMMon);

			$tdat[$key][$i]['Mydate'] = today(null, $tdatMMondate[$dyear], null, true);
			$tdat[$key][$i]['Mmrdate'] = today(array_search($tdat[$key][$i]['Mmr'], $tdata), null, null, true);
			$tdat[$key][$i]['Madate'] = today($tempyr, $tdatMMondate[$tempyr], null, true);
			if ($getExtra) {
				$tdat[$key][$i]['Myanom'] = $tdat[$key][$i]['My'] - $tdatMondate[$dyear];
				$tdat[$key][$i]['Mmranom'] = $tdat[$key][$i]['Mmr'] - $tdatav[$key][$dmonth];
				$tdat[$key][$i]['Maanom'] = $tdat[$key][$i]['Ma'] - $tdatMondate[$tempyr];
			}
			//Year records
			$tdat[$key][$i]['Ya'] = mom($tdatYa, $i);
			$tdat[$key][$i]['Yadate'] = array_search($tdat[$key][$i]['Ya'], $tdatYa);
			if ($getExtra) {
				$tdat[$key][$i]['Yaanom'] = $tdat[$key][$i]['Ya'] - mean($tdatav[$key]);
			}
			//Past 12 months
			for ($m = 0; $m < 12; $m++) {
				$tdatMM[$key][$i][0][$m] = mom($tdatMMa[$m], $i);
				$tdatMM[$key][$i][1][$m] = array_search($tdatMM[$key][$i][0][$m], $tdatMMa[$m]);
				if ($getExtra) {
					$tdatMM[$key][$i][2][$m] = $tdatMM[$key][$i][0][$m] - $lta[$x][date('z', mkdate($m + 1, $tdatMM[$key][$i][1][$m]))];
				}
			}
			for ($j = 0; $j < 2; $j++) {
				$tdatMM[$key][$i]['extr'][$j] = mom($tdatMM[$key][$i][0], $j);
			}
		}

		//n-Day Averages
		for ($p = 0; $p < count($pgather); $p++) {
			for ($d = 1; $d <= $pgather[$p]; $d++) {
				$tdat[$key][2][$pgather[$p]] += $tdatall[$datallCnt - $d] / $pgather[$p];
				if ($getExtra) {
					$tdat[$key][2][$pgather[$p] . 'anom'] += ($tdatall[$datallCnt - $d] / $pgather[$p] - $lta[$x][date('z', mkday($dday - $d + 1))] / $pgather[$p]);
				}
			}
		}

		$tdat[$key][2]['a'] = mean($tdatall);
		$tdat[$key][2]['mr'] = mean($tdata);
		$tdat[$key][2]['dr'] = mean($tdatD);
		if ($getExtra) {
			$tdat[$key][2]['aanom'] = $tdat[$key][2]['a'] -
				($dz * $tdat_ytdanom + ($datallCnt - $dz) * mean($tdatav[$key])) / $datallCnt;
			$tdat[$key][2]['mranom'] = $tdat[$key][2]['mr'] - $tdatav_curr;
			$tdat[$key][2]['dranom'] = $tdat[$key][2]['dr'] - $lta[$x][$dz];
		}

		for ($m = 0; $m < 12; $m++) {
			$tdatMM[$key][2][0][$m] = mean($tdatMMa[$m]);
// 			$tdatMM[$key][2][1][$m] = '';
			$tdatMM[$key][2][2][$m] = $tdatMM[$key][2][0][$m] - $tdatav[$key][$m];
			for ($j = 0; $j < 2; $j++) {
				$tdatMM[$key][2]['extr'][$j] = mom($tdatMM[$key][2][0], $j);
			}
		}

		for ($s = 0; $s < 4; $s++) {
			for ($s2 = 0; $s2 < 3; $s2++) {
				$tdatSS[$key][$s] += $tdatMM[$key][2][0][$snums[$s][$s2]] / 3;
				if ($getExtra) {
					$tdatSSanom[$key][$s] += $tdatMM[$key][2][2][$snums[$s][$s2]] / 3;
				}
			}
		}

		//Extreme Daily Ranked
		$tdatGood = array_filter($tdatall, 'isNotBlank');
		$data_sort = $tdatGood;
		sort($data_sort, SORT_NUMERIC);
		for ($i = 1; $i <= $GLOBALS['rankNum']; $i++) {
			$highest[$i] = $data_sort[count($data_sort) - $i];
			$dayRecH = array_search($highest[$i], $tdatGood);
			$highest_day[$i] = today(true, true, true, null, daytotime($dayRecH));
			$tdatGood[$dayRecH] -= 0.01; //prevent duplicated dates
			$lowest[$i] = $data_sort[$i - 1];
			$dayRecL = array_search($lowest[$i], $tdatGood);
			$lowest_day[$i] = today(true, true, true, null, daytotime($dayRecL));
			$tdatGood[$dayRecL] -= 0.01; //prevent duplicated dates
		}
		//Extreme Monthly Ranked
		$tdatallMGood = array_filter($tdatallM, 'isNotBlank');
		$data_sortM = $tdatallMGood;
		sort($data_sortM, SORT_NUMERIC);
		if ($debug) {
			print_m($tdatallM);
			print_m($tdatallMGood);
			print_m($data_sortM);
		}
		for ($i = 1; $i <= $GLOBALS['rankNumM']; $i++) {
			$highestM[$i] = $data_sortM[count($data_sortM) - $i];
			$highMnum = array_search($highestM[$i], $tdatallMGood);
			$stampH = monthtotime($highMnum);
			$highest_dayM[$i] = today(date('Y', $stampH), date('n', $stampH), false, true);
			$lowestM[$i] = $data_sortM[$i - 1];
			$lowMnum = array_search($lowestM[$i], $tdatallMGood);
			$stampL = monthtotime($lowMnum);
			$lowest_dayM[$i] = today(date('Y', $stampL), date('n', $stampL), false, true);
		}
		//Extreme Daily Ranked for current month
		$tdatcurrMonGood = array_filter($tdatcurrMon, 'isNotblank');
		$data_sortCM = $tdatcurrMonGood;
		sort($data_sortCM, SORT_NUMERIC);
		for ($i = 1; $i <= $GLOBALS['rankNumCM']; $i++) {
			$highestCM[$i] = $data_sortCM[count($data_sortCM) - $i];
			$dayRecHCM = array_search($highestCM[$i], $tdatcurrMonGood);
			$highest_dayCM[$i] = today(true, null, true, null, daytotimeCM($dayRecHCM));
			$tdatcurrMonGood[$dayRecHCM] = -999; //prevent duplicated dates
			$lowestCM[$i] = $data_sortCM[$i - 1];
			$dayRecLCM = array_search($lowestCM[$i], $tdatcurrMonGood);
			$lowest_dayCM[$i] = today(true, null, true, null, daytotimeCM($dayRecLCM));
			$tdatcurrMonGood[$dayRecLCM] = -999; //prevent duplicated dates
		}
		$ranks[$c]['daily'] = array(array($lowest, $lowest_day), array($highest, $highest_day));
		$ranks[$c]['dailyCM'] = array(array($lowestCM, $lowest_dayCM), array($highestCM, $highest_dayCM));
		$ranks[$c]['monthly'] = array(array($lowestM, $lowest_dayM), array($highestM, $highest_dayM));

		unset($tdat1, $tdat2, $tdat3, $tdatall, $tdatMMa, $tdatGood, $tdatallM, $data_sort, $tdatcurrMon); //destroy variables
		$c++;
	}

	$datToday = array($tdatday, $tdatdaydate, $tdatdayanom);
	$datYest = array($tdatyest, $tdatyestdate, $tdatyestanom);

	return array($datToday, $datYest, $tdat, $tdatMM, $tdatSS, $tdatSSanom, $avLast24, //6
		$ranks, $totals, $daysof, $totalsAnoms, $datallCnt); //11
}

$detailData = getDetailedData(0);

$outputTp = '
$nighttimeMin = ' . var_export($nighttimeMin, true) . ';
$daytimeMax = ' . var_export($daytimeMax, true) . ';
$nighttimeMinT = ' . var_export($nighttimeMinT, true) . ';
$daytimeMaxT = ' . var_export($daytimeMaxT, true) . ';
$tdatToday = ' . var_export($detailData[0], true) . ';
$tdatYest = ' . var_export($detailData[1], true) . ';
$tdat = ' . var_export($detailData[2], true) . ';
$tdatMM = ' . var_export($detailData[3], true) . ';
$tdatSS = ' . var_export($detailData[4], true) . ';
$tdatSSanom = ' . var_export($detailData[5], true) . ';
$hrsfrostmidnight = ' . var_export($hrsfrostmidnight, true) . ';
$daysTminL0C = ' . var_export($daysTminL0C, true) . ';
$daysTminyearL0C = ' . var_export($daysTminyearL0C, true) . ';
$last24houravtemp = ' . var_export($detailData[6], true) . ';
$tranks = ' . var_export($detailData[7], true) . ';
$tcountall = ' . var_export($detailData[11], true) . ';
';
unset($detailData);
file_put_contents($root . "TemperatureTags.php", "<?php $viewable $outputTp ?>");


#######################  HUMIDITY  ###########################################################
$detailDataH = getDetailedData(3);
$detailDataD = getDetailedData(17);

$outputRh = '
$hdatToday = ' . var_export($detailDataH[0], true) . ';
$hdatYest = ' . var_export($detailDataH[1], true) . ';
$hdat = ' . var_export($detailDataH[2], true) . ';
$hdatMM = ' . var_export($detailDataH[3], true) . ';
$hdatSS = ' . var_export($detailDataH[4], true) . ';
$last24houravhum = ' . var_export($detailDataH[6], true) . ';
$hranks = ' . var_export($detailDataH[7], true) . ';
$hcountall = ' . var_export($detailDataH[11], true) . ';
';
unset($detailDataH);

$outputDp = '
$ddatToday = ' . var_export($detailDataD[0], true) . ';
$ddatYest = ' . var_export($detailDataD[1], true) . ';
$ddat = ' . var_export($detailDataD[2], true) . ';
$ddatMM = ' . var_export($detailDataD[3], true) . ';
$ddatSS = ' . var_export($detailDataD[4], true) . ';
$last24houravdew = ' . var_export($detailDataD[6], true) . ';
$dranks = ' . var_export($detailDataD[7], true) . ';
$dcountall = ' . var_export($detailDataD[11], true) . ';
';
unset($detailDataD);
file_put_contents($root . "HumidityTags.php", "<?php $viewable $outputRh $outputDp ?>");


#######################  PRESSURE  ######################################################
if(date('i') == '05' || isset($_GET['BLRdebugTags'])) {
	$detailDataP = getDetailedData(6);

	$outputPr = '
	$pdatToday = ' . var_export($detailDataP[0], true) . ';
	$pdatYest = ' . var_export($detailDataP[1], true) . ';
	$pdat = ' . var_export($detailDataP[2], true) . ';
	$pdatMM = ' . var_export($detailDataP[3], true) . ';
	$pdatSS = ' . var_export($detailDataP[4], true) . ';
	$last24houravbaro = ' . var_export($detailDataP[6], true) . ';
	$pranks = ' . var_export($detailDataP[7], true) . ';
	$pcountall = ' . var_export($detailDataP[11], true) . ';
	';
	unset($detailDataP);
	file_put_contents($root . "PressureTags.php", "<?php $viewable $outputPr ?>");

#######################  FEELS-LIKE  ######################################################
	$detailDataF = getDetailedData(29);

	$outputFe = '
	$fdatToday = ' . var_export($detailDataF[0], true) . ';
	$fdatYest = ' . var_export($detailDataF[1], true) . ';
	$fdat = ' . var_export($detailDataF[2], true) . ';
	$fdatMM = ' . var_export($detailDataF[3], true) . ';
	$fdatSS = ' . var_export($detailDataF[4], true) . ';
	$last24houravfeel = ' . var_export($detailDataF[6], true) . ';
	$franks = ' . var_export($detailDataF[7], true) . ';
	$fcountall = ' . var_export($detailDataF[11], true) . ';
	';
	unset($detailDataF);
	file_put_contents($root . "FeelTags.php", "<?php $viewable $outputFe ?>");
}

#######################  Wind  ######################################################
$detailDataW = getDetailedData(9, 'A', isset($_GET['printDebugFull']));

$outputWd = '
$wdatToday = ' . var_export($detailDataW[0], true) . ';
$wdatYest = ' . var_export($detailDataW[1], true) . ';
$wdat = ' . var_export($detailDataW[2], true) . ';
$wdatMM = ' . var_export($detailDataW[3], true) . ';
$wdatSS = ' . var_export($detailDataW[4], true) . ';
$wdatSSanom = ' . var_export($detailDataW[5], true) . ';
$last24houravwind = ' . var_export($detailDataW[6], true) . ';
$wranks = ' . var_export($detailDataW[7], true) . ';
$wcountall = ' . var_export($detailDataW[11], true) . ';
';
unset($detailDataW);
file_put_contents($root . "WindTags.php", "<?php $viewable $outputWd ?>");


#######################  Rain2  ######################################################
$detailDataR = getDetailedData(13);

$outputRn2 = '
$rdatToday = ' . var_export($detailDataR[0], true) . ';
$rdatYest = ' . var_export($detailDataR[1], true) . ';
$rdat = ' . var_export($detailDataR[2], true) . ';
$rdatMM = ' . var_export($detailDataR[3], true) . ';
$rdatSS = ' . var_export($detailDataR[4], true) . ';
$rranks = ' . var_export($detailDataR[7], true) . ';
$rtotals = ' . var_export($detailDataR[8], true) . ';
$rdaysof = ' . var_export($detailDataR[9], true) . ';
$rtotAnoms = ' . var_export($detailDataR[10], true) . ';
$rcountall = ' . var_export($detailDataR[11], true) . ';
';
unset($detailDataR);
file_put_contents($root . "Rain2Tags.php", "<?php $viewable $outputRn2 ?>");

#######################  Sunshine  ######################################################
if($argc > 1 || isset($_GET['BLRdebugTags'])) {
	$detailDataS = getDetailedData(0, 'M');

//	$sdat = ' . var_export($detailDataS[2], true) . ';
//	$sdatMM = ' . var_export($detailDataS[3], true) . ';
//	$sdatSS = ' . var_export($detailDataS[4], true) . ';
//	$sranks = ' . var_export($detailDataS[7], true) . ';
	$outputSn = '
	$stotals = ' . var_export($detailDataS[8], true) . ';
	$sdaysof = ' . var_export($detailDataS[9], true) . ';
	$stotAnoms = ' . var_export($detailDataS[10], true) . ';
	$scountall = ' . var_export($detailDataS[11], true) . ';
	';
	unset($detailDataS);
	file_put_contents($root . "SunTags.php", "<?php $viewable $outputSn ?>");

}

//Debugging purposes
if (isset($_GET['BLRdebugTags'])) {
	$procTime = microtime(true) - $t_start;
	echo "Executed in $procTime seconds";
}

echo "END: ". date('r'). "\n";
?>