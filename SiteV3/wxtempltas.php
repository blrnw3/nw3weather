<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 20;
	$subfile = true;
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - LTA temperature detail</title>

	<meta name="description" content="Long-term climate/weather average/mean temperatures day-by-day for London, NW3" />

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
<h1>Daily long-term temperature averages / <?php echo $unitT; ?> &nbsp; (and Max Sun Hrs)</h1>

<p>These figures are based on the monthly values, but the detail of the intra-month progression is derived from analysis of
<acronym title="Central England Temperature: The CET series is the longest continuous temperature record in the world">CET</acronym>
figures from the last hundred years. The data has been deliberately smoothed to provide a more realistic basis for anomalies.<br />
The raw CET data can be viewed here: <a href="/CETanalysis.xls" title="Excel 2003 spreadsheet with raw CET data from 1900-2010">CETanalysis</a> (.xls, 2.70 MB).

<p style="margin: 10px 0px;">
<a name="graph" />
<img src="graphclim365.php?type0&amp;type1" alt="graph year 1" />
<img src="graphclim365.php?type2&amp;type3" alt="graph year 1" />
<img src="graphclim365.php?type4" alt="graph year 1" />



<table class="table1" width="500" cellpadding="1" cellspacing="0">

<tr class="table-top">
<td rowspan="2" colspan="2" width="15%" class="td4">Date</td>
<td colspan="4" width="72%" class="td14C">Temperature / &deg;<?php echo $unitT; ?></td>
<td rowspan="2" width="13%" class="td18C">Max <br />Sun Hrs</td>
</tr>
<tr class="table-top">
<td class="td12C" width="18%">Min</td>
<td class="td14C" width="18%">Max</td>
<td class="td16C" width="18%">Mean</td>
<td class="td10C" width="18%">Range</td>
</tr>
<?php

for($m = 0; $m < 12; $m++) {
	echo '<tr><td rowspan="',get_days_in_month($m+1),'" class="td4"><b>',$months[$m],'</b></td>';
	for($d = 0; $d < get_days_in_month($m+1); $d++) {
		if($d % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
		if($d > 0) { echo '<tr class="row',$style,'">'; }
		echo '<td class="td4">',$d+1,'</td>';
		for($v = 0; $v < 5; $v++) {
			if($v == 4) { $dpa = 1; } else { $dpa = 0; }
			echo '<td class="td',$lta_type[$v],'C">',conv($lta[$v][date('z',mkdate($m+1,$d+1,2009))],$lta_unit[$v],0,0,$dpa),'</td>';
		}
		echo '</tr>';
	}
}
?>
</table>

</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>

</body>
</html>