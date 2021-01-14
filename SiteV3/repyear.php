<?php require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 87; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Annual Reports</title>

	<meta name="description" content="NW3 weather annual reports. Hand-written by Ben Masschelein-Rodgers" />

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
	<h1>Annual Summaries</h1>

<h2 id="report-2020">Year 2020 - warm, v. sunny spring; record October rain; least wintry since 2014</h2>
<p style="margin-bottom: 0.5em;">
<p>
	2020 was the twelfth year of records for this weather station, and the tenth complete year in its current location in nw3. <br />
	For the first time since I moved to the US in 2015 I was unable to visit nw3 due to lockdowns, and without its regular maintenance the station did have
	a few issues. The rain and wind sensors failed, but thankfully with local help I've been able to keep things running (rain gauge fixed, wind sensor soon).
	More happily, while stuck at home in northern California, I finally set up a sister weather station here, with a website launch coming in June 2021!
</p>
<p>
	As for the weather, here are my highlights from a warm, sunny year:
	<ul>
		<li>The year began with record January warmth, and near-record Feb warmth; both months 2.6C above average</li>
		<li>Unsurprisingly it was the least wintry year since 2014, with just a single day of snowfall, no lying snow, and a mere 8 air frosts</li>
		<li>Feb was the windiest of any month I've recorded as a whole, and set records for highest gust (58 mph) and windiest day (16 mph)</li>
		<li>The warmth continued into the spring - Apr and May's avg maxima were 4.4 and 3.5 C above avg respectively</li>
		<li>May was exceptionally sunny with 306 hrs - 72% above avg and 69% of the maximum possible! Prior record from Jul 18 exceeded by over 40 hrs</li>
		<li>With sunny months in March, April and Sept too, the year as a whole had it's 2nd highest total, just behind 2018, with 16% more than average</li>
		<li>Rare for nw3, August was the best of the summer months with maxima 2C above avg and a notable heatwave - all days 7th-12th were above 30C and
			the overnight low on the 12th was a record high of 21.1C</li>
		<li>October was the wettest month I've recorded with 166 mm. More than half of that came in a three day downpour - 2nd-4th</li>
		<li>For the 7th year running, the overall average temperature was above the long-term average. It was the 2nd warmest, just behind 2014.</li>
	</ul>
</p>

<p>NB: After extensive analysis of the data from official met office sites at Heathrow and Northolt in west London (my closest two),
	and updating my long-term-average (LTA) period from 1971-2000 to 1981-2010,
	 I made some small adjustments to the climate averages used on this website.
	 Key changes: avg temp increased slightly, especially nights; rain decreased slightly; winter sun increased modestly.
	 When the official LTA data comes out for 1991-2020 I will update them again, probably in a year from now
	 (the standard practice is for the LTA reference period to update every 10 years and look back at a fixed 30-year period).
	</p>
	<p>
Separately I checked my data for possible sensor calibration issues: sun and temp data looks good but rain was too low so I adjusted all 2020 rain totals by +15%.
Furthermore I applied an additional +10% ongoing correction to the rain gauge itself, giving a +20% total ongoing correction.</p>

	<div style="margin-left: 0.1em">
		<h3>Monthly charts</h3>
		<img height="240" width="436" src="/graph12.php?x=436&y=240&type=tmean&mmm=2.2&year=2020" />
		<img height="240" width="436" src="/graph12.php?x=436&y=240&type=rain&mmm=2.2&year=2020" />
		<img height="240" width="436" src="/graph12.php?x=436&y=240&type=tmeana&mmm=2.2&year=2020" />
		<img height="240" width="436" src="/graph12.php?x=436&y=240&type=sunhr&mmm=2.2&year=2020" />
		<h3>Daily charts</h3>
		<div style="margin:1em"></div>
		<img height="240" width="436" src="/graph31.php?x=436&y=240&type=tmax&year=2020&month=0" />
		<img height="240" width="436" src="/graph31.php?x=436&y=240&type=rain&year=2020&month=0" />
		<img height="240" width="436" src="/graph31.php?x=436&y=240&type=tmaxa&year=2020&month=0" />
		<img height="240" width="436" src="/graph31.php?x=436&y=240&type=wmean&year=2020&month=0" />
		<a href="/charts.php">More charts</a>
	</div>

<h3>Key averages and extremes</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>12.2 C</b> [+1.3 C from the <abbr title="Long-term average">LTA</abbr>].
		Absolute Min and Max: <b>-1.8 C</b> (31st Dec), and <b>36.2 C</b> (31st Jul). <br />
		Air frosts: <b>8</b>. Ice days: <b>0</b>. Days above 30C: <b>10</b>. Max feels-like: <b>42 C</b>.
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>640 mm</b> [+6%] across <b>172</b> days (47%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>~34 mm</b> (3rd Oct). <br />
		Wettest Month: <b>166 mm</b> [+144%] (Oct). Most rainy: <b>24 days</b> (Oct). <br />
		Driest: <b>5 mm</b> [-89%] (May). Fewest days: <b>2 days</b> in May. <br />
		Most rain in an hour: <b>~10 mm</b>, from a thunderstorm on the <a href="/wxhistday.php?year=2020&month=08&day=16">16th of Aug</a>
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1730 hrs</b> [+16%] from a possible 4045. (43%) <br />
		Days with more than a minute of sunshine: <b>312</b> days (85%). <br />
		Days with at least 95% of their maximum possible sun: <b>36</b> (10%). <br />
		Sunniest month: <b>306 hrs</b> [+72%] (May). Dullest than avg: <b>-36%</b> in Oct.
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There were <b>zero</b> days with lying snow, and just <b>1</b> with falling snow (giving <b>3 cm</b> worth),
		as well as <b>8</b> air frosts [-18] (we had <b>36</b> total hrs below zero, the majority in Dec).
		The minimum wind chill was <b>-7C</b> on Dec 31st.
	</dd>
	<dt class="wind">Wind</dt>
	<dd>The maximum wind gust was <b>58 mph</b> on Feb 9th, and the maximum 1-min-avg wind was <b>34 mph</b> <br />
		The windiest day was the 9th Feb with a mean of <b>16 mph</b>. Feb was also the windiest month at 8.7 [+3.6 mph].
	</dd>
	<dt>Other Events</dt>
	<dd>There were <b>2</b> known days of hail, <b>11</b> with thunder (5 in Aug), and <b>8</b> days with fog at 9am.<br />
	</dd>
	</dl>
</p>
<h3>Website stats</h3>
<p>
	nw3weather again experienced strong growth, with traffic up another 21%. Stats:
	<ul>
		<li>Total visits: <b>184,398</b> (a 28% increase over 2019)</li>
		<li>Mean: <b>504</b> per day, Median: <b>429</b> (median lower than mean since busy days - commonly very wet, hot/cold, or snowy days - skew the mean)</li>
		<li>Min: 224 on 12 Sep (<a href="/wxhistday.php?year=2020&month=09&day=12">a sunny Saturday, the quietest day of the week</a>)</li>
		<li>Max: <b>1,917</b> on 04 Oct (<a href="/wxhistday.php?year=2020&month=10&day=04">last day of a record-breaking three-day rain storm</a>)</li>
		<li>Number of tweets: <b>34</b> (+2). Number of Followers: 199 (+45) (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>).</li>
		<li>New features: wind rose graphics for any custom period, including automated daily, monthly, annual generation.</li>
	</ul>
	<img src="/2020/sessions2020.jpg" /> <br />
</p>
<p>
<h3>Weather cam timelapse for 2020</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_yearly_2020.mp4" type="video/mp4" />
Your browser does not support the video tag.
</video>

<br /><a href="timelapsechive.php" title="Webcam timelapse archive"><b>See full timelapse archive</b></a>
</p>
<p>
<a href="/TablesDataMonth.php?vartype=rain" title="Monthly Rain Records">
	These pages are useful for comparing all aspects of the weather across the years
</a>.
</p>
</p>
<span style="color:#555; font-size: 90%">
	Updated: 14th Jan 2021
</span>

	<hr />

<h2 id="report-2019">Year 2019 - Heatwaves in Feb and July, winter sun, soggy Q4</h2>
<p style="margin-bottom: 0.5em;">
<p>
	2019 was the eleventh year of records for this weather station, and the ninth complete year in its current location in nw3. <br />
	A notable addition to the site this year was pond temperature monitoring - my local assistant (and pond fan)
	heads to the Hampstead Heath ponds most mornings to swim and note down the water temperature, which they
	then submit to nw3weather so I can display it on the site and also <a href="/wxdataday.php?vartype=pond" title="pond temp history">keep a record over the year</a>.
</p>
<p>
	As for the weather, we had plenty of warm, sunny spells, some record breaking heat in July,
	and a welcome return of the rain in late September. <br />My weather highlights:
	<ul>
		<li>The year began with continuing dryness - we hit 16 consecutive days without rain - the longest all year and a winter record</li>
		<li>It was a respectable year for snow with 17cm falling and 5 days with snow on the ground - about average </li>
		<li>Then began a warm spell mid Feb to late March - 3C above average and with only two daytimes colder than average</li>
		<li>The 18.1 C reached on 26 Feb was a winter record. Some London sites hit 20.</li>
		<li>It was a good year for winter sun lovers - Jan, Feb & Dec recorded 50-60% more than normal. Feb's was a record and more than Mar or Oct.</li>
		<li>Summer was warm and sunny overall, for the 2nd year running. July and Aug were ~2C above average</li>
		<li>Most notable was the record-breaking heatwave in late July and new record maximum of 36.8 C on the 25th</li>
		<li>By mid-Sept we were running a ~30% rainfall deficit, but this all changed by month's end and all four of the final months were wetter than avg</li>
		<li>Notable rainfall of ~30 mm on Sep 24th sadly coincided with a malfunction of my rain sensor; soon fixed!</li>
		<li>There were 26 days with measurable rain in Nov, bested in the past 10 years only by Jan 2014</li>
		<li>Oct and Nov were the only months with below average temperatures, and only by half a degree.</li>
	</ul>
</p>

<p>NB: After extensive analysis of rainfall data across three official met office sites in London, I determined that my new weather station, installed in Sep 2017,
was miscalibrated. Accordingly, on 10th Jan 2020 I adjusted all rainfall data by +10% since then. With this correction 2019 ended up with average rainfall.</p>
	<div style="margin-left: 0.1em">
		<img src="/2019/2019_temp.png" />
		<img src="/2019/2019_rain.png" />
		<img src="/2019/2019_sun.png" />
		<img src="/2019/2019_tempa.png" />
		<img src="/2019/2019_wind.png" />
		<img src="/2019/2019_snow.png" />
		<div style="margin:1em"></div>
		<img src="/2019/2019_d_tmax.png" />
		<img src="/2019/2019_d_rain.png" />
		<img src="/2019/2019_d_tmeana.png" />
		<img src="/2019/2019_d_pmin.png" />
		<a href="/charts.php">More charts</a>
	</div>

<h3>Summary of Data</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>11.7 C</b> [+1.0 C from the <abbr title="Long-term average">LTA</abbr>].
		Absolute Min and Max: <b>-4.6 C</b> (31st Jan), and <b>36.8 C</b> (25th Jul). <br />
		Air frosts: <b>14</b>. Ice days: <b>0</b>. Days above 30C: <b>7</b>. Max feels-like: <b>45 C</b>.
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>618 mm</b> [-1%] across <b>174</b> days (48%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>~32 mm</b> (24th Sept). <br />
		Wettest Month: <b>93 mm</b> [+65%] (Dec). Most rainy: <b>26 days</b> (Nov). <br />
		Driest: <b>12 mm</b> [-75%] (April). Fewest days: <b>9 days</b> in Feb. <br />
		Most rain in an hour: <b>~11 mm</b>, from a thunderstorm on the <a href="/wxhistday.php?year=2019&month=10&day=1">1st of Oct</a>
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1594 hrs</b> [+8%] from a possible 4045. (39%) <br />
		Days with more than a minute of sunshine: <b>314</b> days (86%). <br />
		Days with at least 95% of their maximum possible sun: <b>32</b> (9%). <br />
		Sunniest month: <b>205 hrs</b> [+13%] (Aug). Most sunnier than avg: <b>+66%</b> in Feb. Dullest than avg: <b>-19%</b> in June.
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There were <b>five</b> days with lying snow (max: <b>5 cm</b> on 1st Feb), and <b>4</b> with falling snow (giving <b>17 cm</b> worth),
		as well as <b>14</b> air frosts [-12] (we had <b>84</b> total hrs below zero, the majority in Jan).
		The minimum wind chill was <b>-7C</b> on Feb 1st.
	</dd>
	<dt class="wind">Wind</dt>
	<dd>The maximum wind gust was <b>48 mph</b> on Aug 10th, and the maximum 1-min-avg wind was <b>32 mph</b> <br />
		The windiest day was the 16th of Mar with a mean of <b>12.8 mph</b>. March was also the windiest month at 6.4 [+1.2 mph].
	</dd>
	<dt>Other Events</dt>
	<dd>There were <b>0</b> known days of hail, <b>7</b> with thunder (4 in Jul), and <b>7</b> days with fog at 9am. <br />
	</dd>
	</dl>
</p>
<p>
	As for website traffic, nw3weather again experienced strong growth, with traffic up another 21%. Strong Q4 thanks to the rain. Stats:
	<ul>
		<li>Total visits: <b>143,609</b> (a 21% increase over 2018)</li>
		<li>Mean: <b>393</b> per day, Median: <b>338</b> (median lower than mean since busy days - commonly very wet, hot/cold, or snowy days - skew the mean)</li>
		<li>Min: 193 on 23 Mar (<a href="/wxhistday.php?year=2019&month=03&day=23">a dull Saturday, the quietest day of the week</a>)</li>
		<li>Max: <b>1661</b> on 25 Jul (<a href="/wxhistday.php?year=2019&month=07&day=25">a day of record-breaking heat</a>)</li>
		<li>Number of tweets: <b>32</b> (+1). Number of Followers: 154 (+26) (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>).</li>
	</ul>
	<img src="/2019/2019_visits.PNG" /> <br />
<!--	<img src="/static-images/decade_stats.PNG" />-->
</p>
<p>
<h3>Weather cam timelapse for 2019</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_yearly_midday_2019.mp4" type="video/mp4" />
Your browser does not support the video tag.
</video>

<br /><a href="timelapsechive.php" title="Webcam timelapse archive"><b>See full timelapse archive</b></a>
</p>
<p>
<a href="/TablesDataMonth.php?vartype=rain" title="Monthly Rain Records">
	These pages are useful for comparing all aspects of the weather across the years
</a>.
</p>
</p>
<span style="color:#555; font-size: 90%">
	Updated: 10th Jan 2020
</span>

	<hr />

<h2>Year 2018 - 'beast from the east', a real summer, plentiful sun, new hardware</h2>
<p style="margin-bottom: 0.5em;" id="report-2018">
<p>
	2018 was the tenth year of records for this weather station! (and the eighth complete year in its current location in nw3). <br />
	In June I installed a new high-resolution IP web camera (20x more pixels than the current),
	and added functionality to the site providing daily, monthly and annual timelapses from the footage.
</p>
<p>
	This year also saw the death of my local weather server PC, which mysteriously and catastrophically shut down for good in late September.
	From across the Atlantic, I scrambled to find a replacement and within two weeks a new micro PC was up and running,
	thanks to the heroic efforts of my onsite assistant, who declared setting it up as "the hardest thing I have ever done".
	To be fair, we didn't have a working keyboard, so I had to explain over the phone how to use the on-screen keyboard
	to install remote access software so I could take over and complete the setup.
</p>
<p>
	And the weather itself? Drier and warmer than average, with a stand out summer (hot, dry, sunny) and notably cold late winter spell.
	It was also by far the sunniest year I've recorded. My weather highlights:
	<ul>
		<li>Late Feb into March saw a powerful cold snap bring our coldest day on record (-4.1C) and a spell of sub-zero weather lasting over 100 hours</li>
		<li>Snowiest year since 2013, with 8 days of snow on the ground and 16cm total snowfall</li>
		<li>March had 52% above avg rainfall, with rain on 23 days; both new records for that month</li>
		<li>The 19th of April was the hottest April day at 28.4C, and over 15C above average,
			<a href="http://nw3weather.co.uk/RankDay.php?vartype=tmaxa&month=0&rankLimit=20" title="see rankings">a record anomaly</a></li>
		<li>June was the driest month on record, with only 0.8mm of rain (99% below average), from 4 separate days each with under an hour of rainfall</li>
		<li>July was the warmest month I've recorded, 3.8C above average, with a daily mean high of 27.2C</li>
		<li>July was also the sunniest month I've recorded, 42% above avg, with daily sun averaging 8.5 hrs</li>
		<li>Summer as a whole, especially mid June to early Aug, was hot, dry & sunny; most notable was how long those conditions lasted</li>
		<li>Experienced our oldest October night on record, -0.8 C</li>
		<li>We had ~15% less rain than average (driest since 2011), and no month had more than 70 mm (a first)</li>
		<li>It was the fifth consecutive year with above average temperatures (+1.3C, same as 2017). Only Feb and March were colder than avg</li>
		<li>The year as a whole was by far the sunniest year I've recorded, with 18% more than average.</li>
	</ul>
</p>
	<div style="margin-left: 0.1em">
		<img src="/2018/2018_temp.png" />
		<img src="/2018/2018_rain.png" />
		<img src="/2018/2018_sun.png" />
		<img src="/2018/2018_pres.png" />
		<img src="/2018/2018_wind.png" />
		<img src="/2018/2018_snow.png" />
		<div style="margin:1em"></div>
		<img src="/2018/2018_d_tmax.png" />
		<img src="/2018/2018_d_tmeana.png" />
		<a href="/charts.php">More charts</a>
	</div>
<p>
	As for website traffic, nw3weather again had its busiest ever year since launching in Sep 2010. Stats:
	<ul>
		<li>Total visits: <b>118,259</b> (a 21% increase over 2017; was 2018 more interesting weather-wise?)</li>
		<li>Mean: <b>324</b> per day, Median: <b>282</b> (median lower than mean since busy days - commonly very wet, hot/cold, or snowy days - skew the mean)</li>
		<li>Min: 173 on 22nd Dec (<a href="/wxhistday.php?year=2018&month=12&day=22">a Saturday, the quietest day of the week</a>)</li>
		<li>Max: <b>1256</b> on 28th Feb (<a href="/wxhistday.php?year=2018&month=02&day=28">snowy with record-breaking cold</a>)</li>
		<li>Number of tweets: <b>31</b>. Number of Followers: 128 (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>).</li>
	</ul>
	<img src="/2018/2018_visits.PNG" />
	<p>Additionally, to support the new volume of image and video data from the IP cam (I now store snapshots every 5 minutes, along with the daily timelapses),
		I increased the storage of the web server to 75 GB. I expect to need an extra 25 GB per year.
	</p>
</p>
<h3>Summary of Data</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>12.1 C</b> [+1.3 C from the <abbr title="Long-term average">LTA</abbr>].
		Absolute Min and Max: <b>-5.4 C</b> (28th Feb), and <b>33.9 C</b> (26th Jul). <br />
		Air frosts: <b>23</b>. Ice days: <b>4</b>. Days above 30C: <b>12</b>
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>530 mm</b> [-15%] across <b>145</b> days (40%) of <abbr title="=0.2mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>17 mm</b> (10th Nov). <br />
		Wettest Month: <b>67 mm</b> [+52%] (Mar). Most rainy: <b>23 days</b> (March again). <br />
		Driest: <b>0.8 mm</b> [-99%] (June) across just <b>4 days</b> of rainfall. <br />
		Most rain in an hour: <b>12.2 mm</b>, from a thunderstorm on the <a href="/wxhistday.php?year=2018&month=07&day=13">13th of July</a>
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1734 hrs</b> [+18%] from a possible 4045. (42%) <br />
		Days with more than a minute of sunshine: <b>305</b> days (84%). <br />
		Days with at least 95% of their maximum possible sun: <b>39</b> (11%). <br />
		Sunniest month: <b>263 hrs</b> [+42%] (July). Most sunnier than avg: <b>+44%</b> in Oct. Dullest than avg: <b>-41%</b> in Apr.
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There were <b>eight</b> day with lying snow (max: <b>6 cm</b> on 1st March), and <b>8</b> with falling snow (giving <b>16 cm</b> worth),
		as well as <b>23</b> air frosts [-3] (<b>218</b> hrs below zero, the majority in Feb &amp; March).
		The minimum wind chill was <b>-15C</b> on Feb 28th.
	</dd>
	<dt class="wind">Wind</dt>
	<dd>The maximum wind gust was <b>50 mph</b> on Jan 3rd, and the maximum 1-min-avg wind was <b>31 mph</b> <br />
		The windiest day was the 3rd of Jan with a mean of <b>12.6 mph</b>. Jan was also the windiest month, [+1.1 mph].
	</dd>
	<dt>Other Events</dt>
	<dd>There were <b>0</b> known days of hail, <b>9</b> with thunder (4 in May), and <b>0</b> known days with fog at 9am. <br />
	</dd>
	</dl>
</p>
<p>
<h3>Weather cam timelapse for 2018</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_yearly_2018.mp4" type="video/mp4" />
Your browser does not support the video tag.
</video>
<br /><a href="timelapsechive.php" title="Webcam timelapse archive"><b>See full timelapse archive</b></a> (high-res cam available 20th Jun onwards)
</p>
<p>
<a href="/TablesDataMonth.php?vartype=rain" title="Monthly Rain Records">
	These pages are useful for comparing all aspects of the weather across the years
</a>.
</p>
</p>
<span style="color:#555; font-size: 90%">
	Updated: 7th Jan 2019
</span>

	<hr />

<h2>Year 2017 - warm spring, wet summer, snow returns</h2>
<p style="margin-bottom: 0.5em;" id="report-2017">
<p>
	2017 was the ninth year of records for this weather station, and the seventh complete year in its current location in nw3. <br />
	On the whole, it was another warm year, with average rain and sun. <br />
	The big news this year was that I completely replaced the weather station with an upgraded model: A Davis VP2, in early Sept. So far it's been working great!<br />

	<br />
	Weather Highlights:
	<ul>
		<li>Exceptional and extended Spring warmth: From late Feb to late Jun the mean temperature was 2.5C above the long-term average</li>
		<li>June was the warmest ever, and included a record 5 day spell of maximum temperatures above 30C</li>
		<li>For the 2nd year running, no month was more than 0.5C below average</li>
		<li>Bone-dry April: Bested only by April 2011's 2mm, the 3mm in April '17 is our 2nd driest month ever, and just 6% of the expected total</li>
		<li>Wet summer: Average rainfall for May-Aug was 56% above average (mostly due to several big falls). A welcome change after April</li>
		<li>Interestingly, the year had only 145 days with measurable rain, a record low, despite the total rain being 6% above avg</li>
		<li>Sunny January: Fourth year in a row above average, and included 4 days having their maximum possible sunshine</li>
		<li>On the 10th Dec we had our first substantial snow in five years, with a nice 4cm covering.</li>
	</ul>
</p>
	<div style="margin-left: 0.1em">
		<img src="/2017/2017_temp.png" />
		<img src="/2017/2017_rain.png" />
		<img src="/2017/2017_sun.png" />
		<img src="/2017/2017_pres.png" />
		<img src="/2017/2017_wind.png" />
		<img src="/2017/2017_snow.png" />
		<a href="/charts.php">More charts</a>
	</div>
<p>
	For the website, nw3weather again had its <a href="/wx8.php#site-traffic">busiest ever year</a> since launching in Sep 2010. Stats:
	<ul>
		<li>Total visits: <b>98,075</b> (a 16% increase over 2016, and double the 2012 total)</li>
		<li>Mean: 269 per day, Median: 240 (median lower than mean since busy days - commonly very wet, windy or snowy days - skew the figure)</li>
		<li>Min: 126 on Christmas Eve (<a href="/wxhistday.php?year=2017&month=12&day=24">an overcast, dry, warm and unremarkable winter day</a>)</li>
		<li>Max: <b>1486</b> on 10th Dec (<a href="/wxhistday.php?year=2017&month=12&day=10">Snow Day!</a>)</li>
		<li>No. tweets: 25. No. Followers: 69 (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>).</li>
	</ul>
	<img src="/2017/2017_visits.PNG" />
</p>
<h3>Summary of Data</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>12.0 C</b> (+1.3 C from the <abbr title="Long-term average">LTA</abbr>).
		Absolute Min and Max: <b>-3.6 C</b> (22nd Jan), and <b>33.0 C</b> (21st Jun).
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>648 mm</b> (+4%) across <b>145</b> days (40%) of <abbr title="=0.1mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>39 mm</b> (9th Aug). <br />
		Wettest Month: <b>89 mm</b> [+112%] (Jul). Most rainy: <b>20 days</b> (Feb). <br />
		Driest: <b>3 mm</b> [-94%] (Apr) across just <b>4 days</b> of rainfall.
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1454 hrs</b> (-1%) from a possible 4045. (36%) <br />
		Days with more than a minute of sunshine: <b>301</b> days (82%). <br />
		Sunniest month: <b>200 hrs</b> [+5%] (Jun). Most sunnier than avg: +62% in Jan
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There were <b>three</b> day with lying snow (max: 4cm on 10th Dec), and <b>6</b> with falling snow,
		as well as <b>21</b> air frosts [-5] (133 hrs below zero).
	</dd>
	<dt>Other Events</dt>
	<dd>There were <b>2</b> days of hail, <b>1</b> with thunder (11th Sep), and <b>4</b> with fog at 9am.
	</dd>
	</dl>
</p>
<p>
<h3>Weather cam timelapse for 2017</h3>
<video width="640" height="480" controls>
  <source src="/cam/timelapse/skycam_2017_9to3.mp4" type="video/mp4">
Your browser does not support the video tag.
</video>
</p>
<p>
<a href="/TablesDataMonth.php?vartype=rain" title="Monthly Rain Records">
	These pages are useful for comparing all aspects of the weather across the years
</a>.
</p>
</p>
<span style="color:#555; font-size: 90%">
	Updated: 15th Jan 2018
</span>

	<hr />

			<h2>Year 2016</h2>
<p style="margin-bottom: 0.5em;" id="report-2016">
<p>
	2016 was the eighth year of records for this weather station, and the sixth complete year in its current location in nw3.<br />
	Highlights:
	<ul>
		<li>December rainfall: 81% below average, third driest month on record, and first time the driest month of the year was a winter month</li>
		<li>Very wet, stormy, dull June - 67% up on rain, and just half the expected sunshine. 7 days had thunder, a record</li>
		<li>Winter sunshine: Jan, Feb, Dec and Nov were all sunnier than average</li>
		<li>Foggy December: 7 days, a record (previous was 3 in Nov' 11), helping achieve a record year with 10 foggy days</li>
		<li>Late April cold: The last week of April was > 3C below avg and had a couple of snow showers (the latest seen at nw3weather)</li>
		<li>Anomalous overall warmth: 7 out of 12 months were > 1C above avg, and the lowest anomaly was just -0.5C (a record)</li>
		<li>September was warmer than June, and only a degree cooler than Jul/Aug, followed by a 6 deg drop into Oct</li>
	</ul>
</p>
	<div style="margin-left: 4em">
		<img src="/2016/2016_temp.png" />
		<img src="/2016/2016_rain.png" />
		<img src="/2016/2016_sun.png" />
		<img src="/2016/2016_snow.png" />
		<a href="/charts.php">More charts</a>
	</div>
<p>
	The weather station itself had a bad year, the wind sensor failing in early Feb (not yet fixed), and multiple
	outages of the thermo/hygro sensor (ranging from a few hours to a few days); indeed this was replaced in late Sep.
	This coming year I plan to replace the entire weather station, probably in September.
</p>
<p>
	For the website, nw3weather just about saw its <a href="/wx8.php#site-traffic">busiest year</a> since launching in Sep 2010. Stats:
	<ul>
		<li><b>Total visits: 84,738</b> (just 0.7% / 600 more than 2015)</li>
		<li><b>Mean: 232 per day, Median: 213</b> (median lower than mean since busy days - commonly very wet, windy, hot or cold days - skew the figure)</li>
		<li><b>Min: 121 on Christmas Day</b> (a dull, warm, unremarkable <a href="/wxhistday.php?year=2016&month=12&day=25">winter day</a>)</li>
		<li><b>Max: 813 on 23rd June</b> (EU referendum day, though also a <a href="/wxhistday.php?year=2016&month=6&day=23">very wet, stormy day</a>)</li>
		<li><b>No. tweets: 52</b> (<a href="https://twitter.com/nw3weather">twitter.com/nw3weather</a>)</li>
	</ul>
</p>
<h3>Summary of Data</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>11.7 C</b> (+1.0 C from the <abbr title="Long-term average">LTA</abbr>).
		Absolute Min and Max: <b>-3.5 C</b> (19th Jan), and <b>32.6 C</b> (19th Jul).
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>570 mm</b> (-9%) across <b>161</b> days (44%) of <abbr title="=0.1mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>30 mm</b> (23rd Jun). <br />
		Wettest Month: <b>92 mm</b> [+67%] (Jun). Most rainy: <b>23 days</b> (Jan). <br />
		Driest: <b>11 mm</b> [-81%] (Dec) across <b>8 days</b> of rainfall.
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1426 hrs</b> (-3%) from a possible 4045. (35%) <br />
		Days with more than a minute of sunshine: <b>311</b> days (85%). <br />
		Sunniest month: <b>192 hrs</b> [+5%] (Aug).
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There was <b>one</b> day with lying snow (1cm on 17th Jan), and <b>5</b> with falling snow,
		as well as <b>16</b> air frosts (-10).
	</dd>
	<dt>Other Events</dt>
	<dd>There were <b>2</b> days of hail, <b>13</b> with thunder (7 in Jun), and <b>10</b> with fog at 09z (7 in Dec).
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
	Updated: 1st Jan 2017
</span>

	<hr />

		<h2>Year 2015</h2>
<p style="margin-bottom: 0.5em;" id="report-2015">
<p>
	2015 was the seventh year of records for this weather station, and the fifth complete year in its current location in nw3.<br />
	Generally unremarkable when averaged over the entire year (temperature +1.1C, rain -5%, sun -2%), 2015
	will nonetheless be remembered for its quite spectacular December. The heights reached by the daily night and day
	temperatures are impressive enough, but most remarkable and unusual was the sheer persistence of the warmth:
	<ul>
		<li>Mean temperature, 11.5C (10C by night, 13C by day)... +6C above the LTA average of 5.5C</li>
		<li>Highest temperature by day: 16.0C (winter month record)</li>
		<li>Highest temperature by night: 14.1C (record for any month between Dec-Apr)</li>
		<li>EVERY day hit at least 10C (consider that the AVERAGE December high is only 7.8C)</li>
		<li>NO night fell below 4.7C (the AVERAGE December low is 3.2C)</li>
		<li>The previous December record for mean temp of 7.1C was bested by over 4C</li>
		<li>Five years ago the mean temp was 1.3C, 10C lower than this year</li>
		<li>The mean overnight temperature, 10C, was higher than ANY May month on record</li>
		<li>All but two of the 20 highest December temperatures in the last seven years, and all of the top 13, came in 2015 </li>
		<li>It was significantly the most anomalous for temperature in 84 months on record, at +6.0C. Previous record was +4.7C in Apr 2011</li>
	</ul>
	It's perhaps hard to justify the hype when the record spans only seven years, but the 367-year CET (Central England)
	series reproduces these results: it had its <a href="http://www.metoffice.gov.uk/hadobs/hadcet/mly_cet_mean_sort.txt">warmest December on record</a>,
	besting its previous result by 1.6C. It was also warmer than 366 out of 367 past Novembers.
</p>
<p>
	Also worth a note is the wind speed, which averaged over the year was the highest on record.
	December also recorded the strongest monthly mean speed for any month at 7.8 mph.
	June gets a mention for having only 9mm of rain, the second driest on record after April 2011's 2mm.
	April was the sunniest month of the year. And July 1st recorded the highest temp on the record, 34C.
</p>
<p>
	For the website, nw3weather again saw its busiest year since launching in Sep 2010, with 84,000 visits (up from 75,000 in 2014),
	giving a mean figure of 231 per-day. The median was 210, since busy days (commonly very wet, windy, hot or cold days) skew the figure.
	The most in a single day was 679 on <a href="/wxhistday.php?year=2015&month=7&day=1" title="">1st July</a>,
	the hottest day in nw3weather history. The fewest was 116 on 17th Oct, a quiet Saturday (which is generally the quietest day of the week).
	2015 also saw the introduction of the nw3weather twitter feed, a way for me to share crazy (and mundane) statistics from the data I record.
</p>
<h3>Summary of Data</h3>
<p>
<dl>
	<dt class="temp">Temperature</dt>
	<dd>Mean <b>11.8 C</b> (+1.1 C from the <abbr title="Long-term average">LTA</abbr>).
		Absolute Min and Max: <b>-4.1 C</b> (23rd Jan), and <b>34.2 C</b> (1st Jul).
	</dd>
	<dt class="rain">Rainfall</dt>
	<dd>Total: <b>594 mm</b> (-5%) across <b>172</b> days (47%) of <abbr title="=0.1mm">recordable rain</abbr>. <br />
		Highest daily (starting at midnight) total: <b>32 mm</b> (24th Jul). <br />
		Wettest Month: <b>78 mm</b> [+47%] (Aug). Most rainy: <b>24 days</b> (Dec). <br />
		Driest: <b>9.4 mm</b> [-83%] (Jun) across <b>6 days</b> of rainfall.
	</dd>
	<dt class="sun">Sunshine</dt>
	<dd>Total: <b>1440 hrs</b> (-2%) from a possible 4038. (36%) <br />
		Days with more than a minute of sunshine: <b>308</b> days (84%). <br />
		Dullest Month: <b>36 hrs</b> [-47%] (Nov) across <b>20 days</b> of countable sunshine. <br />
		Sunniest: <b>202 hrs</b> [+47%] (Apr).
	</dd>
	<dt class="snow">Winter Events</dt>
	<dd>There was <b>one</b> day with lying snow (2cm on 3rd Feb), and <b>5</b> with falling snow,
		as well as <b>13</b> air frosts (-13), 7 in Jan, 5 in Feb, and 1 in Nov.
	</dd>
	<dt>Other Events</dt>
	<dd>There were <b>0</b> days of hail, <b>6</b> with thunder, and <b>3</b> with fog at 09z.
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
	Updated: 4th Jan 2016
</span>

	<hr />

	<h2>Year 2014</h2>
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
<h3>Summary of Data</h3>
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