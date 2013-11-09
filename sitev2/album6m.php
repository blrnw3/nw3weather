<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 71.6; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) | Photos | London Snowfall April 2008</title>

	<meta name="description" content="Old v2 - Weather photography following the heavy London snowfall of April 2008 taken by Ben Lee-Rodgers of NW3 weather" />
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

<h1>North London, Heavy Sowfall of April 2008</h1>
<?php $imgref = '/photos/snowapr08'; // Album properties
	$imgnum = 12;
	$albnum = 6;
	$imgdescrip = array(
	'East Finchley: Snow-covered Garden',
	'East Finchley: Snow-covered Apple Tree',
	'Edge of Kenwood, Hampstead Heath: Snowy field',
	'Kenwood, Hampstead Heath: Snowy Foliage',
	'Kenwood, Hampstead Heath: Snow-lined branches',
	'Kenwood, Hampstead Heath: Snowy Trees',
	'Kenwood, Hampstead Heath: More Snowy Trees',
	'Kenwood House, Hampstead Heath: View to the Lake from Main Path',
	'Kenwood, Hampstead Heath: Snowy Tree',
	'Kenwood, Hampstead Heath: View to a Snowy Kenwood House',
	'Deep in Hampstead Heath: Snowy Landscape',
	'Deep in Hampstead Heath: Another Snowy Landscape');

	if($unitR == 'mm') { $depth = '6 cm'; } else { $depth = '2.5 in'; }
	$albdescrip = 'The photos were taken after an unusually late, heavy snowfall of ' . $depth . ' on the 6th April 2008.
	<br /> Jump to main photos or hover over thumbnail to get the location.';
	
	require('albgen.php');
?>

</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>