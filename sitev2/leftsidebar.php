<?php
//FTP script for saving images
function ftpimg($imgcopy,$savename) {
	$conn = ftp_connect("ftp.nw3weather.co.uk",21,3) or die("Could not connect");
	ftp_login($conn,"blr@nw3weather.co.uk","16huhik#");
	echo ftp_get($conn,$savename,$imgcopy,FTP_BINARY);
	ftp_close($conn);
}

$browser = $_SERVER['HTTP_USER_AGENT']; $bstyle = 'outset #611D76';
if(!strpos($browser,'Firefox') > 0) { $stbfix = '<span style="border-bottom: 1px dotted">'; $enbfix = '</span>'; }

function strip_units ($data) {
		preg_match('/([\d\.\+\-]+)/',$data,$t);
		return $t[1];
	}

function average($array, $count = 1) {
	return array_sum($array)/$count;
}

function mean($array) {
	return array_sum($array)/count($array);
}

function fullify($date, $type = 1) {
	if($type == 1) { $date = date('d',mktime(1,1,1,1,$date)); }
		else { $date = date('m',mktime(1,1,1,$date)); }
	return($date);
}

function acronym($title, $value, $show = 0) {
	echo '<acronym style="'; if($show == 0) { echo 'border-bottom-width: 0'; } else { echo 'border-bottom: 1px dotted'; }
	echo '" title="', $title, '">', $value, '</acronynm>';
}

function getnoaafile ($filename) {
	$rawdata = array();
	$fd = @fopen($filename,'r');
	$startdt = 0;
	if ( $fd ) {
		while ( !feof($fd) ) {
			$gotdat = trim ( fgets($fd,8192) );
			if ($startdt == 1 ) {
				if ( strpos ($gotdat, "--------------" ) !== FALSE ){
					$startdt = 2;
				} else {
					$foundline = preg_split("/[\n\r\t ]+/", $gotdat );
					$rawdata[intval ($foundline[0]) -1 ] = $foundline;
				}
			}
			if ($startdt == 0 ) {
				if ( strpos ($gotdat, "--------------" ) !== FALSE ){
					$startdt = 1;
				}
			}
		}
		fclose($fd);
	}
	return($rawdata);
}

function get_days_in_month($month, $year) {
	return $month == 2 ? ($year % 4 ? 28 : ($year % 100 ? 29 : ($year %400 ? 28 : 29))) : (($month - 1) % 7 % 2 ? 30 : 31);
}

function get_days_in_year($year) {
	return date("z", mktime(0,0,0,12,31,$year)) + 1;
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

function log_events($txtname, $content) {
	global $file, $browser;
	$file_1 = fopen($txtname,"a");
	$ip_log = $_SERVER['REMOTE_ADDR'];
	if(strlen($ip_log < 14)) { $ip_log .= "\t"; }
	fwrite($file_1, date("H:i:s d/m/Y") . "\t" . $content . "\t File" . $file . " \t " . $ip_log. "\t". $browser . "\r\n");
	fclose($file_1);
}

if(date("I") == 1): $dst = "BST"; else: $dst = "GMT"; endif;

//Season processing
$sc = date('n')%3 + 1; //Months elapsed during current meteorological season
if($date_month == 12 || $date_month < 3): $season = '1';
elseif($date_month > 2 && $date_month < 6): $season = '2';
elseif($date_month > 5 && $date_month < 9): $season = '3';
else: $season = '4'; endif;
$snames = array('Winter', 'Spring', 'Summer', 'Autumn');
$seasonname = $snames[$season-1];

function monthfull($mn) {
	$monthshort = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
	return $monthshort[$mn-1];
}
function month($mn) {
	$monthshort = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');
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
		if ($vartr > 0) { echo '<img src="/static-images/rising.gif" alt="rising"/>'; } else { echo '<img src="/static-images/falling.gif" alt="falling"/>'; }
	} 
	elseif(abs($vartr) > 0 && abs($vartr2) > $v3) {
		if ($vartr > 0) { echo '<img src="/static-images/rising.gif" height="10" width="9" alt="rising" />'; } 
		else { echo '<img src="/static-images/falling.gif" height="10" width="9" alt="steady" />'; }
	}
	else { echo '<img src="/static-images/steady.jpg" height="3" alt="steady" />'; }
}

if($time > 21): $tl_day = 'Today'; else: $tl_day = 'Yesterday'; endif;
	   if($date_month == 05 || $date_month == 07 || $date_month == 10 || $date_month == 12):
			$dipm = 30;
		 elseif($date_month == 03 && $date_year % 4 == 0):
			$dipm = 29;
		 elseif($date_month == 03 && $date_year % 4 != 0):
			$dipm = 28;
		 else: $dipm = 31;
		endif;
 ?>
	<div id="side-bar">
<div class="leftSideBar">
  <p class="sideBarTitle">Navigation</p>
  <ul>    <li><?php if($file == 1): echo '<b>Home</b>'; else: echo '<a href="./" title="Return to main page">Home</a>'; endif; ?></li>  
  <li><?php if($file == 2): echo '<b>Webcam</b>'; elseif($file == 21): echo '<a href="wx2.php" title="Return to main Webcam page"><b>Webcam</b></a>'; else: echo '<a href="wx2.php" title="Live Webcam and Timelapses">Webcam</a>'; endif; ?></li>  
  <li><?php if($file == 3): echo '<b>Graphs</b>'; else: echo '<a href="wx3.php" title="Latest Daily and Monthly Graphs &amp; Charts">Graphs</a>'; endif; ?></li> 
		<li><?php if($file == 4): echo '<b>Data</b>'; else: echo '<a href="wx4.php" title="Extremes and Trends">Data</a>'; endif; ?></li> 
		<li><?php if($file == 5): echo '<b>Forecast</b>'; else: echo '<a href="wx5.php" title="Full Local Forecasts and Latest Maps">Forecast</a>'; endif; ?></li> 
		<li><?php if($file == 6): echo '<b>Astronomy</b>'; else: echo '<a href="wx6.php" title="Sun and Moon Data">Astronomy</a>'; endif; ?></li>   
		<li><?php if($file == 7): echo '<b>Photos</b>'; elseif(intval($file) == 71): echo '<a href="wx7.php" title="Return to main Photo page"><b>Photos</b></a>'; else: echo '<a href="wx7.php" title="My Weather Photography">Photos</a>'; endif; ?></li>  
  <li><?php if($file == 8): echo '<b>About</b>'; else: echo '<a href="wx8.php" title="About this Weather Station and Website">About</a>'; endif; ?></li>  
  <li><?php if($file == 9): echo '<b>Links</b>'; else: echo '<a href="wx9.php" title="Useful Links">Links</a>'; endif; ?></li>
		<li><hr /></li>	 
		<li><span style="color:#38610B"><b>Detailed Data:</b></span></li>
		<li><?php if($file == 12): echo '<b>Rain</b>'; else: echo '<a href="wx12.php" title="Detailed Rain Data">Rain</a>'; endif; ?></li>	
		<li><?php if($file == 13): echo '<b>Wind</b>'; else: echo '<a href="wx13.php" title="Detailed Wind Data">Wind</a>'; endif; ?></li>	
		<li><?php if($file == 14): echo '<b>Temperature</b>'; else: echo '<a href="wx14.php" title="Detailed Temperature Data">Temperature</a>'; endif; ?></li>
		<li><?php if($file == 10): echo '<b>Humidity</b>'; else: echo '<a href="wx10.php" title="Detailed Humidity Data">Humidity</a>'; endif; ?></li>  
		<li><?php if($file == 20): echo '<b>Climate</b>'; elseif($file == 201): echo '<a href="wxaverages.php" title="Return to Climate summary"><b>Climate</b></a>'; else: echo '<a href="wxaverages.php" title="Long-term climate averages">Climate</a>'; endif; ?></li>   
	  <li><?php if($file == 15): echo '<b>System</b>'; else: echo '<a href="wx15.php" title="System Status and Miscellaneous"><font color="#C8C8C8">System</font></a>'; endif; ?></li> 
		<li><hr /></li>
	<li><span style="color:#0B614B"><b>Historical:</b></span></li>
	<li><?php if(($file == 40 || ($file > 49 && $file < 56)) && $summary == 0): echo '<b>Annual Tables</b>'; else: echo '<a href="wxhist12.php" title="Detailed Historical Annual Breakdowns">Annual Tables</a>'; endif; ?></li>	
	<li><?php if(($file == 40 || ($file > 49 && $file < 56)) && $summary == 1): echo '<b>Summary Tables</b>'; else: echo '<a href="wxsumhist12.php" title="Detailed Historical Summary Data">Summary Tables</a>'; endif; ?></li>
	<!--<li><?php // if($file == 84): echo '<b>Records</b>'; else: echo '<a href="wxrecords.php" title="Detailed Historical Records">Records</a>'; endif; ?></li> -->
	<li><?php if($file == 88): echo '<b>Annual Reports</b>'; else: echo '<a href="wxhistyear.php" title="Detailed Historical Annual Reports">Annual Reports</a>'; endif; ?></li>
	<li><?php if($file == 86): echo '<b>Monthly Reports</b>'; else: echo '<a href="wxhistmonth.php" title="Detailed Historical Monthly Reports">Monthly Reports</a>'; endif; ?></li>
	<li><?php if($file == 85): echo '<b>Daily Reports</b>'; else: echo '<a href="wxhistday.php" title="Detailed Historical Daily Reports">Daily Reports</a>'; endif; ?></li>
	<li><?php if($file == 87): echo '<b>Other</b>'; elseif($file == 871): echo '<a href="Historical.php" title="Return to Main historical-other page"><b>Other</b></a>'; else: echo '<a href="Historical.php" title="Historical data about page and other links">Other</a>'; endif; ?></li>
	<li><br /></li>
	<li><?php if($file == 96): echo '<b>Blog</b>'; else: echo '<a href="news.php" title="Website and weather station blog and news">Blog</a>'; endif; ?>
		<?php if(mktime()-3600*24*7 < mktime(23,0,0,10,1,2012)) { echo '<acronym style="border-bottom-width: 0" title="Latest post: 1st Oct"><sup style="color:green">new post</sup></acronym>'; } ?></li>
	<li><?php if($file == 95): echo '<b>Site map</b>'; else: echo '<a href="sitemap.php" title="Full website map/directory">Site map</a>'; endif; ?></li>
	</ul>
	<?php if($file == 111) { echo '<br /><br /><p class="sideBarTitle">Site Options</p><table><tr><td align="center">'; print_css_style_menu(1); echo '</td></tr></table>'; } ?>
	<p class="sideBarTitle">Site Options</p><table align="center">
	<tr><td><b>Units</b></td></tr><tr><td><form method="get" name="SetUnits" action=" <?php echo htmlentities($_SERVER['PHP_SELF']),'">
	<input name="unit" type="radio" value="US" onclick="this.form.submit();"'; if($unitT == 'F') { echo ' checked="checked"'; } echo ' />Imperial <br />
	<input name="unit" type="radio" value="UK" onclick="this.form.submit();"'; if($unitT == 'C') { echo ' checked="checked"'; } echo ' />UK <br />
	<input name="unit" type="radio" value="EU" onclick="this.form.submit();"'; if($unitW != 'mph') { echo ' checked="checked"'; } echo ' />Metric <br />
	<noscript><input type="submit" value="Go" /></noscript> </form></td></tr>'; ?>
	<tr><td align="center"><br /><b>Auto-update</b><span style="font-size:80%">	<sup><acronym title="Automatically refreshes the page when new data is available">[?]</acronym></sup></span></td></tr>
	<tr><td><form method="get" name="SetUpdate" action=" <?php echo htmlentities($_SERVER['PHP_SELF']),'">
	<input name="update" type="radio" value="on" onclick="this.form.submit();"'; if($auto == 'on') { echo ' checked="checked"'; } echo ' />On <br />
	<input name="update" type="radio" value="off" onclick="this.form.submit();"'; if($auto == 'off') { echo ' checked="checked"'; } echo ' />Off <br />
	<noscript><input type="submit" value="Go" /></noscript> </form></td></tr>'; ?>
    </table>
   </div>
 </div>