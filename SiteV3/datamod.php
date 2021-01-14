<?php
require('unit-select.php');
	 ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Data Input</title>

	<meta name="description" content="Data input/mod" />

	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
	<link rel="stylesheet" type="text/css" href="widestyle.css" media="screen" title="screen" />

	<?php include_once("ggltrack.php"); ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main">

<?php
if(!$me) {
	die('Not allowed from this IP. Admin only!');
}

if(isset($_GET['cerealify'])) {
	serialiseCSVm();
	die();
}

if(isset($_GET['dtm'])) { $dtm = $_GET['dtm']; } else { $dtm = 1; }
$mod_timestamp = mkdate(date('n'),date('j')-$dtm, date('Y'));

$sunrise_time = date_sunrise($mod_timestamp, SUNFUNCS_RET_STRING, $lat, $lng, $zenith, date('I'));
$sunset_time = date_sunset($mod_timestamp, SUNFUNCS_RET_STRING, $lat, $lng, $zenith, date('I'));

echo "Sunrise: $sunrise_time. Sunset: $sunset_time.<br />";
echo "Camlink: <a href='/highreswebcam.php?camtype=sky&light=day&width=6&freq=10'>Highres cam for today</a>";

//If less than sunhrs scrape time... WARN
if(date('Hi') < $sunGrabTime) {
	echo "<h2><b>WARNING: TOO EARLY!</b></h2><p>Cannot make any edits until after $sunGrabTime</p>";
}

//Link to EGLC pressure
echo '<br /><a href="http://www.wunderground.com/history/airport/EGLC/',
	date('Y/n/d',mktime(1,1,1,date('n'),date('d')-$dtm)),
	'/DailyHistory.html">EGLC History for yesterday</a><br />';

echo 'datt size in B: ', filesize($fullpath."datt" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv"), '<br />';
if(!isset($_POST['pwd'])) {
	$sun = file(ROOT.'maxsun.csv');
	echo '<br />Max sun for this day: ', $sun[date('z',$mod_timestamp)], ' hours';
}
echo '<br /><a href="datamod.php?dtm=', $dtm, '">Self link</a>';

$moddata = file($fullpath."dat" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv");
$modline = explode(',', $moddata[count($moddata)-$dtm]);

$moddatat = file($fullpath."datt" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv");
$modlinet = explode(',', $moddatat[count($moddatat)-$dtm]);

$moddatam = file($fullpath."datm" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv");
$modlinem = explode(',', $moddatam[count($moddatam)-$dtm]);
$modlinem = str_ireplace('?', ',', $modlinem);

// Work out which days need modding
$missing = [];
for($i = count($moddatam)-1; $i >= 0; $i--) {
	if(strContains($moddatam[$i], ',blr,')) {
		$missing[] = count($moddatam) - $i;
	}
}
echo '<br />Missing days (dtm): ';
foreach($missing as $m) {
	echo "<a href='/datamod.php?dtm=$m'>$m</a>, ";
}

if(isset($_POST['pwd'])) {
	if($_POST['pwd'] == 'datachanges') {
		for($i = 0; $i < count($modline)-2; $i++) {
			if(isset($_POST[$types_original[$i]]) && $_POST[$types_original[$i]] != '') {
				$modline[$i] = $_POST[$types_original[$i]];
			}
			if(isset($_POST[$types_original[$i].'t']) && $_POST[$types_original[$i].'t'] != '') {
				$modlinet[$i] = $_POST[$types_original[$i].'t'];
			}
		}
		$moddata[count($moddata)-$dtm] = implode(',', $modline);
		$fildat = fopen($fullpath."dat" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv","w");
		for($i = 0; $i < count($moddata); $i++) {
			$newline[$i] = explode(',', $moddata[$i]);
			$newline[$i][count($newline[$i])-1] = intval($newline[$i][count($newline[$i])-1]);
			//array_splice($newline[$i],-1);
			fputcsv($fildat, $newline[$i]);
		}
		fclose($fildat);

		$moddatat[count($moddatat)-$dtm] = implode(',', $modlinet);
		$fildatt = fopen($fullpath."datt" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv","w");
		for($i = 0; $i < count($moddatat); $i++) {
			$newlinet[$i] = explode(',', $moddatat[$i]);
			$newlinet[$i][count($newlinet[$i])-1] = intval($newlinet[$i][count($newlinet[$i])-1]);
			fputcsv($fildatt, $newlinet[$i]);
		}
		fclose($fildatt);

		for($i = 0; $i < count($modlinem); $i++) {
			if(isset($_POST[$types_m_original[$i]]) && $_POST[$types_m_original[$i]] != '') {
				$modlinem[$i] = str_ireplace(',', '?',$_POST[$types_m_original[$i]]);
			}
		}
		$moddatam[count($moddatam)-$dtm] = implode(',', $modlinem);
		$fildatm = fopen($fullpath."datm" . date('Y',mktime(1,1,1,date('n'),date('j')-$dtm,date('Y'))) . ".csv","w");
		for($i = 0; $i < count($moddatam); $i++) {
			$newlinem[$i] = str_ireplace('"','',explode(',', $moddatam[$i]));
			$newlinem[$i][count($newlinem[$i])-1] = intval($newlinem[$i][count($newlinem[$i])-1]);
			fputcsv($fildatm, $newlinem[$i]);
		}
		fclose($fildatm);
		echo "<p>Saved!</p>";
//		serialiseCSVm();

//		exec('/usr/local/bin/php -q /var/www/html/cron_tags.php blr ftw > /dev/null &');
	}
	else {
		echo 'password fail';
		print_m($_POST);
	}
}

//Form
$flags = array();
if($modline[$types["tmin"]] !== $modline[$types["nightmin"]]) {
	$flags[$types["nightmin"]] = true;
}
if($modline[$types["tmax"]] !== $modline[$types["daymax"]]) {
	$flags[$types["daymax"]] = true;
}
$modTimestamp = mkdate($dmonth,$dday-$dtm,$dyear);
$target_st = date('Y', $modTimestamp) . '/stitchedmaingraph_';
$target_en = date('Ymd', $modTimestamp) .'.png';
echo '<form method="get" action=""><input type="text" name="dtm" /> <input type="submit" value="Choose day" /></form><br />';
echo 'Viewing ', date( 'jS F Y', $modTimestamp );
echo '<br />
	<form method="post" action="">
	<table border="1" cellpadding="4">';
for($i = 0; $i < count($modline); $i++) {
	if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
	$flagClass = $flags[$i] ? " style='font-weight:bold;font-size:110%;text-decoration:underline;'" : "";
	echo '<tr class="row', $style ,'">
		<td class="td', $nums_all[$i] + 10 ,'C">', $types_original[$i], '</td>
		<td class="td', $nums_all[$i] + 10 ,'C"',$flagClass,'>', $modline[$i], ' </td>
		<td><input type="text" name="', $types_original[$i], '" /> </td>
		<td class="td', $nums_all[$i] + 10 ,'C">', $modlinet[$i], ' </td>
		<td><input type="text" name="', $types_original[$i], 't" /> </td>';
		if($i==0) {
			echo '<td align="center" rowspan=', count($modline), '">';

			$graphres = $target_st .''. $target_en;
			if(file_exists(ROOT. $graphres)) {
				echo '<img src="/'. $graphres .'" alt="day graph" '. GRAPH_DIMS_LARGE .' />
					';
			} else {
				echo '<h3>FAIL GRAPH BIG!</h3>';
			}
				echo '</td>';
		}
		echo '</tr>';
}
echo '</table>
	<br />
	<table border="1" cellpadding="5">';
for($i = 0; $i < count($modlinem); $i++) {
	if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
	echo '<tr class="row', $style ,'"><td class="td', $data_m_num[$i] + 10 ,'C">', $types_m_original[$i], '</td>
		<td class="td', $data_m_num[$i] + 10 ,'C">', $modlinem[$i], ' </td>
		<td><input type="text" name="', $types_m_original[$i], '" /> </td>';
	if($i==0) {
		echo '<td align="center" rowspan=', count($modlinem), '">';
		echo '<div><video id="tvid" width="864" height="650" controls><source src="/cam/timelapse/skycam_', date('Ymd', $modTimestamp) ,'.mp4" type="video/mp4"></video></div>';
		echo '</td>';
	}
	echo '</tr>';
}
echo '</table><br />
	<input type="text" name="user" />
	<input type="password" name="pwd" />
	<select name="dtm"><option value="', $dtm, '">Yesterday-', $dtm, '</option></select>
	<input type="submit" value="Submit Changes" />
	</form>';
	echo '<br />
	';
	$graphres = $target_st .'small_'. $target_en;


?>

<img src="/<?php echo date("Y/Ymd", $modTimestamp); ?>dailywebcam.jpg" alt="daycamsum" />
<h2>Rules for manual observations</h2>
<p>
	<dl>
		<dt>Snow</dt>
		<dd>y: all snow; 0.1: trace; float: estimate of snow amount if there was some rain too</dd>
		<dt>Ly snow</dt>
		<dd>0.1: trace; float: non-trace, specified quantity</dd>
		<dt>hail</dt>
		<dd>1-3 scale: 1 small, 2 med, 3 large</dd>
		<dt>Thunder</dt>
		<dd>1-4 scale: 1 thunder; 2 light TS, 3 med TS, 4 sev TS</dd>
		<dt>Fog</dt>
		<dd>blank or 1</dd>
		<dt>comms</dt>
		<dd>
			<pre>
$finds = array('"','?','(S)','(M)','(L)','Shwr','L ','M ','H ','T ','S ','V ','-','Snw','L-Sn','Sn','LySn','w/ ','AF', 'T-S', //1
	'occ',"tr'cm",'bkn','Dz','Rn','oc','poss',' yy','aa','L/','M/','H/','T/','S/','V/', 'T-storm', 'T-Storm', //2
	'L-','M-','H-','T-','S-','V-','xx','Sh','Lyn','Drzl','Slt','SnowS','brks','inc ', //3
	"Sl't", 'sct', 'erws', 'bk', 'L,','M,','H,','T,','S,','V,', 'Heath', 'Severe Heat', 'Heat', 'Fz', ' w ', //4
	'L)','M)','H)','T)','S)','V)','L;','M;','H;','T;','S;','V;','nowow', //5
	'hhh', 'Max Sun'); //6
$repls = array('',',','','','','Sh','Light ','Moderate ','Heavy ','Torrential ','Slight ','Very Heavy ','-','Sn','LySn','Snow','Lying Snow','with ','Air frost', 'T-storm', //1
	'oc','trace','bk','Drizzle','Rain','occasional','possible','','','Light/','Moderate/','Heavy/','Torrential/','Slight/','Very Heavy/', 'T-Storm', 'Thunderstorm',//2
	'Light-','Moderate-','Heavy-','Torrential-','Slight-','Very Heavy-','','Shower','Lying','Drizzle','Sleet','Snow S','breaks', 'including ', //3
	'Sleet', 'scattered', 'ers', 'broken','Light,','Moderate,','Heavy,','Torrential,','Slight,','Very Heavy,', 'hhh', 'Heat', '', 'Freezing', ' with ', //4
	'Light)','Moderate)','Heavy)','Torrential)','Slight)','Very Heavy)','Light;','Moderate;','Heavy;','Torrential;','Slight;','Very Heavy;','now', //5
	'Heath',''); //6
			</pre>
		</dd>
	</dl>
</p>
<?php
if(file_exists(ROOT. $graphres)) {
	echo '<img src="/'. $graphres .'" alt="day graph" '. GRAPH_DIMS_SMALL .' />
		';
} else {
	echo '<h3>FAIL GRAPH SMALL!</h3>';
}
?>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>
<?php
##################################### MONTHLY REPORT GENERATION ###################################################

?>