<?php require('unit-select.php');
	
	$file = 3;
	$subfile = true;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
function zerolead_hax($tag) {
	$tag = intval($tag);
	if($tag < 10 && $tag >= 0) { $tag = '0'.$tag; }
	return $tag;
}


$yproc = date("Y", mktime(0,0,0,$dmonth,$dday-1)); $mproc = date("m", mktime(0,0,0,$dmonth,$dday-1)); $dproc = date("d", mktime(0,0,0,$dmonth,$dday-1));
$datedescrip = 'Yesterday'; $direc = '';
if(isset($_GET['month'])) {
	$yproc = $_GET['year']; $mproc = $_GET['month']; $dproc = $_GET['day'];
	$datedescrip = date('jS F Y',mktime(0,0,0,intval($_GET['month']),intval($_GET['day']),$_GET['year']));
}
$datetag = $yproc . zerolead_hax($mproc) . zerolead_hax($dproc);
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Daily graph viewer</title>

	<meta name="description" content="View daily historical weather data (temperature, wind speed and direction, rainfall, dew point) graphs from NW3 weather.
		Graph for: <?php echo date('d F Y',mktime(0,0,0,intval($mproc),intval($dproc),$yproc)); ?>" />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main">

<h1>Daily Graph Archive</h1>

<br />
<a name="start"></a>

<?php
if(isset($_GET['month'])) { $gmnum = intval($_GET['month']); $gdnum = intval($_GET['day']); $gynum = $_GET['year']; $diff = 1; }
 else { $gmnum = date('m'); $gdnum = date('d'); $gynum = date('Y'); $diff = 2; }
$prevd = date("d", mktime(0,0,0,$gmnum,$gdnum-$diff,$gynum));
$prevm = date("m", mktime(0,0,0,$gmnum,$gdnum-$diff,$gynum));
$prevy = date("Y", mktime(0,0,0,$gmnum,$gdnum-$diff,$gynum));
$nextd = date("d", mktime(0,0,0,$gmnum,$gdnum+$diff,$gynum));
$nextm = date("m", mktime(0,0,0,$gmnum,$gdnum+$diff,$gynum));
$nexty = date("Y", mktime(0,0,0,$gmnum,$gdnum+$diff,$gynum));
$cond1 = mktime(0,0,0,$mproc,$dproc,$yproc) > mktime(0,0,0,2,1,2009) && mktime(0,0,0,$mproc,$dproc,$yproc) < mktime(1,0,0,$dmonth,$dday,$dyear);
$cond2 = mktime(0,0,0,$mproc,$dproc,$yproc) < mktime(0,0,0,$dmonth,$dday-1,$dyear) && mktime(1,0,0,$mproc,$dproc,$yproc) > mktime(0,0,0,1,31,2009);
if(!$cond1) { echo '<b>Archive begins on 1st February 2009</b>'; }
?>

<table width="460"><tr><td align="left">
<?php if($cond1) { echo '<a href="grapharchive.php?year=', $prevy, '&amp;month=', $prevm, '&amp;day=', $prevd, '#start" title="View previous day&#39;s 24hr graph">'; } ?>
&lt;&lt;Previous <?php if($cond1) { echo '</a>'; } ?></td>
<td align="center">
<form method="get" action="">
<?php dateFormMaker($yproc, $mproc, $dproc); ?>
<input type="submit" value="View" />
</form>
</td><td align="right">
<?php if($cond2) { echo '<a href="grapharchive.php?year=', $nexty, '&amp;month=', $nextm, '&amp;day=', $nextd, '#start" title="View next day&#39;s 24hr graph">'; } ?>
Next&gt;&gt; <?php if($cond2) { echo '</a>'; } ?></td>
</tr></table>

<?php
if(file_exists($root.'logfiles/daily/'.$datetag. 'log.txt')) {
	echo  '<img title="graph for ', $datedescrip, '" alt="Daily graph" src="/graphday.php?x=800&amp;y=450&amp;date=',  $datetag, '" />
		 <img title="graph for ', $datedescrip, '" alt="Daily graph2" src="/graphday2.php?x=800&amp;y=400&amp;date=',  $datetag, '" />
		 <img title="graph for ', $datedescrip, '" alt="Daily graph3" src="/graphdayA.php?x=800&amp;y=150&amp;type=wdir&amp;date=',  $datetag, '" />';
}
else {
	echo 'Daily graph not available for this day'; if(preg_match('/^66\.249/',$_SERVER['REMOTE_ADDR']) == 0) {
	mail("blr@nw3weather.co.uk","Graph not found","Notice! Graph requested but not available. File: ".$datetag.".gif; User: ".
	$_SERVER['REMOTE_ADDR']. ' '.$_SERVER['HTTP_USER_AGENT'], "From: server"); }
}
?>

</div>

<!-- ##### Footer ##### -->
	<?php require('footer.php'); ?>

</body>
</html>