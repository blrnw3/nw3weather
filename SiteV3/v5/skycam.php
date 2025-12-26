<?php
require("Page.php");
Page::init([
	"fileNum" => 2,
	"isSubFile" => true,
	"title" => "Webcam - Highres Image Only",
	"description" => 'NW3 weather Webcam high-resolution sky cam image, auto-refreshing live from Hampstead, North London.'
]);
Page::Start();
?>
	
<h1>Live Skycam</h1>

<img name="refresh-lg" src="/skycam.jpg" title="Latest high-res skycam" width="100%" alt="Weathercam" />

<p>The camera is a Hikvision 5MP DS-2CD2055FWD-I with 4mm focal length and is looking NE over Hampstead Heath (see <a href="wx8.php#location" title="About page">map</a>).
<br />The image is updated every 10s throughout the day and night, with a ~20s delay due to image upload and processing time.</p>

<p><a href="./wx2.php">Return to main webcam page</a></p>

<noscript><p><b>NB:</b> Javascript is required for the automatic updates</p></noscript>

<?php
Page::End();
?>
