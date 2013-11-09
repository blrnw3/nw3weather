<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 71.9; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) | Photos | Winter 10/11</title>

	<meta name="description" content="Old v2 - Weather photography from the UK Winter of 2010-2011 taken by Ben Lee-Rodgers of NW3 weather" />
	
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

<h1>UK, Winter 2010-2011</h1>
<?php $imgref = '/photos/winter1011'; // Album properties
	$imgnum = 11;
	$albnum = 9;
	$imgdescrip = array(
	'Cambridge, 29th November: First snowfall of the season',
	'London, 9th December: Cloudscape of rooftop view to the west',
	'London, 9th December: Sunset',
	'Oxford, 18th December: Icicles',
	'Oxford, 18th December: Frozen river',
	'Oxford, 18th December: Rooftop view of the only significant snowfall of the season',
	'London, 20th December: Snow on Hampstead Heath with weather station visible in the foreground',
	'London, 25th December: Thickly-frozen lake on Hampstead Heath',
	'London, 25th December: View over the frozen paths and hills of Hampstead Heath as twilight nears',
	'Harlech (North Wales), 30th December: Sunset over the Irish Sea',
	'London, 9th January: Moon and Jupiter viewed at dusk after a clear day');
	
	$albdescrip = 'The photos were taken at various locations around the country and on various dates, though concentrated in this season&apot;s extremely cold December.
<br /> Jump to main photos or hover over thumbnail to get the location and date.'; // End of album properties

	require('albgen.php');
?>

</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>