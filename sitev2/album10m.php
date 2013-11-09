<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 71.11; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) | Photos | Summer 11</title>

	<meta name="description" content="Old v2 - Weather photography from around Europe in the Summer of 2011 taken by Ben Lee-Rodgers of NW3 weather" />
	
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

<h1>Europe, Summer 2011</h1>
<?php $imgref = '/photos/summer11'; // Album properties
	$imgnum = 15;
	$albnum = 10;
	$imgdescrip = array(
	'Kew Gardens, 22nd April: Summer starts early in London as the temperature hits 26 &deg;C',
	'Cambridge, 7th May: A very dry spring lead to drought conditions in Cambridgeshire',
	'Cambridge, 8th May: Sunset after a 26 day dry-spell finally ends',
	'Cambridgeshire, 10th June: The weather finally turned in June, with frequent rain',
	'Cambridgeshire, 13th June: A brief pause in the June wet-spell',
	'Cambridgeshire, 13th June: Light cumulus humilis, a typical summer&#39;s day',
	'Highlands, 22nd June: The weather in Scotland was similarly wet',
	'Highlands, 23rd June: Strong sunshine illuminating Loch Arkaig',
	'Snowdonia, 9th August: A pleasant day in Wales, but overall August was another dull month',
	'Snowdonia, 9th August: River levels recovered well after the dry spring',
	'Tuscany, 15th August: Meanwhile in Italy, another clear evening',
	'Tuscany, 15th August: The lights come on',
	'Tuscany, 15th August: Darkness arrives',
	'Tuscany, 18th August: The sun sets with the temperature above 30&deg;C',
	'Tuscany, 22nd August: Yet another clear evening');
	
	$albdescrip = 'The first photos were taken at various locations around the UK, which experienced a warm, sunny and dry spring but a cool, wet summer;
	the final sunsets are from Italy, which along with much of Southern Europe had a warm, dry summer.
	<br /> Click on or hover over thumbnail to get the location and date of a photo.'; // End of album properties

	require('albgen.php');
?>

</div>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>