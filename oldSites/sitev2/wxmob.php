<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php include("main_tags.php");
$file = 0; ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Mobile - Home</title>
	<meta name="description" content="Old v2 - Mobile weather from NW3 weather station in Hampstead, North London - live weather updates and extensive historical data.
	Reduced-content-and-styling version of the home page, designed for mobile browsing." />
<meta http-equiv="content-type" content="application/xhtml+xml; charset=ISO-8859-1" />
<link rel="stylesheet" type="text/css" href="weathermob.css" media="screen" title="screen" />
</head>
<body>
	<!-- ##### Header ##### -->
	<div id="header">
<h1 class="headerTitle"><a href="http://nw3weather.co.uk" title="Browse to homepage">NW3 Weather</a>	</h1>
<div class="subHeader">	Hampstead, London, England </div>
<div class="subHeaderRight"><?php $shr = 'Last Updated: '.$date. ' '. $time. ' '. $dst; echo $shr; ?>  </div>
</div>
<div id="side-bar"><div class="leftSideBar"><p class="sideBarTitle">Navigation</p>
<ul><li><a href="index.php">Main Site</a></li><li><a href="curr24hourgraph.gif">Graph</a></li></ul></div></div>
	<!-- ##### Main Copy ##### -->
<div id="main-copy">
<h1>Latest Weather</h1>
<table cellpadding="5" cellspacing="0" width="99%">
<tr class="table-top">
<td width="21%"><b>MEASURE</b></td>
<td width="24%"><b>CURRENT</b></td>
<td width="21%"><b>MAX/MIN</b></td></tr>
<tr class="column-light">
<td><b>Temperature </b></td>
<td><b><?php echo sprintf('%.1f',$temp); ?> &deg;C</b></td>
<td><span class="small"><?php echo sprintf('%.1f',$maxtemp), ' &deg;C at ', $maxtempt; ?><br/>
<?php echo sprintf('%.1f',$mintemp), ' &deg;C at ', $mintempt; ?></span></td>
</tr>
<tr class="column-dark2">
<td><b>Relative Humidity</b></td>
<td><b><?php echo $hum; ?>%</b></td>
<td><span class="small"><?php echo $highhum; ?>% at <?php echo $highhumt; ?><br/>
<?php echo $lowhum; ?>% at <?php echo $lowhumt; ?></span></td>
</tr>
<tr class="column-light">
<td height="31"><b>Dew Point</b></td>
<td><b><?php echo sprintf('%.1f',$dew); ?> &deg;C</b></td>
<td><span class="small"><?php echo sprintf('%.1f',$maxdew), ' &deg;C at ', $maxdewt, '<br/>', sprintf('%.1f',$mindew), ' &deg;C at ', $mindewt; ?></span></td>
</tr>
<tr class="column-dark2">
<td><b>Pressure</b></td>
<td><b><?php echo round($baro,0); ?> hPa </b></td>
<td><span class="small"><?php echo round($highbaro,0); ?> hPa at <?php echo $highbarot; ?><br/>
<?php echo round($lowbaro,0); ?> hPa at <?php echo $lowbarot; ?></span></td>
</tr>
<tr class="column-light">
<td height="31"><b>Wind</b></td>
<td><b><?php echo $avgspd; ?> <?php echo $dirlabel; ?></b></td>
<td><span class="small"><b>Max hour gust:</b> <?php echo sprintf('%.1f',$maxgsthr), ' mph'; ?><br/><b>Max day gust:</b> <?php echo $maxgst; ?></span>
</td>
</tr>
<tr class="column-dark2">
<td height="31"><b>Daily Rain</b></td>
<td><b><?php echo $dayrn; ?></b></td>
<td><span class="small"><b>Last Hour:</b> <?php echo $hourrn; ?> mm<br/><b>Month Rain:</b> <?php echo $monthrn; ?></span></td></tr></table>
<br />
<table cellspacing="2" cellpadding="3" border="1" width="100%" style="border-collapse: collapse">
<tr>
<td width="21%"><span class="small"><b>Annual Rain:</b><br/><?php echo $yearlyraininmm; ?> mm</span></td>
<td width="24%"><span class="small"><b>Last Rain:</b><br/><?php $splitdolra = explode("/", $dateoflastrain); echo $timeoflastrainalways, ', ';
if($splitdolra[0] == $date_day && $splitdolra[1] == $date_month):
echo "Today"; elseif($splitdolra[0] == $date_day-1 && $splitdolra[1] == $date_month): echo "Yesterday"; else: echo $dateoflastrain; endif; ?></span></td>
<td width="21%"><span class="small"><b>Free Memory:</b><br/><?php if($freememory<0): echo 4000+$freememory; else: echo $freememory; endif; ?> MB</span></td>
</tr>
</table>
<p>To see how these figures compare to expected values,
view the <a href="wxaverages.php" title="Long-term NW3 climate averages">climate averages</a> page.</p>
<p>This weather station has been recording data for<b> <?php echo intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,2,1,2009))/(24*3600)); ?></b> days
(<?php echo intval((mktime(0,0,0,$date_month,$date_day,$date_year)-mktime(0,0,0,7,18,2010))/(24*3600)); ?> at NW3)</p>
</div>
<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body></html>