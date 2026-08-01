<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 87; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Historical Main</title>
	<meta name="description" content="Old v2 - Historical data and records information page/directory from NW3 weather station" />

	<? require('chead.php'); ?>
	<?php include_once("ggltrack.php") ?>
</head>

<body>


	<!-- ##### Header ##### -->
	<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<? require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
<div id="main-copy">

<h1>Historical Data Directory / Information</h1>

<h2>Information</h2>

<p>
Automatic data recording began at the beginning of February 2009, so this is when the <a href="wxhistday.php">daily</a> and <a href="wxhistmonth.php">monthly</a> reports start.
This is similarly true for the annual tables for <a href="wxhist10.php">dew point</a>, <a href="wxhist0.php">pressure</a> and <a href="wxhist10.5.php">relative humidity</a>;
 but both the <a href="wxhist12.php">rain</a> and <a href="wxhist14.php">temperature</a> reports have been extended back to January 2009, whilst the 
 <a href="wxhist13.php">wind</a> reports don't begin until August 2009. The same is true of the corresponding <a href="wxsumhist12.php">summary tables</a>.
 <br />
 The reasons for this, and other occasional data absences, are generally displayed on the relevant page under the data table, though a full history of the weather station
 can also be viewed <a href="wx8.php#History">here</a>.
 </p>
 
 <p>
 All of the historical pages make use of automatically-generated raw daily data reports, which are produced for the current day at 00:11 the following day.
 Any errors that appear are generally corrected within a few days, but please report errors via the <a href="contact.php">contact page</a> if you notice any.</p>
<!--
<h3>Averages and Extremes for every day and month (starting Jan '10)</h3>
Includes daily graphs

<h3>Detailed past rainfall records (starting Jan 2009)</h3>
Monthly totals and annual summaries (see wettest, driest months and days etc.) <br />
<a href="wxsumhist12.php" title="Click to view monthly data report"><b>here</b></a> <br /><br />
Daily totals and monthly summaries <br />
<a href="wxhist12.php" title="Click to view daily rainfall data reports"><b>here</b></a>

<h3>Detailed past temperature records (starting Jan 2009)</h3>
Monthly averages and extremes, and annual summaries (see warmest, coldest months and days etc) <br />
<a href="wxsumhist14.php" title="Click to view monthly data report"><b>here</b></a> <br /><br />
Daily extremes and monthly summaries <br />
<a href="wxhist14.php" title="Click to view daily temperature data reports"><b>here</b></a>
<br /><br />
-->
<h2> Other Historical Data pages </h2>

An old-style daily breakdown of the figures for each day of the current month is available here: <br />
<a href="dailyreport.php" title="<?php echo $monthname; ?> <?php echo $date_year; ?> basic summary">
<span style="font-size:110%">Summary for this month</span></a>

<br />

<h3>Graphical Archive</h3>
<a href="graphviewer.php">24hr Daily Graphs</a><br />
<a href="wcarchive.php">Daily Webcam Image Summaries</a>


<a name="xls"></a>
<h3>Highly-detailed Daily and Monthly Spreadsheet-based Analysis (starting Feb '09)</h3>

<p>I have also produced spreadsheets containing a much more detailed anaylsis of every day and month for which I have recorded data with this weather station.
<br />These give a better idea of how anomalous the conditions were, as the figures are compared to the long-term averages of nearby
Met Office sites.
<br />
Each year has a spreadsheet for monthly summaries, and another for day-by-day monthly analysis:
<br /><br />
<p style="font-size:120%; color:red">These have now stopped as I am in the process of fully converting to web-based reports. <br />
 News of these will be relayed here at the earliest opportunity (probably sometime in early July). <br />
<b>Update (30 Sep) </b>Apologies for the delay. The process is ongoing, but please see the latest <a href="/news.php">blog post</a> for more information.</p>
<a href="/DailyData2012.xls" title="Daily detail for 2012"><span style="color:green">Daily Data 2012</span></a> (.xls, 2.8MB, last updated: 04/04/12)<br />
<span style="color:green">Monthly Data 2012 available on request</span>
<br /><br />
<a href="/DailyData2011.xls" title="Daily detail for 2011"><span style="color:green">Daily Data 2011</span></a> (.xls, 4.0MB, last updated: 02/01/12)<br />
<a href="/MonthlyData2011.xls" title="Monthly detail for 2011"><span style="color:green">Monthly Data 2011</span></a> (.xls, 5.0MB, last updated: 02/01/12)
<br /><br />
<a href="/DailyData2010.xls" title="Daily detail for 2010"><span style="color:green">Daily Data 2010</span></a> (.xls, 3.9MB, last updated: 05/01/11)<br />
<a href="/MonthlyData2010.xls" title="Monthly detail for 2010"><span style="color:green">Monthly Data 2010</span></a> (.xls, 5.5MB, last updated: 05/01/11)
<br /><br />
<a href="/DailyData2009.xls" title="Daily detail for 2009"><span style="color:green">Daily Data 2009</span></a> (.xls, 3.2MB, last updated: 01/01/10)<br />
<a href="/MonthlyData2009.xls" title="Monthly detail for 2009"><span style="color:green">Monthly Data 2009</span></a> (.xls, 4.5MB, last updated: 01/01/10)
</p>

<p><b>NB:</b> Data from before July 2010 was recorded at a different site a few miles north of this one, but with the same setup, aside from an absence of wind data. <br />
Prior to February 2009, a different weather station was used, collecting only temperature records, but reports are not available for this period. <br />
A full discussion of changes to the weather station and its setup/location can be found <a href="wx8.php#History" title="Found on the about page">here</a>.</p>

</div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>

</body>
</html>