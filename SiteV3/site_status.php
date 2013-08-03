<?php
//$statusMessage = 'Site update due to be completed on 22nd May';
$statusMessage = '<b>Site update, 22nd May:</b> I have <a href="news.php" title="details of the upgrade"> made a few changes</a>
 to nw3weather. Enjoy.';
$showMessage = false;
if($showMessage) {
	showStatusDiv($statusMessage, false);
}

//function depth($depth) { global $unitT; if($unitT == 'F') { $depth = round($depth/2.54).'</b> in.'; } else { $depth .= '</b> cm'; } return $depth; }

#### TOO BUGGY - NEED TO IMPROVE AND RE-INSTATE
//Script to display when a record is set
/*
require($root.'fnctnrec.php');
if($mrecordlowhum > 10 && $Rmrecordlowhum > 10) { //prevent displaying when data is corrupted
$todayRecords = array($mintemp,$minwindch,$lowhum,$mindew,$lowbaro,$maxtemp,$highhum,$maxdew,$highbaro,$dayrn,$maxhourrn,$consecdayswithrain+0.1,$dayswithnorain+0.1,$maxgst,$maxavgspd,$mintemp,$maxtemp);
$monthRecords = array($Rmrecordlowtemp,$Rmrecordlowchill,$Rmrecordlowhum,$Rmrecordlowdew,$Rmrecordlowbaro,$Rmrecordhightemp,$Rmrecordhighhum,$Rmrecordhighdew,$Rmrecordhighbaro,$Rmrecorddailyrain,$Rmhrrecordrainrate,100,100,$Rmrecordwindgust,$Rmrecordwindspeed,-100,100);
$yearRecords = array($Ryrecordlowtemp,$Ryrecordlowchill,$Ryrecordlowhum,$Ryrecordlowdew,$Ryrecordlowbaro,$Ryrecordhightemp,$Ryrecordhighhum,$Ryrecordhighdew,$Ryrecordhighbaro,$Ryrecorddailyrain,$Ryhrrecordrainrate,$Ryrecorddayswithrain,$Ryrecorddaysnorain,$Ryrecordwindgust,$Ryrecordwindspeed,-100,100);
$alltRecords = array($Rrecordlowtemp,$Rrecordlowchill,$Rrecordlowhum,$Rrecordlowdew,$Rrecordlowbaro,$Rrecordhightemp,$Rrecordhighhum,$Rrecordhighdew,$Rrecordhighbaro,$Rrecorddailyrain,$Rhrrecordrainrate,$Rrecorddayswithrain,$Rrecorddaysnorain,$Rrecordwindgust,$Rrecordwindspeed,$Rrecordlowtempcurrentmonth,$Rrecordhightempcurrentmonth);
$monthRecDay = array($Rmrecordlowtempday,$Rmrecordlowchillday,$Rmrecordlowhumday,$Rmrecordlowdewday,$Rmrecordlowbaroday,$Rmrecordhightempday,$Rmrecordhighhumday,$Rmrecordhighdewday,$Rmrecordhighbaroday,$Rmrecorddailyrainday,$Rmhrrecordrainrateday,$Rmrecorddayswithrainday,$Rmrecorddaysnorainday,$Rmrecordhighgustday,$Rmrecordhighavwindday,'','');
$yearRecDay = array($Ryrecordlowtempday,$Ryrecordlowchillday,$Ryrecordlowhumday,$Ryrecordlowdewday,$Ryrecordlowbaroday,$Ryrecordhightempday,$Ryrecordhighhumday,$Ryrecordhighdewday,$Ryrecordhighbaroday,$Ryrecorddailyrainday,$Ryhrrecordrainrateday,$Ryrecorddayswithrainday,$Ryrecorddaysnorainday,$Ryrecordhighgustday,$Ryrecordhighavwindday,'','');
$alltRecDay = array($Rrecordlowtempday,$Rrecordlowchillday,$Rrecordlowhumday,$Rrecordlowdewday,$Rrecordlowbaroday,$Rrecordhightempday,$Rrecordhighhumday,$Rrecordhighdewday,$Rrecordhighbaroday,$Rrecorddailyrainday,$Rhrrecordrainrateday,$Rrecorddayswithrainday,$Rrecorddaysnorainday,$Rrecordhighgustday,$Rrecordhighavwindday,$Rrecordlowtempcurrentmonthday,$Rrecordhightempcurrentmonthday);
$yearRecMonth = array($Ryrecordlowtempmonth,$Ryrecordlowchillmonth,$Ryrecordlowhummonth,$Ryrecordlowdewmonth,$Ryrecordlowbaromonth,$Ryrecordhightempmonth,$Ryrecordhighhummonth,$Ryrecordhighdewmonth,$Ryrecordhighbaromonth,$Ryrecorddailyrainmonth,$Ryhrrecordrainratemonth,$Ryrecorddayswithrainmonth,$Ryrecorddaysnorainmonth,$Ryrecordhighgustmonth,$Ryrecordhighavwindmonth,'','');
$alltRecMonth = array($Rrecordlowtempmonth,$Rrecordlowchillmonth,$Rrecordlowhummonth,$Rrecordlowdewmonth,$Rrecordlowbaromonth,$Rrecordhightempmonth,$Rrecordhighhummonth,$Rrecordhighdewmonth,$Rrecordhighbaromonth,$Rrecorddailyrainmonth,$Rhrrecordrainratemonth,$Rrecorddayswithrainmonth,$Rrecorddaysnorainmonth,$Rrecordhighgustmonth,$Rrecordhighavwindmonth,date('m'),date('m'));
$alltRecYear = array($Rrecordlowtempyear,$Rrecordlowchillyear,$Rrecordlowhumyear,$Rrecordlowdewyear,$Rrecordlowbaroyear,$Rrecordhightempyear,$Rrecordhighhumyear,$Rrecordhighdewyear,$Rrecordhighbaroyear,$Rrecorddailyrainyear,$Rhrrecordrainrateyear,$Rrecorddayswithrainyear,$Rrecorddaysnorainyear,$Rrecordhighgustyear,$Rrecordhighavwindyear,$Rrecordlowtempcurrentmonthyear,$Rrecordhightempcurrentmonthyear);
$rectype = array(0,0,0,0,0,1,1,1,1,1,1,1,1,1,1,0,1);
for($i = 0; $i < count($todayRecords); $i++) { if($i == 11 || $i == 12) { $prep[$i] = 'up to '; } else { $prep[$i] = 'on '; } }
$descrip = array('Low temperature', 'Low windchill', 'Low humidity', 'Low dew point', 'Low pressure', 'High temperature', 'High humidity', 'High dew point', 'High pressure',
	'Most rain in one day', 'Most rain in one hour', 'Longest wet spell', 'Longest dry spell', 'Highest gust speed', 'Highest average wind speed', 'Lowest '.$monthname.' temperature', 'Highest '.$monthname.' temperature');
$format = array(1,1,9,1,3,1,9,1,3,2,2,6,6,4,4,1,1); $col = array('#DCD0E8', '#DCC0B6', '#DCE0E4');
$extracond = ( 2 > 1); $extracond2 = 2 < 3;
for($a=0;$a<3;$a++) {
	$tablest[$a] = '<div id="record"><table width="98%" align="center" cellpadding="4">
			<tr bgcolor="' .$col[$a].'"><td align="center" style="color:#670F03;font-size:115%">';
}
for($i = 0; $i < count($todayRecords); $i++) { // All-time records
	if((floatval($todayRecords[$i]) < floatval($alltRecords[$i]) && $rectype[$i] == 0) || (floatval($todayRecords[$i]) > floatval($alltRecords[$i]) && $rectype[$i] == 1)) {
		if($counta < 1) {
			echo $tablest[2];
		}
		echo 'New All-time record has been set! Record: ', $descrip[$i], ', <b>', conv($todayRecords[$i],$format[$i],1),
		'</b> &nbsp;(Previous: ', conv($alltRecords[$i],$format[$i],1), ' ',$prep[$i], date('jS M Y',mktime(0,0,0,$alltRecMonth[$i],$alltRecDay[$i],$alltRecYear[$i])), ')<br />';
		$counta = $counta + 1; $supera[$i] = 1;
	}
	if($counta >= 1 && $i+1 == count($todayRecords)) {
		echo '</td></tr></table></div>';
	}
}
$dmon = date('n'); if(isset($_GET['test'])) {
	$dmon = 2;
}

for($i = 0; $i < count($todayRecords); $i++) { // Year Records
	//if(isset($_GET['test'])) {
	if(mktime(0,0,0,1,intval($yearRecDay[$i])) == mktime(0,0,0,1,date('j')-1) && intval($yearRecMonth[$i]) == date('n')) {
		$yearRecdate[$i] = 'Yesterday'; $prep[$i] = ' ';
	} else { $yearRecdate[$i] = date('jS F',mktime(0,0,0,intval($yearRecMonth[$i]),intval($yearRecDay[$i])));
	}
	if(mktime(0,0,0,1,intval($yearRecDay[$i])) == mktime(0,0,0,1,date('j')) && intval($yearRecMonth[$i]) == date('n')) {
		$yearRecdate[$i] = 'Midnight'; $prep[$i] = 'at ';
	}
	if($prep[12] != 'up to ') {
		$yearRecdate[12] = 'Yesterday'; $yearRecords[12] = $yearRecords[12]-1; $prep[12] = 'up to ';
	}
	//}
	if($i == 11) {
		$extracond = floatval($dayrn) > 0; $extracond2 = intval($consecdayswithrain) > 4;
	} else { $extracond = 2 < 3; $extracond2 = 2 < 4;
	}
	if($i == 12) {
		$extracond2 = intval($dayswithnorain) > 4;
	} elseif($i != 11) {
		$extracond2 = 2 < 3;
	}
	if((floatval($todayRecords[$i]) < floatval($yearRecords[$i]) && $rectype[$i] == 0) || (floatval($todayRecords[$i]) > floatval($yearRecords[$i]) && $rectype[$i] == 1)) {
		if((($dmon == 2 && intval($dday) > 5) || $dmon > 2) && $extracond && $extracond2 && $supera[$i] != 1) {
			if($county < 1) {
				echo $tablest[1];
			}
			echo 'New Year record has been set! Record: ', $descrip[$i], ', <b>', conv($todayRecords[$i],$format[$i],1),
			'</b> &nbsp;(Previous: ', conv($yearRecords[$i],$format[$i],1), ' ',$prep[$i],$yearRecdate[$i], ')<br />';
			$county = $county + 1; $supery[$i] = 1;
		}
	}
	if($county >= 1 && $i+1 == count($todayRecords)) {
		echo '</td></tr></table></div>';
		debug_print_backtrace();
	}
}
for($i = 0; $i < count($todayRecords); $i++) { // Month Records
	if(mktime(0,0,0,1,intval($monthRecDay[$i])) == mktime(0,0,0,1,date('j')-1)) { $monthRecDate[$i] = 'Yesterday'; } else { $monthRecDate[$i] = $prep[$i].'Day '.$monthRecDay[$i]; }
	//if($i == 11) { $extracond = floatval($dayrn) > 0; $extracond2 = intval($consecdayswithrain) > 4; } else { $extracond = 2 < 3; $extracond2 = 2 < 4; }
	//if($i == 12) { $extracond2 = intval($dayswithnorain) > 4; } elseif($i != 11) { $extracond2 = 2 < 3; }
	if((floatval($todayRecords[$i]) < floatval($monthRecords[$i]) && $rectype[$i] == 0) || (floatval($todayRecords[$i]) > floatval($monthRecords[$i]) && $rectype[$i] == 1)) {
	if(intval($dday) > 5 && $extracond && $extracond2 && ($supery[$i] != 1) && ($supera[$i] != 1)) {
		if($countm < 1) { echo $tablest[0]; }
		echo 'New Month record has been set! Record: ', $descrip[$i], ', <b>', conv($todayRecords[$i],$format[$i],1),
		'</b> &nbsp;(Previous: ', conv($monthRecords[$i],$format[$i],1), ' ', $monthRecDate[$i], ')<br />';
		$countm = $countm + 1;
	} }
	if($countm >= 1 && $i+1 == count($todayRecords)) { echo '</td></tr></table></div>'; }
}
}
 */
//END
//echo '<br />';

//Bad browser warning
if(preg_match("/.*MSIE [5|6|7].*/", $browser)) {
	showStatusDiv('You are using a browser ('.$browser.') that is not compatible with nw3weather. Browse at your peril!
		Also, <a href="http://www.updatebrowser.net/">consider upgrading</a>.');
	log_events("BadBrowser.txt");
}
//echo $browser;

//Start old data warnings
$message = "A local hardware fault has been detected and is preventing data updates -
	the site administrator has been notified and will investigate ASAP.";
$message2 = 'Planned system maintenance is taking place - updates will resume shortly';

$diff = sysWDtimes();
echo "<!-- WDAge: $diff -->";

$planned = false; //Change to true if maintenance planned
if($planned) { showStatusDiv($message2); }

if(!$planned && $diff > 900) { // 15mins downtime before alert message is triggered
	showStatusDiv( $message .' &nbsp;
	   System time: '. date('d/m/y, H:i T', $timestampWD) .
	   '; &nbsp; Server time: '. date('d/m/y, H:i T') .
	   '<br />Problem Age: '. round($diff/60) .' mins ('. round($diff/3600). ' hours)'
	);
	$mail5 = true;
}
 ?>