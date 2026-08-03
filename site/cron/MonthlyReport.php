<?php
/**
 * Monthly email report generator — ported from monthrepgen.php onto LTA/Util.
 */
class MonthlyReport {
private static function comparator($anom, $isShort, $extremeLow, $extremeHigh, $thresh1, $thresh2, $thresh3) {
	$absAnom = abs($anom);
	if($absAnom < $thresh1)
		return $isShort ? 'normal' : 'close to';

	if($absAnom <= $thresh2) {
		$comp = $isShort ? 'slightly' : 'a little';
	} elseif($absAnom <= $thresh3) {
		$comp = $isShort ? 'reasonably' : 'somewhat';
	} else {
		$comp = $isShort ? 'very' : 'much';
	}
	$hl = ($anom < 0) ? $extremeLow : $extremeHigh;
	$than = $isShort ? '' : ' than ';
	return $comp .' '. $hl . $than;
}

private static function signify($val) {
	return ( ($val < 0) ? '' : '+' ) . $val;
}

private static function pluralFix($val, $wereWas = false, $unit = 'day') {
	if($val == 1) {
		$str = $unit;
		$str2 = 'was';
	} else {
		$str = $unit.'s';
		$str2 = 'were';
		$val = ($val == 0) ? 'no' : $val;
	}
	return $wereWas ? "$str2 <b>$val</b> $str" :
		"<b>$val</b> $str";
}

private static function minMaxMeanSumCount($arr, $type, $morx) {
	$min = PHP_INT_MAX;
	$max = PHP_INT_MIN;
	$sum = 0;
	$count = 0;

	$validCnt = 0;
	foreach($arr as $val) {
		if(!Util::isBlank($val)) {
			$val = floatval($val);
			$validCnt++;

			if($val < $min) { $min = $val; }
			if($val > $max) { $max = $val; }
			$sum += $val;
			$count += (int)($val > 0);
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
private static function DATtoMDAT($arr) {
	$mdat = array();
	$morx = count($arr) > 5 && count($arr) < 20; //datm
	foreach ($arr as $type => $arr0) {
		foreach ($arr0 as $year => $arr1) {
			foreach ($arr1 as $month => $arr2) {
				$mdat[$type][$year][$month] = self::minMaxMeanSumCount($arr2, $type, $morx);
			}
		}
	}
	return $mdat;
}
public static function generate($repMonth, $repYear) {
	$DATM = unserialize(file_get_contents(CACHE_ROOT .'serialised_datm.txt'));
	$DATA = unserialize(file_get_contents(CACHE_ROOT .'serialised_dat.txt'));
	$MDAT = self::DATtoMDAT($DATA);
	$MDATM = self::DATtoMDAT($DATM);

	$mmsm = array('min', 'max', 'sum', 'cnt');
	$req = array(0,1,2,13);
	$reqT = array(0,1,2,3);
	$reqm = array(0,3,4,5,6,7);
	$reqmT = array(1,2,3);
	$manualRaw = array();
	$manualRawM = array();

	foreach ($reqm as $value) {
		foreach ($reqmT as $value2) {
			$manualRawM[$value][$mmsm[$value2]] = $MDATM[$value][$repYear][$repMonth][$value2];
		}
	}
	foreach ($req as $value) {
		foreach ($reqT as $value2) {
			$manualRaw[$value][$mmsm[$value2]] = $MDAT[$value][$repYear][$repMonth][$value2];
		}
	}

	$tempAv = $manualRaw[2]['sum'];
	$tempAnomImd = $manualRaw[2]['sum'] - ((LTA::$vars['tmin']['monthly'][$repMonth-1] + LTA::$vars['tmax']['monthly'][$repMonth-1]) / 2);
	$tempComparator = self::comparator($tempAnomImd, false, 'colder', 'warmer', 0.5, 1, 2.5);
	$tempAnom = $tempAnomImd;//, 1.1, true, true);
	$tempLo = $manualRaw[0]['min'];
	$tempHi = $manualRaw[1]['max'];

	$rainAv = $manualRaw[13]['sum'];
	$rainAnomImd = Util::percent($manualRaw[13]['sum'] - LTA::$vars['rain']['monthly'][$repMonth-1], LTA::$vars['rain']['monthly'][$repMonth-1], 0, false, false);
	$rainComparator = self::comparator($rainAnomImd, false, 'drier', 'wetter', 10, 20, 50);
	$rainAnom = self::signify($rainAnomImd);
	$rainCnt = $manualRaw[13]['cnt'];
	$rainHi = $manualRaw[13]['max'];
	$rainYrImd = $rainYrCnt = $annualsumCum = 0;
	for($m = 1; $m <= $repMonth; $m++) {
		$rainYrImd += $MDAT[13][$repYear][$m][2];
		$rainYrCnt += $MDAT[13][$repYear][$m][3];
		$annualsumCum += LTA::$vars['rain']['monthly'][$m-1];
	}
	$rainYr = $rainYrImd;
	$rainYrAnom =  self::signify(Util::percent($rainYrImd - $annualsumCum, $annualsumCum, 0, false, false));//, 0, false, true);

	$sunAv = $manualRawM[0]['sum'];
	$sunAnomImd = Util::percent($manualRawM[0]['sum'] - LTA::$vars['sunhr']['monthly'][$repMonth-1], LTA::$vars['sunhr']['monthly'][$repMonth-1], 0, false, false);
	$sunComparator = self::comparator($sunAnomImd, true, 'dull', 'sunny', 6, 13, 32);
	$sunAnom = self::signify($sunAnomImd);
	$sunMax = LTA::$vars['maxsun']['monthly'][$repMonth-1];
	$sunCnt = $manualRawM[0]['cnt'];
	$sunHi = $manualRawM[0]['max'];

	$AFs = Util::cond_count($DATA[0][$repYear][$repMonth], false, 0);
	$AFsFull = self::pluralFix($AFs, false, 'air frost');
	$bigRns = Util::cond_count($DATA[13][$repYear][$repMonth], true, 10);
	$bigRnsFull = self::pluralFix($bigRns);
	$bigGusts = Util::cond_count($DATA[11][$repYear][$repMonth], true, 30);
	$maxDepth = $manualRawM[4]['max'];
	$fallSnow = self::pluralFix($manualRawM[3]['cnt'], true);
	$fallSnowAnomI = round($manualRawM[3]['cnt'] - LTA::$vars['fsdays']['monthly'][$repMonth-1]);
	$fallSnowAnom = abs($fallSnowAnomI);
	$fallSnowAnom2 = ($fallSnowAnomI < 0) ? 'below' : 'above';
	$lySnow = self::pluralFix($manualRawM[4]['cnt']);
	$AFavr = self::signify($AFs - LTA::$vars['afdays']['monthly'][$repMonth-1]);
	$LSavr = self::signify(round($manualRawM[4]['cnt'] - LTA::$vars['lsdays']['monthly'][$repMonth-1]));
	$hail  = self::pluralFix($manualRawM[5]['cnt'], true);
	$mm10 = 10; //, 2, true, false, -1);
	$mph30 = 30; //, 4, true, false, -1);

	$export = array(
		"date" => array($repMonth, $repYear),
		"temp" => array($tempComparator, $tempAv, $tempAnom, $tempLo, $tempHi),
		"rain" => array($rainComparator, $rainAv, $rainAnom, $rainCnt, $rainHi, $rainYr, $rainYrAnom, $rainYrCnt),
		"sun" => array($sunComparator, $sunAv, $sunAnom, $sunMax, $sunCnt, $sunHi),
		"winter" => array($AFs, $manualRawM[3]['cnt'], $fallSnow, $fallSnowAnom, $fallSnowAnom2, $AFsFull, $AFavr, $lySnow, $LSavr, $maxDepth),
		"other" => array($hail, $manualRawM[6]['cnt'], $manualRawM[7]['cnt'], $bigRnsFull, 10, $bigGusts, 30)
	);
//	var_dump($export);

	$exported = var_export($export, true);
	$output = "<?php\n\t\t\$export = " . $exported . ";\n\t\t?>";

	file_put_contents(ROOT . $repYear . "/report$repMonth.php", $output);
	return "
date array(repMonth, repYear),
temp array(tempComparator, tempAv, tempAnom, tempLo, tempHi),
rain array(rainComparator, rainAv, rainAnom, rainCnt, rainHi, rainYr, rainYrAnom, rainYrCnt),
sun array(sunComparator, sunAv, sunAnom, sunMax, sunCnt, sunHi),
winter array(AFs, manualRawM[3]['cnt'], fallSnow, fallSnowAnom, fallSnowAnom2, AFsFull, AFavr, lySnow, LSavr, maxDepth),
other array(hail, manualRawM[6]['cnt'], manualRawM[7]['cnt'], bigRnsFull, 10, bigGusts, 30)

$exported";
}
}
