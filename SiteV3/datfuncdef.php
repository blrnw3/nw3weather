<?php
//Main daily data variables
$types_original = array('tmin','tmax','tmean','hmin','hmax','hmean','pmin','pmax','pmean','wmean','wmax','gust','wdir','rain','hrmax','10max','ratemax', //16
						'dmin','dmax','dmean','nightmin','daymax','tc10max','tchrmax','hchrmax','tc10min','tchrmin','hchrmin','w10max','fmin', 'fmax', 'fmean', 'afhrs'); //32
$types = array_flip($types_original);
$data_colours = array('#FFD750','orange','tan3','chartreuse','darkolivegreen','chartreuse3','darkorchid4','orchid1','purple','red','firebrick1','firebrick2','firebrick3',
					'royalblue','royalblue1','royalblue2','royalblue3','darkseagreen','darkslategray','darkseagreen4','peachpuff','darkgoldenrod1',
					'tan1','tan2','darkolivegreen4','darkgoldenrod2','darkgoldenrod3','darkolivegreen1','lightpink2', 'azure3', 'bisque2', 'beige', 'cadetblue4');
$data_description = array('Minimum Temperature','Maximum Temperature','Mean Temperature', //2
					'Minimum Humidity','Maximum Humidity','Mean Humidity', //5
					'Minimum Pressure','Maximum Pressure','Mean Pressure', //8
					'Mean Wind Speed','Maximum Wind Speed','Maximum Gust','Mean Wind Direction', //12
					'Rainfall','Maximum Hourly Rain','Maximum 10-min Rain','Maximum Rain Rate', //16
					'Minimum Dew Point','Maximum Dew Point','Mean Dew Point', //19
					'Night Minimum (21-09)','Day Maximum (09-21)', //21
					'Max 10m Temp Rise','Max 1hr Temp Rise','Max 1hr Hum Rise', //24
					'Max 10m Temp Fall','Max 1hr Temp Fall','Max 1hr Hum Fall', //27
					'Max 10m Wind Speed', //28
					'Minimum Feels-like', 'Maximum Feels-like', 'Mean Feels-like', //31
					'Air-frost Hrs'); //32
$data_unit = array(0,0,0, 4,4,4, 3,3,3, 2,2,2,5, 1,1,1,6, 0,0,0, 0,0, 0,0,4,0,0,4, 2, 0,0,0, 7); //Display correct unit (C,mm,mph...)
$data_num = array(4,4,4, 0,0,0, 6,6,6, 3,3,3,3, 2,2,2,2, 0,0,0, 4,4, 4,4,0,4,4,0, 3, 4,4,4, 4); //Apply correct CSS
$typeconv = array(1,1,1, 5,5,5, 3,3,3, 4,4,4,4.5, 2,2,2,2.1, 1,1,1, 1,1, 1.1,1.1,5,1.1,1.1,5, 4, 1,1,1, 9); //For use in conv function
$wxtable_colr = array(0,0,0, 1,1,1, 2,2,2, 3,3,3,4, 5,5,5,6, 0,0,0, 0,0, 7,7,8,7,7,8, 3, 0,0,0, 10); //For use in data-type tables to get correct ValueColour
$round_size = array(5,5,5, 10,10,10, 10,10,10, 2,5,10,20, 10,1,0.5,10, 5,5,5, 5,5, 0.5,1,5,0.5,1,5, 2, 5,5,5, 5); //For intelligent auto-scale of charts
$round_sizei = array(10,10,10, 10,10,10, 0.5,0.5,0.5, 2,5,10,20, 0.5,0.1,0.05,0.5, 10,10,10, 10,10, 1,2,5,1,2,5, 2, 10,10,10, 5); //Imperial version of above
$sumq = array(false,false,false, false,false,false, false,false,false, false,false,false,false, true,false,false,false, //Is quantity summable (e.g. rain total)
			false,false,false, false,false, false,false,false,false,false,false, false, false,false,false, true);
$anomq = array(true,true,true, false,false,false, false,false,false, true,false,false,false, true,false,false,false, //Does anomaly exist (e.g. rain)
		 	false,false,false, false,false, false,false,false,false,false,false, false, false,false,false, false);

//Derived daily quantities
$types_derived = array('trange','hrange','prange','ratemean');
$typesx = array_flip($types_derived);
$data_coloursx = array('green','darkred','black','cyan');
$data_descriptionx = array('Temperature Range', 'Humidity Range', 'Pressure Range', 'Mean Rain Rate');
$data_unitx = array(0,4,3,6);
$data_numx = array(4,0,6,2);
$typeconvx = array(1.1,5,3,2.1);
$wxtable_colrx = array(0,8,9,5);
$round_sizex = array(5,10,5,2);
$round_sizeix = array(10,10,1,0.2);
$sumqx = array(false,false,false,false);
$anomqx = array(true,false,false,false);

//Manual-input daily variables
$types_m_original = array('sunhr','wethr', 'cloud','snow','lysnw', 'hail','thunder','fog', 'comms','extra','issues','away','spare');
$types_m = array_flip($types_m_original);
$data_coloursm = array('yellow','aqua', 'black','black','black','black','black','black');
$data_m_description = array('Sun Hours', 'Wet Hours', 'Cloud Cover', acronym('Possible events: Air frost, Dense fog, Snowfall, Lying Snow, Hail, Thunder(storm), Max sun.','Events',true),
						'Comments', 'Extra Comments', 'Issues',	acronym('This tells whether I was absent from North London on the day. When absent I am unable to make detailed weather observations of any events such as fog, rain, snow and thunder,	instead using reports from fellow observers at nearby sites, webcam footage, or the observations of friends/family.',
						'Observer Absent?', true));
$data_descriptionm = array('Sun Hours', 'Wet Hours', 'Cloud Cover', 'Falling Snow', 'Lying Snow', 'Hail', 'Thunder', 'Dense Fog');
$data_unitm = array(7,7, 10,11,11,10,10,10);
$data_m_num = array(8,9, 10,-6,-6,-6,-6,-6);
$typeconvm = array(9,9, false,6,6,0.1,0.2,false);
$wxtable_colrm = array(10,10, 10,10,10,10,10,10);
$round_sizem = $round_sizeim = array(5,5, 1,1,1, 1,1,1);
$sumqm = array(true,true, true,true,true, true,true,true);
$anomqm = array(true,true, false,false,false,false,false,false);

//All daily
$types_alltogether = array_merge($types_original,$types_derived,$types_m_original);
$types_all = array_flip($types_alltogether);
$colours_all = array_merge($data_colours, $data_coloursx, $data_coloursm);
$descriptions_all = array_merge($data_description, $data_descriptionx, $data_descriptionm);
$units_all = array_merge($data_unit, $data_unitx, $data_unitm);
$nums_all = array_merge($data_num, $data_numx, $data_m_num);
$typeconvs_all = array_merge($typeconv, $typeconvx, $typeconvm);
$wxtablecols_all = array_merge($wxtable_colr, $wxtable_colrx, $wxtable_colrm);
$roundsizes_all = array_merge($round_size, $round_sizex, $round_sizem);
$roundsizeis_all = array_merge($round_sizei, $round_sizeix, $round_sizeim);
$sumq_all = array_merge($sumq, $sumqx, $sumqm);
$anomq_all = array_merge($anomq, $anomqx, $anomqm);

$mappingsToDailyDataKey = array('t' => 'temp', 'h' => 'humi', 'p' => 'pres', 'd' => 'dewp', 'w'=> 'wind', 'r' => 'rain', 'f' => 'feel');

//groupings for use in colgroup drop-down
$categories = array(
	'Temperature' => array('tmin','tmax','tmean'),
	'Humidity' => array('hmin','hmax','hmean'),
	'Pressure' => array('pmin','pmax','pmean'),
	'Wind' => array('wmean','wmax','gust','wdir'),
	'Rainfall' => array('rain','hrmax','10max','ratemax'),
	'Dew Point' => array('dmin','dmax','dmean'),
	'Change' => array('tc10max','tchrmax','hchrmax','tc10min','tchrmin','hchrmin'),
	'Range' => array('trange','hrange','prange'),
	'Observations' => array('sunhr','wethr','ratemean','cloud','snow','lysnw','hail','thunder','fog'),
	'Misc.' => array('nightmin','daymax','w10max','afhrs'),
	'Feels-like' => array('fmin','fmax','fmean')
);

//Monthly quantities
$types_monthly_original = array( 'af', 'windy', 'rainy', 'rainy+', 'rainmean');
$types_monthly = array_flip($types_monthly_original);
$colours_monthly = array('blue', 'red', 'blue', 'blue', 'blue');
$descrip_monthly = array( 'Air Frosts', acronym('10-min max > 15mph', 'Windy Days', true), 'Rain Days', 'Rain Days > 1mm', 'Rain-day mean');
$units_monthly = array(9,9,9,9,1);
$nums_monthly = array(4,3,2,2,2);
$typeconvs_monthly = array(8,8,8,8,2);
$wxtablecols_monthly = array(10,10,10,10,5);

$sumormean = array('mean', 'total');

$ccd = array('c' => 'Sunny', 'f' => 'Mostly Sunny', 'p' => 'Partly Cloudy', 'b' => 'Mostly Cloudy', 'o' => 'Overcast', '-' => 'transitioned to', ';' => 'with periods of',
			'/' => 'or', 'h' => 'Hazy', 'u' => 'unknown');

/**
 * Converts an array indexed as [month][day] to one indexed by [dayOfYear]
 * @param mixed array[][]
 * @return mixed array[]
 */
function MDtoZ($arr) {
	$z = array();
	$cnt = count($arr);
	for($mon = 1; $mon <= $cnt; $mon++) {
		if(is_array($arr[$mon])) {
			$z = array_merge($z, $arr[$mon]);
		}
	}
	return $z;
}

/**
 * Converts an array indexed as [month][day] to one indexed by [month], containing means/sums over the number of days
 * @param mixed $arr 2D array
 * @param boolean $sum [=false] true if sum rather than mean
 * @param int $type [=2] 0: min, 1:max, 2:mean, 3:count
 * @return mixed 1D array of means/sums
 */
function MDtoMsummary($arr, $sum = false, $type = 2) {
	//var_dump($type); echo  ' ';
	//WARNING: DO NOT MODIFY!!!!! Direct casting to int results in very weird bug (3 -> 2)
	$type = round($type);
	//var_dump($type); echo  ' ';
	$type = (int)$type;
	//var_dump($type); echo  ' ';
	for($mon = 1; $mon <= count($arr); $mon++) {
		$div = (!$sum) ? count($arr[$mon]) : 1;

		$summary[$mon] = ($type === 2) ? mean($arr[$mon], $div) :
			( ($type === 1) ? mymax($arr[$mon]) :
				( ($type === 0) ? mymin($arr[$mon]) : sum_cond($arr[$mon], true, 0) ) );
	}
	return $summary;
}

function minMaxMeanSumCount($arr, $type, $morx) {
	$min = PHP_INT_MAX;
	$max = PHP_INT_MIN;
	$sum = 0;
	$count = 0;

	$validCnt = 0;
	foreach($arr as $val) {
		if(!isBlank($val)) {
			$val = floatval($val);
			$validCnt++;

			if($val < $min) { $min = $val; }
			if($val > $max) { $max = $val; }
			$sum += $val;
			$count += (int)($val > 0.1);
		}
	}
	if($validCnt === 0)
		return null;

	$summable = ($type === 13 || $morx); //rain, sun, wet

	$sum /= $summable ? 1 : $validCnt; //only one of mean or sum is required

	return $summable ?
		array($min, $max, $sum, $count) :
		array($min, $max, $sum);
}
/**
 * Converts DATA, DATX, or DATM to monthly equivalent
 * @param mixed $arr one of above
 * @return mixed monthly array indexed in same way but without day, and with:
 * 0:min, 1:max, 2:mean/sum, [3:count (if countable)]
 */
function DATtoMDAT($arr) {
	$mdat = array();
	$morx = count($arr) > 5 && count($arr) < 20; //datm
	foreach ($arr as $type => $arr0) {
		foreach ($arr0 as $year => $arr1) {
			foreach ($arr1 as $month => $arr2) {
				$mdat[$type][$year][$month] = minMaxMeanSumCount($arr2, $type, $morx);
			}
		}
	}
	return $mdat;
}

/**
 * Gets data from the right global ALL array (DATA, DATAM, DATAX).
 * @param mixed $varNum
 * @return mixed
 */
function varNumToDatArray($varNum) {
	global $types, $types_derived, $types_m, $types_all;

	if($varNum < count($types)) {
		return $GLOBALS['DATA'][$varNum];
	} elseif($varNum < count($types)+count($types_derived)) {
		return $GLOBALS['DATX'][$varNum - count($types)];
	} else {
		return $GLOBALS['DATM'][$varNum - count($types_all) + count($types_m)];
	}
}

/**
 * Gets useful data from the right global ALL array (DATA, DATAM, DATAX).
 * @param string $variable
 * @param mixed $year Pass true to get all years or specify an integer single year. Defaults to current year.
 * @param float $accumType [=1] re-order data: 0 does nothing, 1 applies MDtoZ, 2.x applies MDtoSummary [.x for min/max/mean/count]
 * @return mixed array required
 */
function newData($variable, $year = null, $accumType = 1) {
	global $dyear, $types_all, $sumq_all;

	$varNum = $types_all[$variable];
	//echo 'variable is ' . $variable;
//	echo '<br />varnum is ' . $varNum;

	//$quantity = ($quantity === null) ? 1 : $quantity;
	$year = ($year === null) ? $dyear : $year;
	$mmm = ( ($accumType-2)*10 );

	$data = varNumToDatArray($varNum);

	if($year === true) { //all years desired
		$all = array();
		for($y = 2009; $y <= $dyear; $y++) { //Rain tags
			$all = array_merge($all, ($accumType === 1) ? MDtoZ($data[$y]) :
				MDtoMsummary($data[$y], $sumq_all[$varNum], $mmm));
		}
		return $all;
	}

	if($accumType === 0) {
		return $data[$year];
	} else {
		//die('mmm = '. $mmm);
		return ($accumType === 1) ? MDtoZ($data[$y]) :
			MDtoMsummary($data[$year], $sumq_all[$varNum], $mmm);
	}
}

function typeToConvType($type) {
	return $GLOBALS['typeconvs_all'][$GLOBALS['types_all'][$type]];
}

function graphDaily($type, $len = 31) {
	$data = newData($type, true);
	$format = ($len > 50) ? 'd-M' : 'd';
	for($d = 1; $d <= $len; $d++) {
		$graph[$len-$d] = floatval( conv($data[count($data)-$d], typeToConvType($type), 0, 0, 1, 0, 0, true) );
		$labels[$len-$d] = date( $format, mkday(DDAY - $d + 1) );
	}
	$interval = round($len / 21);
	return array($graph, $labels, $interval);
}
/**
 * Daily chart data for a specified month
 */
function graphMonth($type, $month, $year) {
	$datay = newData($type, $year, 0);
	$data = $datay[$month]; unset($datay);
	$len = count($data);
	for($d = 0; $d < $len; $d++) {
		$graph[$d] = conv($data[$d+1], typeToConvType($type), 0, 0, 1, 0, 0, true);
		$labels[$d] = $d+1;
	}
	return array($graph, $labels);
}
/**
 * Monthly chart data for a specified period up to present
 * @param enum $mmm [=2.2] mean/max/min/count
 */
function graphMonthly($type, $len = 12, $mmm = 2.2) {
	$data = newData($type, true, $mmm);
	$format = ($len > 18) ? 'M-y' : 'M';
	$cnt = count($data);
	for($mon = $cnt - $len; $mon < $cnt; $mon++) {
		$base = $mon - ($cnt - $len); //re-index from 0
		$graph[$base] = conv($data[$mon], typeToConvType($type), 0,0,1,0,0, true);
		$labels[$base] = date($format, monthtotime($mon));
	}
	$interval = round($len / 12);
	return array($graph, $labels, $interval);
}
function graphYear($type, $year, $mmm = 2.2) {
	$test = array_merge( newData($type, $year, $mmm) );
	for ($mon = 0; $mon < count($test); $mon++) {
		$new[$mon] = conv($test[$mon], typeToConvType($type), 0, 0, 1,0,0, true);
	}
	return array($new, $GLOBALS['months']);
}


$daytypes = array('temp' => 6, 'hum' => 7, 'dew' => 9, 'rain' => 10, 'baro' => 8, 'wdir' => 11, 'gust' => 4, 'wind' => 3);
$daycols = array('temp' => 'orange', 'hum' => 'darkgreen', 'dew' => 'chartreuse', 'rain' => 'blue', 'baro' => 'darkred', 'wdir' => 'red', 'gust' => 'palevioletred1', 'wind' => 'darkblue');
$daynames = array('temp' => 'Temperature / '. $unitT, 'hum' => 'Humidity / %', 'dew' => 'Dew Point / '. $unitT, 'rain' => 'Rainfall / '.$unitR, 'baro' => 'Pressure / '.$unitP,
	'wdir' => 'Wind Direction / deg', 'gust' => 'Gust / '.$unitW, 'wind' => 'Wind Speed /'.$unitW);
$nums = array(1, 2, 3, 4, 5, 6, 8, 10, 12, 15, 20, 24, 30, 32, 45, 60, 72, 90); //excl: 9, 16, 18, 36, 40, 48, 82, 96, 120, 144, 180, 240, 360, 160, 288, 720, 1440
$nums_poss = array(1,2,3,4,5,6,8,9,10,12,15,16,18,20,24,30,32);

/**
 * Good implementation of calculating the mean wind direction from an array of wdirs and speeds
 * @param array $wdir raw array
 * @param array $speed so calm times can be ignored
 * @return int
 */
function wdirMean($wdir, $speed) {
	$bitifier = 36; //constant - the quantisation level to convert 360 degrees into a bitier signal
	$calmThreshold = 1; //constant - values when the wind speed was below this are ignored

	$end = count($wdir);

	$freqs = array();
	for($i = 0; $i <= 360/$bitifier; $i++) {
		$freqs[$i] = 0;
	}

	//get frequencies for each bitified angle
	for($i = 0; $i < $end; $i++) {
		if($speed[$i] > $calmThreshold) { // pivot not to be affected by calm times
			$freqs[round($wdir[$i] / $bitifier)]++;
		}
	}

	//choose a pivot
	$minfreq = min($freqs);
	$pivot = array_search($minfreq, $freqs);
	$pivot *= $bitifier;

	//calculate the mean
	$sum = 0;
	$count = 0;
	for($i = 0; $i < $end; $i++) {
		//values from calm times or near pivot are anomalous => ignore
		if(abs($wdir[$i] - $pivot) >= $bitifier && $speed[$i] > $calmThreshold) {
			$sum += $wdir[$i];
			$count++;
			if($wdir[$i] > $pivot) {
				$sum -= 360;
			}
		}
	}
	//clean-up
	$mean = ($count === 0) ? 0 : roundToDp($sum / $count, 0);
	if($mean < 0) {
		$mean += 360;
	}

	return $mean;
}

/**
 * Processes a daily logfile into useful data - max, mins, means etc.
 * @param string $procfil [=today] Ymd format for the day to process
 * @return array of data for the chosen daily logfile
 */
function dailyData($procfil = 'today') {
	for($t = 6; $t < 10; $t++) {
		$datt[$t]['max'] = $datt[$t]['max2'] = -99;
		$datt[$t]['min'] = $datt[$t]['min2'] = 1100;
	}
	$round_pt = array(0,0,0,1,0,0, 2,1,1,2);
	$trendKeys = array('wind', 'gust', 'wdir', 'temp', 'humi', 'pres', 'dewp');
	$daytypes = array_flip(array('temp' => 6, 'humi' => 7, 'dewp' => 9, 'rain' => 10, 'pres' => 8, 'wdir' => 5, 'gust' => 4, 'wind' => 3));
	$rntipmm = 0.18; //constant

	$daymax1 = $daymax2 = -99;
	$nightmin1 = $nightmin2 = $nightmin1T = $nightmin2T = 99;
	$frostMins = 0;
	$lineLength = 11;
	$trends = $rnCums = $rncumArr = array();
	$rncum = $w10 = 0;
	$mins = $maxs = $means = $timesMin = $timesMax = array();

	$filcust = file(ROOT. "logfiles/daily/" . $procfil . 'log.txt');
	$end = count($filcust); //should be 1440

	for($i = 0; $i < $end; $i++) {
		$custl = explode(',', $filcust[$i]);
		$custmin[$i] = intval($custl[1]); $custhr[$i] = intval($custl[0]);

		for($t = 0; $t < $lineLength; $t++) {
			$dat[$t][$i] = floatval($custl[$t]);
			if($t > 5 && $t < 10) {
				$custl[$t] = floatval($custl[$t]);
				if($custl[$t] >= $datt[$t]['max']) { $datt[$t]['max'] = $custl[$t]; $datt[$t]['timeLmax'] = mktime($custhr[$i],$custmin[$i]); }
				if($custl[$t] <= $datt[$t]['min']) { $datt[$t]['min'] = $custl[$t]; $datt[$t]['timeLmin'] = mktime($custhr[$i],$custmin[$i]); }
				if($custl[$t] > $datt[$t]['max2']) { $datt[$t]['max2'] = $custl[$t]; $datt[$t]['timeHmax'] = mktime($custhr[$i],$custmin[$i]); }
				if($custl[$t] < $datt[$t]['min2']) { $datt[$t]['min2'] = $custl[$t]; $datt[$t]['timeHmin'] = mktime($custhr[$i],$custmin[$i]); }
			}
		}

		$feels[$i] = feelsLike($custl[6], $custl[4], $custl[9]);

		//cumulative rain
		if($i > 0) {
			$rnChange = $dat[10][$i] - $dat[10][$i-1];
			// account for potential glitches where rain decreases
			$rncum += ($rnChange > 0) ? $rnChange : 0;
		}
		$rncumArr[$i] = $rncum;

		//Frost hours
		if($custl[6] < 0) {
			$frostMins++;
		}
		//Day max
		if($custhr[$i] >= 9 && $custhr[$i] < 21) {
			if($custl[6] >= $daymax1) { $daymax1 = $custl[6]; $daymaxt1 = mktime($custhr[$i],$custmin[$i]); }
			if($custl[6] > $daymax2) { $daymax2 = $custl[6]; $daymaxt2 = mktime($custhr[$i],$custmin[$i]); }
		}
		//Night Min
		if($custhr[$i] < 9) {
			if($custl[6] <= $nightmin1) { $nightmin1 = $custl[6]; $nightmint1 = mktime($custhr[$i],$custmin[$i]); }
			if($custl[6] < $nightmin2) { $nightmin2 = $custl[6]; $nightmint2 = mktime($custhr[$i],$custmin[$i]); }
		}
		//Night Min Tomorrow
		if($custhr[$i] >= 21) {
			if($custl[6] <= $nightmin1T) { $nightmin1T = $custl[6]; $nightmint1T = mktime($custhr[$i],$custmin[$i]); }
			if($custl[6] < $nightmin2T) { $nightmin2T = $custl[6]; $nightmint2T = mktime($custhr[$i],$custmin[$i]); }
		}
		//Max rain rate
		for($r=1; $r<60; $r++) {
			if($i > $r) {
				$rnr[$i] = $dat[10][$i] - $dat[10][$i-$r];
				if($rnr[$i] > 0.5) {
					if($r === 1) { $rr[$i] = 60*$rnr[$i]; }
					else { $rr[$i] = round(60/($r-1)*$rntipmm, 1); }
					break;
				}
			}
		}
		$w10 += $dat[3][$i];
		//10-min trend extremes
		if($i >= 10) {
			$w10 -= $dat[3][$i-10];
			$wind10[$i] = $w10 / 10;
			$rn10[$i] = $dat[10][$i] - $dat[10][$i-10];
			$t10[$i] = $dat[6][$i] - $dat[6][$i-10];
		}
//		$w60 += $dat[3][$i]/60;
//		$wind60[$i] = $w60;
		//hour trend extremes
		if($i > 60) {
			$tchangehr[$i] = $dat[6][$i] - $dat[6][$i-60];
			$hchangehr[$i] = $dat[7][$i] - $dat[7][$i-60];
//			$w60 -= $dat[3][$i-60]/60;
			$rn60[$i] = $dat[10][$i] - $dat[10][$i-60];
		}
	}

	//Trends
	if($end > 400) {
		$rnCums['10m'] = $rncumArr[$end-11];
		for($i = 1; $i <= 361; $i += 60) { //last 1-6hrs rain
			$rnCums[] = $rncumArr[$end-$i];
		}

		$trendLen = count($trendKeys);
		for($i = 1; $i <= 121; $i += 5) {
			for($j = 0; $j < $trendLen; $j++) {
				$trends[$i-1][$trendKeys[$j]] = $dat[$j+3][$end-$i];
			}
			$trends[$i-1]['rain'] = $rncumArr[$end-$i];
		}
	}

	if($daymax1 == -99) {
		$daymax1 = $timesMax['day'] = '-';
	} else {
		$timesMax['day'] = date( 'H:i', ($daymaxt1 + $daymaxt2) / 2 );
	}
	$mins['night'] = $nightmin1;
	$mins['nightTomoz'] = $nightmin1T;
	$maxs['day'] = $daymax1;
	$timesMin['night'] = date( 'H:i', ($nightmint1 + $nightmint2) / 2 );
	$timesMin['nightTomoz'] = date( 'H:i', ($nightmint1T + $nightmint2T) / 2 );

	$maxs['wind'] = max($dat[3]); $timesMax['wind'] = timeFromMM($maxs['wind'], $dat[3], $custhr, $custmin);
	$maxs['gust'] = max($dat[4]); $timesMax['gust'] = timeFromMM($maxs['gust'], $dat[4], $custhr, $custmin);

	$minFeel = min($feels); $timesMin['feel'] = timeFromMM($minFeel, $feels, $custhr, $custmin);
	$maxFeel = max($feels); $timesMax['feel'] = timeFromMM($maxFeel, $feels, $custhr, $custmin);
	$mins['feel'] = round($minFeel, 1);
	$maxs['feel'] = round($maxFeel, 1);

	if(is_array($rn60)) {
		$maxs['rnhr'] = max($rn60); if($maxs['rnhr'] > 0.2) { $timesMax['rnhr'] = timeFromMM($maxs['rnhr'], $rn60, $custhr, $custmin); }
		$maxs['tchangehr'] = max($tchangehr); $timesMax['tchangehr'] = timeFromMM($maxs['tchangehr'], $tchangehr, $custhr, $custmin);
		$maxs['hchangehr'] = max($hchangehr); $timesMax['hchangehr'] = timeFromMM($maxs['hchangehr'], $hchangehr, $custhr, $custmin);
		$tchhr = min($tchangehr); $timesMin['tchangehr'] = timeFromMM($tchhr, $tchangehr, $custhr, $custmin);
		$hchhr = min($hchangehr); $timesMin['hchangehr'] = timeFromMM($hchhr, $hchangehr, $custhr, $custmin);
		$mins['tchangehr'] = -1 * $tchhr;
		$mins['hchangehr'] = -1 * $hchhr;

	}
	if(is_array($t10)) {
		$w10max = max($wind10); $timesMax['w10m'] = timeFromMM($w10max, $wind10, $custhr, $custmin);
		$maxs['w10m'] = round($w10max, 1);
		$maxs['rn10'] = max($rn10); if($maxs['rn10'] > 0.2) { $timesMax['rn10'] = timeFromMM($maxs['rn10'], $rn10, $custhr, $custmin); }
		$t10min = min($t10); $timesMin['tchange10'] = timeFromMM($t10min, $t10, $custhr, $custmin);
		$mins['tchange10'] = -1 * $t10min;
		$maxs['tchange10'] = max($t10); $timesMax['tchange10'] = timeFromMM($maxs['tchange10'], $t10, $custhr, $custmin);
	}
	if(is_array($rr)) {
		$maxs['rate'] = max($rr);
		$timesMax['rate'] = timeFromMM($maxs['rate'], $rr, $custhr, $custmin);
		$maxs['rate'] = $maxs['rate'];
	}
	for($t = 6; $t < 10; $t++) {
		$timesMin[$daytypes[$t]] = date('H:i',($datt[$t]['timeHmin']+$datt[$t]['timeLmin'])/2);
		$timesMax[$daytypes[$t]] = date('H:i',($datt[$t]['timeHmax']+$datt[$t]['timeLmax'])/2);
		$mins[$daytypes[$t]] = $datt[$t]['min'];
		$maxs[$daytypes[$t]] = $datt[$t]['max'];
		$means[$daytypes[$t]] = round( mean($dat[$t]), $round_pt[$t] );

		if($end > 61) {
			$hrChanges[$daytypes[$t]] = $dat[$t][$end-1] - $dat[$t][$end-61];
			$hr24Changes[$daytypes[$t]] = $dat[$t][$end-1] - $dat[$t][1];
		}
	}

	$hrChanges['wind'] = $dat[3][$end-1] - $dat[3][$end-61];
	$hr24Changes['wind'] = $dat[3][$end-1] - $dat[3][1];

	$means['wind'] = round(mean($dat[3]), 1);
	$means['w10m'] = round(mean($wind10), 1);
	$means['wdir'] = wdirMean($dat[5], $dat[3]);
	$means['feel'] = round(mean($feels), 1);
	$means['rain'] = $rncum;
	if($means['rain'] < 0.2) {
		$maxs['rnhr'] = $maxs['rn10'] = null;
	}
	$rnCums[0] = $rncum;

	//rain duration
	if($rncum > 0 && $rnCums[0] - $rnCums[1] != 0) {
		$duration = 0;
		$lastTip = 1;
		for($i = 0; $i < $end; $i++) {
			if($rncumArr[$end-$i-1] == $rncumArr[$end-$i-2]) {
				$lastTip++;
			} else {
				$duration += $lastTip;
				$lastTip = 1;
			}
			if($lastTip >= 60) {
				break;
			}
		}
	}

	//wet hours rough estimate
	$wetmins = 0;
	if($rncum > 0) {
		$notRained = 0;
		$raining = false;
		for($i = 1; $i < $end-1; $i++) {
			$notRained++;
			if($rncumArr[$i] != $rncumArr[$i+1]) {
				$notRained = 0;
				$raining = true;
			}
			if($raining) {
				$wetmins++;
			}
			if($notRained > 30) {
				$raining = false;
			}
		}
	}
	$wethrs = ceil($wetmins / 60);

	//current rain rate guess (based on last rain tip - so inaccurate when tipped after long break -> revert to max rate
	if($rnCums[0] - $rnCums[1] != 0) {
		$last = 60;
		for($i = 1; $i < 61; $i++) {
			if($rncumArr[$end-$i-1] != $rncum) {
				$last = $i;
				break;
			}
		}
		$tipQuantity = ($last === 1) ? round(($rncum - $rncumArr[$end-2])/$rntipmm) : 1;
		$currRateGuess = round(60/$last*$rntipmm*$tipQuantity, 1);
		$currRate = ($currRateGuess > $maxs['rate']) ? $maxs['rate'] : $currRateGuess;
	} else {
		$currRate = 0;
	}

	if($procfil == date('Ymd')) {
		//last rain
		$prevRnOld = file_get_contents("lastrn");
		if($rncum > 0) {
			//Only look at recent values, since this script is meant to be run every minute anyway,
			// so in ideal conditions only really need to check most recent two rnCumArr values.
			//Also, this fixes an awkward bug that presents itself 24hrs after rain, ie. in rnCumArr[0] territory,
			// so it is best to avoid this
			$limitRnLook = 300;
			for($i = 1; $i < $limitRnLook; $i++) {
				if($rncumArr[$end-$i-1] != $rncum) {
					$prevRn = mktime($custhr[$end-1], $custmin[$end-1] - $i, 0);
					if($prevRn != $prevRnOld) {
						file_put_contents("lastrn", $prevRn);
					}
					break;
				}
			}
			if($i === $limitRnLook) {
				$prevRn = $prevRnOld;
			}
		} else {
			$prevRn = $prevRnOld;
		}

		$diff = time() - $prevRn;
		$ago = secsToReadable($diff);
		$dateAgo = date('jS M', $prevRn);
		if(date('Ymd') == date('Ymd', $prevRn)) {
			$dateAgo = 'Today';
		} elseif(date('Ymd', mkdate(date('n'), date('j')-1)) == date('Ymd', $prevRn)) {
			$dateAgo = 'Yesterday';
		}
		$lastRnFull = acronym(date('H:i ', $prevRn) .' '. $dateAgo, $ago . ' ago', true);
	}

	//maxhr gust
	$maxhrgst = 0;
	for($i = 1; $i <= 60; $i++) {
		if($dat[4][$end-$i] > $maxhrgst) {
			$maxhrgst = $dat[4][$end-$i];
		}
	}

	$frosthrs = round($frostMins / 60, (int)($frostMins < 10) + 1);
	$rnDuration = roundToDp($duration / 60, 1);

	return array("min" => $mins, "max" => $maxs, "mean" => $means, "timeMin" => $timesMin, "timeMax" => $timesMax,
				"trend" => $trends, "trendRn" => $rnCums, "changeHr" => $hrChanges, "changeDay" => $hr24Changes,
				"misc" => array("frosthrs" => $frosthrs, "rnrate" => $currRate, "rnduration" => $rnDuration,
								"rnlast" => $lastRnFull, "wethrs" => $wethrs, "maxhrgst" => $maxhrgst, "cnt" => $end,
								"prevRn" => date('r', $prevRn), "prevRnOld" => date('r', $prevRnOld)
							)
			);
}

function timeFromMM($mm, $arr, $hrs, $mins) {
	$line = array_search($mm, $arr);
	return zerolead($hrs[$line]).':'.zerolead($mins[$line]);
}
?>