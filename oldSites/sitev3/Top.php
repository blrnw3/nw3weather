<?php
echo HTML_START;

echo '<head>
	<title>NW3 Weather ' . $htmlTITLE . '</title>
	<meta name="description" content="'.$htmlMETA_NAME.'" />';

require('chead.php');
include('ggltrack.php');

echo '</head>
	<body>';

require('header.php');
require('leftsidebar.php');

echo '<div id="main">
	';

?>