<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 85; ?>
<?php
if(isset($_GET['month'])) { $mproc = $_GET['month']; } else { $mproc = date('m'); }
if(isset($_GET['year'])) { $yproc = $_GET['year']; } else { $yproc = date('Y'); }
$datefile = $yproc . date('m',mktime(1,1,1,$mproc));
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Daily reports</title>

	<meta name="description" content="Old v2 - Detailed historical daily breakdown report for <?php echo date('F Y',mktime(0,0,0,$mproc,1,$yproc)); ?> 
	with daily weather data graph from NW3 weather" />

<?php require('chead.php'); ?>
<?php include_once("ggltrack.php") ?> 

<!-- toggle 24 Hour Daily Chart images -->
<script type="text/javascript" language="JavaScript">
function toggleDisplay(imgID) {
if(document.getElementById(imgID).style.display == "none" ) {
			document.getElementById(imgID).style.display = "";
} else {
			document.getElementById(imgID).style.display = "none";
}
}
</script>
<!-- end toggle 24 Hour Daily Chart images -->

</head>

<body>
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

	<div id="main-copy">
	
<?php if($unitT == 'F' || $unitW == 'km/h') { echo '<b>Sorry, unit conversion not yet available for this page</b>'; } ?>
	
<h1>Daily Reports</h1>
<?php 

# 4 digit year of oldest report on server
$startyear = 2009;
# Location of where the data files are located.
$webdir = './';
# Location of where the graph files are located. 
$wxh_graphs_path = '';

require_once('include-wxhistory.php');
?>

<?php
echo '<table style="border: solid 2px #088A4B" width="60%" align="left"><tr><td align="center" style="color:#4B088A">';
echo '<b>Site Version 3:</b> A <a href="/sitev3/wxhistday.php"> new version of this page</a> is now avaliable for preview. See <a href="/news.php">blog post</a> for info.';
echo '</td></tr></table><br /><br />';

$dim = date('t',mktime(0,0,0,$mproc,1,$yproc));
if(intval($mproc) == 10 && $yproc == 2009) echo '<b>Special note</b>: Data is suspect for this month due to partial data loss';
if(intval($mproc) < 8 && intval($mproc) != 1 && $yproc == 2009) echo '<b>Special note</b>: Wind data not valid for this month (valid records began in Aug 2009)';
if(intval($mproc) < 8 && intval($mproc) > 3 && $yproc == 2010) echo '<b>Special note</b>: Wind data not valid for this month (suspended 17th April - 28th July; replaced by METAR data from Heathrow)'; 
$cond1 = mktime(0,0,0,$mproc,1,$yproc) > mktime(0,0,0,2,2,2009) && mktime(0,0,0,$mproc,1,$yproc) < mktime(1,0,0,$date_month+1,1,$date_year);
$cond2 = mktime(0,0,0,$mproc,$dim,$yproc) < mktime(0,0,0,$date_month,$date_day-1,$date_year) && mktime(1,0,0,$mproc,3,$yproc) > mktime(0,0,0,1,1,2009);
$cond3 = mktime(0,0,0,$mproc,1,$yproc) < mktime(0,0,0,$date_month,$date_day-1,$date_year) && mktime(1,0,0,$mproc,3,$yproc) > mktime(0,0,0,2,1,2009);
if(!$cond1 && mktime(0,0,0,$mproc,1,$yproc) < time()) { echo '<br /><b>First report available is February 2009</b>'; }
if(date('j') == 1 && $mproc == intval(date('m'))) { echo 'First report data for this month available from 00:12 on the 2nd'; }

$prevm = date("m", mktime(0,0,0,$mproc-1)); $nextm = date("m", mktime(0,0,0,$mproc+1));
if(intval($mproc) == 12) { $nexty = $yproc+1; } else { $nexty = $yproc; } if(intval($mproc) == 1) { $prevy = $yproc-1; } else { $prevy = $yproc; }
?>

<table width="600">
<tr>
<td align="left">
<?php if($cond1) { echo '<a href="wxhistday.php?year=', $prevy, '&amp;month=', $prevm, '" title="View previous month&#39;s report">'; } ?>
&lt;&lt;Previous Month<?php if($cond1) { echo '</a>'; } ?></td>
<td align="center"><form method="get" action="">
<select name="year">
<?php 
for($i = 2009; $i <= $date_year; $i++) {
	echo '<option value="', $i, '"';
	if(isset($_GET['year'])) { if(intval($_GET['year']) == $i) { echo 'selected="selected"'; } } else { if(date("Y", mktime(0,0,0,$date_month,$date_day-1,$date_year)) == $i) { echo 'selected="selected"'; } }
	echo '>', $i, '</option>'; 
} ?>
</select>
<select name="month">
<?php $months = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
for($i = 0; $i < 12; $i++) { 
	echo '<option value="', sprintf('%1$02d',$i+1), '"';
	if(isset($_GET['month'])) { if(intval($_GET['month']) == $i+1) { echo 'selected="selected"'; } } else { if(date("m", mktime(0,0,0,$date_month,$date_day-1)) == $i+1) { echo 'selected="selected"'; } }
	echo '>', $months[$i], '</option>'; 
} ?>
</select>
<input type="submit" value="View Report" />
</form>
</td><td align="right">
<?php if($cond2) { echo '<a href="wxhistday.php?year=', $nexty, '&amp;month=', $nextm, '" title="View next month&#39;s report">'; } ?>
Next Month&gt;&gt;<?php if($cond2) { echo '</a>'; } ?></td>
</tr></table>

<?php 
if($cond3) {
	echo '<p> Shortcut to day: ';
	for($i=1; $i<=31; $i++) {
		if($i <= date('t',mktime(0,0,0,$mproc)) && mktime(0,0,0,$mproc,$i,$yproc) < mktime(0,0,0,$date_month,$date_day,$date_year)) { echo '<a href="#', $i, '">', $i, '</a> '; }
	}
	echo '<br /><br />
	The monthly summary can be found at the <a href="#summary" title="Jump to monthly summary"> bottom</a>
	 (Or in much greater detail
	 <a href="wxhistmonth.php?month=',$mproc,'&amp;year=',$yproc,'" title="Monthly report for ', monthfull($mproc), ' ', $yproc,'">here</a>)
	</p>';
}
?>

<noscript>
<p>Javascript must be enabled to view the 24 Hour Graph images.</p>
</noscript>

<?php
$rep = date("F", mktime(0,0,0,$mproc)).date("Y", mktime(0,0,0,1,1,$yproc)).'.htm';
if(file_exists($rep)) { 
	display_data($datefile); // Call wx-hist-include
	echo '<br />Rainfall values which are asterisked correspond to manually recorded snowfall, and are estimates of the water equivalent (the rain guage doesn&#39;t work in snow).';
}
else { echo 'Daily breakdown not available for ', monthfull($mproc), ' ', $yproc; if($yproc == 2009 && intval($mproc == 1)) echo ' <br />Records began in Feb 2009'; }
?>
</div><!-- end main-copy -->

<!-- ##### Footer ##### -->
<? require('footer.php'); ?> 

</body>
</html> 