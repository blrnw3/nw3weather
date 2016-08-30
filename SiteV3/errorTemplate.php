<?php require('unit-select.php');
	$file = 0;
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">


<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Error</title>

	<?php require('chead.php'); ?>
	<?php include_once("ggltrack.php"); ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main">

<?php
$errorType = (int) $argv[1];
$codes = array(400, 401, 403, 404, 412, 500);
$errorIndex = array_search($errorType, $codes);

$codeNames = array('Bad Request',
	'Access Unauthorised',
	'Access Forbidden',
	'Page Not Found',
	'Header-precondition Failed',
	'Internal Server Error');
$codeHelps = array(
	'Check the URL syntax for malformation, or re-navigate',
	'Invalid credentials were supplied - try again if you think	you are allowed to access this page',
	'No-one is allowed there - ever ( except me, of course ;) )',
	'Please check the URL or re-navigate',
	'Looks like you sent some dodgy HTTP headers. Please re-navigate',
	'The Web Server could not cope - it\'s probably overloaded. Try again in a bit.'
);


echo "<h1>{$codeNames[$errorIndex]}!</h1>
	<h2>{$codeHelps[$errorIndex]}</h2>";
?>

</div>

<!-- ##### Footer ##### -->
	<?php require('footer.php'); ?>

</body>
</html>