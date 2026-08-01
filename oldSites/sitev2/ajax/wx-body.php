<?php $path = $_SERVER['DOCUMENT_ROOT'];
$ajax = 1;
$crsize = filesize($path.'/clientraw.txt');
// if($crsize < 700 && $crsize != false) {
	// usleep(500000);
	// clearstatcache(); $crsize2 = filesize($path.'/clientraw.txt');
	// $file_2 = fopen($path."/sleep_clientraw.txt","a");
	// fwrite($file_2, date("H:i:s d/m/Y") . " \t " . $crsize . " \t " . $crsize2 . " \t " . $_SERVER['REMOTE_ADDR']. " \t ". $_SERVER['HTTP_USER_AGENT'] . "\r\n");
	// fclose($file_2);
// }
require('../unit-select.php');
include('../phptags.php');
include('../main_tags.php');
$file = 1;
$client = file($path. '/clientraw.txt');
$live = explode(" ", $client[0]);
$craw_lag = -mktime($live[29], $live[30], $live[31])+time();
if(intval($live[5]) < 10 || intval($crsize) == 0 || $craw_lag > 99) {
	$live[5] = $hum; $live[4] = $temp; $live[72] = $dew; $live[6] = $baro; $live[1] = $avgspd; $live[3] = $dirlabel; $live[140] = $gstspd; $live[7] = $dayrn;
	$live[29] = $time_hour; $live[30] = $time_minute; $live[31] = '00'; $live[8] = $monthrn; $live[10] = $currentrainratehr / 60;
}
?>

<div id="main-copy">
<div style="column-dark2">

<script type="text/javascript">
<!--
function showElapsedTimea() {
var startYear = <?php echo $date_year; ?>; var startMonth = <?php echo $date_month - 1; ?>; var startDay = <?php echo $date_day; ?>; var startHour = <?php echo $live[29]; ?>;
var startMinute = <?php echo $live[30]; ?>; var startSecond = <?php echo $live[31]; ?>; 
var startDate = new Date(); startDate.setUTCFullYear(startYear); startDate.setUTCMonth(startMonth); startDate.setUTCDate(startDay); startDate.setUTCHours(startHour);
startDate.setUTCMinutes(startMinute); startDate.setUTCSeconds(startSecond);
var rightNow = new Date(); //rightNow.getUTCFullYear(); rightNow.getUTCMonth(); rightNow.getUTCDate(); rightNow.getUTCHours(); rightNow.getUTCMinutes(); rightNow.getUTCSeconds();
//console.log(rightNow.getTime());
var d = new Date(); var offset = d.getTimezoneOffset();
var elapsedTime = rightNow.getTime() - startDate.getTime() + <?php if(date("I") == 1) { echo 60*60000; } else { echo 0; } ?>;
var elapsedSeconds = Math.round(elapsedTime / 1000);
//if (elapsedSeconds < 0 && elapsedSeconds > -4) { elapsedSeconds = 0 }
if (elapsedSeconds > 99 && elapsedSeconds < 3000) { elapsedSeconds = '>99' }
var newdata = '';  if (elapsedSeconds < 5 && elapsedSeconds > -5) { newdata = '  - NEW!'; }
//colour = 'black'; if (elapsedSeconds < 5) { colour = 'blue' }
var all = ' - ' + elapsedSeconds + ' s ago' + newdata;
if (elapsedSeconds < 0 || elapsedSeconds > 3000) { all = ''; }
 document.getElementById('elapsedTime').innerHTML = all; t = setTimeout('showElapsedTimea()',1000); 
}
// -->
</script>

<span>Data recorded at <?php if(date("I") == 1): $dst = "BST"; else: $dst = "GMT"; endif; echo $live[29], ':', $live[30], ':', $live[31], ' ', $dst; ?></span>
<table cellpadding="5" cellspacing="0" width="99%">
<tr class="table-top">
<td width="21%"><b>Measure</b></td>
<td width="24%"><b>Current<span id="elapsedTime"><script>
<!--
showElapsedTimea()
//-->
</script> </span></b></td>
<td width="21%"><b>Max/Min</b></td>
<td width="20%"><b>Rate</b></td>
<td width="14%"><b>24hr Mean</b></td>
</tr>

<tr class="column-light">
<td><b>Temperature </b></td>
<td <?php echo flash($temp, $live[4]); ?>><b><?php echo conv($live[4],1,1); ?></b> &nbsp; <?php arrow($temp0minuteago, $temp10minuteago, $temp30minuteago, 0.4, 0.9, 0.8); ?></td>
<td><span style="font-size: 10px;"><?php echo conv($maxtemp,1,1), ' at ', $maxtempt; ?><br/>
<?php echo conv($mintemp,1,1), ' at ', $mintempt; ?></span></td>
<td><span style="font-size: 10px;"><?php echo conv2($tempchangehour,1,1) ?> /hr</span></td>
<td><?php echo conv($last24houravtemp,1,1); ?></td>
</tr>

<tr class="column-dark2">
<td><b>Relative Humidity</b></td>
<td <?php echo flash($hum, $live[5]); ?>><b><?php echo $live[5]; ?>%</b> &nbsp; <?php arrow($hum0minuteago, $hum15minuteago, $hum45minuteago, 2, 9, 8); ?></td>
<td><span style="font-size: 10px;"><?php echo $highhum; ?>% at <?php echo $highhumt; ?><br/>
<?php echo $lowhum; ?>% at <?php echo $lowhumt; ?></span></td>
<td><span style="font-size: 10px;"><?php echo $humchangelasthour; ?>% /hr</span></td>
<td><?php echo round($last24houravhum,0); ?>%</td>
</tr>

<tr class="column-light">
<td height="31"><b>Dew Point</b></td>
<td <?php echo flash($dew, $live[72]); ?>><b><?php echo conv($live[72],1,1); ?></b> &nbsp; <?php 
if($live[5] < 60 && $live[5] >= 40) { arrow($dew0minuteago, $dew10minuteago, $dew30minuteago, 0.7, 1.5, 1.4); }
elseif($live[5] < 40) { arrow($dew0minuteago, $dew10minuteago, $dew45minuteago, 0.4, 0.8, 0.7); }
else { arrow($dew0minuteago, $dew10minuteago, $dew60minuteago, 1.2, 2.2, 2.1); } ?></td>
<td><span style="font-size: 10px;"><?php echo conv($maxdew,1,1), ' at ', $maxdewt, '<br/>', conv($mindew,1,1), ' at ', $mindewt; ?></span>
</td>
<td><span style="font-size: 10px;"><?php echo conv2($dewchangelasthour,1,1); ?>/hr</span></td>
<td><?php $gamma = (17.271*$last24houravtemp)/(237.7+$last24houravtemp)+log($last24houravhum/100); echo conv((237.7*$gamma)/(17.271-$gamma),1,1); ?></td>
</tr>

<tr class="column-dark2">
<td><b>Pressure</b></td>
<td><b><?php echo conv($live[6],3,1); ?></b> &nbsp; <?php arrow($baro0minuteago, $baro30minuteago, $baro120minuteago, 1.1, 2.5, 2.5); ?></td>
<td><span style="font-size: 10px;"><?php echo conv($highbaro,3,1); ?> at <?php echo $highbarot; ?><br/>
<?php echo conv($lowbaro,3,1); ?> at <?php echo $lowbarot; ?></span></td>
<td><span style="font-size: 10px;"><?php echo conv2($trend,3,1); ?>/hr<br/><?php echo $pressuretrendname; ?></span></td>
<td><?php echo conv($last24houravbaro,3,1); ?></td>
</tr>

<tr class="column-light">
<td height="31"><b>Wind</b></td>
<td><b><?php echo conv($live[1]*1.152,4,1), ' ', degname($live[3]); ?></b> -
 Gusting to <b><?php if(floatval($live[140]) < floatval($live[1])) { echo conv3(ceil($live[1]*1.152),4,1); } else { echo conv3($live[140]*1.152,4,1); } ?></b></td>
<td><span style="font-size: 10px;"><b>Max gust last hour:</b> <?php echo conv($maxgsthr,4,1); ?><br/><b>Max gust today:</b> <?php echo conv($maxgst,4,1); ?></span>
</td>
<td><span style="font-size: 10px;"><a href="Beaufort Scale.php" title="Definition of Beaufort terms">Beaufort Scale:</a><br /><?php echo $bftspeedtext; ?> </span></td>
<td><span style="font-size: 11px;"><?php echo conv($avgspeedsincereset,4,1), ' <br />', degname($last24houravdir), ' (', $last24houravdir; ?>&deg;)</span></td>
</tr>

<tr class="column-dark2">
<td height="31"><b>Daily Rain</b></td>
<td <?php echo flash($dayrn, $live[7]); ?>><b><?php echo conv($live[7],2,1); ?></b> &nbsp; <?php if(floatval($dayrn) > 0) { arrow($rain0minuteago, $rain20minuteago, $rain45minuteago, 0.1, 2.0, 1.9); } ?></td>
<td><span style="font-size: 10px;"><b>Last Hour:</b> <?php echo conv($hourrn,2,1); ?><br/><b>Month Rain:</b> <?php echo conv($live[8],2,1); ?></span></td>
<td><span style="font-size: 10px;"><?php echo conv(60*$live[10],2,1); ?>/h<br/><b>Last 10 mins:</b> <?php echo conv($rainlast10min,2,1); ?></span></td>
<td><b>Total: </b><?php echo conv($totalrainlast24hours,2,1); ?></td>
</tr>

<tr class="column-light">
<td height="7"><b></b></td>
<td><b></b></td>
<td><span style="font-size: 10px;"></span></td>
<td><span style="font-size: 10px;"></span></td>
<td></td>
</tr>

<tr><td colspan="5">

<table cellspacing="2" cellpadding="3" border="1" width="100%" style="border-collapse: collapse">
<tr>
<td width="21%"><span style="font-size: 10px;"><b>Feels Like:</b><br/><?php if(($temp < 27 && $heati >= $temp && $humidex > $temp) || $humidex > $temp): echo conv3($humidex,1,1);
elseif($humidex < $temp && $windch >= $temp): echo conv3($temp,1,1); else: echo conv3($feelslikedp,1,1); endif; ?> &nbsp; (Daily Min: <?php echo conv3($minwindch,1,1); ?>)</span></td>
<td width="24%"><span style="font-size: 10px;"><b>10-min Av Wind:</b><br/><?php echo conv($avtenminutewind,4,1), ' ', $curdir10minutelabel; ?></span></td>
<td width="21%"><span style="font-size: 10px;"><b>Annual Rain:</b><br/><?php echo conv($yearlyraininmm,2,1); ?></span></td>
<td width="20%"><span style="font-size: 10px;"><b>Last Rain:</b><br/><?php $splitdolra = explode("/", $dateoflastrain); echo $timeoflastrainalways, ', '; 
if($splitdolra[0] == $date_day && $splitdolra[1] == $date_month) { echo "Today"; }
	elseif($splitdolra[0] == $date_day-1 && $splitdolra[1] == $date_month) { echo "Yesterday"; }
	else { echo datefull($splitdolra[0]), ' ', monthfull($splitdolra[1]); } ?></span></td>
<td width="14%"><span style="font-size: 10px;"><b>Free Memory:</b><br/><?php if($freememory<0): echo 4000+$freememory; else: echo $freememory; endif; ?> MB</span></td>
</tr>
</table>

</td></tr>
</table>

</div>

<p>
To see how these figures compare to expected values,
view the <a href="wxaverages.php" title="Long-term NW3 climate averages">climate averages</a> page.</p>

<p>This weather station has been recording data for<b> <?php echo intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,2,1,2009))/(24*3600)); ?></b> days
(<?php echo intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,7,18,2010))/(24*3600)); ?> at NW3)</p>
</div>

<?php
//Convert from UK units to US or EU
function conv($tag, $type, $unit) {	//unit=1 displays the units
	global $unitT, $unitW, $unitR, $unitP;
	$dat = floatval($tag);
	if($unit == 1) { $uarr = array(' &deg;'.$unitT,' '.$unitR,' '.$unitP,' '.$unitW); }
	$un = '&deg;'.$uarr;
	if ($type == 1) {
		if($unitT == 'F') { $conv = $dat*9/5+32; $clean = sprintf("%01.1f", $conv).$uarr[0]; } // C => F
		else { $clean = sprintf("%01.1f", $dat).$uarr[0]; }
	}
	if ($type == 2) {
		if($unitR == 'in') { $conv = $dat/25.4; $clean = sprintf("%01.2f", $conv).$uarr[1]; } // mm => in
		else { $clean = sprintf("%01.1f", $dat).$uarr[1]; }
	}
	if ($type == 3) {
		if($unitP == 'inHg') { $conv = $dat/33.864; $clean = sprintf("%01.2f", $conv).$uarr[2]; } // hPa => mmHg
		else { $clean = sprintf("%01.0f", $dat).$uarr[2]; }
	}
	if ($type == 4) {
		if($unitW == 'km/h') { $conv = $dat*1.6093; $clean = sprintf("%01.1f", $conv).$uarr[3]; } // mph => kmh
		elseif($unitW == 'm/s') { $conv = $dat*0.44704; $clean = sprintf("%01.1f", $conv).$uarr[3]; } // mph => mps
		elseif($unitW == 'knots') { $conv = $dat*0.86898; $clean = sprintf("%01.1f", $conv).$uarr[3]; } // mph => knot
		else { $clean = sprintf("%01.1f", $dat).$uarr[3]; }
	}
	if ($type == 0) { $clean = sprintf("%01.0f", $dat); }
	if ($type == 6) { $clean = sprintf("%01.0f", $dat). ' days'; }
	return $clean;
}

function conv2($tag, $type, $unit) {
	global $unitT, $unitW, $unitR, $unitP;
	$dat = floatval($tag);
	if($unit == 1) { $uarr = array(' &deg;'.$unitT,' '.$unitR,' '.$unitP,' '.$unitW); }
	if ($type == 1) {
		if($unitT == 'F') { $conv = $dat*9/5; $clean = sprintf("%+.1f", $conv).$uarr[0]; } // C => F
		else { $clean = sprintf("%+.1f", $dat).$uarr[0]; }
	}
	if ($type == 2) {
		if($unitR == 'in') { $conv = $dat/25.4; $clean = sprintf("%+.2f", $conv).$uarr[1]; } // mm => in
		else { $clean = sprintf("%+.1f", $dat).$uarr[1]; }
	}
	if ($type == 3) {
		if($unitP == 'inHg') { $conv = $dat/33.864; $clean = sprintf("%+.2f", $conv).$uarr[2]; } // hPa => mmHg
		else { $clean = sprintf("%+.0f", $dat).$uarr[2]; }
	}
	if ($type == 4) {
		if($unitW == 'km/h') { $conv = $dat*1.6093; $clean = sprintf("%+.1f", $conv).$uarr[3]; } // mph => kmh
		elseif($unitW == 'm/s') { $conv = $dat*0.44704; $clean = sprintf("%+.1f", $conv).$uarr[3]; } // mph => mps
		elseif($unitW == 'knots') { $conv = $dat*0.86898; $clean = sprintf("%+.1f", $conv).$uarr[3]; } // mph => knot
		else { $clean = sprintf("%+.1f", $dat).$uarr[3]; }
	}
	if ($type == 0) { $clean = sprintf("%+.0f", $dat); }
	return $clean;
}

function conv3($tag, $type, $unit) {
	global $unitT, $unitW, $unitR, $unitP;
	$dat = floatval($tag);
	if($unit == 1) { $uarr = array(' &deg;'.$unitT,' '.$unitR,' '.$unitP,' '.$unitW, ' lbft', ' kgm'); }
	if ($type == 1) {
		if($unitT == 'F') { $conv = $dat*9/5+32; $clean = sprintf("%.0f", $conv).$uarr[0]; } // C => F
		else { $clean = sprintf("%.0f", $dat).$uarr[0]; }
	}
	if ($type == 2) {
		if($unitR == 'in') { $conv = $dat/25.4; $clean = sprintf("%.1f", $conv).$uarr[1]; } // mm => in
		else { $clean = sprintf("%.0f", $dat).$uarr[1]; }
	}
	if ($type == 3) {
		if($unitP == 'inHg') { $conv = $dat/33.864; $clean = sprintf("%+.2f", $conv).$uarr[2]; } // hPa => mmHg
		else { $clean = sprintf("%+.0f", $dat).$uarr[2]; }
	}
	if ($type == 4) {
		if($unitW == 'km/h') { $conv = $dat*1.6093; $clean = sprintf("%.0f", $conv).$uarr[3]; } // mph => kmh
		elseif($unitW == 'm/s') { $conv = $dat*0.44704; $clean = sprintf("%.0f", $conv).$uarr[3]; } // mph => mps
		elseif($unitW == 'knots') { $conv = $dat*0.86898; $clean = sprintf(".0f", $conv).$uarr[3]; } // mph => knot
		else { $clean = sprintf("%.0f", $dat).$uarr[3]; }
	}
	if ($type == 5) {
		if($unitT == 'F') { $conv = $dat/16.01846; $clean = sprintf("%.3f", $conv).$uarr[4]; } // kgm-3 => lbft-3
		else { $clean = sprintf("%.2f", $dat).$uarr[5]; }
	}
	if ($type == 0) { $clean = sprintf("%.1f", $dat); }
	return $clean;
}

function datefull($test) {
 if($test == 01 || $test == 21 || $test == 31):
  $dayD = round($test,0).'<sup>st</sup>';
  elseif($test == 02 || $test == 22):
  $dayD = round($test,0).'<sup>nd</sup>';
  elseif($test == 03 || $test == 23):
  $dayD = round($test,0).'<sup>rd</sup>';
  else: 
  $dayD = round($test,0).'<sup>th</sup>';
 endif;
 return $dayD;
}

function monthfull($mn) {
	$monthshort = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	return $monthshort[$mn-1];
}

function degname($winddegree) {
	$windlabels = array ("N","NNE", "NE", "ENE", "E", "ESE", "SE", "SSE", "S", "SSW","SW", "WSW", "W", "WNW", "NW", "NNW","N");
	$windlabel = $windlabels[ round($winddegree / 22.5, 0) ];
	return "$windlabel";
}
	
function arrow($var, $var1, $var2, $v1, $v2, $v3) {
	$vartr = $var - $var1; $vartr2 = $var - $var2; 
	if(abs($vartr) > 0 && abs($vartr2) > $v1 && abs($vartr2) < $v2) {
		if ($vartr > 0) { echo '<img src="/static-images/rising.gif" alt="rising" title="rising" />'; } else { echo '<img src="/static-images/falling.gif" alt="falling" title="falling" />'; }
	} 
	elseif(abs($vartr) > 0 && abs($vartr2) > $v3) {
		if ($vartr > 0) { echo '<img src="/static-images/rising.gif" height="10" width="9" alt="rising" title="rising rapidly" />'; } 
		else { echo '<img src="/static-images/falling.gif" height="10" width="9" alt="falling" title="falling rapidly" />'; }
	}
	else { echo '<img src="/static-images/steady.jpg" height="3" alt="steady" title="steady" />'; }
}
function flash($old, $new) {
	global $live;
	if(floatval($old) != floatval($new) && $live[31] < 58 && $live[31] > 40) { echo 'style="color:#3FC30F"'; }
}

?>