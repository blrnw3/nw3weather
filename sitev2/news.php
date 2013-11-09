<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("main_tags.php");
	$file = 96; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Blog</title>

	<meta name="description" content="Old v2 - NW3 weather website blog and news, updates and error notices." />
	
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
	<h1>Site Blog and Weather Station news/updates</h1>
	
	<table width="800">
	
	<tr><td><h2>Site update - version 3 in development, now available for preview (beta?)</h2>
		<p>I've been working on this on and off since June (during the summer holiday), but as my studies have now resumed I've come to the realisation that I'm not going
		to be able to finish it to a level that's acceptable for a full release (i.e. replacing the current version of the website) before I get tied up again with work.
		However, I don't want all the great new features to sit around unused, so I'm releasing <a href="/sitev3" title="Test out new website version">site v.3</a> 
		as a 'beta' (incomplete, potentially buggy, but usable). I'll continue to upgrade, fix, and add new features to site v.3, so please note that content may change or
		dissapear from it at any time. Any feature suggestions, reports of problems, or general feedback are all welcome - please use the <a href="contact.php">contact page</a>.
		Many pages are unchanged but all have adopted the new look, and dynamic graphs has been implemented. A list of new or significantly altered pages will be maintained here:
		</p>
		<ul>
		<li><a href="/sitev3/wx3.php">Graphs and Charts in new format</a></li>
		<li><a href="/sitev3/wx12.php">More in-depth detailed data page for rainfall</a></li>
		<li><a href="/sitev3/wx14.php">More in-depth detailed data page for temperature</a></li>
		<li><a href="/sitev3/chartviewer.php">Current 31-day and 12-month chart viewer</a> (new)</li>
		<li><a href="/sitev3/wxhistday.php">In-depth daily reports</a></li>
		<li><a href="/sitev3/wxdataday.php">Expanded Set of historical data tables</a></li>
		<li><a href="/sitev3/graphviewer.php">Advanced customisable graph viewer</a> (new)</li>
		</ul>
		<p>
		One of the biggest changes has been behind-the-scenes - a change to the data writing and gathering procedure: this is now done on the web-server by programmes I've written (in PHP),
		rather than by software on my local PC. This enables faster processing, and more importanly enhanced control, which is important as it means I can more easily and
		rapidly correct data glitches. Furthermore, it allows easy input of manual data and observations (e.g. sun hours, days of thunder) into the data files,
		so these now appear as weather variables just as the automatic ones like temperature and rainfall, albeit with a delay of around 24hrs (more if I am away).
		</p>
		As a final disclaimer, note well that the site has only be tested in the most recent version of the major browsers - old versions of IE especially may not display
		items as expected. <br />
		Also, you can return to the current (site v.2) version of any page using the link two places above the "Site Options" heading in the sidebar. <br />
		</p>
		<p>
		On a related note, site v.3 uses new values for the long-term-average rainfall to reflect recent analysis 
		(see post from 7th Jan below and the <a href="/Rainfall-adjustments2.xls" title="Analysis of rainfall at NW3 and nearby sites">updated xls</a>). 
		Updated values found <a href="/sitev3/wxaverages.php">here</a>. The rest of the climate averages will be updated soon to reflect the newly available 1981-2010
		standard averaging period.
		</p>
		<img src="/static-images/newmain.jpg" title="New site banner" alt="nw3weather site v.3 banner">
		<br /><br />
		<span style="color:gray;font-weight:italic">Posted: 1st Oct 2012</span><br />
	</td></tr>
	
	<tr><td><h2>Weather Station Problems - data downtime</h2>
		<p>In the last few months the weather station console, which receives data from the sensors wirelessly, has been dropping the link to these sensors with increasing frequency.
		Yesterday's downtime was the longest so far and unusually affected all sensors; mostly it is just the wind sensor that drops out. A manual reset was required to solve the problem.
		I am beginning to wonder whether the console may be failing, so further periods of downtime seem likely, and I may have to replace it, at surprisingly considerable cost;
		in fact I may well buy a complete new weather station in the summer should problems persist. I have owned and operated the station for almost three years. </p><p>
		As a side note, all data that gets lost in these periods of downtime is usually reconstructed using data from other weather stations in London, principally the nearest - at Whitestone Pond.
		Periods of downtime are easily discerned from the graphs, which show either 'flatline' (wind data) or clear evidence of interpolation (temp, hum data).
		Yesterday's (15z-23z) is a prime example:
		</p>
		<img alt="graph" src="20120107.gif" title="Daily weather graph for 7th Jan 2012 showing evidence of data reconstruction" /> <br />
		NB: The <a href="wx3.php">graph page</a> can be used to check for recent downtime; old graphs are archived <a href="graphviewer.php">here</a>.
		<br />
		<span style="color:gray;font-weight:italic">Posted: 8th Jan 2012</span><br />
	</td></tr>
	<tr><td><h2>Rainfall Figures - adjustments to correct possible under-reading</h2>
		<p>Early in 2011 I suspected that the electronic rain gauge may be under-reading, so I decided to set up a manual rain gauge that could be used to check the performance of the automatic one.
		Suspicion arose due to conflicting figures with an "official" Met Office-standard weather station at Whitestone Pond, about 2km away.
		On completion of the set-up of a traditional rain gauge in September 2011, manual rainfall data collection began, and the figures compared to my automatically-recorded rainfall.
		As of December 2011, early results from comparing the data from the two rain gauges suggested a correction of +5% was needed on the automatic one, which was implemented for the start of 2012.
		Further data will be collected in 2012, with a final correction to be decided on for the start of 2013.</p><p>
		In January 2012, I made a full comparison of rainfall data collected here versus figures from the Whitestone Pond station, as well as a few others around London.
		The results are available as a <a href="Rainfall-adjustments.gif" title="View results as an image">.gif</a> and as the original <a href="Rainfall-adjustments.xls" title="View results as an Excel file">.xls</a> file.
		Averaged over the available timeframe, closest agreement was with the MetO station at RAF Northolt, 15km away but at a similar elevation.
		After adjusting for the +5% correction suggested by manual data collection, the figures recorded here are still some 15% lower than those from Whitestone Pond.
		However, the agreement of my figures with Northolt's suggest that this is possibly down to elevation difference (55m at NW3weather versus 140m at WS Pond),
		since this station is at the bottom of Hampstead Heath whereas WS Pond sits atop the highest point in London.
		<br />Regardless of the cause, the essential conclusion is that the long-term rainfall averages I derived from the WS Pond station are incorrect, and need to be adjusted downwards.
		I plan to implement these changes for the start of 2013 so that I have time to adjust other figures (such as temperature) as well, should it be necessary.<br />
		</p>
		<img alt="auto gauge" src="auto-gauge.JPG" title="Automatic rain gauge" /> <img alt="manual gauge" src="manual-gauge.JPG" title="Manual rain gauge" /><br />
		<span style="color:gray;font-weight:italic">Posted: 7th Jan 2012</span><br />
	</td></tr>
	<tr><td><h2>Site News - two new pages, more in development</h2>
		<p>Welcome to the NW3weather blog! This is the first of two new pages for the New Year.
		The second is a logical addition to the historical reports, <a href="wxhistyear.php">annual weather summaries</a>, to compete the set which started with only daily and monthly reports.
		A summary section has been added to the <a href="wxhistmonth.php#summary">monthly weather summaries</a>, though this is not yet finalised.
		Furthermore, the daily weather reports page is in need of a complete overhaul to bring it in line with the style of the others, but I've put that on hold for now.<br />
		Development of more pages with historical data and records is underway, but the next update will not come before July 2012, when I will have time to continue with site development.
		</p>
		<span style="color:gray;font-weight:italic">Posted: 7th Jan 2012</span><br />
	</td></tr>
	</table>
	 <p><b>Updates: </b>The <sup style="color:green">new</sup> superscript remains in place in the sidebar for one week after the last blog post. Hover over it to get the date of last posting.</p>
</div>

<!-- ##### Footer ##### -->
	<? require('footer.php'); ?>

</body>
</html>