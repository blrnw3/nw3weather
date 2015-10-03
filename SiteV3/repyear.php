<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 87; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Annual Reports</title>

	<meta name="description" content="NW3 weather annual reports. Hand-written by Ben Lee-Rodgers" />

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
	<h1>2014 Weather Report</h1>

<p style="margin-bottom: 0.5em;" id="report-2014">
<p>
	2014 was the sixth year of records for this weather station, and the fourth complete year in its current location in nw3.<br />
	It was the wettest of these, with a 31% excess compared to the expected long-term climatic average.
	Five of the top-ten wettest days across all six years fell in 2014's Aug-Nov period alone, which also contains the
	overall wettest day, the 13th Oct 2014, with 41 mm.
	For the first time, more than half of days had some measurable rainfall, whilst January had just <b>one</b> complete dry day all month.
</p>
<p>
	Despite this, 2014 was both the warmest and sunniest in my records.
	The mean temperature was 1.3 C above average (Across England
	<a href="http://www.metoffice.gov.uk/hadobs/hadcet/data/download.html" title="Hadcet data">the anomaly was similar</a>,
	and moreover 2014 was the warmest year in 357 years of records).
	August was the only month to come in below average, while Jan-Apr all came at least two degrees above.
	For the first time, there was absolutely <b>no</b> snowfall, and the year had the fewest air frosts on record.
</p>
<p>
	For the website, nw3weather saw its busiest year since launching in Sep 2010, with 75,000 visits (up from 63,000 in 2013),
	giving a mean figure of 206 per-day. The median was 188, since busy days (commonly very wet or windy days) skew the figure.
	The most in a single day was 600 on <a href="/wxhistday.php?year=2014&month=2&day=14" title="">14th Feb</a>,
	a wet and extremely windy day (50 mph gusts). The fewest was 106 on 6th Sep, a quiet and overcast Saturday.<br />
	Looking ahead to 2015, this should see the completion of back-end website upgrades, with only minor UI and informational changes.
</p>
<h2>Summary of Data</h2>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>12.3 C</b> (+1.3 C from the <abbr title="Long-term average">LTA</abbr>).
		Absolute Min and Max: <b>-1.4 C</b> (30th Dec), and <b>31.2 C</b> (18th Jul).
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>815.2 mm</b> (+31%) across <b>185</b> days (51%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>40.7 mm</b> (13th Oct). <br />
		Wettest Month: <b>141.5 mm</b> across <b>30 days</b> (Jan).
		Driest: <b>22.9 mm</b> (Mar); Least Rainy: <b>5 days</b> (Sep).
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1599 hrs</b> (+9%) from a possible 4038. <br />
		Days with more than a minute of sunshine: <b>322</b> days (88%).
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There were <b>no</b> days of falling snow or sleet
	(20 below the <abbr title="Long-term average">LTA</abbr>),
		and <b>7</b> air frosts (-19).
	</dd>
	<dt>Other Events</dt>
	<dd>There were <b>2</b> days of hail, <b>14</b> with thunder, and <b>1</b> with fog at 09z.
	</dd>
	</dl>
</p>
<p>
<a href="/TablesDataMonth.php?vartype=rain" title="Monthly Rain Records">
	These pages are useful for comparing all aspects of the weather across the years
</a>.
</p>
</p>
<span style="color:#555; font-size: 90%">
	Updated: 10th Jan 2015
</span>


<p style="font-weight: bold">
This page is for archived annual reports which first appear on the home page.
Expansion plans are afoot but may take a while. 
</p>

</div>

<!-- ##### Footer ##### -->
	<?php require('footer.php'); ?>

</body>
</html>