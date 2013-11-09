<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 871; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Monthly Breakdown</title>

	<meta name="description" content="Old v2 - Old-style current month weather breakdown statistics/data" />

<?php require('chead.php'); ?>
<?php include_once("ggltrack.php") ?> 
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
	
<h1>Current Month Breakdown</h1>

<pre>
<?php 
	$data = file('dailyreport.htm');
	$end = 200;
	for ($i = 1; $i < $end; $i++) {
		if(strpos($data[$i],"<PRE>") > -1) { $start = $i; }
		if(strpos($data[$i],"</PRE>") > -1) { $end = $i; }
	}
	for($l = $start+1; $l < $end; $l++) {
		echo $data[$l];
	}
?>
</pre>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?> 

</body>
</html> 