<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 71.4; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) | Photos | Cloudscapes</title>

	<meta name="description" content="Old v2 - Weather photography of assorted cloudscapes in Europe taken by Ben Lee-Rodgers of NW3 weather" />
	
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

<h1>Assorted Cloudscapes</h1>
<?php $imgref = '/photos/ascloud'; // Album properties
	$imgnum = 15;
	$albnum = 4;
	$imgdescrip = array(
	'London, 31st July 2010: Cirrocumulus Floccus',
	'Mallorca, 1st April 2010: Cirrocumulus Undulatus',
	'London, 1st May 2008: Evening Rainbow',
	'London, 3rd September 2009: Altocumulus Floccus',
	'Italy, 14th August 2007: Cumulus Congestus',
	'Italy, 18th August 2007: Cirrocumulus Stratiformis',
	'Wales, 2nd August 2007: Sunset with various Altocumulus',
	'French Alps, 13th July 2009: Cirrocumulus Lenticularis (rare!)',
	'London, 5th November 2007: Altocumulus Translucidus',
	'London, 8th September 2010: Rainbow and Cumulonimbus Incus',
	'London, 15th June 2009: Cumulonimbus Calvus',
	'London, 15th June 2009: Post-thunderstorm overhead Cb',
	'London, 27th June 2009: Distant Cb Cal',
	'Italy, 12th August 2009: Decaying Cumulus Congestus',
	'London, 27th October 2008: Cirrus Fibratus');
	
	$albdescrip = 'The photos were taken in various location across Western Europe. New photos are added yearly.<br />
	Hover over a photo to get its details.'; // End of album properties

	require('albgen.php');
?>

</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>