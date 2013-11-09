<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 71.5; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) | Photos | Winter 08/09</title>

	<meta name="description" content="Old v2 - Weather photography of the England winter of 2008-2009 taken by Ben Lee-Rodgers of NW3 weather" />
	
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

<h1>England, Winter 08/09</h1>
<?php $imgref = '/photos/winter0809'; // Album properties
	$imgnum = 11;
	$albnum = 5;
	$imgdescrip = array(
	'Rimy Bushes',
	'Rimy Tree',
	'Rimy Bush',
	'Rimy Tree',
	'Rimy Park',
	'Snowy Garden',
	'14cm of accumulated snow',
	'Snowy Road',
	'Icicles on windowsill',
	'Snowy Tree in Park',
	'Icicles were widespread');
	
	$albdescrip = 'There are two sets of photos: the first set is of a rime-covered Dunham Park in Greater Manchester, taken on 1st January;
	the second set was captured on 2nd February and depict North London after heavy snowfall.'; // End of album properties

	require('albgen.php');
?>

</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>