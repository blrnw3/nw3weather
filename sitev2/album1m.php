<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 71.1; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) | Photos | Italy 2010</title>

	<meta name="description" content="Old v2 -  Weather photography from Tuscany, Italy in summer (August) 2010 taken by Ben Lee-Rodgers of NW3 weather" />
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
<div align="center">
<h1>Tuscany, Italy, August 2010</h1>
<?php $imgref = '/photos/italy2010'; // Album properties
	$imgnum = 9;
	$albnum = 1;
	$imgdescrip = array(
	'Approaching Thunderstorm',
	'Cumulonimbus Formations',
	'Sundog',
	'Shelf Cloud',
	'Post-thunderstorm Mammatus Cloud',
	'Thunderstorm Clears',
	'Hazy Sunset',
	'Post-Thunderstorm Mist',
	'Clean Sunset');

	if($unitW == 'mph') { $dist = '~20 miles'; } else { $dist = '~30 km'; } 
	$albdescrip = 'All photos were taken near a town called Castelmuzio, which is ' . $dist . ' south of Sienna.';
	
	require('albgen.php');
?>

</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>