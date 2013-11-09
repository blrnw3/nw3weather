<?php
//Get yesterday extreme values for those which have no WD custom tag
$filex = file('wx18.html'); $filex2 = file('wx19.html');
$extrad1 = explode("=", $filex2[13]); $extrad2 = explode("=", $filex[13]); $extrad3 = explode("=", $filex[15]);
$nmin = floatval($extrad1[1]); $dmax = floatval($extrad2[1]);
$mrain = floatval($extrad3[1]); if ($mrain == 0) { $mrain = ''; } $mrain2 = floatval($extrad3[1]); 
$mryest = $maxrainrateyesthr; if ($mryest == 0) { $mryest = ''; } if(floatval($ystdyrain) == 0.3) { $mryest = '-'; } $mryest2 = $maxrainrateyesthr;
	
function degname2($winddegree) {
	$windlabels = array ("N", "NE", "E", "SE", "S", "SW", "W", "NW", "N");
	$windlabel = $windlabels[ round($winddegree / 45) ];
	return "$windlabel";
}

//Get data from custom log
function customlog() {
	$filcust = file('customtextout.txt');
	for($i = 1; $i < count($filcust); $i++) {
		$custl = explode(' ', ltrim(str_ireplace('  ', ' ', $filcust[$i])));
		$rn10[$i] = floatval($custl[5]);
		$wind10[$i] = round(floatval($custl[7])*1.152,1);
		$wind60[$i] = round(floatval($custl[8])*1.152,1);
		$tchangehr[$i] = floatval($custl[9]);
		$hchangehr1 = $custl[10]; $hchangehr2 = $custl[11];
		if(intval($hchangehr1) == 0) { $hchangehr[$i] = floatval($hchangehr2); } else { $hchangehr[$i] = floatval($hchangehr1); }
		$custmin[$i] = $custl[1]; $custhr[$i] = $custl[0];
	}
	$rn10max = max($rn10); $rn10maxt = $custhr[array_search($rn10max,$rn10)].':'.$custmin[array_search($rn10max,$rn10)];
		if ($rn10max == 0) { $rn10max2 = ''; $rn10maxt = 'n/a'; } else { $rn10max2 = $rn10max; }
	$wind10max = max($wind10); $wind10maxt = $custhr[array_search($wind10max,$wind10)].':'.$custmin[array_search($wind10max,$wind10)];
	$wind60max = max($wind60); $wind60maxt = $custhr[array_search($wind60max,$wind60)].':'.$custmin[array_search($wind60max,$wind60)];
	$tchangehrmax = max($tchangehr); $tchangehrmaxt = $custhr[array_search($tchangehrmax,$tchangehr)].':'.$custmin[array_search($tchangehrmax,$tchangehr)];
	$hchangehrmax = max($hchangehr); $hchangehrmaxt = $custhr[array_search($hchangehrmax,$hchangehr)].':'.$custmin[array_search($hchangehrmax,$hchangehr)];
	$tchangehrmin = min($tchangehr); $tchangehrmint = $custhr[array_search($tchangehrmin,$tchangehr)].':'.$custmin[array_search($tchangehrmin,$tchangehr)];
	$hchangehrmin = min($hchangehr); $hchangehrmint = $custhr[array_search($hchangehrmin,$hchangehr)].':'.$custmin[array_search($hchangehrmin,$hchangehr)];
	
	return array($rn10max, $rn10max2, $wind10max, $wind10maxt, $wind60max, $wind60maxt,
				$tchangehrmax, $tchangehrmaxt, $hchangehrmax, $hchangehrmaxt, $tchangehrmin, $tchangehrmint, $hchangehrmin, $hchangehrmint, $rn10maxt);
}

//Function to get data from monthyyyy.htm files
function gethistory($file) {
	$data = file($file); $end = 1200;
	for ($i = 1; $i < $end; $i++) {
		if(strpos($data[$i],"remes for the month") > 0) { $end = $i; }
		if(strpos($data[$i],"remes for day") > 0) { $daya = explode(" ", $data[$i]); $a = intval(substr($daya[7],1,2)); }
		if(strpos($data[$i],"aximum hum") > 0) { $hmaxa = explode(" ", $data[$i]); $hmaxv[$a] = intval($hmaxa[12]); $hmaxt[$a] = trim($hmaxa[18]); }
		if(strpos($data[$i],"inimum hum") > 0) { $hmina = explode(" ", $data[$i]); $hminv[$a] = intval($hmina[11]); $hmint[$a] = trim($hmina[17]); }
		if(strpos($data[$i],"aximum dew") > 0) { $dmaxa = explode(" ", $data[$i]); $dmaxv[$a] = floatval($dmaxa[11]); }
		if(strpos($data[$i],"inimum dew") > 0) { $dmina = explode(" ", $data[$i]); $dminv[$a] = floatval($dmina[11]); }
		if(strpos($data[$i],"aximum pre") > 0) { $pmaxa = explode(" ", $data[$i]); $pmaxv[$a] = floatval($pmaxa[11]); }
		if(strpos($data[$i],"inimum pre") > 0) { $pmina = explode(" ", $data[$i]); $pminv[$a] = floatval($pmina[11]); }
		if(strpos($data[$i],"aximum tem") > 0) { $tmaxa = explode(" ", $data[$i]); $tmaxv[$a] = floatval($tmaxa[9]); $tmaxt[$a] = trim($tmaxa[15]); }
		if(strpos($data[$i],"inimum tem") > 0) { $tmina = explode(" ", $data[$i]); $tminv[$a] = floatval($tmina[9]); $tmint[$a] = trim($tmina[15]); }
		if(strpos($data[$i],"verage tem") > 0) { $tavea = explode(" ", $data[$i]); $tavev[$a] = floatval($tavea[8]); }
		if(strpos($data[$i],"verage dew") > 0) { $davea = explode(" ", $data[$i]); $davev[$a] = floatval($davea[11]); }
		if(strpos($data[$i],"verage win") > 0) { $wavea = explode(" ", $data[$i]); $wavev[$a] = floatval($wavea[10]); }
		if(strpos($data[$i],"verage hum") > 0) { $havea = explode(" ", $data[$i]); $havev[$a] = intval($havea[11]); }
		if(strpos($data[$i],"verage bar") > 0) { $pavea = explode(" ", $data[$i]); $pavev[$a] = intval($pavea[10]); }
		if(strpos($data[$i],"all for da") > 0) { $raina = explode(" ", $data[$i]); $rainv[$a] = $raina[12]; }
		if(strpos($data[$i]," direction") > 0) { $wdira = explode(" ", $data[$i]); if(intval($wdira[10]) == 0):	$wdirv[$a] = degname2(intval($wdira[11]));
		$wdirv2[$a] = intval($wdira[11]); else: $wdirv[$a] = degname2(intval($wdira[10])); $wdirv2[$a] = intval($wdira[10]); endif; }
	}
	return array($hmaxv,$hminv,$dmaxv,$dminv,$tmaxv,$tminv,$tavev,$davev,$wavev,$havev,$rainv,$wdirv,$tmint,$tmaxt,$hmint,$hmaxt,$wdirv2,$pminv,$pmaxv,$pavev);
}

//Check if yesterday's data has been written to mrep.csv to prevent duplicate line-writing
$checkm = file("mrep.csv");
$flm = intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,2,1,2009))/(24*3600))+2;
$icheckm = explode(",", $checkm[$flm]);

//Prepare then write data to CSV for use in web monthly reports if not already written today and if not close to midnight
if ($icheckm[0] != date("d", mktime(0,0,0,$date_month,$date_day-1)) && mktime($time_hour, $time_minute) > mktime(0,0)+60*11 && $time_hour < 16) {

	//Prepare the data for mrep.csv
	$custom = customlog();
	$listm = array(date("d", mktime(0,0,0,$date_month,$date_day-1)), date("m", mktime(0,0,0,$date_month,$date_day-1)), date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)),
			$nmin, $dmax, $mrain2, $mryest2, $custom[0], $custom[14], $maxaverageyestnodir, $maxgustyestnodir, $custom[2], $custom[3], $custom[4], $custom[5],
			$custom[6], $custom[7], $custom[8], $custom[9], $custom[10], $custom[11], $custom[12], $custom[13]); // wxhistmonth extra data
	//Write the data
	$filem = fopen("mrep.csv","a");
	fputcsv($filem, $listm);
	fclose($filem);
}

//Check if yesterday's data has been written to data.csv to prevent duplicate line-writing
$check = file("data.csv");
$fl = intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,1,1,2012))/(24*3600))+1;
$icheck = explode(",", $check[$fl]);

//Prepare then write data to CSV for use in Daily Data yyyy.xls if not already written today and if not close to midnight
if ($icheck[0] != date("d", mktime(0,0,0,$date_month,$date_day-1)) && mktime($time_hour, $time_minute) > mktime(0,0)+3600*16 && $time_hour < 23) {
	
	//Get WU view count
	$filwu = file("wugrab.html");
	for ($i = 500; $i < 750; $i++) {
		if(strpos($filwu[$i],"Viewed") > 0) {
			$wuvul = explode(" ", $filwu[$i]);
		}
	}
	$wuvu = $wuvul[3];

	//Get parameters from monthyyyy.htm files
	$report = date("F", mktime(0,0,0,$date_month,$date_day-1)).date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'.htm';
	$tempvar = gethistory($report);
	
	//Find average times of extremes
	$tminy = floatval($mintempyest);
	$tmaxy = floatval($maxtempyest);
	$bminy = intval($minbaroyest);
	$bmaxy = intval($maxbaroyest);
	$date_day_yest = date("j", mktime(0,0,0,$date_month,$date_day-1));
	$list2 = array( '','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','','',
				$dmax, $mintempyestt, $maxtempyestt, $minhumyestt, $maxhumyestt, $maxgustyestt,
				$tempvar[12][$date_day_yest], $tempvar[13][$date_day_yest], $tempvar[14][$date_day_yest], $tempvar[15][$date_day_yest]);
	$time1 = explode(":", $list2[34]); $time2 = explode(":", $list2[35]); $time3 = explode(":", $list2[36]); $time4 = explode(":", $list2[37]); $time5 = explode(":", $list2[38]);
	$time6 = explode(":", $list2[39]); $time7 = explode(":", $list2[40]); $time8 = explode(":", $list2[41]);$time9 = explode(":", $list2[42]);
	$avtmint = date("H:i", (mktime($time1[0],$time1[1]) + mktime($time6[0],$time6[1])) / 2);
	$avtmaxt = date("H:i", (mktime($time2[0],$time2[1]) + mktime($time7[0],$time7[1])) / 2);
	if (strlen($list2[42]) > 3) { $avhmint = date("H:i", (mktime($time3[0],$time3[1]) + mktime($time8[0],$time8[1])) / 2); }
	if (strlen($list2[42]) > 3) { $avhmaxt = date("H:i", (mktime($time4[0],$time4[1]) + mktime($time9[0],$time9[1])) / 2); }
	if(date('H',mktime($avhmint)) > date('H',mktime($sunset))+1 || date('H',mktime($avhmint)) < date('H',mktime($sunrise))) { $avhmint .= '*'; }
	if(date('H',mktime($avhmaxt)) > date('H',mktime($sunrise))+3) { $avhmaxt .= '*'; }
	if(intval($time9[1]) == 0 && intval($time9[0]) == 0) { $avhmaxt = "23:59*"; }
	if($nmin < $tminy) { $avtmint = '21:00*'; }
	
	 //Parameters to be written
	$custom = customlog();
	$list = array(date("d", mktime(0,0,0,$date_month,$date_day-1)), date("m", mktime(0,0,0,$date_month,$date_day-1)),
			$tminy, $tmaxy, $tempvar[6][$date_day_yest], $tmaxy-$tminy, '', $minhumyest, $maxhumyest, $tempvar[9][$date_day_yest], $maxhumyest-$minhumyest, '',
			$bminy, $bmaxy, '', '', '', $tempvar[8][$date_day_yest], $maxaverageyestnodir, $maxgustyestnodir, $tempvar[11][$date_day_yest], '',
			floatval($ystdyrain), $mrain, $custom[1], '', '', $mryest, '', floatval($mindewyest), floatval($maxdewyest), $tempvar[7][$date_day_yest],
			$nmin, $dmax, $mintempyestt, $maxtempyestt, $minhumyestt, $maxhumyestt, $maxgustyestt,
			substr($tempvar[12][$date_day_yest],0,5), substr($tempvar[13][$date_day_yest],0,5), substr($tempvar[14][$date_day_yest],0,5), substr($tempvar[15][$date_day_yest],0,5),
			$avtmint, $avtmaxt, $avhmint, $avhmaxt, $wuvu, $custom[2], $time, ' \n');
	
	//Do the actual writing of data
	$file = fopen("data.csv","a");
	fputcsv($file, $list);
	fclose($file);
}

if($file < 1) { $custom = customlog(); for ($i = 0; $i < count($custom); $i++) { echo $custom[$i], ', '; } }
?>