<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 71.7; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) | Photos | Summer 08</title>

	<meta name="description" content="Old v2 - Weather photography from around Europe in the summer of 2008 taken by Ben Lee-Rodgers of NW3 weather" />
	
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

<h1>Europe, Summer 2008</h1>
<?php $imgref = '/photos/summer08'; // Album properties
	$imgnum = 10;
	$albnum = 7;
	$imgdescrip = array(
	'Cornwall: Cloudscape - Cumulus and Cirrus fibratus',
	'Cornwall: Sea reflections',
	'Cornwall: Cirrus over sea',
	'Cornwall: More reflections',
	'Cornwall: North Atlantic waves',
	'Wales: Sunset over Llyn peninsular',
	'Wales: Altocumulus perlucidus',
	'Italy: Sunset part 1',
	'Italy: Sunset part 2',
	'Italy: Sunset part 3');
	
	$albdescrip = 'The photos were taken in the months of July and August in three different areas:
	Cornwall, England; Gwynedd, Wales; and Tuscany, Italy. <br /> Hover over or click on a photo to get its details'; // End of album properties

	require('albgen.php');
?>

</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>