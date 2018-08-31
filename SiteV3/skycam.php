<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 2;
	$subfile = true;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather Station - Webcam - High-res Image</title>

	<meta name="description" content="NW3 weather Webcam sky high resolution skycam image, auto-refreshing live from Hampstead, North London." />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?>

	<!-- override style for widescreen -->
	<style type="text/css">
	#page {
		width: inherit;
		max-width: 2109px;
		min-width: 1012px;
	}
	#main {
		width: inherit;
	}
	#footer {
		width: 100%;
	}
	#header {
		width: 100%;
	}
	</style>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>
	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
<div id="main">

	<h1>Live Skycam</h1>

<img name="refresh-lg" src="./skycam.jpg" title="Latest high-res skycam" width="100%" alt="Weathercam" />

<p>The camera is a Hikvision 5MP H.265+ DS-2CD2055FWD-I with 4mm focal length and is looking NE over Hampstead Heath (see <a href="wx8.php#location" title="About page">map</a>).
<br />The image is updated every 10s throughout the day and night, with a ~20s delay due to image upload and processing time.</p>

<p>NB: This page is in an experimental full-width format so you can enjoy the full HD resolution of the image.</p>

<p><a href="./wx2.php">Return to main webcam page</a></p>

<noscript><p><b>NB:</b> Javascript is required for the automatic updates</p></noscript>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>