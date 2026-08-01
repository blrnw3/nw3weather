<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<title>WxApp Usage Stats</title>
		<?php
		require '../config.php';
		require './functions.php';
		if(!$desktop) {
			echo ' <meta name="viewport" content="width=device-width, user-scalable=no" />';
		} else {
			$deskOn = '&desktop';
		}
		?>
		<meta name="description" content="City weather rankings across Europe" />
		<meta name="keywords" content="weather, Europe, nw3, data, records, statistics, rankings, live" />
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
		<meta http-equiv="content-language" content="en-GB" />

		<link rel="stylesheet" type="text/css" href="./style.css" media="screen" title="screen" />
		<style type="text/css">
		</style>
	</head>
	<body>
		<!-- ##### Header ##### -->
	<div id="background">
<div id="page">
<div id="header">
  <?php echo date('D d M Y, H:i', 70 + (int) file_get_contents($API_root ."updated.txt")); ?> GMT
</div>

	<!-- ##### Main ##### -->
	<div id="main">
	<h2>Stats since 25th Jan 2014</h2>
<?php
//Connect to server
$con = mysql_connect("localhost",$db_username,$db_password);
if (!$con) {
	die('Fatal Error');
}

//Connect to database
$db = mysql_select_db($db_name, $con);
if (!$db) {
	die('Fatal Error');
}

//Query database
$cond = isset($_GET['recency']) && ($_GET['recency'] == 0);
$type = $cond ? "`LastGet` DESC, `Accesses` DESC" : "`Accesses` DESC, `LastGet` DESC";
$link = $cond ? "Popularity" : "Recency";
$linkVal = $cond ? 1 : 0;
$past24hrs = time() - 24 * 3600;

$result1 = mysql_query("SELECT SUM( `Accesses` ) AS cnt FROM `$db_table`");
$result = mysql_query("SELECT `Name`, `Country`, `Accesses`, `LastGet` FROM `$db_table` ORDER BY ".$type);
$result2 = mysql_query("SELECT COUNT(`Accesses`) FROM `$db_table` WHERE `Accesses` = 0");
$result3 = mysql_query("SELECT COUNT(`Accesses`) FROM `$db_table` WHERE `LastGet` > $past24hrs");

$overall = mysql_fetch_array($result1);
$unuseda = mysql_fetch_array($result2);
$recent = mysql_fetch_array($result3);
$unused = $unuseda[0];
$totCnt = $overall[0];
$reqCnt = floor($totCnt / 9);
$recentGets = $recent[0];

$self = $_SERVER['PHP_SELF'];
echo "<p style='margin-left:0.6em;'>
	<b>$reqCnt</b> total query blocks (9 city queries per block)<br />
	<b>$recentGets</b> different cities queried in the past 24 hrs<br />
	<b>$unused</b> cities never queried :(
	</p>
	<a href='". $self ."?recency=". $linkVal. $deskOn ."'>Sort by $link</a>
";

//Produce output from query
$c = 0;
$currTime = time();
echo  '
	<table>
	<tr>
		<th id="tableTitle" colspan="5">Most Popular Requests</th>
	</tr>
	<tr class="table-top">
		<td>City</td>
		<td>Country</td>
		<td>Count</td>
		<td>Last</td>
		<td>Rank</td>
	</tr>';
while($row = mysql_fetch_array($result)) {
	if($row[0] == 'Hampstead') {
		$extraClass = ' userCity';
	} else {
		$extraClass = '';
	}
	echo '<tr class="'.alternateColour($c, "row").' r'. $extraClass .'">
		<td class="city">'.$row[0].'</td>
		<td class="city">'.$row[1].'</td>
		<td class="'.valcolr($row[2]).'">'.$row[2].'</td>
		<td class="'.valcolr(($currTime - $row['LastGet'])/200).'">'. secsToReadable($currTime - $row['LastGet']) .'</td>
		<td class="rank">'.$c.'</td>
	</tr>';
	$c++;
}

echo "</table>";

mysql_close($con);
?>
		</div>

<!-- ##### Footer ##### -->
<div id="footer">
	<div>
		<a href="#header">Top</a>
	</div>
	<div>
		&copy; 2012-<?php echo date('Y'); ?>, Ben Lee-Rodgers
	</div>
	<div>
		NB: Accuracy and reliability of data is not guaranteed
	</div>
	<div>
		<span style="font-size:85%">
			<?php $phpload = roundToDp( microtime(get_as_float) - $scriptbeg, 3 );
				echo 'Version 1.0 | Script executed in ' . $phpload . 's'; ?>
		</span>
	</div>
</div>

</div>
</div>

	</body>
</html>
<?php
if(!$me) {
	file_put_contents( $WEB_root .'visitStatsLog.txt', date("H:i:s d/m/Y") . "\t" .
		$phpload . "\t" . "$deskon  " . $link. "\t" .  $ipaddy. ' ' .
		$_SERVER['HTTP_USER_AGENT'] . "\r\n", FILE_APPEND );
}
?>
