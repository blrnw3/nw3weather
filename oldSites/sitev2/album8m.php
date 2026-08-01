<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 71.8; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) | Photos | London TS, Jan 11</title>

	<meta name="description" content="Old v2 - Weather photography from the North London Thunderstorm of January 2011 taken by Ben Lee-Rodgers of NW3 weather" />
	
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

<h1>London NW3, January 2011 Thunderstorm</h1>
<?php $imgref = '/photos/janlondonts'; // Album properties
	$imgnum = 7;
	$albnum = 8;
	$imgdescrip = array(
	'View to the north-east part 1',
	'View to the north-east part 2',
	'View to the north-east part 3',
	'View to the north-east part 4',
	'View to the south-east part 1',
	'View to the south-east part 2',
	'View to the south-east part 3');
	
	$albdescrip = 'The photos were taken on the afternoon of 14th January 2011 following a rare winter thunderstorm.'; // End of album properties

	require('albgen.php');
?>

</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>