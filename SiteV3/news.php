<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 96; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Blog</title>

	<meta name="description" content="NW3 weather website blog and news, updates and error notices." />

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
	<h1>Site Blog and Weather Station news/updates</h1>

	<table width="850">
	<tr id="post-20170906"><td>
		<h2>New weather station</h2>

		<p>Aged 18, a near-penniless student with mounting debt, I opted for one of the cheaper models of personal weather stations - Oregon Scientific's WMR-200.
			It was known to be a little unreliable, but the top-of-the-line brand was more than double the price.
			Nevertheless, it served me well for the best part of seven years, and I had learned to tolerate its many quirks and
			frequent issues maintaining connection to the wireless sensors. Leaving the UK in 2015 made things even more difficult,
			as operating and maintaining an aging, unreliable piece of kit remotely proved challenging. My on-site assistant
			was able to perform basic repair with over-the-phone instructions, but keeping everything running smoothly
			proved impossible as the sensors started failing more frequently.
			Well eight years later, a working man, I could finally afford that more expensive brand, so I bought a Davis Vantage Pro 2
			personal weather station and scheduled a trip home to install it.
		</p>
		<p>It was long-overdue; two out of the three outdoor sensors had failed (wind in Feb '16 and rain in March '17), and the other (temperature/humidity)
			having failed and been replaced a year ago. Last weekend I was finally able to completely replace the entire weather station before it collapsed completely.
			After a few weeks of planning in July, I bought the new station and plotted a strategy for the two days
			I would have to install it. The weather gods were largely on my side and produced a spell of dry weather on Sat and Sun,
			enabling a successful and largely smooth installation (plus a full clean and repaint of the radiation shield).
			By 9pm on Sunday 4th Sep 2017, the new station was operational
			and sending data to this website, and the old, now crumbling station of nearly 9 years operation was discarded.
		</p>
		<p>This new hardware <i>should</i> be more reliable and offer more accurate data due to higher quality sensors
			and a more advanced data transmission system. Key advantages over the old Oregon Scientific WMR-200 station:
			<ul>
				<li>Higher quality of sensors - ALL are better constructed so are more accurate and long-lasting. Particularly the barometer and wind sensor</li>
				<li>More frequent updates (every 3-20s rather than 15-60s). This is especially important for wind speed so I anticipate being able to record higher wind gusts</li>
				<li>More powerful, centralised transmitter (rather than having one in each sensor), will improve signal reliability.
				The old sensors often suffered a drop-off in signal, and replacing the batteries in three separate sensors was a pain.
				The one watch-out with the new sensors is the cables (which link to the central transmitter), as although weather-proof, are a new source of potential	failure.</li>
			</ul>
		</p>
		<p>Finally I'd like to thank my weather assistant for her years of help, and ongoing support keeping the station operational.
			Additional thanks to my sisters for helping with the installation and tolerating the flies &amp; spiders crawling around the old sensors.
			I'm also grateful to my aunt for lending me tools essential to completing the job.
		</p>
		<p>Special thanks are due to the owners of the weather stations I used for wind, rain, and sometimes temperature data whilst my own
			sensors were failing me: <a href="http://www.harpendenweather.co.uk">Harpenden weather</a>,
			<a href='http://weather.casa.ucl.ac.uk'>UCL casa</a>, and <a href='http://weather.bencook.net/'>N16 weather</a>.
		</p>
		<p>Photos of the installation process and of the new weather station are in a <a href="/wx_albgen.php?albnum=6&view=Full"> new photo album</a>. Favourites below:<br /><br />
			<img src="/photos/wx_replace/3s.jpg" width="385" alt="Old sensors" title="Old Sensors" />
			<img src="/photos/wx_replace/1s.jpg" width="420" alt="New Sensors" title="New Sensors" />
			<img src="/photos/wx_replace/16.jpg" width="370" alt="dismantling pole" title="Replacing the old wind sensor" />
			<img src="/photos/wx_replace/15.jpg" width="480" alt="new wx stn" title="New weather station" />
		</p>
		<a href="/wx_albgen.php?albnum=6&view=Full">Full album</a>

		<p><span style="color:gray;font-style:italic">Posted: 6th Sep 2017</span></p>
    </td></tr>
	<tr id="post-20160917"><td>
		<h2>More data downtime - Temperature & Humidity</h2>
		<p>On 4th Sep the thermo/hygrometer (combined temperature and humidity sensor) failed. It had been suffering
		a lot lately, but this time it really was dead, and my (proxy) efforts to revive it failed. I bought a new sensor
		and my weather assistant kindly installed it for me (thanks, mum!). Temperature data was back after its longest
		absence in over 7 years running this weather station. Phew.
		</p>

		<p>What a time to lose data though - on 13th Sep London recorded its hottest September day in 100 years!
		The value from the proxy station I use was an impressive 32.2 C, which would be my 5th hottest ever.
		Although the data is likely similar to what mine would have been, I can't say for sure, and
		overnight temperatures are certainly a bit higher there (<a href="http://weather.casa.ucl.ac.uk">UCL</a>, Bloomsbury, central London).
		</p>

		<p>Unfortunately the wind sensor is still broken and due to the difficulty of replacing that, I won't be.
		However, I do want to continue running this station (remotely, with the help of my on-site assistant) for
		as long as possible. I'm currently investigating other weather stations that might be more reliable,
		and all being well I would like to have a new one installed by the end of 2017.</p>
		<span style="color:gray;font-style:italic">Posted: 17th Sep 2016</span><br />
    </td></tr>
	<tr id="post-20160415"><td>
		<h2>Recent live data downtime</h2>
		<p>As promised, moving abroad brought with it the risk of increased data downtime for the weather station left behind.
		Just three months after leaving, the wind sensor stopped reporting on the 11th Feb, so I began to serve up data from
		<a href="http://www.harpendenweather.co.uk">Harpenden Weather</a>, a nearby weather station with good wind data
		(wind speeds don't vary much across 50 miles or so).
		</p>
		<p>Then, on 12th April, the other two sensors (temp/hum and rain) stopped reporting too, and I thought nw3weather's days were over.
		I began to serve up temp, hum, and rain data from <a href="http://weather.casa.ucl.ac.uk">UCL's weather station</a>
		in Bloomsbury whilst I tried to think
		of ways to solve the crisis. At this point nw3weather.co.uk was simply a proxy for data from nearby weather stations;
		temperature, humidity and rainfall are much more localised phenomenon so even though UCL is only a few miles away,
		the data can be quite different.
		</p>
		<p>On 14th April I was able to call home and my weather assistant kindly followed my debugging instructions. 15 minutes later
		ALL sensors were functioning again. All it took was a hard reset of the weather console!
		</p>
		<p><img src="/static-images/volatile_nw3data.png" alt="weather graph apr 13-14 2016" title="A volatile few days" /><br />
		<b>Volatile data</b>: T/H and rain sensors stop reporting on the 12th. On the 13th, UCL's data is hooked in (the rain data took some time to get right),
		followed by a return to nw3 data at 20:45 on the 14th (with a surprisingly smooth temperature transition).</p>
		<span style="color:gray;font-style:italic">Posted: 15th Apr 2016</span><br />
	</td></tr>
	<tr id="post-20151107"><td>
		<h2>Moving to New York</h2>
		<p>Tomorrow I am moving to live in New York. However my beloved weather station is staying here in nw3 in the care of my family.
		The impact of this to my dear fans should be minimal during normal operation, but please be aware that any problems which arise may take much longer to resolve.
		Sadly the art of weather station maintenance is not easy to teach and I have failed to adequately prepare anyone in the UK to deal with potential issues.
		Thankfully software problems are generally easy to resolve remotely, or with a quick call home.
		</p>

		<p>In all likelihood, though, if anything serious were to happen such as a hardware failure then I would be unable to fix the problem until I returned to the UK to visit.
		<br />Some sample disaster scenarios:
		<ul>
		<li>Sensor malfunction</li>
		<li>Hard drive failure</li>
		<li>Sensor batteries run out</li>
		<li>High wind topples the wooden housing of the T/H and rain sensors</li>
		</ul>
		Fortunately the only one of those that has happened in seven years of running the station is battery failure, and that is the easiest to fix.
		Furthermore, I have just replaced the batteries in all sensors to reduce the chance of them dying before my next trip back.
		Nevertheless the others are a very real possibility, especially now that the station is quite old (I've been talking about replacing it for years).
		</p>

		<p>The other impact of my departure is that I won't be around to experience the London weather and report on a number of things which require manual observation rather than automated sensors.
		<br />These are:
		<ul>
		<li>Snowfall (falling and lying)</li>
		<li>Thunderstorms</li>
		<li>Hail</li>
		</ul>
		I will have to rely on family and friends left in the UK, as well as official weather reports, to help me fill in these details, plus any loyal fans out there!
		</p>

		<p>
		Despite my absence, however, I fully expect to be able to run the weather station in much the same way as I did whilst I lived here.
		After all, when I first set it up I was often away from London for months at a time whilst studying at university. <br />
		And maybe one day nw3weather will have a sister station in the USA.
		</p>

		<p>P.S. This is why I moved the weather server and webcams to the loft last weekend (they were previously in my bedroom, which is no longer mine).</p>
		<p><b>Summary: I'm moving abroad but the station and site are staying put and will be unaffected unless disaster strikes.</b></p>
		<span style="color:gray;font-style:italic">Posted: 7th Nov 2015</span><br />
	</td></tr>
	<tr id="post-20151003"><td>
		<h2>nw3weather joins twitter</h2>
		<p>
			After experimenting with various ways of sharing interesting stats about the weather that this
			station records, I've decided for now that using Twitter is the best way. It is easy to reach a
			wide audience and very easy to post text updates and photos. I plan to use it for sharing both stats
			("today was the warmest in 18 months") and station issues ("webcam not working"). Also photos I hope.
			I'm not convinced by the 140 character limit, which I generally find annoying as I have to resort to
			trimming short what I want to say or reverting to text speak or poor grammar. However, I will try it out. <br />
		</p>
		<p>
			<a href="https://twitter.com/nw3weather">Link to nw3weather's twitter page</a> <br />
			I have also put the feed at the bottom of the homepage so visitors can find it.
		</p>
		<p>P.S. The 2014 annual weather report has been archived <a href="repyear.php">here</a></p>
		<span style="color:gray;font-style:italic">Posted: 3rd Oct 2015</span><br />
	</td></tr>
	<tr id="post-20141116"><td>
		<h2>The weather station (and me) in the Ham and High, 30th Oct 2014</h2>
		<p>
			After the record rainfall (for this station) on 13th October 2014, I was contacted by
			someone at the local paper, the Ham and High, for an interview about the significance
			of the event. Pictures of me looking silly with my manual rain gauge were taken and
			the interview was done, but they ended up not using my contribution for that piece.
			They did, however, run a entirely different piece just about me and the weather station,
			following a further, more personal, interview some days later.
			<a href="/static-images/me_hnh.pdf" title="full-page pdf">Here it is!</a>
			<br />The article is now <a href="http://www.hamhigh.co.uk/news/weather/gospel_oak_storm_chaser_rivals_met_office_with_1_000_weather_station_1_3830494">available online</a> too.
		</p>
		<img src="/static-images/me_hnh.png" title="Me in the Ham and High" alt="me" /> <br />
		<p>I thought they were stretching it a bit thin calling me a storm chaser...<p>
		<span style="color:gray;font-style:italic">Posted: 16th Nov 2014</span><br />
	</td></tr>
	<tr id="post-20140330"><td>
		<h2>Weather station maintenance / minor upgrade</h2>
		<p>
			After almost five years of constant weathering, the radiation shield for the thermo/hygro sensor
			was showing serious signs of decay. To remedy this, the base and roof have both been replaced; the body
			itself seems to be in reasonable enough condition to survive another few winters at least, but has been
			thoroughly cleaned to maintain its reflective gloss. The rain gauge has also been cleaned out, having built
			up a considerable layer of moss and dead insects within its internals.
		</p>
		<p>
			In addition to this maintenance, I have replaced the rain gauge collection funnel with another of
			a larger diameter (25 cm, up from 19.5 cm), thus increasing the gauge's resolution from approx. 0.30 mm to 0.18 mm.
			The consequences of this change are:
			<ul>
				<li>A little under 0.2 mm of rain needs to fall to trigger a 'rain day', rather than 0.3 mm</li>
				<li>Rain rate is more accurate during heavy rain/showers</li>
				<li>Brief showers are more likely to be detected</li>
				<li>Quicker response in reporting rain after it starts to fall.</li>
			</ul>
		</p>
		<p>
		So, good news all around. However, the sensors themselves are showing signs of wear and will probably need replacing
		next year. Or perhaps I'll replace the entire weather station with a newer and better model.
		Either way, more weather hardware upgrades to come in 2015 (along with the website upgrades)!
		</p>
		<p>
			<h3>Before:</h3>
			<img src="/photos/oldwx_body.jpg"></img><img src="/photos/oldwx_base.jpg"></img>
			<img src="/photos/oldwx_roof.jpg"></img><img src="/photos/oldwx_funnel.jpg"></img>

			<h3>After:</h3>
			<img src="/photos/newwx_body.jpg"></img><img src="/photos/newwx_full.jpg"></img>
			<img src="/photos/newwx_top.jpg"></img><img src="/photos/newwx_bot.jpg"></img>
		</p>
		<p>A <a href="./wx_albgen.php?albnum=4">full photo album</a> has now been published</p>
		<span style="color:gray;font-style:italic">Posted: 30th Mar 2014</span><br />
	</td></tr>
	<tr><td>
		<h2>Source code release and latest website development plans</h2>
		<p>
			All source code is now <a href="https://github.com/blrnw3/nw3weather">available on GitHub</a>, organised by site version. <br />
			The latest website upgrade is underway (site v.4), and the beginnings of the new code base are available there, too.
		</p>
		<p>
			This next iteration of nw3weather will initially involve entirely back-end upgrades, of the data model and major refactoring of the source code.
			Following the completion of this upgrade in approx. mid-2014, further features will be worked on and test releases to the live site will be
			performed before a full live release in 2015, probably.<br />
			I have now moved to <a href="https://www.pivotaltracker.com/s/projects/943352">using PivotalTracker</a> as a project management tool for nw3weather development.
			This enables easy management of bugs, features and chores throughout the development process, and this is the best place for nw3weather users to
			follow the latest goings on.
		</p>

		<p>P.S. I will be performing some minor weather station maintenance and upgrades in the spring;
			detailed information will be posted when this is ready to be undertaken.</p>
		<span style="color:gray;font-style:italic">Posted: 4th Jan 2014</span><br />
	</td></tr>
	<tr><td>
		<h2>Site update - version 3 released</h2>
		<p>
			<strong>Site v3 has now officially launched!</strong> <br />
			Some of the biggest changes include:
		</p>
			<ul>
				<li>Graphs and charts - now prettier and available for many more data types and periods (also, unit-switchable)</li>
				<li>Manual observations - These are now better-incorporated into the site, making it easier to track snowfall and sunshine hours etc.</li>
				<li>Site design - I've settled on a fixed-width layout for now, as it is much easier to get things positioned properly</li>
				<li>Data control - almost all processing of images and raw data is now done on the server (rather than local PC);
					this makes it much easier to correct glitches, add features, and reduce inconsistencies and errors</li>
				<li>Rankings - It's now possible to view ranked data in a number of places</li>
				<li>Javascript - This is increasingly being used to improve usability, but I'm aware that a lot more needs to be done in this area.</li>
			</ul>
		<p>
			On account of these fairly significant changes, the loss of a few of the site v2 features, and given that I no longer have any interest in supporting
			old (pre-8) versions of Internet Explorer, you may well decide to stick with the old site, version 2. <br />
			Thus, <strong>I will be keeping site v2 going for a while yet, at <a href="/oldSites/sitev2/">this link</a></strong>.
		</p>
		<p>
			As ever, I will continue to work on improving the site, and <a href="contact.php" title='contact me'>welcome suggestions</a> for the next site upgrade.
			Additionally, feel free to contact me if you think I've unreasonably removed a feature during the upgrade, or you find a bug. Thanks.
		</p>
		<p>
			Finally, be aware that site v3 uses new values for the long-term-average rainfall to reflect recent analysis
			(see post from 7th Jan 2012 below and the <a href="/Rainfall-comparison.xls" title="Analysis of rainfall at NW3 and nearby sites">updated xls</a>).
			Updated values are to be found on the <a href="/wxaverages.php">new climate page</a>.
			The rest of the climate averages will be updated for the start of 2014 to reflect the newly available 1981-2010 standard averaging period.
		</p>
		<p>I really hope you all like the changes I've made, and thank you for visiting nw3weather.</p>
		<img src="/static-images/newmain.jpg" title="New site banner" alt="nw3weather site v3 banner" />
		<br /><br />
		<span style="color:gray;font-style:italic">Posted: 22nd May 2013</span><br />
	</td></tr>

	<tr><td>
		<h2>Site update - version 3 released this month</h2>
		<p>
			Upgrades to the site are now complete, and site v3 is almost ready to launch.
			I'll be doing some testing and final refinements this month, ready for a release on or close to <b>22nd May</b>.
			The changes are fairly minor from a user-perspective, and for the most part I've merely been adding new features. <br />
		</p>
		<span style="color:gray;font-style:italic">Posted: 6th May 2013</span><br />
	</td></tr>

	<tr><td>
		<h2>Site update - version 3 in development, now available for preview (beta?)</h2>
		<p>I've been working on this on and off since June, but I've come to the realisation that I'm not going
		to be able to finish it to a level that's acceptable for a full release this year.
		However, I'm releasing <a href="/sitev3" title="Test out new website version">site v.3</a>
		as a 'beta' (incomplete, potentially buggy, but usable). I'll continue to upgrade, fix, and add new features, so please note that content may change or
		disappear from it at any time. Any feature suggestions, reports of problems, or general feedback are all welcome - please use the <a href="contact.php">contact page</a>.
		Many pages are unchanged but all have adopted the new look, and server-side graphs has been implemented. <br />
		</p>
		<span style="color:gray;font-style:italic">Posted: 30th Sep 2012</span><br />
	</td></tr>

<!--	<tr><td><h2>Site update - version 3 in development, now available for preview (beta?)</h2>
		<p>I've been working on this on and off since June (during the summer holiday), but as my studies have now resumed I've come to the realisation that I'm not going
		to be able to finish it to a level that's acceptable for a full release (i.e. replacing the current version of the website) before I get tied up again with work.
		However, I don't want all the great new features to sit around unused, so I'm releasing <a href="/sitev3" title="Test out new website version">site v.3</a>
		as a 'beta' (incomplete, potentially buggy, but usable). I'll continue to upgrade, fix, and add new features to site v.3, so please note that content may change or
		dissappear from it at any time. Any feature suggestions, reports of problems, or general feedback are all welcome - please use the <a href="contact.php">contact page</a>.
		Many pages are unchanged but all have adopted the new look, and dynamic graphs has been implemented. A list of new or significantly altered pages will be maintained here:
		</p>
		<ul>
		<li><a href="/wx3.php">Graphs and Charts in new format</a></li>
		<li><a href="/wx12.php">More in-depth detailed data page for rainfall</a></li>
		<li><a href="/wx14.php">More in-depth detailed data page for temperature</a></li>
		<li><a href="/chartviewer.php">Current 31-day and 12-month chart viewer</a> (new)</li>
		<li><a href="/wxhistday.php">In-depth daily reports</a></li>
		<li><a href="/wxdataday.php">Expanded Set of historical data tables</a></li>
		<li><a href="/graphviewer.php">Advanced customisable graph viewer</a> (new)</li>
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
		Updated values found <a href="/wxaverages.php">here</a>. The rest of the climate averages will be updated soon to reflect the newly available 1981-2010
		standard averaging period.
		</p>
		<img src="/static-images/newmain.jpg" title="New site banner" alt="nw3weather site v.3 banner">
		<br /><br />
		<span style="color:gray;font-style:italic">Posted: 30th Sep 2012</span><br />
	</td></tr>-->

	<tr><td>
		<h2>Weather Station Problems - data downtime</h2>
		<p>In the last few months the weather station console, which receives data from the sensors wirelessly, has been dropping the link to these sensors with increasing frequency.
		Yesterday's downtime was the longest so far and unusually affected all sensors; mostly it is just the wind sensor that drops out. A manual reset was required to solve the problem.
		I am beginning to wonder whether the console may be failing, so further periods of downtime seem likely, and I may have to replace it, at surprisingly considerable cost;
		in fact I may well buy a complete new weather station in the summer should problems persist. I have owned and operated the station for almost three years. </p><p>
		As a side note, all data that gets lost in these periods of downtime is usually reconstructed using data from other weather stations in London, principally the nearest - at Whitestone Pond.
		Periods of downtime are easily discerned from the graphs, which show either 'flatline' (wind data) or clear evidence of interpolation (temp, hum data).
		Yesterday's (15z-23z) is a prime example:
		</p>
		<img alt="graph" src="/2012/stitchedmaingraph_20120107.png" title="Daily weather graph for 7th Jan 2012 showing evidence of data reconstruction" <?php echo GRAPH_DIMS_LARGE; ?> />
		NB: The <a href="wx3.php">graph page</a> can be used to check for recent downtime; old graphs are archived <a href="grapharchive.php">here</a>.
		<br />
		<span style="color:gray;font-style:italic">Posted: 8th Jan 2012</span><br />
	</td></tr>

	<tr id="post-20120107"><td><a name='rainfall'></a>
		<h2>Rainfall Figures - adjustments to correct possible under-reading</h2>
		<p>Early in 2011 I suspected that the electronic rain gauge may be under-reading, so I decided to set up a manual rain gauge that could be used to check the performance of the automatic one.
		Suspicion arose due to conflicting figures with an "official" Met Office-standard weather station at Whitestone Pond, about 2km away.
		On completion of the set-up of a traditional rain gauge in September 2011, manual rainfall data collection began, and the figures compared to my automatically-recorded rainfall.
		As of December 2011, early results from comparing the data from the two rain gauges suggested a correction of +5% was needed on the automatic one, which was implemented for the start of 2012.
		Further data will be collected in 2012, with a final correction to be decided on for the start of 2013.</p><p>
		In January 2012, I made a full comparison of rainfall data collected here versus figures from the Whitestone Pond station, as well as a few others around London.
		The results are available as a <a href="/Rainfall-adjustments.gif" title="View results as an image">.gif</a> and as the original <a href="/Rainfall-comparison.xls" title="View results as an Excel file">.xls</a> file.
		Averaged over the available timeframe, closest agreement was with the MetO station at RAF Northolt, 15km away but at a similar elevation.
		After adjusting for the +5% correction suggested by manual data collection, the figures recorded here are still some 15% lower than those from Whitestone Pond.
		However, the agreement of my figures with Northolt's suggest that this is possibly down to elevation difference (55m at NW3weather versus 140m at WS Pond),
		since this station is at the bottom of Hampstead Heath whereas WS Pond sits atop the highest point in London.
		<br />Regardless of the cause, the essential conclusion is that the long-term rainfall averages I derived from the WS Pond station are incorrect, and need to be adjusted downwards.
		I plan to implement these changes for the start of 2013 so that I have time to adjust other figures (such as temperature) as well, should it be necessary.<br />
		</p>
		<img alt="auto gauge" src="/static-images/auto-gauge.JPG" title="Automatic rain gauge" /> <img alt="manual gauge" src="/static-images/manual-gauge.JPG" title="Manual rain gauge" /><br />
		<span style="color:gray;font-style:italic">Posted: 7th Jan 2012</span><br />
	</td></tr>

	<tr><td><h2>Site News - two new pages, more in development</h2>
		<p>Welcome to the NW3weather blog! This is the first of two new pages for the New Year.
		The second is a logical addition to the historical reports, <a href="/wxhistyear.php">annual weather summaries</a>, to compete the set which started with only daily and monthly reports.
		A summary section has been added to the <a href="/wxhistmonth.php#summary">monthly weather summaries</a>, though this is not yet finalised.
		Furthermore, the daily weather reports page is in need of a complete overhaul to bring it in line with the style of the others, but I've put that on hold for now.<br />
		Development of more pages with historical data and records is underway, but the next update will not come before July 2012, when I will have time to continue with site development.
		</p>
		<span style="color:gray;font-style:italic">Posted: 7th Jan 2012</span><br />
	</td></tr>
	</table>
	 <p><b>Updates: </b>The <sup style="color:green">new</sup> superscript remains in place in the sidebar for three days after the last blog post. Hover over it to get the date of last posting.</p>
</div>

<!-- ##### Footer ##### -->
	<?php require('footer.php'); ?>

</body>
</html>
