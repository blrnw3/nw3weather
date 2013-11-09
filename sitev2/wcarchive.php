<?php require('unit-select.php');
		include("main_tags.php");
		$file = 871;
	?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Webcam image archive</title>

	<meta name="description" content="Old v2 - View daily historical web cam (weather skycam) image summaries from Hampstead Heath, North London, provided by NW3 weather.
		Images" />

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

<h1>Webcam image summary Archive</h1>
<!-- <span>(Starting 01/08/10)</span> -->

<br />
<a name="start"></a>

<?php
if(isset($_GET['day'])) { $dproc = intval($_GET['day']); $diff = 1; } else { $dproc = date('j',time()-86000); $diff = 2; }
if(isset($_GET['month'])) { $mproc = intval($_GET['month']); } else { $mproc = date('n',time()-86000); }
if(isset($_GET['year'])) { $yproc = $_GET['year']; } else { $yproc = date('Y',time()-86000); }
$sproc = mktime(0,0,0,$mproc,$dproc,$yproc);
$datetag = date('Ymd',$sproc) . 'daily';
$datedescrip = date('jS F Y',$sproc);
if($yproc < date('Y')) { $direc = $yproc.'/'; }

$prevs = $sproc - 3600*24; $nexts = $sproc + 3600*24;
$prevd = date('j', $prevs); $prevm = date('n', $prevs); $prevy = date('Y', $prevs);
$nextd = date('j', $nexts); $nextm = date('n', $nexts); $nexty = date('Y', $nexts);

$cond1 = $sproc > mktime(0,0,0,8,1,2010);
$cond2 = $sproc < mktime(0,0,0,$dmonth,$dday+1,$dyear) && mktime(1,0,0,$mproc,$dproc,$yproc) > mktime(0,0,0,7,31,2010);
$cond3 = $sproc < mktime(0,0,0,6,27,2012);
if($cond3) { $endtag = 'gif'; } else { $endtag = 'jpg'; $direc = date('Y',$sproc). '/'; }
if(!$cond1) { echo '<b>Archive begins on 1st August 2010</b>'; }
if(time() - $sproc < 24*3600) { $datetag = 'today'; $direc = ''; }
?>

<table width="612"><tr><td align="left">
<?php if($cond1) { echo '<a href="wcarchive.php?year=', $prevy, '&amp;month=', $prevm, '&amp;day=', $prevd, '#start" title="View previous day&#39;s images">'; } ?>
&lt;&lt;Previous <?php if($cond1) { echo '</a>'; } ?></td>
<td align="center">
<form method="get" action="">
<select name="year">
<?php 
for($i = 2010; $i <= $date_year; $i++) {
	echo '<option value="', $i, '"';
	if($yproc == $i) { echo ' selected="selected"'; }
	echo '>', $i, '</option>'; 
} ?>
</select>
<select name="month">
<?php $months = array('Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
for($i = 0; $i < 12; $i++) { 
	echo '<option value="', sprintf('%1$02d',$i+1), '"';
	if($mproc == $i+1) { echo ' selected="selected"'; }
	echo '>', $months[$i], '</option>'; 
} ?>
</select>
<select name="day">
<?php
for($i = 1; $i <= 31; $i++) {
	echo '<option value="', sprintf('%1$02d',$i), '"';
	if($dproc == $i) { echo ' selected="selected"'; }
	echo '>', $i, '</option>'; } ?>
</select>
<input type="submit" value="View" />
</form> &nbsp; <a href="wcarchive.php" title="Return to latest avaialble image">Reset</a>
</td><td align="right">
<?php if($cond2) { echo '<a href="wcarchive.php?year=', $nexty, '&amp;month=', $nextm, '&amp;day=', $nextd, '#start" title="View next day&#39;s images">'; } ?>
Next&gt;&gt; <?php if($cond2) { echo '</a>'; } ?></td>
</tr>

<?php
echo '<tr><td colspan="3">';

if(file_exists($root. $direc. $datetag. 'webcam.'.$endtag)) {
	echo  '<img title="Image for', $datedescrip, '" alt="Summary Image" src="/', $direc, $datetag, 'webcam.', $endtag, '" />';
}
else { echo 'Image not available for this day'; $mail9 = true; }

echo '</td></tr>';
 
if($cond3) {
	echo '<tr><td colspan="3">';
	 if(file_exists($root. $direc. $datetag. 'webcam2.gif')) { echo  '
	<img title="Image 2 for', $datedescrip, '" alt="Summary Image 2" src="/', $direc, $datetag, 'webcam2.gif" />'; }
	else { echo 'Image 2 not available for this day'; } echo '</td></tr>';
}
?>

</table>

</div>

<!-- ##### Footer ##### -->
	<? require('footer.php'); ?>
	
</body>
</html>