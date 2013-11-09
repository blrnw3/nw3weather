<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 30; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) Station - Beaufort Scale</title>

	<meta name="description" content="Old v2 - Beaufort Wind Scale descriptions and categories" />
	
	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?> 
</head>

<body>
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
<div id="main-copy">
	<table cellpadding="20" width="99%" border="0" align="center"><tr><td>
	<img src="/static-images/img33_Beaufort_NOAA.gif" alt="beaufort scale" />
	</td></tr>
	</table>

</div>

<!-- ##### Footer ##### -->
	<?php require('footer.php'); ?>

</body>
</html>