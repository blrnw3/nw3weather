<?php require('unit-select.php');
$client = file('clientraw.txt');
$live = explode(" ", $client[0]);
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include($root."phptags.php");
	$file = 112; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - PHP test 2</title>

	<meta name="description" content="Old v2 - PHP script testing 2 for NW3 weather" />

	<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
	<link rel="stylesheet" type="text/css" href="weather-print.css" media="print" />
	<link rel="stylesheet" type="text/css" href="weather-screen2.css" media="screen" title="screen" />

</head>

<body id="worn">
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<? require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main-copy">
<? require('site_status2.php'); ?>

<h1>PHP test page 2</h1>
<form method="get" action="">
<select name="31type" onchange='this.form.submit()'>
<?php
$typegraph31 = 'rain';
if(isset($_GET['31type'])) { $typegraph31 = $_GET['31type']; }
for($i = 0; $i < count($types); $i++) {
	echo '<option value="', $types_original[$i], '"';
	if($typegraph31 == $types_original[$i]) { echo ' selected="selected"'; }
	echo '>', $data_description[$i], '</option>
	';
} ?>
</select>
</form>
<?php
//for($day = 0; $day < 31; $day++) { echo $day, ': ', $graph[$day], '<br />'; }
echo '<img src="graph31.php?type=' . $typegraph31 . '" alt="Last 31 days graph" /><br />';

$moddata = file($fullpath."dat" . date('Y',mktime(1,1,1,$date_month,$date_day-1,$date_year)) . ".csv");
$modline = explode(',', $moddata[count($moddata)-1]);
echo '<form method="get" action=""><table border="1" cellpadding="5">';
for($i = 1; $i < count($modline); $i++) {
	echo '<tr><td>', $types_original[$i], '</td>
		<td>', $modline[$i], ' </td>
		<td><input type="text" name="', $types_original[$i], '" /> </td>
		';
}

echo '</table><br />
	<input type="password" name="pwd" />
	<input type="submit" value="Submit Changes" /></form>';
?>
</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>