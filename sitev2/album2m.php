<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 71.2; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) | Photos | Wales Summer 2010</title>

	<meta name="description" content="Old v2 - Weather photography of sunsets, cloudscapes and landscapes in North Wales (Harlech and Snowdon) in the Summer of 2010, taken by Ben Lee-Rodgers of NW3 weather" />
	
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

<h1>North Wales, Summer 2010</h1>
<?php $imgref = '/photos/walesaug10'; // Album properties
	$imgnum = 9;
	$albnum = 2;
	$imgdescrip = array(
	'5th July: Sunset one, part one',
	'5th July: Sunset one, part two',
	'5th July: Sunset one, part three',
	'25th August: Sunset two, part one',
	'25th August: Sunset two, part two',
	'25th August: Sunset two, part three',
	'25th August: Sunset two, part four',
	'27th August: Harlech Beach, Cloudscape',
	'30th August: Snowdon, Landscape');
	
	$albdescrip = 'The photos were taken in July and August 2010 in North Wales
	- the sunsets and cloudscape in Harlech, overlooking the Lleyn Peninsular; and the landscape from Snowdon.'; // End of album properties

	require('albgen.php');
?>

</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>