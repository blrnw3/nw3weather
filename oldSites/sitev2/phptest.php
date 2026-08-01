<?php require('unit-select.php');

include_once('changecss.php');
$SITE['CSSscreen'] = validate_style_choice();

$client = file('clientraw.txt');
$live = explode(" ", $client[0]);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php"); include("fnctnrec.php");
	$file = 111; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - PHP test</title>

	<meta name="description" content="Old v2 - PHP script testing for NW3 weather" />

	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
	<link rel="stylesheet" type="text/css" href="<?php echo $SITE['CSSscreen']; ?>" media="screen" title="screen" />
	<link rel="stylesheet" type="text/css" href="weather-print.css" media="print" />
	<link rel="stylesheet" type="text/css" href="weather-screen2.css" media="screen" title="screen" />
	<link rel="stylesheet" type="text/css" href="wxreports.css" media="screen" title="screen" />
	
<?php
$_SESSION['count'][$file] = $_SESSION['count'][$file] + 1;
if($auto == 'on') {
	if($_SESSION['count'][$file] < 8) {
		if($baro >= 0) {
			if(date("s")<48 && date("i")%5 == 0): $reftime = 48-date("s"); elseif(date("s")<48): $reftime = 60* (5-(date("i")-0)%5) + 48-date("s");
			else: $reftime = 60* (4-(date("i")-0)%5) + 108-date("s"); endif;
			echo '<meta http-equiv="refresh" content="', $reftime+1, '" />';
		}
	}
}
?>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/
libs/jquery/1.3.0/jquery.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
 	 $("#newdatahead").load("./ajax/wx-body.php");
	var refreshId = setInterval(function() {
	$("#newdatahead").load('./ajax/wx-body.php');
   }, 5000);
});
</script>

<?php include_once("ggltrack.php") ?>
</head>

<body id="worn">
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<? require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main-copy">
<div id="newdatahead">Testing...</div>
<?php
//Script to display when a record is set
if($mrecordlowhum > 10 && $Rmrecordlowhum > 10) { //prevent displaying when data is corrupted
$todayRecords = array($mintemp,$minwindch,$lowhum,$mindew,$lowbaro,$maxtemp,$highhum,$maxdew,$highbaro,$dayrn,$maxhourrn,$consecdayswithrain,$dayswithnorain,$maxgst,$maxavgspd,$mintemp,$maxtemp);$monthRecords = array($Rmrecordlowtemp,$Rmrecordlowchill,$Rmrecordlowhum,$Rmrecordlowdew,$Rmrecordlowbaro,$Rmrecordhightemp,$Rmrecordhighhum,$Rmrecordhighdew,$Rmrecordhighbaro,$Rmrecorddailyrain,$Rmhrrecordrainrate,$Rmrecorddayswithrain,$Rmrecorddaysnorain,$Rmrecordwindgust,$Rmrecordwindspeed,-100,100);
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
for($i = 0; $i < count($todayRecords); $i++) {
	if((floatval($todayRecords[$i]) < floatval($alltRecords[$i]) && $rectype[$i] == 0) || (floatval($todayRecords[$i]) > floatval($alltRecords[$i]) && $rectype[$i] == 1)) {
		if($counta < 1) { echo $tablest[2]; }
		echo 'New All-time record has been set! Record: ', $descrip[$i], ', Value: ', conv($todayRecords[$i],$format[$i],1),
		' &nbsp;(Previous: ', conv($alltRecords[$i],$format[$i],1), ' ',$prep[$i], date('jS M Y',mktime(0,0,0,$alltRecMonth[$i],$alltRecDay[$i],$alltRecYear[$i])), ')<br />';
		$counta = $counta + 1; $supera[$i] = 1;
	}
	if($counta >= 1 && $i+1 == count($todayRecords)) { echo '</td></tr></table></div>'; }
}
for($i = 0; $i < count($todayRecords); $i++) {
	if($i == 11) { $extracond = floatval($dayrn) > 0; $extracond2 = intval($consecdayswithrain) > 2; } else { $extracond = 2 < 3; $extracond2 = 2 < 4; }
	if($i == 12) { $extracond2 = intval($dayswithnorain) > 2; } elseif($i != 11) { $extracond2 = 2 < 3; }
	if((floatval($todayRecords[$i]) < floatval($yearRecords[$i]) && $rectype[$i] == 0) || (floatval($todayRecords[$i]) > floatval($yearRecords[$i]) && $rectype[$i] == 1)) {
	if(intval($date_day) > 5 && intval($date_month) > 1 && $extracond && $extracond2 && $supera[$i] != 1) {
		if($county < 1) { echo $tablest[1]; }
		echo 'New Year record has been set! Record: ', $descrip[$i], ', Value: ', conv($todayRecords[$i],$format[$i],1),
		' &nbsp;(Previous: ', conv($yearRecords[$i],$format[$i],1), ' ',$prep[$i], date('jS F',mktime(0,0,0,$yearRecMonth[$i],$yearRecDay[$i])), ')<br />';
		$county = $county + 1; $supery[$i] = 1;
	} }
	if($county >= 1 && $i+1 == count($todayRecords)) { echo '</td></tr></table></div>'; }
}
for($i = 0; $i < count($todayRecords); $i++) {
	if(mktime(0,0,0,1,intval($monthRecDay[$i])) == mktime(0,0,0,1,date('j')-1)) { $monthRecDate[$i] = 'Yesterday'; } else { $monthRecDate[$i] = $prep[$i].'Day '.$monthRecDay[$i]; }
	if($i == 11) { $extracond = floatval($dayrn) > 0; $extracond2 = intval($consecdayswithrain) > 2; } else { $extracond = 2 < 3; $extracond2 = 2 < 4; }
	if($i == 12) { $extracond2 = intval($dayswithnorain) > 2; } elseif($i != 11) { $extracond2 = 2 < 3; }
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
//END
?>
<h1>PHP test page</h1>

<a href="data.csv">Data csv</a>
<br />
<a href="
<?php
if($time_hour > 21) {
	echo $date_year,$date_month,$date_day;
}
else {
	echo date("Ymd", mktime(0,0,0,$date_month,$date_day-1,$date_year));
} ?>dayvideo.wmv"><? echo $tl_day; ?>'s timelapse</a>
<h2>There are <span style="font-weight:bold;font-size:120%;color:blue"><?php echo round((mktime(12,0,0,6,4,2012)-time())/86400) ?></span> days until the end of NST! </h2>
<h3>Latest Graph</h3>
<?php $hor = 'hide'; if(!isset($_GET['show']) && (isset($_GET['hide']) or $_SESSION['count'][$file] > 3)) { $hor = 'show'; } ?>
<a href="phptest.php?<?php echo $hor; ?>"><?php echo $hor; ?> images</a>
<?php if(!isset($_GET['show']) && (isset($_GET['hide']) or $_SESSION['count'][$file] > 3)) { echo '<!--'; } ?>
<img src="<?php echo date("Ymd", mktime(0,0,0,$date_month,$date_day-1,$date_year)); ?>
.gif" alt="daily graph" />
<br />
<img src="<?php echo date("Ymd", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'dailywebcam'; ?>
.gif" alt="daily webcam image summary" />
<img src="<?php echo date("Ymd", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'dailywebcam2'; ?>
.gif" alt="daily webcam image summary 2" />
<?php if(!isset($_GET['show']) && (isset($_GET['hide']) or $_SESSION['count'][$file] > 3)) { echo '-->'; } ?>

<br /><br />
<script type="text/javascript">
<!--
function showElapsedTimea() {
	var startHour = <?php echo $live[29]; ?>; var startMinute = <?php echo $live[30]; ?>; var startSecond = <?php echo $live[31]; ?>;
	var d = new Date(); var Uhour = d.getUTCHours(); var Uminute = d.getUTCMinutes(); var Usecond = d.getUTCSeconds();
	var dayTimeUTC = Uhour*3600 + Uminute*60 + Usecond;
	var dayTimeServer = startHour*3600 + startMinute*60 + startSecond;
	var elapsedSeconds = -dayTimeServer + dayTimeUTC + 2 + <?php if(date("I") == 1) { echo 3600; } else { echo 0; } ?>;
	//if (elapsedSeconds > 99) { elapsedSeconds = '>99' }
	if (elapsedSeconds < 0) { elapsedSeconds = 0 }
	var newdata = ''; if (elapsedSeconds < 5) { newdata = '  - NEW!'; }
	document.getElementById('elapsedTime').innerHTML = elapsedSeconds + ' s ago' + newdata; t = setTimeout('showElapsedTimea()',1000); 
}
// -->
</script>
<table cellpadding="5" cellspacing="0" width="99%">
<tr class="table-top">
<td><b>Current - <span id="elapsedTime"><script>
<!--
showElapsedTimea()
//-->
</script> </span></b></td></tr></table><br />

<?php
// IP-pattern detection script
/*
$pregtest = array('66.249.38.72', '212.45.66.249', '66.249.1.345', '109.154.34.123');
for($i=0;$i<=3;$i++) {
	if(preg_match('/^66\.249/',$pregtest[$i]) > 0) { echo 'match'; } else { echo 'not a match'; } echo '---';
	$tester[$i] = strpos($pregtest[$i],'66.249');
	if($tester[$i] === 0) { echo 'match-',$tester[$i]; } else { echo 'not a match'; } echo '<br />'; 
	//NB: === is required to match value AND type (float rather than bool)
} 
*/
echo 'Session count: ', $_SESSION['count'][$file], '<br />';
$scriptbeg = microtime(get_as_float);
//display("wugrab.html", " ", 626, 627, 2, 5);

//Get WU view count
$filwu = file("wugrab.html");
for ($i = 500; $i < 750; $i++) {
	if(strpos($filwu[$i],"Viewed") > 0) {
		$wuvul = explode(" ", $filwu[$i]);
	}
}
$wuvu = $wuvul[3];
echo $wuvu;
echo ' views of WU page<br />';

//Link to EGLC pressure
echo '<a href="http://www.wunderground.com/history/airport/EGLC/', date('Y/n/d',mktime(1,1,1,date('n'),date('d')-1)), '/DailyHistory.html">EGLC History for yesterday</a><br />';

//Get data from custom log
echo '<b>Cutom log process output</b><br />';
$filcust = file('customtextout.txt');
for($i = 1; $i < count($filcust); $i++) {
	$custl[$i] = explode(' ', ltrim(str_ireplace('  ', ' ', $filcust[$i])));
	$rn10[$i] = floatval($custl[$i][5]);
	$wind10[$i] = round(floatval($custl[$i][7])*1.152,1);
	$wind60[$i] = round(floatval($custl[$i][8])*1.152,1);
	$tchangehr[$i] = floatval($custl[$i][9]);
	$hchangehr1 = $custl[$i][10]; $hchangehr2 = $custl[$i][11];
	if($hchangehr1 == '+') { $hchangehr[$i] = floatval($hchangehr2); $t10temp[$i] = floatval($custl[$i][14]); }
	else { $hchangehr[$i] = floatval($hchangehr1); $t10temp[$i] = floatval($custl[$i][13]); }
	$custmin[$i] = $custl[$i][1];
	$custhr[$i] = $custl[$i][0];
	//if($i > 10) { $t10[$i] = floatval($custl[$i][9])-floatval($custl[$i-10][9]); }
	if($i > 10) { $t10[$i] = $t10temp[$i]-$t10temp[$i-10]; }
}
$rn10max = max($rn10); $rn10maxt = $custhr[array_search($rn10max,$rn10)].':'.$custmin[array_search($rn10max,$rn10)];
	if ($rn10max == 0) { $rn10max2 = ''; $rn10maxt = 'n/a'; } else { $rn10max2 = $rn10max; }
$wind10max = max($wind10); $wind10maxt = $custhr[array_search($wind10max,$wind10)].':'.$custmin[array_search($wind10max,$wind10)];
$wind60max = max($wind60); $wind60maxt = $custhr[array_search($wind60max,$wind60)].':'.$custmin[array_search($wind60max,$wind60)];
$tchangehrmax = max($tchangehr); $tchangehrmaxt = $custhr[array_search($tchangehrmax,$tchangehr)].':'.$custmin[array_search($tchangehrmax,$tchangehr)];
$hchangehrmax = max($hchangehr); $hchangehrmaxt = $custhr[array_search($hchangehrmax,$hchangehr)].':'.$custmin[array_search($hchangehrmax,$hchangehr)];
$tchangehrmin = min($tchangehr); $tchangehrmint = $custhr[array_search($tchangehrmin,$tchangehr)].':'.$custmin[array_search($tchangehrmin,$tchangehr)];
$hchangehrmin = min($hchangehr); $hchangehrmint = $custhr[array_search($hchangehrmin,$hchangehr)].':'.$custmin[array_search($hchangehrmin,$hchangehr)];
$t10min = min($t10); $t10mint = $custhr[array_search($t10min,$t10)].':'.$custmin[array_search($t10min,$t10)];
$t10max = max($t10); $t10maxt = $custhr[array_search($t10max,$t10)].':'.$custmin[array_search($t10max,$t10)];
echo 'First line: ', $filcust[1], '<br />';
echo 'Rain10 max: ', $rn10max, ' at ', $rn10maxt, '<br />';
echo 'Wind10 max: ', $wind10max, ' at ', $wind10maxt, '<br />';
echo 'Wind60 max: ', $wind60max, ' at ', $wind60maxt, '<br />';
echo 'TchangeHr max: ', $tchangehrmax, ' at ', $tchangehrmaxt, '<br />';
echo 'HchangeHr max: ', $hchangehrmax, ' at ', $hchangehrmaxt, '<br />';
echo 'TchangeHr min: ', $tchangehrmin, ' at ', $tchangehrmint, '<br />';
echo 'HchangeHr min: ', $hchangehrmin, ' at ', $hchangehrmint, '<br />';
echo 'Tchange10 min: ', $t10min, ' at ', $t10mint, '<br />';
echo 'Tchange10 max: ', $t10max, ' at ', $t10maxt, '<br />';

//Display data from a file
function display($file, $char, $s1, $e1, $s2, $e2) {
	echo '<h3>'. $file. ' data: </h3>';
	$client = file($file);
	for ($l = $s1; $l < $e1; $l++) {
		echo 'Line '.$l.' = ';
		$client_get = explode($char, $client[$l]);
		for ($i = $s2; $i < $e2; $i++) {
			echo 'sec ', $i, ' is ', $client_get[$i], ', ';
		}
		echo '<br />';
	}
}

//Get yesterday extreme values which have no WD custom tag
$filex = file('wx18.html');
$filex2 = file('wx19.html');
$extrad1 = explode("=", $filex2[13]);
$extrad2 = explode("=", $filex[13]);
$extrad3 = explode("=", $filex[15]);
$nmin = floatval($extrad1[1]);
$dmax = floatval($extrad2[1]);
$mrain = floatval($extrad3[1]); if ($mrain == 0) { $mrain = ''; } $mrain2 = floatval($extrad3[1]);
$mryest = $maxrainrateyesthr; if ($mryest == 0) { $mryest = ''; } $mryest2 = $maxrainrateyesthr;
if(floatval($ystdyrain) == 0.3) { $mryest = '-'; }


//display("clientraw.txt", " ", 0, 4, 0, 50);
//display("June2011.htm", " ",0,50,1,13);

function degname2($winddegree) {
	$windlabels = array ("N", "NE", "E", "SE", "S", "SW", "W", "NW", "N");
	$windlabel = $windlabels[ round($winddegree / 45, 0) ];
	return "$windlabel";
}

//Function to get data from monthyyyy.htm files
function gethistory($file) {
	$data = file($file);
	$end = 1200;
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

//Get parameters from monthyyyy.htm files for writng to data.csv
$names = array('Hmax','Hmin','Dmax','Dmin','Tmax','Tmin','Tave','Dave','Wave','Have','Rain','Wdir','Tmin-t','Tmax-t','Hmin-t','Hmax-t','Wdir2','Pmin','Pmax','Pave');
$report = date("F", mktime(0,0,0,$date_month,$date_day-1)).date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)).'.htm';
$tempvar = gethistory($report);
for ($i = 0; $i < 20; $i++) {
	echo '<b>', $names[$i], ':</b> ';
	for($d = 1; $d <= date("j", mktime(0,0,0,$date_month,$date_day-1)); $d++) {
		echo $tempvar[$i][$d].'; ';
	}
	echo '<br />';
}

//Check if yesterday's data has been written to data.csv to prevent duplicate line-writing
$check = file("data.csv");
$fl = intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,1,1,2012))/(24*3600))+1;
$icheck = explode(",", $check[$fl]);
echo $icheck[0].', '.$icheck[1], '---', $fl, '/', $fl2;

//Find average times of extremes for writing to data.csv
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
if(date('H',mktime($avhmaxt)) > date('H',mktime($sunrise))+4) { $avhmaxt .= '*'; }
if(intval($time9[1]) == 0 && intval($time9[0]) == 0) { $avhmaxt = "23:59*"; }
if($nmin < $tminy) { $avtmint = '21:00*'; }
echo '<br />Write daily data to CSV (last 10 lines displayed) <br />';

 //Parameters to be written to daily data CSV 
$list = array(date("d", mktime(0,0,0,$date_month,$date_day-1)), date("m", mktime(0,0,0,$date_month,$date_day-1)),
			$tminy, $tmaxy, $tempvar[6][$date_day_yest], $tmaxy-$tminy, '', $minhumyest, $maxhumyest, $tempvar[9][$date_day_yest], $maxhumyest-$minhumyest, '',
			$bminy, $bmaxy, '', '', '', $tempvar[8][$date_day_yest], $maxaverageyestnodir, $maxgustyestnodir, $tempvar[11][$date_day_yest], '',
			floatval($ystdyrain), $mrain, $rn10max2, '', '', $mryest, '', floatval($mindewyest), floatval($maxdewyest), $tempvar[7][$date_day_yest],
			$nmin, $dmax, $mintempyestt, $maxtempyestt, $minhumyestt, $maxhumyestt, $maxgustyestt,
			substr($tempvar[12][$date_day_yest],0,5), substr($tempvar[13][$date_day_yest],0,5), substr($tempvar[14][$date_day_yest],0,5), substr($tempvar[15][$date_day_yest],0,5),
			$avtmint, $avtmaxt, $avhmint, $avhmaxt, $wuvu, $wind10max, $time, ' \n');

//Write data to CSV for use in Daily Data yyyy.xls
if ($icheck[0] != date("d", mktime(0,0,0,$date_month,$date_day-1)) && ((mktime($time_hour, $time_minute) > mktime(0,0)+20*60 && $time_hour != 23) || isset($_GET['overide']))) {		
	$file = fopen("data.csv","a");
	fputcsv($file, $list);
	fclose($file);
}

//Read output of above CSV for
$fil2 = fopen('data.csv', 'r'); $cntr = 0;
	while ( !feof($fil2) ) {
		if($cntr > $fl-10) { echo fgets($fil2), '<br />'; } else { fgets($fil2); }
		$cntr = $cntr + 1;
	}
fclose($fil2);

//Read output of each day's data for use in Daily Data yyyy.xls 
$listlength = count($list);
//echo $listlength, '<br />';
//for ($i = 0; $i < $listlength; $i++) {
//	echo 'Item '. $i. ' = '.$list[$i], '<br />';
//}

//Check mktime function which is used in CSV writing
echo '<br />'.date("m", mktime(0,0,0,$date_month,$date_day-1));
echo '<br />'.date("d", mktime(0,0,0,$date_month,$date_day-1));
echo '<br />';
echo 'data.csv is ', $fl, ' lines long';
echo '<br /><br />';

//Check that correct monthly history report is being read, and check that it uploaded
echo $report;
echo '<br />Modified: ', date("H:i, d M",filemtime($report));

echo '<br /><br />';

$todayRecords = array($mintemp,$minwindch,$lowhum,$mindew,$lowbaro,$maxtemp,$highhum,$maxdew,$highbaro,$dayrn,$maxhourrn,$consecdayswithrain,$dayswithnorain,$maxgst,$maxavgspd,$mintemp,$maxtemp);
$monthRecords = array($mrecordlowtemp,$mrecordlowchill,$mrecordlowhum,$mrecordlowdew,$mrecordlowbaro,$mrecordhightemp,$mrecordhighhum,$mrecordhighdew,$mrecordhighbaro,$mrecorddailyrain,$mhrrecordrainrate,$mrecorddayswithrain,$mrecorddaysnorain,$mrecordwindgust,$mrecordwindspeed,100,100);
$yearRecords = array($yrecordlowtemp,$yrecordlowchill,$yrecordlowhum,$yrecordlowdew,$yrecordlowbaro,$yrecordhightemp,$yrecordhighhum,$yrecordhighdew,$yrecordhighbaro,$yrecorddailyrain,$yhrrecordrainrate,$yrecorddayswithrain,$yrecorddaysnorain,$yrecordwindgust,$yrecordwindspeed,100,100);
$alltRecords = array($recordlowtemp,$recordlowchill,$recordlowhum,$recordlowdew,$recordlowbaro,$recordhightemp,$recordhighhum,$recordhighdew,$recordhighbaro,$recorddailyrain,$hrrecordrainrate,$recorddayswithrain,$recorddaysnorain,$recordwindgust,$recordwindspeed,$recordlowtempcurrentmonth,$recordhightempcurrentmonth);
$descrip = array('Low temperature', 'Low windchill', 'Low humidity', 'Low dew point', 'Low pressure', 'High temperature', 'High humidity', 'High dew point', 'High pressure',
 'Most rain in one day', 'Most rain in one hour', 'Longest wet spell', 'Longest dry spell', 'Highest gust speed', 'Highest average wind speed', 'Lowest '.$monthname.' temperature', 'Highest '.$monthname.' temperature');
for($i = 0; $i < count($todayRecords); $i++) {
	if(floatval($todayRecords[$i]) == floatval($alltRecords[$i])) { echo 'New All-time record has been set! Record: ', $descrip[$i], '<br />'; }
	elseif(floatval($todayRecords[$i]) == floatval($yearRecords[$i])) { echo 'New Year record has been set! Record: ', $descrip[$i], '<br />'; }
	elseif(floatval($todayRecords[$i]) == floatval($monthRecords[$i])) { echo 'New Month record has been set! Record: ', $descrip[$i], '<br />'; }
	echo '<b>', $descrip[$i], '</b>: ', $todayRecords[$i], '---', $monthRecords[$i], '---', $yearRecords[$i], '---', $alltRecords[$i], '<br />';
}

echo '<br /><br />';
// variables for monthly reports
$listm = array(date("d", mktime(0,0,0,$date_month,$date_day-1)), date("m", mktime(0,0,0,$date_month,$date_day-1)), date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)),
			$nmin, $dmax, $mrain2, $mryest2, $rn10max, $rn10maxt, $maxaverageyestnodir, $maxgustyestnodir, $wind10max, $wind10maxt, $wind60max, $wind60maxt,
			$tchangehrmax, $tchangehrmaxt, $hchangehrmax, $hchangehrmaxt, $tchangehrmin, $tchangehrmint, $hchangehrmin, $hchangehrmint, ' \n');

//Check if yesterday's data has been written to mprep.csv to prevent duplicate line-writing
$checkm = file("mrep.csv");
//if($date_year == 2011) { $flengthm = date("z", mktime(0,0,0,$date_month,$date_day,2011))-201; }
//elseif($date_year == 2012) { $flengthm = 365-201+date("z", mktime(0,0,0,$date_month,$date_day,2012)); }
//for($y = 2009; $y < $date_year; $y++) {

$flm = intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,2,1,2009))/(24*3600))+2; echo '<br />', $flm, '<br />', date("d", mktime(0,0,0,$date_month,$date_day-1));
$icheckm = explode(",", $checkm[$flm]);

//Write data to CSV for use in monthly reports
if ($icheckm[0] != date("d", mktime(0,0,0,$date_month,$date_day-1)) && ((mktime($time_hour, $time_minute) > mktime(0,0)+20*60 && $time_hour != 23) || isset($_GET['overide']))) {		
	$filem = fopen("mrep.csv","a");
	fputcsv($filem, $listm);
	fclose($filem);
}

echo '<b>Extra data for use in the monthly reports</b> (last 10 lines)<br />';
//Read output of above CSV (last 10 lines)
$fil2m = fopen('mrep.csv', 'r'); $cntrm = 0;
	while ( !feof($fil2m) ) {
		if($cntrm > $flm-10) { echo fgets($fil2m), '<br />'; } else { fgets($fil2m); }
		$cntrm = $cntrm + 1;
	}
fclose($fil2m);

echo '<p>', $flm, ' days of records</p>';

//echo '<b>Extra data for use on wx14</b><br />';
function getmaxmin($file) {
	$data = file($file);
	$end = count($data);
	for ($i = $end-55; $i < $end; $i++) {
		if(strpos($data[$i],"daily max") > 0) { $daymax = explode(" ", $data[$i]); $daymaxv = floatval(substr($daymax[5],1)); }
		if(strpos($data[$i],"daily min") > 0) { $daymin = explode(" ", $data[$i]); $dayminv = floatval(substr($daymin[5],1)); }
		if(strpos($data[$i],"verage temp") > 0) { $daymean = explode(" ", $data[$i]); $daymeanv = floatval($daymean[8]); }
	}
	return array($dayminv, $daymaxv, $daymeanv);
}

for($i = 1; $i <= 12; $i++) {
	if($i > intval($date_month)-1) { $datyr = $date_year-1; } else { $datyr = $date_year; }
	$report2[$i] = date("FY", mktime(0,0,0,$i,$date_day-1,$datyr)).'.htm';
	$minmax[$i] = getmaxmin($report2[$i]);
	echo $minmax[$i][0], '---', $minmax[$i][1], '---', $minmax[$i][2];
	echo '<br />';
}

// Get browser info
$answer = array('no', 'yes');
$browser = $_SERVER['HTTP_USER_AGENT'];
echo '<br /><b>Brower:</b> ', $browser, '<br />';
if(!strpos($browser,'Firefox') > 0) { $stbfix = '<span style="border-bottom: 1px dotted">'; $enbfix = '</span>'; }
echo $stbfix, 'some text', $enbfix, ' <br />';
//$browser2 = get_browser(null,true); Doesn't work (only detects whether brower is JS-capable). Also, not working on my server.
//echo 'Javascript enabled? ', $answer[$browser2[javascript]], '<br />';

echo 'PHP script executed in ', microtime(get_as_float) - $scriptbeg, ' s';
echo '<br />IP address: ', $_SERVER['REMOTE_ADDR'];

$ipfull = $_SERVER['REMOTE_ADDR']; $ip = explode('.',$ipfull);
// $ips = array($ip[0], $ip[1], $ip[2], $ip[3]);
if(strpos($_SERVER['HTTP_USER_AGENT'],'zilla/5.0 (Windows NT 6.0; rv:7.0.1) Gecko/20100101 Firefox/7.0.1') > 0 ) {
	if($ip[0] == 86 && $ip[1] > 128 && $ip[1] < 186) {
		echo '<br />Succesful code!<br /><br />';
	}
}

//Function to analyse indoor log files and give summary data
function inlog($inlog,$tlimu,$tliml,$hlimu,$hliml) {
	$inlognm = './logfiles/'.$inlog.'indoorlog.txt'; $str1 = substr($inlog,-4,4); $inmon = intval(str_replace($str1,'',$inlog));
	if(file_exists($inlognm)) { $indata = file($inlognm); }
	echo '<b>Report for ', date('M Y',mktime(0,0,0,$inmon,1,intval($str1))), '</b><br />Invalid lines: <br />'; $intmin = 30; $inhmin = 60;
	for($i = 1; $i < count($indata); $i++) {
		$indatal = explode(' ', ltrim(str_ireplace('  ', ' ', $indata[$i])));
		if($indatal[5] < 30 && $indatal[5] > 7 && $indatal[6] > 24 && $indatal[6] < 75) { //Global thresholds
			if($indatal[5] < $tlimu && $indatal[5] > $tliml) {
				$intsum = $intsum + $indatal[5]; $intcount = $intcount + 1;
				if($indatal[5] > $intmax) { $intmax = $indatal[5]; $intmaxt = $indatal[3].':'.$indatal[4].', Day '.$indatal[0]; }
				if($indatal[5] < $intmin) { $intmin = $indatal[5]; $intmint = $indatal[3].':'.$indatal[4].', Day '.$indatal[0]; }
			} else { echo  $indata[$i], '<br />'; }
			
			if($indatal[6] > $hliml && $indatal[6] < $hlimu) {
				$inhsum = $inhsum + $indatal[6]; $inhcount = $inhcount + 1;
				if($indatal[6] > $inhmax) { $inhmax = $indatal[6]; $inhmaxt = $indatal[3].':'.$indatal[4].', Day '.$indatal[0]; }
				if($indatal[6] < $inhmin) { $inhmin = $indatal[6]; $inhmint = $indatal[3].':'.$indatal[4].', Day '.$indatal[0]; }
			} else { echo  $indata[$i], '<br />'; }
		} else { echo  $indata[$i], '///GLOBAL LIMIT FAIL<br />'; }
	}
	echo '<br />Indoor Log for ', $inlog, ' contains ', $intcount, '/', $inhcount, ' out of ', count($indata)-1, ' valid lines (',
	date('t',mktime(0,0,0,$inmon))*24*60, ' expected)<br />
	 Average temperature was: ', round($intsum/$intcount,2), ' &deg;C (Min: ', $intmin, ' at ', $intmint, '; Max: ', $intmax, ' at ', $intmaxt, ')<br />
	 Average relative humidity was: ', round($inhsum/$inhcount,1), '% (Min: ', $inhmin, ' at ', $inhmint, '; Max: ', $inhmax, ' at ', $inhmaxt, ')<br /><br />';
}

//Function to analyse indoor log files and return summary data for use in table
function inlog2($inmonth,$tlimu,$tliml,$hlimu,$hliml) {
	global $inyear;
	$inlognm = './logfiles/'.$inmonth.$inyear.'indoorlog.txt';
	if(file_exists($inlognm)) { $indata = file($inlognm); }
	$intmin = 30; $inhmin = 60;
	for($i = 1; $i < count($indata); $i++) {
		$indatal = explode(' ', ltrim(str_ireplace('  ', ' ', $indata[$i])));
		if($indatal[5] < 30 && $indatal[5] > 7 && $indatal[6] > 24 && $indatal[6] < 75) { //Global thresholds
			if($indatal[5] < $tlimu && $indatal[5] > $tliml) {
				$intsum = $intsum + $indatal[5]; $intcount = $intcount + 1;
				if($indatal[5] > $intmax) { $intmax = $indatal[5]; $intmaxt = $indatal[3].':'.$indatal[4].', Day '.$indatal[0]; }
				if($indatal[5] < $intmin) { $intmin = $indatal[5]; $intmint = $indatal[3].':'.$indatal[4].', Day '.$indatal[0]; }
			}
			
			if($indatal[6] > $hliml && $indatal[6] < $hlimu) {
				$inhsum = $inhsum + $indatal[6]; $inhcount = $inhcount + 1;
				if($indatal[6] > $inhmax) { $inhmax = $indatal[6]; $inhmaxt = $indatal[3].':'.$indatal[4].', Day '.$indatal[0]; }
				if($indatal[6] < $inhmin) { $inhmin = $indatal[6]; $inhmint = $indatal[3].':'.$indatal[4].', Day '.$indatal[0]; }
			}
		}
	}
	if($intcount > 0 && $inhcount > 0) {
		return array($intmin, $intmint, $intmax, $intmaxt, round($intsum/$intcount,1), $inhmin, $inhmint, $inhmax, $inhmaxt, round($inhsum/$inhcount),
				count($indata)-1-$intcount, count($indata)-1-$inhcount, date('t',mktime(0,0,0,$inmonth))*24*60-count($indata)-1, date('t',mktime(0,0,0,$inmonth))*24*60);
	} else { return 'n/a'; }
}
/*
inlog('12011',27,11,48,31);
inlog('22011',27,11,48,31);
inlog('32011',27,11,51,31);
inlog('42011',30,14,62,32);
inlog('52011',30,15,62,33);
inlog('62011',30,16,62,35);
inlog('72011',30,17,62,35);
inlog('82011',30,17,63,35);
inlog('92011',30,17,67,40);
inlog('12010',30,8,45,24);
inlog('22010',30,8,55,25);
inlog('32010',27,8,51,25);
inlog('42010',23.6,8,65,37);
inlog('52010',30,8,65,30);
inlog('62010',30,8,72,30);
inlog('72010',27,19,68,30);
inlog('82010',30,8,65,30);
inlog('92010',25,17,65,45);
inlog('102010',30,8,73,30);
inlog('112010',26,8,65,30);
inlog('122010',30,7,49,30);
*/

//Display table of indoor data
$monthshort = array('Measure', 'Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
echo '<a name="indoor"></a>View indoor data for '; for($y = 2010; $y <= date('Y'); $y++) { echo '<a href="phptest.php?inyear=',$y,'#indoor">',$y,'</a>&nbsp;&nbsp; '; }
if(isset($_GET['inyear'])) {
$inyear = $_GET['inyear'];
$inlogdata = array(
				array(), //2009
				array(inlog2('1',30,8,45,24), inlog2('2',30,8,55,25), inlog2('3',27,8,51,25), inlog2('4',23.6,8,65,37), inlog2('5',30,8,65,30), inlog2('6',30,8,72,30), //2010
					inlog2('7',28,19,68,30), inlog2('8',30,8,65,30), inlog2('9',25,17,65,45), inlog2('10',30,8,73,30), inlog2('11',26,8,65,30), inlog2('12',30,7,49,30) ),
				array(inlog2('1',27,11,48,31), inlog2('2',27,11,48,31), inlog2('3',27,11,51,31), inlog2('4',30,14,62,32), inlog2('5',30,15,62,33), inlog2('6',30,16,62,35), //2011
					inlog2('7',30,17,62,35), inlog2('8',30,17,63,35), inlog2('9',30,17,67,40) )
			);
$inlabel = array('Tmin', 'Tmin D', 'Tmax', 'Tmax D', 'Tave', 'Hmin', 'Hmin D', 'Hmax', 'Hmax D', 'Have', 'Tcnt loss', 'Hcnt loss', 'Exp-TotCnt', 'Exp count');
echo '<h2>Indoor data for ', $inyear, '</h2><table class="table1" width="90%" cellpadding="4"><tr>';
for($i = 0; $i < 13; $i++) { echo '<td class="td4"><b>', $monthshort[$i], '</b></td>'; }
echo '</tr>';
for($r = 0; $r < count($inlabel); $r++) {
	echo '<tr><td class="td4"><b>', $inlabel[$r], '</b></td>';
	for($i = 0; $i < 12; $i++) {
		echo '<td class="td4">';
		if($r == 4 || $r == 9) { echo '<b>'; }
		echo $inlogdata[$inyear-2009][$i][$r];
		if($r == 4 || $r == 9) { echo '</b>'; } 
		echo '</td>';
	}
	echo '</tr>';
}
echo '</table>';
}

echo '<br /><br /><b>Month-averaged wind speed for each hour of the day</b><br />';
//Function to analyse log files and return various data for interest
function outlog($outmonth,$outyear) {
	$outlognm = './logfiles/'.$outmonth.$outyear.'lgcsv.csv';
	if(file_exists($outlognm)) { $outdata = file($outlognm); }
	for($i = 1; $i < count($outdata); $i++) {
		$outdatal = explode(',',$outdata[$i]);
		$day = $outdatal[0]; $hour = $outdatal[3]; $minute = $outdatal[4];
		$wind[$hour][$day][$minute] = $outdatal[9];
	}
	echo 'Final day is ', $day, '; Report month is ', date('F Y',mktime(0,0,0,$outmonth,1,$outyear)), '<br />';
	for($d = 1; $d <= $day; $d++) {
		for($h = 0; $h < 24; $h++) {
			$windavsum[$h][$d] = array_sum($wind[$h][$d]); $windavcount[$h][$d] = count($wind[$h][$d]);
		}
	}
	for($h = 0; $h < 24; $h++) {
		$windhourlyav[$h] = array_sum($windavsum[$h])/array_sum($windavcount[$h]);
		echo 'Hour ',$h, ': ', conv($windhourlyav[$h]*1.152,4,1),'<br />';
	}
	echo 'Overall: ', conv(array_sum($windhourlyav)/24*1.152,4,1),'<br />';
}
//outlog(8,2011); //call function
//outlog(1,2011);

echo '<br />File size of phptags.php / bytes: ', filesize($root.'phptags.php');
echo '<br />File size of main_tags.php / bytes: ', filesize($root.'main_tags.php'), '<br /><br />';

echo $recordhighbaro, ' ', $Rrecordhighbaro;

//if($_SERVER['REMOTE_ADDR'] == '131.111.131.12') {
/*
	if($time < $sunrise || $time > $sunset) { $img = 'webcamimage.gif'; } else { $img = 'currcam.jpg'; }
	$conn = ftp_connect("ftp.nw3weather.co.uk",21,3) or die("Could not connect");
	ftp_login($conn,"blr@nw3weather.co.uk","16huhik#");
	echo ftp_get($conn,$root.'img-save/webcam'.date('Y.m.d-H').'.jpg',$img,FTP_BINARY);
	echo ftp_get($conn,$root.'img-save/groundcam'.date('Y.m.d-H').'.jpg','jpggroundcam.jpg',FTP_BINARY);
	ftp_close($conn); 
*/
//}


/*
$mon=$_GET['month']; $yr=$_GET['year'];
$moredata = file('mrep.csv');
$st = intval((mktime(12,0,0,$mon,1,$yr)-mktime(0,0,0,2,1,2009))/(24*3600))+1;
$en = $st + date('t', mktime(0,0,0,$mon,1,$yr));
for($l = $st; $l < $en; $l++) {
	$mhrndata[$l] = explode(',',$moredata[$l]);
	echo $mhrndata[$l][6], ' on day ', $mhrndata[$l][0], '<br />';
	$mhrna[$l] = floatval($mhrndata[$l][6]);
}
$mhrn = max($mhrna);
echo $mhrn;
*/


//for ($i = 0; $i <4; $i++) {
//	echo '<b>Variable '.$i.' data:</b><br />';
//	for ($d = 1; $d <32; $d++) {
//		echo $tempvar[3][$d].', ';
//	}
//	echo '<br /><br />';
//}

//$nl = array('test');
//$mdata = fopen('dd2011.csv', "w");
//for ($i = 0; $i <12; $i++) {
//	fputcsv($mdata, $tempvar[$i]);
//}
//fclose($mdata);

/*	$fil = fopen('wxtempsummary.php', 'r');
	while ( !feof($fil) ) {
		$data = array();
		$gotline = trim ( fgets($fil) );
		$linefound = preg_split("/[\n\r\t ]+/", $gotline );
		$data[0] = $linefound;
	}
fclose($fil);
for ($i = 200; $i <300; $i++) {
echo $data[0][$i], '<br />';
}

//Get mean extremes from dailydatalog
function getdata ($filename) {
	$rawdata = array();
	$fd = @fopen($filename,'r');
	$startdt = 1;
	while ( !feof($fd) ) { 
		$gotdat = trim ( fgets($fd) );
		$foundline = preg_split("/[\n\r\t, ]+/", $gotdat );
		$date = explode('/',$foundline[0]);
		$rawdata[intval((mktime(0,0,0,$date[1],floatval($date[0]),$date[2])-mktime(0,0,0,2,1,2009))/(24*3600))] = $foundline;
	}
	fclose($fd);
	return($rawdata);
}

	$test = getdata('dailydatalog.txt');
	$min = 100; $max = -100; $minw = 100; $maxw = -100;
	for ($i = 0; $i < intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,2,1,2009))/(24*3600)); $i++) {
		if ($test[$i][9] != "") {
			if ($min > $test[$i][9]): $min = $test[$i][9]; $mint = $test[$i][0]; endif;
			if ($max < $test[$i][9]): $max = $test[$i][9]; $maxt = $test[$i][0]; endif;
		}
		if ($test[$i][3] != "" && $i > 550) {
			if ($minw > $test[$i][3]): $minw = $test[$i][3]; $mintw = $test[$i][0]; endif;
			if ($maxw < $test[$i][3]): $maxw = $test[$i][3]; $maxtw = $test[$i][0]; endif;
		}
	}
		
	echo 'The lowest Tmean was ', $min, ' on ', $mint;
	echo '<br />The highest Tmean is ', $max, ' on ', $maxt;
	echo '<br />The lowest Wmean was ', round($minw*1.1508,1), ' on ', $mintw;
	echo '<br />The highest Wmean is ', round($maxw*1.1508,1), ' on ', $maxtw;
	
	echo '<br />Modified:', date("H:i, d M",filemtime('dailydatalog.txt')), '<br />', filesize('dailydatalog.txt'), ' KB<br />Accessed:', date("H:i, d M",fileatime('dailydatalog.txt')); 
	
	echo '<br /><br /><b>end of getdata</b><br /><br />';

//Finds highest and lowest month means from monthyyyy.htm files
$year = date("Y");
$years = 1 + ($year - 2009);
$minm = 100; $maxm = -100;
for ( $y = 0; $y < $years ; $y ++ ) {
			$yx = $year - $y;
			
		for ( $m = 0; $m < 12 ; $m ++ ) {            
			$filename = date('F', mktime(0,0,0,$m+1,1,2010)) . $yx . ".htm";
							
			if(file_exists($filename)) {
				$arr = file($filename);
				for ($i = 0; $i <1200; $i++) {
					if(strpos($arr[$i],"for the month of") > 0): $line = $arr[$i+3]; endif;
					if(strpos($arr[$i],"Daily report for") > 0): $linem = $arr[$i]; endif;
				}
					$arr2 = explode(" ", $line);
					if ($minm > floatval($arr2[8])): $minm = floatval($arr2[8]); $minmt = substr($linem,53); endif;
					if ($maxm < floatval($arr2[8])): $maxm = floatval($arr2[8]); $maxmt = substr($linem,53); endif;
			}
		}
	}

echo 'The lowest Tmean is ', $minm, ' on ', $minmt;
echo '<br />The highest Tmean is ', $maxm, ' on ', $maxmt; 	

// Find all-time driest month for all but current month
$year = date("Y");
$years = 1 + ($year - 2009);
$minr = 100;
for ( $y = 0; $y < $years ; $y ++ ) {
			$yx = $year - $y;
			
		for ( $m = 0; $m < 12 ; $m ++ ) {            
			$filename = date('F', mktime(0,0,0,$m+1,1,2010)) . $yx . ".htm";
							
			if(file_exists($filename) && $filename != date("F",mktime(0,0,0,$date_month,1,2010)) . $date_year . ".htm") {
				$arr = file($filename);
				for ($i = 0; $i <1200; $i++) {
					if(strpos($arr[$i],"for the month of") > 0): $line = $arr[$i+10]; endif;
					if(strpos($arr[$i],"Daily report for") > 0): $linem = $arr[$i]; endif;
				}
					$arr2 = explode(" ", $line);
					if ($minr > ($arr2[10])): $minr = ($arr2[10]); $minrt = substr($linem,53); endif;
			}
		}
	}
echo '<br />The lowest rain is ', $minr, ' on ', $minrt;

// Find driest month for each month of year for all but current month
$year = date("Y");
$years = 1 + ($year - 2009);
$minrc = 100;
for ( $y = 0; $y < $years ; $y ++ ) {
			$yx = $year - $y;
			
		for ( $m = 0; $m < 12 ; $m ++ ) {            
			$filename = date('F', mktime(0,0,0,$m+1,1,2010)) . $yx . ".htm";
							
			if(file_exists($filename) && $filename != date("F",mktime(0,0,0,$date_month,1,2010)) . $date_year . ".htm" && $m+1 == $date_month) {
				$arr = file($filename);
				for ($i = 0; $i <1200; $i++) {
					if(strpos($arr[$i],"for the month of") > 0): $line = $arr[$i+10]; endif;
					if(strpos($arr[$i],"Daily report for") > 0): $linem = $arr[$i]; endif;
				}
					$arr2 = explode(" ", $line);
					if ($minrc > ($arr2[10])): $minrc = ($arr2[10]); $minrt = substr($linem,53); endif;
			}
		}
	}
echo '<br />The lowest ', monthfull($date_month), ' rain is ', $minrc, ' on ', $minrt;

//Finds highest and lowest month windspeed means from monthyyyy.htm files
$year = date("Y");
$years = 1 + ($year - 2009);
$minmw = 100; $maxmw = -100;
for ( $y = 0; $y < $years ; $y ++ ) {
			$yx = $year - $y;
			
		for ( $m = 0; $m < 12 ; $m ++ ) {            
			$filename = date('F', mktime(0,0,0,$m+1,1,2010)) . $yx . ".htm";
							
			if(file_exists($filename)) {
				$arr = file($filename);
				for ($i = 0; $i <1200; $i++) {
					if(strpos($arr[$i],"for the month of") > 0): $line = $arr[$i+7]; endif;
					if(strpos($arr[$i],"Daily report for") > 0): $linem = $arr[$i]; endif;
				}
					$arr2 = explode(" ", $line);
					if ($minmw > floatval($arr2[10])): $minmw = floatval($arr2[10]); $minmwt = substr($linem,53); endif;
					if ($maxmw < floatval($arr2[10])): $maxmw = floatval($arr2[10]); $maxmwt = substr($linem,53); endif;
			}
		}
	}

echo '<br />The lowest Windmean is ', $minmw, ' on ', $minmwt;
echo '<br />The highest Windmean is ', $maxmw, ' on ', $maxmwt;

*/
?>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>