<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 71.3; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) | Photos | Winter 09/10</title>

	<meta name="description" content="Old v2 - Weather photography from the England Winter of 2009-1010 taken by Ben Lee-Rodgers of NW3 weather" />
	
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

<h1>England, Winter 2009-2010</h1>
<?php $imgref = '/photos/winter0910'; // Album properties
	$imgnum = 12;
	$albnum = 3;
	$imgdescrip = array(
	'Hampstead Heath, 22nd December: Scene following 6cm of snow',
	'Hampstead Heath, 22nd December: Snowscape',
	'East Finchley, 23rd December: Thick rime after freezing fog',
	'Dunham Forest, 25th December: Snowy ridge',
	'Dunham Forest, 25th December: Snowscape',
	'Dunham Forest, 25th December: Snowy park',
	'Dunham Forest, 25th December: Snowy golf course',
	'Dunham Forest, 25th December: Sunset',
	'East Finchley, 6th January: First Snow of the new year',
	'East Finchley, 6th January: View over the neighbourhood',
	'East Finchley, 6th January: Snow-wrapped cable',
	'East Finchley, 6th January: 12cm, the greatest depth of the season');
	
	$albdescrip = 'The photos were taken in various location in London and Manchester during the snowy cold snap that lasted from mid-December to mid-January.<br />
	Hover over photo to get details.'; // End of album properties

	require('albgen.php');
?>

</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>