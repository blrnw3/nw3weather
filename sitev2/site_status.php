<?php

if( $recordwindspeed > 35 ) {
	 $maxavgspd = $maxgst * 0.64;
	 $mrecordwindspeed = 15.2;
	 $yrecordwindspeed = $mrecordwindspeed;
	 $recordwindspeed = 27.9;
	 $mrecordhighavwindday = 7;
	  $yrecordhighavwindday = 3;
	   $recordhighavwindday = 3;
	 $yrecordhighavwindmonth = 1;
	  $recordhighavwindmonth = 1;
	  $recordhighavwindyear = 2012;
}

if($file == 6) { $colr = '#E3CEF6'; } else { $colr='#4B088A'; }
//$depth5 = 8; $depth6 = 3; $depth10 = 4; $depth11 = 3; $depth12 = 2;
//function depth($depth) { global $unitT; if($unitT == 'F') { $depth = round($depth/2.54).'</b> in.'; } else { $depth .= '</b> cm'; } return $depth; }
//<b>Site Version 2:</b> As of 10th September, nw3weather has undergone a site upgrade - new features, pages and functionality have been addded, along with a major technical upgrade to the data processing.<br />
//Please use the <a href="contact.php">contact page</a> to report any errors/glitches, and to send comments and suggestions. The old site is now inactive.
//<br /><span style="font-size:85%"><a href="wx8.php#upgrade">View more information on the technical details of the upgrade</a></span>
//echo '<b>Weather Station Fault, 15z 07.01.12:</b> An unidentifiable problem is preventing any data from being available. This is currently being investigated. <br />
/*echo '<b>Snowfall:</b> Please note that this weather station has no automatic equipment for recording snowfall - neither quantity nor lying depth.
	Maunal estimates are made, and these will be noted here. Rain-equivalent totals based on depth measurements and snow-melt will be periodically
	added to the Monthly &#39;rain&#39; figures, and daily totals adjusted accordingly.
	<br /><b>Snow depths</b> (approx, at 09z): 5th - <b>', depth($depth5), ', 6th - <b>', depth($depth6), ', 7th - <b>trace</b>, 8th-9th - <b>none</b>, 10th - <b>',
		depth($depth10), ', 11th - <b>', depth($depth11), ', 12th - <b>', depth($depth12), ', 13th - <b>none</b>.'; */
//echo '<b>Snow report, 19z 18th Jan:</b> Snow depth here is ~<b>4cm</b>. This translates to ~4mm rain-equivalent (snow melt).<br />
//	<b>Update, 22z 20th:</b> Level depth is now ~<b>11cm</b>, up from ~3cm at 09z. Snow has been falling since 08z and is now stopping.';
//echo "<b>Unscheduled Downtime: </b>Recent network issues appear to have stopped presenting, but reliable service is not expected until approx. 6th March";
echo '<table style="border: solid 2px #088A4B" width="95%" align="center"><tr><td align="center" style="color:#4B088A">';
echo '<b>Old Site (version 2):</b> You are viewing an old version of nw3weather. <a href="/">View current version.</a>';
echo '</td></tr></table>';
//if($file == 12) { echo '<b>Snow melt: </b>Please be advised that melting snow is not distinguished from rain, and may therefore cause erroneous reports'; } 

//Script to display when a record is set
/*
include('fnctnrec.php');
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
for($a=0;$a<3;$a++) { $tablest[$a] = '<div align="center"><table style="border: 3px '. $bstyle. '" width="950" align="center" cellpadding="4">
	<tr bgcolor="' .$col[$a].'"><td align="center" style="color:#670F03;font-size:115%">';	}
for($i = 0; $i < count($todayRecords); $i++) { // All-time records
	if((floatval($todayRecords[$i]) < floatval($alltRecords[$i]) && $rectype[$i] == 0) || (floatval($todayRecords[$i]) > floatval($alltRecords[$i]) && $rectype[$i] == 1)) {
		if($counta < 1) { echo $tablest[2]; }
		echo 'New All-time record has been set! Record: ', $descrip[$i], ', <b>', conv($todayRecords[$i],$format[$i],1),
		'</b> &nbsp;(Previous: ', conv($alltRecords[$i],$format[$i],1), ' ',$prep[$i], date('jS M Y',mktime(0,0,0,$alltRecMonth[$i],$alltRecDay[$i],$alltRecYear[$i])), ')<br />';
		$counta = $counta + 1; $supera[$i] = 1;
	}
	if($counta >= 1 && $i+1 == count($todayRecords)) { echo '</td></tr></table></div>'; }
}
$dmon = date('n'); if(isset($_GET['test'])) { $dmon = 2; }

for($i = 0; $i < count($todayRecords); $i++) { // Year Records
	//if(isset($_GET['test'])) {
		if(mktime(0,0,0,1,intval($yearRecDay[$i])) == mktime(0,0,0,1,date('j')-1) && intval($yearRecMonth[$i]) == date('n')) {
			$yearRecdate[$i] = 'Yesterday'; $prep[$i] = ' '; } else { $yearRecdate[$i] = date('jS F',mktime(0,0,0,intval($yearRecMonth[$i]),intval($yearRecDay[$i]))); 
		}
		if(mktime(0,0,0,1,intval($yearRecDay[$i])) == mktime(0,0,0,1,date('j')) && intval($yearRecMonth[$i]) == date('n')) { $yearRecdate[$i] = 'Midnight'; $prep[$i] = 'at '; }
		if($prep[12] != 'up to ') { $yearRecdate[12] = 'Yesterday'; $yearRecords[12] = $yearRecords[12]-1; $prep[12] = 'up to '; } 
	//}
	if($i == 11) { $extracond = floatval($dayrn) > 0; $extracond2 = intval($consecdayswithrain) > 4; } else { $extracond = 2 < 3; $extracond2 = 2 < 4; }
	if($i == 12) { $extracond2 = intval($dayswithnorain) > 4; } elseif($i != 11) { $extracond2 = 2 < 3; }
	if((floatval($todayRecords[$i]) < floatval($yearRecords[$i]) && $rectype[$i] == 0) || (floatval($todayRecords[$i]) > floatval($yearRecords[$i]) && $rectype[$i] == 1)) {
	if((($dmon == 2 && intval($date_day) > 5) || $dmon > 2) && $extracond && $extracond2 && $supera[$i] != 1) {
		if($county < 1) { echo $tablest[1]; }
		echo 'New Year record has been set! Record: ', $descrip[$i], ', <b>', conv($todayRecords[$i],$format[$i],1),
		'</b> &nbsp;(Previous: ', conv($yearRecords[$i],$format[$i],1), ' ',$prep[$i],$yearRecdate[$i], ')<br />';
		$county = $county + 1; $supery[$i] = 1;
	} }
	if($county >= 1 && $i+1 == count($todayRecords)) { echo '</td></tr></table></div>'; }
}

for($i = 0; $i < count($todayRecords); $i++) { // Month Records
	if(mktime(0,0,0,1,intval($monthRecDay[$i])) == mktime(0,0,0,1,date('j')-1)) { $monthRecDate[$i] = 'Yesterday'; } else { $monthRecDate[$i] = $prep[$i].'Day '.$monthRecDay[$i]; }
	//if($i == 11) { $extracond = floatval($dayrn) > 0; $extracond2 = intval($consecdayswithrain) > 4; } else { $extracond = 2 < 3; $extracond2 = 2 < 4; }
	//if($i == 12) { $extracond2 = intval($dayswithnorain) > 4; } elseif($i != 11) { $extracond2 = 2 < 3; }
	if((floatval($todayRecords[$i]) < floatval($monthRecords[$i]) && $rectype[$i] == 0) || (floatval($todayRecords[$i]) > floatval($monthRecords[$i]) && $rectype[$i] == 1)) {
	if(intval($date_day) > 5 && $extracond && $extracond2 && ($supery[$i] != 1) && ($supera[$i] != 1)) {
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
echo '<br />';
if(5-(date("i")-0)%5 < 1): $son ='s'; endif;

//Start corrupted data warnings
if($baro < 10 && $file == 1) {
	echo '<p class="big"><b>The latest data source file was corrupted during upload - Next upload in approx: ';
	if(date("s")<40): echo 40-date("s"); else: echo 100-date("s"); endif;
	echo ' s<br /><br />Thank you for your patience.</b></p>';
	$mail3 = 1;
}
 elseif($baro < 10 && $file > 3) {
	echo '<p class="big"><b>The latest data source file was corrupted during upload - Next upload in approx: ';
	if(date("s")<48 && date("i")%5 == 0): echo 48-date("s"); elseif(date("s")<48): echo 5-(date("i")-0)%5, ' minute',$son, ', ', 48-date("s");
	else: echo 4-(date("i")-0)%5, ' minute', $son, ', ', 108-date("s"); endif;
	echo ' s<br /><br />Thank you for your patience.</b></p>';
	$mail4 = 1;
}
//END
	
//Start old data warnings
$message = "A local hardware fault has been identified and is preventing updates -
 the site administrator has been emailed and will be along shortly to investigate; the server time is ";
//$message = "A local hardware fault has been identified and is preventing updates -
//	If the issue is not self-correcting, updates will not resume until the problem can be fixed manually.
// I am away until 14th September and unlikely to be able to solve the problem remotely; apologies for the potential disruption.";
$message2 = 'Planned maintenance taking place - updates will resume shortly';
 
$sys = mktime($time_hour,$time_minute,0,$date_month,$date_day,$date_year);
$planned = false; //Change to true if maintenance planned
if($planned) { echo $message2; }

if($baro > 900 && $file > 1 && !$planned) {
	if(mktime($time_hour,$time_minute,0,$date_month,$date_day,$date_year)+3000 < date("U")) {
		 echo $message, date("d/m/y, H:i T"), '<br />';
		 echo 'System timestamp: ', $sys, ', Server timestamp: ', date("U"), '; Difference: ', -$sys + date("U"), 's';
		 $mail5 = 1;
	}
	if(mktime($time_hour,$time_minute,0,$date_month,$date_day,$date_year)+350 < date("U") && !$planned) { //Missed upload of phptags
		//$mail6 = 1;
	}
}

if($baro > 900 && $file < 2 && !$planned) { //Home page old data
	if(mktime($time_hour,$time_minute,0,$date_month,$date_day,$date_year)+1250 < date("U")) {
		 echo $message, date("d/m/y, H:i T"), '<br />';
		 echo 'System timestamp: ', $sys, ', Server timestamp: ', date("U"), '; Difference: ', -$sys + date("U"), 's';
		 $mail7 = 1;
	}
}
//END
 ?>