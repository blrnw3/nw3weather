<?php require('unit-select.php');
		include("main_tags.php");
		$file = 871;
	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
$yproc = date("Y", mktime(0,0,0,$date_month,$date_day-1)); $mproc = date("m", mktime(0,0,0,$date_month,$date_day-1)); $dproc = date("d", mktime(0,0,0,$date_month,$date_day-1));
$datedescrip = 'Yesterday'; $direc = $yproc;
if(isset($_GET['month'])) {
	$yproc = $_GET['year']; $mproc = $_GET['month']; $dproc = $_GET['day'];
	$datedescrip = date('jS F Y',mktime(0,0,0,intval($_GET['month']),intval($_GET['day']),$_GET['year']));
	$direc = (int)$_GET['year'].'/';
}
$datetag = $yproc.$mproc.$dproc;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Daily graph viewer</title>

	<meta name="description" content="Old v2 - View daily historical weather data (temperature, wind speed and direction, rainfall, dew point) graphs from NW3 weather. 
		Graph for: <?php echo date('d F Y',mktime(0,0,0,intval($mproc),intval($dproc),$yproc)); ?>" />

	<? require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?> 
</head>

<body>
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<? require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main-copy">

<h1>Daily Graph Archive</h1>

<br />
<a name="start"></a>

<?php
if(isset($_GET['month'])) { $gmnum = intval($_GET['month']); $gdnum = intval($_GET['day']); $gynum = $_GET['year']; $diff = 1; }
 else { $gmnum = date('m'); $gdnum = date('d'); $gynum = date('Y'); $diff = 2; }
$prevd = date("d", mktime(0,0,0,$gmnum,$gdnum-$diff));
$prevm = date("m", mktime(0,0,0,$gmnum,$gdnum-$diff));
$prevy = date("Y", mktime(0,0,0,$gmnum,$gdnum-$diff,$gynum));
$nextd = date("d", mktime(0,0,0,$gmnum,$gdnum+$diff));
$nextm = date("m", mktime(0,0,0,$gmnum,$gdnum+$diff));
$nexty = date("Y", mktime(0,0,0,$gmnum,$gdnum+$diff,$gynum));
$cond1 = mktime(0,0,0,$mproc,$dproc,$yproc) > mktime(0,0,0,2,2,2009) && mktime(0,0,0,$mproc,$dproc,$yproc) < mktime(1,0,0,$date_month,$date_day,$date_year);
$cond2 = mktime(0,0,0,$mproc,$dproc,$yproc) < mktime(0,0,0,$date_month,$date_day-1,$date_year) && mktime(1,0,0,$mproc,$dproc,$yproc) > mktime(0,0,0,2,1,2009);
if(!$cond1) { echo '<b>Archive begins on 2nd February 2009</b>'; }
?>

<table width="460"><tr><td align="left">
<?php if($cond1) { echo '<a href="graphviewer.php?year=', $prevy, '&amp;month=', $prevm, '&amp;day=', $prevd, '#start" title="View previous day&#39;s 24hr graph">'; } ?>
&lt;&lt;Previous <?php if($cond1) { echo '</a>'; } ?></td>
<td align="center">
<form method="get" action="">
<select name="year">
<?php 
for($i = 2009; $i <= $date_year; $i++) {
	echo '<option value="', $i, '" ';
	if(isset($_GET['year'])) { if(intval($_GET['year']) == $i) { echo 'selected="selected"'; } } else { if(date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)) == $i) { echo 'selected="selected"'; } }
	echo '>', $i, '</option>'; 
} ?>
</select>
<select name="month">
<?php $months = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
for($i = 0; $i < 12; $i++) { 
	echo '<option value="', sprintf('%1$02d',$i+1), '" ';
	if(isset($_GET['month'])) { if(intval($_GET['month']) == $i+1) { echo 'selected="selected"'; } } else { if(date("m", mktime(0,0,0,$date_month,$date_day-1)) == $i+1) { echo 'selected="selected"'; } }
	echo '>', $months[$i], '</option>'; 
} ?>
</select>
<select name="day">
<?php 
for($i = 1; $i <= 31; $i++) {
	echo '<option value="', sprintf('%1$02d',$i), '" ';
	if(isset($_GET['day'])) { if(intval($_GET['day']) == $i) { echo 'selected="selected"'; } } else { if(date("d", mktime(0,0,0,$date_month,$date_day-1)) == $i) { echo 'selected="selected"'; } }
	echo '>', $i, '</option>'; } ?>
</select>
<input type="submit" value="View" />
</form>
</td><td align="right">
<?php if($cond2) { echo '<a href="graphviewer.php?year=', $nexty, '&amp;month=', $nextm, '&amp;day=', $nextd, '#start" title="View next day&#39;s 24hr graph">'; } ?>
Next&gt;&gt; <?php if($cond2) { echo '</a>'; } ?></td>
</tr></table>

<?php 
if(file_exists($absRoot. $direc. $datetag. '.gif')) {
	echo  '<img title="graph for ', $datedescrip, '" alt="Daily graph" src="/', $direc, $datetag, '.gif" />'; 
}
else {
	echo 'Daily graph not available for this day';
	//if(preg_match('/^66\.249/',$_SERVER['REMOTE_ADDR']) == 0) {
	//	mail("blr@nw3weather.co.uk","Graph not found","Notice! Graph requested but not available. File: ".$datetag.".gif; User: ".
		// $_SERVER['REMOTE_ADDR']. ' '.$_SERVER['HTTP_USER_AGENT'], "From: server");
	// }
}
?>

</div>

<!-- ##### Footer ##### -->
	<? require('footer.php'); ?>
	
</body>
</html>