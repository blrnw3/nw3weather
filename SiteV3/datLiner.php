<?php require('unit-select.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php 
	$file = 112; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Datifier</title>

	<meta name="description" content="PHP script testing 2 for NW3 weather" />

	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
	<link rel="stylesheet" type="text/css" href="./mainstyle.css" media="screen" />

</head>

<body>

	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main">

<h1>Dat Line Producer</h1>

<?php
$dproc = isset($_GET['day']) ? intval($_GET['day']) : (int)$day_yest;
$mproc = isset($_GET['month']) ? intval($_GET['month']) : (int)$mon_yest;
$yproc = isset($_GET['year']) ? intval($_GET['year']) : (int)$yr_yest;

$sproc = mktime(12,0,0,$mproc, $dproc, $yproc);
$prevs = $sproc - 3600*24; $nexts = $sproc + 3600*24;
$prevd = date('j', $prevs); $prevm = date('n', $prevs); $prevy = date('Y', $prevs);
$nextd = date('j', $nexts); $nextm = date('n', $nexts); $nexty = date('Y', $nexts);
?>

<table width="800">
<tr>
<td align="left">
<?php
echo '<a href="datLiner.php?year=', $prevy, '&amp;month=', $prevm, '&amp;day=', $prevd, '" title="View previous day&#39;s report">
	&lt;&lt;Previous Day</a>';
?></td>
<td align="center">
	<form method="get" action="">
	<?php dateFormMaker($yproc, $mproc, $dproc); ?>
	<input type="submit" value="View Report" />
	</form>
<a href="datLiner.php" title="Return to most recent day">Reset</a>
</td>
<td align="right">
<?php
echo '<a href="datLiner.php?year=', $nexty, '&amp;month=', $nextm, '&amp;day=', $nextd, '" title="View next day&#39;s report">
	Next Day&gt;&gt;</a>';
?></td>
</tr>
</table>

<?php
//echo $mproc,$dproc.$yproc;
$custom = dailyData( date('Ymd', mkdate($mproc,$dproc,$yproc)) );

//array("min" => $mins, "max" => $maxs, "mean" => $means, "timeMin" => $timesMin, "timeMax" => $timesMax,
				// "trend" => $trends, "trendRn" => $rnCums, "changeHr" => $hrChanges, "changeDay" => $hr24Changes,
				// "misc" => array("frosthrs" => $frosthrs, "rnrate" => $currRate, "rnduration" => $rnDuration,
								// "rnlast" => $lastRnFull, "wethrs" => $wethrs, "maxhrgst" => $maxhrgst, "cnt" => $end,
								// "prevRn" => date('r', $prevRn), "prevRnOld" => date('r', $prevRnOld)
							// )
			// );
print_m($custom);

?>
<br />
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>