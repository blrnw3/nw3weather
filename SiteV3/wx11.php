<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 2;
	$subfile = true;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather Station - Webcam - Image Only</title>

	<meta name="description" content="NW3 weather Webcam sky cam image, auto-refreshing live from Hampstead, North London." />

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?>
</head>

<body onload="camRefesh();camRefreshNew();">
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>
	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
<div id="main">

	<h1>Auto-updating Webcam Image</h1>

<h3>Latest Weathercam Image</h3>

<p>The camera is a Logitech C300 and is looking NE over Hampstead Heath (see <a href="wx8.php#location" title="About page">map</a>).</p>

<img name="refresh" src="<?php echo $camImg; ?>" title="Latest skycam" width="640" height="480" alt="Weathercam" />

<p>The image is updated every minute throughout the day and night.</p>

<?php
if($time < $sunrise || $time > $sunset) {
	echo '<h3>Latest daylight weathercam image</h3><img src="/sunsetcam.jpg" alt="Latest Webcam" width="640" height="480"></img>';
}
?>

<noscript><p><b>NB:</b> Javascript is required for the automatic updates</p></noscript>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>