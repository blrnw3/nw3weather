<?php
//Convert from UK units to US or EU
function conv($tag, $type, $unit) { 	//unit=1 displays the units
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
	if ($type == 7) {
		if($unitT == 'F') { $conv = $dat/0.3048; $clean = sprintf("%01.0f", $conv).' ft'; } // m => ft
		else { $clean = sprintf("%01.0f", $dat).' m'; } 
	}
	if ($type == 8) {
		if($unitR == 'in') { $conv = $dat/25.4; $clean = sprintf("%.1f", $conv).$uarr[1].'/h'; } // mm => in
		else { $clean = sprintf("%.0f", $dat).$uarr[1].'/h'; }
	}
	if ($type == 9) { $clean = sprintf("%01.0f", $dat).'%'; }
	if ($type == 10) {
		if($unitW == 'km/h') { $conv = $dat*1.6093; $clean = sprintf("%01.0f", $conv).$uarr[3]; } // mph => kmh
		elseif($unitW == 'm/s') { $conv = $dat*0.44704; $clean = sprintf("%01.0f", $conv).$uarr[3]; } // mph => mps
		elseif($unitW == 'knots') { $conv = $dat*0.86898; $clean = sprintf("%01.0f", $conv).$uarr[3]; } // mph => knot
		else { $clean = sprintf("%01.0f", $dat).$uarr[3]; }
	}
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
	if ($type == 6) { $clean = sprintf("%01.0f", $dat) . '%'; }
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
	if ($type == 6) { if($dat > 0.99) { $clean = sprintf("%.1f", $dat*100); } else { $clean = sprintf("%.0f", $dat*100); } $clean .= '%'; }
	return $clean;
}

function conv4($tag, $type, $unit) {
	global $unitT, $unitW, $unitR, $unitP;
	$dat = floatval($tag);
	if($unit == 1) { $uarr = array(' &deg;'.$unitT,' '.$unitR,' '.$unitP,' '.$unitW, ' lbft', ' kgm'); }
	if ($type == 1) {
		if($unitT == 'F') { $conv = $dat*9/5; $clean = sprintf("%.1f", $conv).$uarr[0]; } // C => F
		else { $clean = sprintf("%.1f", $dat).$uarr[0]; }
	}
	if ($type == 5) {
		if($unitT == 'F') { $conv = $dat/16.01846; $clean = sprintf("%.2f", $conv).$uarr[4]; } // kgm-3 => lbft-3
		else { $clean = sprintf("%.1f", $dat).$uarr[5]; }
	}
	if ($type == 0) { $clean = sprintf("%.0f", 100*$dat). '%'; }
	if ($type == 2) { if(abs($dat-sprintf("%.2f", $tag)) > 0) { $clean = sprintf("%.2f", $dat); } else { $clean = $tag; } }
	if ($type == 3) { if(abs($dat-sprintf("%.2f", $tag)) > 0) { $clean = sprintf("%.0f", $dat); } else { $clean = $tag; } }
	return $clean;
}

if(date("I") == 1): $dst = "BST"; else: $dst = "GMT"; endif;
if(intval(date('i')) > 34) { $timhr = date('H'); } else { $timhr = date('H', mktime(date('H')-1)); }
$prevday = date('d/m/y',mktime(0,0,0,$date_month,$date_day-1,$date_year));

if($file == 5): $shr = '<span style="color:black">Hampstead weather forecast and maps - satellite, rain radar, pressure charts and more</span>';
elseif($file == 21 && $ex == 1): if($time > $sunset) { $shr = 'Last Updated: '.$date. ' '. $sunset. ' '.$dst; } elseif($time < $sunrise) { $shr = 'Last Updated: '.$prevday. ' '. $sunset. ' '.$dst; } else { $shr = 'Last Updated: '.$date. ' '. $timhr. ':34 '. $dst; }
elseif($file == 20 || $file == 201): $shr = 'Last Updated: 08/08/11';
elseif($file == 1 && $auto == 'on'): $shr = '<span id="datehead"> </span><noscript>Javascript required</noscript>'; //'<script type="text/javascript"> var d = new Date(); document.write(d.getHours()); </script>';
elseif($file == 7): $shr = 'Last Upload: 03/09/11';
elseif($file == 8): $shr = 'Last Updated: 25/09/11';
elseif($file == 87): $shr = 'Last Updated: 10/09/11';
elseif($file == 9): $shr = 'Last Updated: 17/09/11';
elseif($file == 95): $shr = 'Last Updated: 14/09/11';
elseif($file == 71.1): $shr = 'Uploaded: 12/09/11'; elseif($file == 71.2): $shr = 'Uploaded: 12/09/11'; elseif($file == 71.3): $shr = 'Uploaded: 20/09/11';
elseif($file == 71.4): $shr = 'Last Upload: 02/09/11'; elseif($file == 71.5): $shr = 'Uploaded: 27/09/11'; elseif($file == 71.6): $shr = 'Uploaded: 30/09/11';
elseif($file == 71.7): $shr = 'Uploaded: 30/09/11'; elseif($file == 71.8): $shr = 'Uploaded: 19/01/11'; elseif($file == 71.9): $shr = 'Uploaded: 21/03/11';
elseif($file == 71.11): $shr = 'Uploaded: 03/09/11';
elseif($file == 86 || $file == 85 || $file == 871 || $file == 88): $shr = 'Last Updated: ' . date('d/m/y', mktime(0,0,0,$date_month,$date_day,$date_year)) . ' 00:11 ' . $dst;
else: $shr = 'Last Updated: '.$date. ' '. $time. ' '. $dst; 
endif;

$tim = 1000*mktime($time_hour, $time_minute+5, 45); //Check this!!!!!
if(date('U') - filemtime('phptags.php') > 320) { $tim =  1000*mktime($time_hour, $time_minute+10, 45); }

function currconds() {
	global $temp, $dayrn, $baro, $avgspd;
	if((date("s") % 10) % 2 == 1) {	echo 'Temperature: ',conv($temp,1,1), '<br />Wind Speed: ', conv($avgspd,4,1); }
	else { echo 'Daily Rain: ', conv($dayrn,2,1), '<br />Pressure: ', conv($baro,3,1); }
}
 ?>

<script type='text/javascript'>
<!--
function showElapsedTime() {
	var rightNow = new Date(); var elapsedTime = -rightNow.getTime() + <?php echo $tim; ?>;
	var elapsedSeconds = Math.round(elapsedTime / 1000 + 3);
	var message = 'New data available in ';
	var sec = ' s'; var refr = ''; var failcase = '';
	if(elapsedSeconds < 0) { elapsedSeconds = 'available!'; message = 'Updated data '; sec = ''; <?php if($auto == 'on') { echo "refr = ' Page now refreshing';"; } ?>
	if(<?php echo $_SESSION['count'][$file]; ?> > 10) { refr = ' Auto-updates paused. Refresh page manually'; } }
	if(<?php echo date('U') - filemtime('phptags.php'); ?> > 330) { failcase = 'Last data file failed to upload; '; }
	var messagefull = failcase + message + elapsedSeconds + sec + refr;
	if(<?php echo date('U') - filemtime('phptags.php'); ?> > 600) { messagefull = 'Recent data not uploaded. Possible issue detected.'; }
	document.getElementById('elapsedTime').innerHTML = messagefull; t = setTimeout('showElapsedTime()',1000); 
}
// -->
</script>
<?php $rt = 30; if($time < $sunrise || $time > $sunset) { $rt = 100; } ?>
<script type='text/javascript'>
<!--
	var c = 1;
function showElapsedTimeWC() {
	var rightNow = new Date(); var elapsedTime = -rightNow.getTime() + <?php echo mktime()*1000 . ' + ' . $rt*1000; ?>*c;
	var elapsedSeconds = Math.round(elapsedTime / 1000);
	var message = 'Next image refresh in ';
	var sec = ' s'; var refr = ''; var failcase = '';
	if(elapsedSeconds <= 0) { elapsedSeconds = ''; c += 1; message = 'Image refreshed!'; sec = ''; }
	var messagefull = failcase + message + elapsedSeconds + sec + refr;
	document.getElementById('elapsedTime').innerHTML = messagefull; t = setTimeout('showElapsedTimeWC()',1000); 
}
// -->
</script>
<script type='text/javascript'>
<!--
function shownewhead() {
	var curr = new Date(); var currms = curr.getTime();
	var currsec = Math.round(currms / 1000); var data = '';
	if(currsec % 2 == 0) { data = "<?php echo 'Temperature: ',conv($temp,1,1), '<br />Wind Speed: ', conv($avgspd,4,1); ?>"; }
	else { data = "<?php echo 'Daily Rain: ', conv($dayrn,2,1), '<br />Pressure: ', conv($baro,3,1); ?>"; }
	var visitl = currsec-<?php echo date('U'); ?>; if(visitl > 10000) { data = 'Too much data consumed!<br />Please refresh'; }
	document.getElementById('currms').innerHTML = data; t = setTimeout('shownewhead()',5000); 
}
// -->
</script>

<div id="header">
	<h1 class="headerTitle"><a href="http://nw3weather.co.uk" title="Browse to homepage">NW3 Weather</a>	</h1>
	<div class="headerTemp"><?php if($file == 1.1) { currconds(); } else { echo '<span id="currms">
	<script type="text/javascript">
	<!-- 
	shownewhead() 
	//-->
	</script></span>'; } ?></div>
	<div class="subHeader">	Hampstead, London, England; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
	<?php if($file == 4 || $file == 6 || $file == 10 || ($file > 11 && $file < 20)) {
	echo '<span id="elapsedTime"><script type="text/javascript">
	<!-- 
	showElapsedTime() 
	//-->
	</script></span><noscript>Javascript required to display time until next update</noscript> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; }
	if($file == 21 && $me == 1) { echo '<span id="elapsedTime"><script type="text/javascript">
	<!-- 
	showElapsedTimeWC() 
	//-->
	</script></span><noscript>Javascript required to display time until next update</noscript> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'; }?></div>
	<div class="subHeaderRight"><?php echo /* date("H:i:s",filemtime('phptags.php')), '   ', */ $shr; ?>  </div>
</div>