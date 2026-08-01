<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php"); 
	$file = 21; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) Station - Webcam - Image Only</title>

	<meta name="description" content="Old v2 - NW3 weather Webcam sky cam image, auto-refreshing live from Hampstead, North London." />

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
	
	<h1>Auto-updating Webcam Image</h1> 
	
<h3>Latest Weathercam Image</h3>

<p>The camera is a Logitech C300 and is looking NE over Hampstead Heath (see <a href="wx8.php#location" title="About page">map</a>).</p>
 
<?php if($time < $sunrise || $time > $sunset) { $img = '/currcam.jpg'; $rt = 100; } else { $img = '/currcam.jpg'; $rt = 10; }
	$wsizen[0] = filesize($root.$img); usleep(10000); clearstatcache(); $wsizen[1] = filesize($root.$img); $endw = 20;
	for($wcnt = 0; $wcnt < $endw; $wcnt++) {
		if($wsizen[$wcnt+1] - $wsizen[$wcnt] > 1) { usleep(250000); clearstatcache(); $wsizen[$wcnt+2] = filesize($root.$img); $wcnt2++; } else { $endw = $wcnt; }
	}
	if($wcnt2 > 0) { $scriptbeg = $scriptbeg + 0.25*$wcnt2; }
?>

<img name="refresh" src="<?php echo $img; ?>" title="Latest skycam" width="640" height="480" alt="Upload failed" />

<p>The image is updated every minute from approximately surise to sunset (<a href="wx6.php" title="Astronomy">see times</a>), and every 5 minutes otherwise.</p>
	
<?php 
if($time < $sunrise || $time > $sunset) {
	echo '<h3>Latest daylight weathercam image</h3><img src="/sunsetcam.jpg" alt="Latest Webcam" width="640" height="480"></img>';
}
?>

<noscript><p><b>NB:</b> Javascript is required for the automatic updates</p></noscript>
</div>

<script type="text/javascript">
	<!--
	var refresh_t = <?php echo $rt; ?> // interval in seconds
	image = "<?php echo $img; ?>" //name of the image
	function Start() {
	tmp = new Date();
	tmp =  Math.round(tmp.getTime()/60000)
	document.images["refresh"].src = image+'?'+tmp
	setTimeout("Start()", refresh_t*1000)
	}
	Start();
	// -->
</script>

<!-- ##### Footer ##### -->
	<? require('footer.php'); ?>

</body>
</html>