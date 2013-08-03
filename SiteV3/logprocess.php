<?php
ini_set("memory_limit","128M");

include('/home/nwweathe/public_html/basics.php');
require('unit-select.php');
include($root.'functions.php');

$lim = array(1,1,1,1,2,0.4,5,1.5,2,9,15,400,1.5,1.5);
$round_pt = array(0,0,0,1,0,0,2,1,0,1,1);
/*
$start_mon = 8;
$st_yr = 2012;
$names = array( 'Day', 'Hour', 'Minute', '10-min wind speed / mph',
	'Wind direction / degrees', 'Temperature / C', 'Relative humidity / %', 'Pressure / hPa',
	'Day rain total / mm');

//PRODUCE HOURLY LOGFILES FOR EACH MONTH, USING DAILY LOGS.
while(mkdate($start_mon,1+$a,$st_yr) < mkdate($dmonth,$dday,$dyear)) {
	$logd = $root.'logfiles/daily/'.date('Ymd',mkdate($start_mon,1+$a,$st_yr)).'log.txt';
	$yr = date('Y',mkdate($start_mon,1+$a,$st_yr));
	$mon = date('m',mkdate($start_mon,1+$a,$st_yr));
	$day = date('d',mkdate($start_mon,1+$a,$st_yr));
	if($day == 1) {
		$filelog = fopen($root.'logfiles/monthly/'.$yr.zerolead($mon).'hourlog.txt',"w");
		fputcsv($filelog, $names);
	}
	$cnt = 0;
	if(file_exists($logd)) {
		$filo = file($logd);
		for($i = 0; $i < count($filo); $i++) {
			$custl = explode(',', $filo[$i]);
			if(intval($custl[1]) == 57) {
				$data = array($day, zerolead($custl[0]), $custl[1], round($custl[3],1));
				for($j = 5; $j < 11; $j++) {
					$data[$j-1] = round($custl[$j],1);
				}
				$hrdata[$cnt] = $data;
				fputcsv($filelog, $hrdata[$cnt]);
				$cnt++;
			}
			// for($l = 0; $l < count($custl); $l++) { $data[$l][intval($custl[0])][intval($custl[1])] = round($custl[$l],1); }
		}


		// for($h = 0; $h < 24; $h++) {
			// for($l = 0; $l < 10; $l++) { $datahr[$l] = round(mean($data[$l][$h]),$round_pt[$l]); }
			// if(is_array($data[10][$h])) { $datahr[10] = max($data[10][$h]); } // $datahr[2] = $h; $datahr[1] = $day; $datahr[11] = "";
			// fputcsv($filelog, $datahr);
		// }
	 }
	if($day == date('t',mkdate($start_mon,1+$a,$st_yr))) { fclose($filelog); echo $yr, ' ', $mon, ' ', $day, '<br />'; }
	unset($filo,$data,$datahr,$custl);
	$a++;
}
*/

// PROCESS WD CSV MONTHLY LOGFILES INTO DAILY LOGS
### WARNING!!!!! wind speeds end up averaging too-low. cause unknowm #########
$year_proc = 2013;
$mon_st = 4; $mon_en = 4;
$day_st = 17; $day_en = 17; //get_days_in_month($mon,$yr)

for($m = $mon_st; $m <= $mon_en; $m++) {
	echo '<br />', $months[$m-1], '<br />';
	$log = $m.$year_proc;
	$yr = substr($log,-4,4); $mon = intval(str_replace($yr,'',$log));

	$filcust = file($GLOBALS['root'].'logfiles/'.$log.'lgcsv.csv');
	$cnt = count($filcust);
	for($i = 1; $i < $cnt; $i++) {
		$custl = explode(',', $filcust[$i]);
		$custl2 = explode(',', $filcust[$i-1]);
		$j++;
		if($custl2[0] != $custl[0]) { $j = 0; }
		$data[intval($custl[0])][$j] = $custl;
	}
	unset($filcust);

	for($i = $day_st; $i <= $day_en; $i++) {
		$cntf = count($data[$i]);
		echo zerolead($i), ' ', $cntf, '<br />';
		$extension = 'logfiles/daily/'.$yr.zerolead($mon).zerolead($i).'logZ.txt';
		$filelog = fopen($GLOBALS['root'].$extension,"w"); $w10 = 0;

		for($l = 0; $l < $cntf; $l++) {
			$w10 += $data[$i][$l][9]/10;
			if($l > 9) {
				$w10 -= $data[$i][$l-10][9]/10;
				if($w10 < 0) { $w10 = 0; }
				$data[$i][$l][9] = round($w10 * 1.152, 1);

				for($t = 5; $t < 14; $t++) {
					if(abs($data[$i][$l-1][$t] - $data[$i][$l][$t]) > $lim[$t]) {
						echo 'column ', $t, ': ', $data[$i][$l][$t], ' at ',
							date('H:i',mktime($data[$i][$l][3],$data[$i][$l][4])), '<br />';
						if($t > 4 && $t != 10 && $t != 9) { $data[$i][$l][$t] = $data[$i][$l-1][$t]; }
					}
				}

			}
			fwrite($filelog, $data[$i][$l][3].','. $data[$i][$l][4].','. $data[$i][$l][0].','. $data[$i][$l][9].','. round($data[$i][$l][10] * 1.15).','. $data[$i][$l][11].','. $data[$i][$l][5].
				','. $data[$i][$l][6].','.round($data[$i][$l][8]).','. $data[$i][$l][7].','. round($data[$i][$l][13],1)."\r\n");
		}
		fclose($filelog);

		logneatenandrepair($extension);
	}

	unset($data,$custl,$custl2,$j);
}

function logneatenandrepair($input) {
	global $root;
	$cnt = 0;
	$filelog = fopen( $root.str_ireplace('Z.txt','.txt',$input), "w" );
	$filcust = file($root.$input);
	$len = count($filcust);

	for($i = 0; $i < $len; $i++) {
		$custl[$i] = explode(',', $filcust[$i]);
		for($t = 0; $t < 11; $t++) {
			$custl[$i][$t] = round($custl[$i][$t],1);
		}
		$custl[$i][8] = round($custl[$i][8]);
	}

	$linewrite[0] = implode(',', $custl[0]);

	for($i = 1; $i < $len; $i++) {
		$diff = ( mktime($custl[$i][0], $custl[$i][1], 0) - mktime($custl[$i-1][0], $custl[$i-1][1], 0) ) / 60;
		if( $diff > 1 && $diff < 10 ) {
			for($j = 1; $j < $diff; $j++) {
				$linewrite[$i+$j-1+$cnt] = $linewrite[$i+$j-2+$cnt];
			}
			$cnt += $j - 1;
		}
		$linewrite[$i+$cnt] = implode(',', $custl[$i]);
	}

	$lincnt = count($linewrite);
	for($i = 0; $i < $lincnt; $i++) {
		if(strlen($linewrite[$i]) > 10) {
			fwrite($filelog, $linewrite[$i]."\r\n");
		}
	}

	fclose($filelog);
}

/*
$a = 0;
while(mkdate(1,2+$a,2010) < mkdate($dmonth,$dday,$dyear)) {
	$logd1 = date('Ymd',mkdate(1,1+$a,2010)); $logd2 = date('Ymd',mkdate(1,2+$a,2010));
	$filo1 = file($GLOBALS['root'].'logfiles/daily/'.$logd1.'log.txt'); $filo2 = file($GLOBALS['root'].'logfiles/daily/'.$logd2.'log.txt');
	$tvar = explode(',', $filo1[count($filo1)-1]); $rnday = $tvar[10];
	for($i = 0; $i < count($filo2); $i++) {
		$custl1 = explode(',', $filo1[$i]); $custl2 = explode(',', $filo2[$i]);
		$rn24hr = $custl2[10] - $custl1[10] + $rnday;
		if($rn24hr > 20 && $rn24hr > $rnday) { $rnstore[$i] = $rn24hr; }
	}
	if(is_array($rnstore)) {
		echo true_z($a+1, 2010, 'jS M Y, ');
		echo date('H:i ',strtotime('midnight +'. (string)(array_search(max($rnstore),$rnstore)). ' minutes ')).max($rnstore).' mm <br />';
	}
	unset($filo1,$filo2,$rnstore);
	$a++;
}
*/
//CYCLE THROUGH DAILY LOGS TO FIND INSTANCES OF RAIN >20MM IN 24HRS zerolead($custl2[0]).zerolead($custl2[1]
?>