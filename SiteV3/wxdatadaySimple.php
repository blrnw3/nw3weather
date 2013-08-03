<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php 
	$file = 40;
	$detail = 1;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

	<title>NW3 Weather - Annual Data Reports</title>

	<meta name="description" content="Detailed historical annual data reports with monthly summary." />

	<?php require('chead.php'); ?>
	<?php include('ggltrack.php') ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>
	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
	<div id="main">

<?php
require('wxdatagen.php');

$arrnum = $types_all[$type];
//Collect data
if($arrnum < count($types)) { $data = data($type, $year, null, true); }
elseif($arrnum < count($types)+count($types_derived)) { $data = datx($type, $year, null, true); }
else { $data = datm($type, $year); $manual = true; $mmm[2] = 'Total'; $adj = 1; }
if($type == 'rain') {
	$mmm[2] = 'Total';
}
elseif(in_array($type, array('tc10min','tchrmin','hchrmin'))) { $absfix = true; }

table();
tr();
td('Day', 'td4C', 8);
for($i = 1; $i <= 12; $i++) {
	$maxdays[$i] = get_days_in_month($i, $year);
	td($months3[$i-1], 'td4C', 7);
}
tr_end();

for($day = 1; $day <= 31; $day++) {
	tr(null);
	td( $day, 'row'.colcol($day).'" style="text-align:center' );
	for($mnt = 1; $mnt <= 12; $mnt++) {
		if($maxdays[$mnt] < $day || ($year == $dyear && mkdate($mnt,$day,$year) > mkdate($dmonth,$dday-$adj)) || $data[$mnt][$day] === '-') {
			$class = 'reportday'; $put = '-'; $rconv = false;
		}
		else { $rconv = $typeconvs_all[$arrnum]; $class = valcolr(conv($data[$mnt][$day],$rconv,0,0,0,$absfix),$wxtablecols_all[$arrnum]); $put = $data[$mnt][$day]; }
		td( conv($put,$rconv,0), $class );
	}
	tr_end();
}

for($m = 0; $m < 3; $m++) {
	if($m == 0) { $style = '; border-top:5px solid white;'; } else { $style = ''; }
	tr(null);
	td( $mmm[$m], 'reportttl" style="padding:4px'.$style );
	for($i = 1; $i <= 12; $i++) {
		if($m == 0 && is_array($data[$i])) { $data[$i] = array_filter($data[$i],'clearblank'); }
		if(count($data[$i]) == 0) { $put = '---'; $rconv = false; $class = 'reportday'; }
		else {
			$put = mom($data[$i],$m);
			if(($type == 'rain' || $manual) && $m == 2) { $put *= count($data[$i]); }
			$rconv = $typeconvs_all[$arrnum]; $class = valcolr(conv($put,$rconv,0,0,0,$absfix),$wxtablecols_all[$arrnum]);
		}
		td( conv($put,$rconv,0), $class . '" style="padding:4px;font-weight:bold'.$style );
	}
	tr_end();
}

table_end();

echo '<p>
	<b>NB:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>';
if($year == $dyear) { echo " (NB: The anomaly for the current month is unadjusted for the month's degree of completeness)"; }
echo '.</p>';

?>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>