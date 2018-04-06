<?php
//$statusMessage = 'Site update due to be completed on 22nd May';
//$statusMessage = '<b>Site update, 22nd May:</b> I have <a href="news.php" title="details of the upgrade"> made a few changes</a>
// to nw3weather. Enjoy.';
//$statusMessage = 'There may be some downtime today due to planned upgrades to the local network infrastructure';
//$statusMessage = 'Due to planned weather station maintenance this weekend, some sensor readings will be inaccurate until work is complete';
//$statusMessage = '<a href="./news.php#post-20140330" title="Details of the work carried out">Weather station maintenance</a> is now complete, so sensors should be reporting normally';
//$statusMessage = '<b>'.date('H:i d M Y: ', mktime(20,12,0,3,31,2014)) . '</b>'. 'Downtime was due to a local power cut at 11:30 this morning. Now restored with full data recovery.';
//$statusMessage = '<b>08z '. date('d M: ', mktime(8,12,0,2,3,2015)) . '</b>'. '2.5 cm lying snow on the ground at nw3weather HQ. <i>NB: the rain gauge can\'t record snowfall until it melts</i>.';
//$statusMessage = '<b>'. date('d M: ', mktime(8,12,0,5,11,2015)) . '</b>'. 'Absence of live wind data is being investigated. More info tonight';
//$statusMessage = '<b>7pm '. date('d M: ', mktime(8,12,0,5,11,2015)) . '</b>'. 'Live wind data restored after six days downtime due to a battery failure whilst I was away';
//$statusMessage = '<b>11am '. date('d M: ', mktime(11,12,0,7,24,2015)) . '</b>'. 'The temperature/humidity sensor failed at 7am this morning. Data for these measures is currently being sourced from a <a href="http://weather.stevenjamesgray.com/realtime.txt">nearby local weather station</a>. I will attempt to fix the faulty sensor at the weekend.';
//$statusMessage = '<b>11am '. date('d M: ', mktime(11,12,0,7,25,2015)) . '</b>'. 'Temperature/humidity data restored after sensor failure yesterday at 7am. The fault was due to a poor connection at the battery terminals and has now been resolved.';
//$statusMessage = '<b>9am '. date('d M: ', mktime(11,12,0,9,4,2015)) . '</b>'. 'The local network exchange is working again so normal service has resumed. No data was compromised during the outage.';
//$statusMessage = '<b>Webcam glare:</b> I am aware of problems with glare and poor focus with both webcams. This is because they were both unavoidably relocated on Sunday afternoon. I will look to mitigate these problems on Friday.';
//$statusMessage = '<b>Webcam relocation:</b> Both webcams have been relocated one floor up but facing the same direction. Earlier problems with glare and poor focus have now been resolved.';
//$statusMessage = '<b>5pm '. date('d M: ', mktime(17,52,0,12,8,2015)) . '</b>'. 'The local network exchange is up again so normal service has resumed. No data was compromised during outage.';
//$statusMessage = '<b>7am '. date('d M: ', mktime(6,52,0,12,8,2015)) . '</b>'. 'The local network exchange <a href="http://status.zensupport.co.uk/active/3/4500">is down</a> so local data cannot reach the web server. Service should resume once BT fixes the problem.';
//$statusMessage = '<b>1pm '. date('d M: ', mktime(6,52,0,2,11,2016)) . '</b>'. 'The wind sensor is not functioning, and has been down since 1pm on the 10th. I will try to fix it this weekend<br />
//<b>Update, 11pm 15 Feb:</b> The sensor is not repairable for the foreseeable future (probably not until mid-2017). In its long absence, wind data is being served from a nearby weather station, <a href="http://www.harpendenweather.co.uk">Harpenden weather</a>. All other sensors are currently working fine';
//$statusMessage = '<p><b>UPDATE 9pm 14th Apr: nw3weather is back! Updates to follow.</b><br /><b>'. date('d M: ', mktime(6,52,0,4,12,2016)) . '</b>'. 'All sensors are currently not functioning. No live data is available.<br />Meantime, data is being served from nearby weather stations, <a href="http://www.harpendenweather.co.uk">Harpenden weather</a> for wind  <a href="http://weather.casa.ucl.ac.uk">UCL casa</a>data, and <a href="http://weather.casa.ucl.ac.uk">UCL casa</a> for the rest.</p>';
//$statusMessage = '<b>15th Apr</b>: ALL sensors at nw3weather are now reporting correctly. <a href="news.php">Read more about the downtime</a>';
//$statusMessage = '<b>5th Sep 2017, New weather station</b>: <a href="/news.php" title="Read more">Data is now coming from the new sensors I installed on 4th Sep</a>. Enjoy. <a href="/wx_albgen.php?albnum=6&view=Full">See photos</a>';
$statusMessage = '<b>'. date('H:i d M: ', mktime(12,30,0,3,29,2018)) . '</b>'. 'Having some weather server problems today. Slow updates, no webcam, some missing data. Will investigate tonight';
$showMessage = false;
$isBad = true;
// echo "<div class='statusBox info'><b>10am, Mon 16th May</b>: The thermo/hygro sensor is functioning again</div>";
//echo "<div class='statusBox warning' style='background-color: #fc5;'>nw3weather is undergoing server maintenance this weekend. The site may be down for several hours at a minimum. <br />I am moving from a shared hosted solution to a cheaper, more powerful and more flexible cloud VM host</div>";
//echo "<div class='statusBox info'><b>10pm, 8th May</b>: Server maintenance has completed successfully.<br />I have moved from a shared hosting solution to a cheaper, more powerful and more flexible cloud VM host<br /><a href='/contact.php'>Please do report any issues that may have resulted from this</a></div>";
//echo "<div class='statusBox info'><b>4am, 4th Sep 2016</b>: The thermo/hygro sensor is failing to report. Temp/hum data is being served from the weather station at <a href='http://weather.casa.ucl.ac.uk'>UCL casa</a> in Bloomsbury, W1, whilst I seek a fix. <br /> <b>Update, 10pm 11th Sep:</b> Attempts to revive the sensor have failed. I have bought a new sensor and hope to have it installed in the next couple of weeks.<br /> <b>FIXED, 3pm 17th Sep:</b> The new sensor has been installed and appears to be working. Temperature and Humidity data is back!</div>";
//echo "<div class='statusBox info'><b>FIXED, 3pm 17th Sep:</b> The new sensor has been installed and appears to be working. Temperature and humidity data is back! <br /><a href='news.php'>Read more</a></div>";
if($showMessage) {
	showStatusDiv($statusMessage, $isBad);
}
//echo "<div class='statusBox info' style=''><b>Weather station replacement</b>: This weekend I am replacing the weather station with an entirely new one, a Davis VP2. This will improve reliabilty and data quality. Note well that during the upgrade, data may not be accurate.<br /><b>UPDATE 9pm Sunday</b>: Data from the new weather station is now LIVE. Enjoy. <br />I will post pics of the process and of the new hardware tomorrowish.</div>";
//if ($file == 13) echo "<div class='statusBox warning'><b>Broken sensor</b>: As of 8th Feb 2016 the wind sensor is broken. In its long absence, wind data is being served from a nearby weather station, <a href='http://www.harpendenweather.co.uk'>Harpenden weather</a>.</div>";
# if ($file == 12) echo "<div class='statusBox warning'><b>Phantom rain</b>: The rain sensor appears to be intermittently misreporting rainfall. These phantom readings started in Jan '17 and appear to be getting worse. I will correct any misreadings as soon as I spot them.</div>";
//if ($file == 12) echo "<div class='statusBox warning'><b>Broken sensor</b>: The rain sensor has ceased to function as of approx 12 March. I aim to replace it in September. Meantime, data is served from nearby Bloomsbury, courtesy of <a href='http://weather.casa.ucl.ac.uk'>UCL casa</a>.</div>";

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
