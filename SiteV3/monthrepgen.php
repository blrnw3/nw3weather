<?php
function comparator($anom, $isShort, $extremeLow, $extremeHigh, $thresh1, $thresh2, $thresh3) {
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

function signify($val) {
	return ( ($val < 0) ? '' : '+' ) . $val;
}

function pluralFix($val, $wereWas = false, $unit = 'day') {
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

function monthlyReport($repMonth, $repYear) {
	include_once('climavs.php');

	$DATM = unserialize(file_get_contents(ROOT .'serialised_datm.txt'));
	$DATA = unserialize(file_get_contents(ROOT .'serialised_dat.txt'));
	$MDAT = DATtoMDAT($DATA);
	$MDATM = DATtoMDAT($DATM);

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
	$tempAnomImd = $manualRaw[2]['sum'] - $tdatav['mean'][$repMonth-1];
	$tempComparator = comparator($tempAnomImd, false, 'colder', 'warmer', 0.5, 1, 2.5);
	$tempAnom = $tempAnomImd;//, 1.1, true, true);
	$tempLo = $manualRaw[0]['min'];
	$tempHi = $manualRaw[1]['max'];

	$rainAv = $manualRaw[13]['sum'];
	$rainAnomImd = percent($manualRaw[13]['sum'] - $rainav[$repMonth-1], $rainav[$repMonth-1], 0, false, false);
	$rainComparator = comparator($rainAnomImd, false, 'drier', 'wetter', 10, 20, 50);
	$rainAnom = signify($rainAnomImd);
	$rainCnt = $manualRaw[13]['cnt'];
	$rainHi = $manualRaw[13]['max'];
	$rainYrImd = $rainYrCnt = $annualsumCum = 0;
	for($m = 1; $m <= $repMonth; $m++) {
		$rainYrImd += $MDAT[13][$repYear][$m][2];
		$rainYrCnt += $MDAT[13][$repYear][$m][3];
		$annualsumCum += $rainav[$m-1];
	}
	$rainYr = $rainYrImd;
	$rainYrAnom =  signify(percent($rainYrImd - $annualsumCum, $annualsumCum, 0, false, false));//, 0, false, true);

	$sunAv = $manualRawM[0]['sum'];
	$sunAnomImd = percent($manualRawM[0]['sum'] - $sunav[$repMonth-1], $sunav[$repMonth-1], 0, false, false);
	$sunComparator = comparator($sunAnomImd, true, 'dull', 'sunny', 6, 13, 32);
	$sunAnom = signify($sunAnomImd);
	$sunMax = $maxsun[$repMonth-1];
	$sunCnt = $manualRawM[0]['cnt'];
	$sunHi = $manualRawM[0]['max'];

	$AFs = sum_cond($DATA[0][$repYear][$repMonth], false, 0);
	$AFsFull = pluralFix($AFs, false, 'air frost');
	$bigRns = sum_cond($DATA[13][$repYear][$repMonth], true, 10);
	$bigRnsFull = pluralFix($bigRns);
	$bigGusts = sum_cond($DATA[11][$repYear][$repMonth], true, 30);
	$maxDepth = $manualRawM[4]['max'];
	$fallSnow = pluralFix($manualRawM[3]['cnt'], true);
	$fallSnowAnomI = round($manualRawM[3]['cnt'] - $FSav[$repMonth-1]);
	$fallSnowAnom = abs($fallSnowAnomI);
	$fallSnowAnom2 = ($fallSnowAnomI < 0) ? 'below' : 'above';
	$lySnow = pluralFix($manualRawM[4]['cnt']);
	$AFavr = signify($AFs - $AFav[$repMonth-1]);
	$LSavr = signify(round($manualRawM[4]['cnt'] - $LSav[$repMonth-1]));
	$hail  = pluralFix($manualRawM[5]['cnt'], true);
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

	$exported = var_export($export, true);
	$output = '<?php
		$export = ' . $exported . ';
		?>';

	file_put_contents(ROOT.$repYear."/report$repMonth.php", $output);
	return " 
date array(repMonth, repYear),
temp array(tempComparator, tempAv, tempAnom, tempLo, tempHi),
rain array(rainComparator, rainAv, rainAnom, rainCnt, rainHi, rainYr, rainYrAnom, rainYrCnt),
sun array(sunComparator, sunAv, sunAnom, sunMax, sunCnt, sunHi),
winter array(AFs, manualRawM[3]['cnt'], fallSnow, fallSnowAnom, fallSnowAnom2, AFsFull, AFavr, lySnow, LSavr, maxDepth),
other array(hail, manualRawM[6]['cnt'], manualRawM[7]['cnt'], bigRnsFull, 10, bigGusts, 30)

$exported";
}
?>