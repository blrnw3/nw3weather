<?php
use nw3\config\Station;
use nw3\app\util\Html;
use nw3\app\helper;
use nw3\data\Traffic;
?>

<h1>About nw3weather</h1>

<h2>Weather Station</h2>

<h3>Technical</h3>
<p>
	I am operating an Oregon Scientific WMR-200 and using Weather Display software to collect the data in real time from the sensors,
	which is then broadcast to this website. Each sensor polls for data every 15-60s, and is available live on nw3weather with a delay of approx 5s, though
	aggregation-type data (e.g. daily mean, monthly max, graphs) is only calculated every 1-5 minutes. At midnight, daily aggregation data is saved to the
	permanent historical data-sets.
</p>
<p>
	It is, however, important to note that these various updates will not always be carried out due to the large number of
	potential failure nodes in the system - sensors (power, wireless signal), local PC (HDD failure, software crash, internet connection),
	and the web server. The most frequent cause of downtime is local internet connection loss, when live updates cannot be sent to the remote web server.
	I have been lucky not to suffer any serious hardware failures (most failures result in no physical data loss), but undoubtedly the time will come.<br />
	The current uptime status can view on the <a href="./system">system page</a>.
</p>

<h3>Accuracy</h3>
<p>
	The data comes from a personal automatic weather station (PWS/AWS) so there can be no guarantees of accuracy or reliability,
	and there is no immediate quality control, but I do intervene to fix glitches
	or suspected misreadings and the setup shown below is designed to provide the most accurate readings possible. <br />
	For temperature and humidity readings this means shielding the sensors from
	<abbr title="incident solar radiation">insolation</abbr> and providing them with good airflow;
	for rain data the sensor needs to be in as open a place as possible to avoid rain shadow, but not too open since a high wind can cause problems;
	and for wind readings the sensors should be 10m above ground and clear from obstructions (to provide a 'clean' flow of air).
	In an urban environment these conditions are difficult to meet, and this must be considered when using data from such sites,
	especially when comparing them to non-urban sites.
	A more in-depth guide to siting weather stations can be found <a href="http://www.weatherstations.co.uk/gooddata.htm" title="Optimal siting of a PWS">here.</a>
</p>
<p> It should also be noted that at no point do I quote figures for the errors associated with my readings, though of course they technically do exist.
	This is partly due to conventions in displaying weather data, but also because it can be very difficult to ascertain such values, especially for urban sites:
	For a given sensor the errors can vary widely from day to day and are sensitive to quite specific factors.
	All I can say is that I have tried to minimise and correct for errors where possible, but some large errors may well exist.
</p>

<a name="location"></a>
<h2>Location</h2>
<p>
	NW3 weather station is situated on the south-eastern edge of London's Hampstead Heath, a 790 acre ancient park just a few miles north of central London.
	<a href="http://hampsteadheath.net/" title="External Link">View more information about The Heath.</a><br />
	Nearby areas include Hampstead village to the North-West, Highgate to the North, Belsize Park to the South-West, and Kentish Town to the South East.</p>
<div id="map" style="width: 500px; height: 300px">
	Google maps placeholder
</div>
<p>The station co-ordinates are approx. <?php echo Station::LAT ?>, <?php echo Station::LNG; ?>, which is ~<?php echo Station::ALTITUDE ?> (190ft) above mean sea level.</p>

<h2>Setup</h2>
The station comprises several sensors:
<ul>
	<li>A combined thermometer/hygrometer housed in a home-built Stevenson Screen (a radiation shield)</li>
	<li>A combined anemometer &amp; wind vane, located atop a 6m pole attached to the chimney of the house</li>
	<li>A self-tipping rain gauge modified to a resolution of 0.3mm, which is attached to the roof of the Stevenson Screen</li>
	<li>A barometer integrated into the receiving unit (indoors).</li>
</ul>
<?php Html::img("P1010070.JPG", "Thermo/Hygro in radiation shield, Rain Gauge on top", 'about_photo'); ?>
<?php Html::img("P1010074.JPG", "Anemometer &amp; Wind Vane on top of pole", 'about_photo'); ?>
<p>All the sensors are automatic and send data wirelessly to the receiving unit every 14-60s.</p>
<?php Html::img("PICT2516.JPG", "Thermo/Hygro close-up inside radiation shield", 'about_photo'); ?>
<?php Html::img("P1010076.JPG", "Wind Sensors close-up", 'about_photo'); ?>
<p>
	Additionally, there are two webcams used to monitor sky and ground conditions, which along with traditional techniques I use
	to collect data for extra weather variables - sun hours, cloud cover, wet hours, snowfall, lying snow, fog, hail, and thunder(storms).
</p>


<a name="History"></a>

<h2>History</h2>

<p>
	I started collecting data at this site on 28th July 2010.
	Prior to that I had been running the same setup in East Finchley, a town 5 km north of here, since February 2009,
	but I have kept daily temperature records since March 2008 (albeit not yet integrated into this website).
	Consequently, any records from before July 2010 are for East Finchley, though given the small distance involved, any data comparisons are largely valid.
	However, there is one period which is an exception to this - from 17th April to 27th July 2010 the
	<abbr title="Automatic Weather Station">AWS</abbr> was on a temporary site
	in Finchley Central (~1 km from East Finchley), and I am not confident
	in the accuracy of some of the figures. Furthermore, I collected absolutely no
	wind records for this period - instead using adjusted data from the <abbr title="routine aviation weather report">METAR</abbr>s of Heathrow.
</p>
<p>
	The sky-facing webcam (Logitech C300) was added to the station on 1st August 2010; The ground-facing one on 19th December 2010.
</p>

<h3>Technical history</h3>
<ul>
	<li type="disc">The rain gauge was modified in March 2009 - the original had a resolution of only 1mm.
		This involved fitting a funnel to the collecting channel so that less rain is needed to cause a 'tip' in the internal bucket
		(each tip of 10ml results in a signal being sent to the receiving unit, which registers the rain).</li>
	<li type="disc"> In July 2009 I built the radiation shield for the temp/hum sensor to improve the accuracy of the readings:
		The walls are double louvred hardwood, the roof is double insulated plywood with a cavity for air flow, and
		the whole thing is painted in white gloss. Maintenance is carried out twice a year, mainly to keep the paint glossy for maximal radiation reflection.</li>
	<li type="disc">The combined wind sensor unit was mounted on a 6.1m aluminium-alloy aerial pole in August 2009.</li>
	<li type="disc">A manual rain gauge was installed on 4th September 2011 - this is for the verification and calibration of the automatic gauge data. </li>
</ul>


<h2>Website</h2>

<p>
	The site has been in continuous, if fitfull and highly variable, development since the summer of 2010.
	<br /><code>nw3weather.co.uk</code> launched on 10th September 2010, I having acquired the domain name soon after moving here in July.
	The most recent version (v3) launched on 22nd May 2013. <br />
	At approx 8am daily, I submit manual observations for the previous day to the system, as well as fixing glitches and correcting misreads.
	These changes are reflected on the site almost immediately.
</p>

<h3>Technical</h3>
<p>
	The process involved in converting readings from the sensors to the data viewable on this website can be summarised thus: <br />
	<?php echo Html::img('Sensors_to_WWW.jpg', "Data flow chart", 'about_photo_solo') ?>
	<br /> Each sensor wireless sends data to the base station, which transmits it to my home PC (a dedicated low-power machine running Windows 7).
	Weather Display software renders and uploads this data via
	<abbr title="file transfer protocol">FTP</abbr> to a web server -
	which broadcasts the PHP-rendered web pages to the World Wide Web so anyone making a valid HTTP request can view them.
	The web server is hosted by <a href='http://www.clook.net/'>Clook</a> on a shared package running the classic Linux/Apache setup.
	All web pages and data procedures are written in PHP (inc. graphs powered by the JPGraph library),
	with some Javascript/JQuery/AJAX used to provide a basic level of dynamism.
	Data storage is a combination of <abbr title='comma separated value'>CSV</abbr> (long-term),
	<a href='https://en.wikipedia.org/wiki/Serialisation'>PHP-serialised</a> (short-term), and PHP variable-exported.
</p>
<!--<ul>
	<li><?php Html::a('dat2012.csv', 'Sample csv', 'Daily data from 2012') ?></li>
	<li><?php Html::a('sample.xhtml', 'Sample script', 'Source-code for monthly data tables webpage') ?></li>
</ul>-->

<h3>History</h3>
All old site versions are still available to view, but are no longer maintained and may not display the latest data.

<p>
	<b><a href="/oldSites/sitev2">Site version 2</a></b> (10th Sep 2011 - 20th May 2013). <br />
	This involved a change in the procedure for getting data, using
	<abbr title="PHP: Hypertext Preprocessor (processed by the server into HTML before the page loads)">PHP</abbr>, which
	was also used extensively across the site to enable most of the new features and site-areas: obtaining all the historical data, displaying when
	a record is set, the unit selection module, records for the current day (in the detailed pages), and a lot more besides.
	A small amount of Javascript and AJAX were also introduced to develop the auto-updating option.<br />
</p>

<p>
	<b><a href="/oldSites/sitev1">Site version 1</a></b> (10th Sep 2010 - 10th Sep 2011). <br />
	My first foray into web development.
	No scripting was used at all - all pages came pre-rendered from the local PC using Weather Display's inbuilt functionality to insert live data
	into prepared templates, which I wrote but were based on <a href="http://www.carterlake.org/webtemplates.php" title="Weather Display Templates">carterlake.org</a>'s.
</p>

<p>
	<b><a href="/oldSites/sitev0">Site version 0</a></b> (pre-nw3weather, Aug 2009 - Sep 2010). <br />
	Initial experimentation. It was simply a webpage pre-generated by Weather Display and hosted on
	a free web server. I only set it up so I could view my data away from home.
</p>

<p>
	Spreadsheet-based data collection and analysis was done between March 2008 and mid-2012.
	At first, before nw3weather came into existence, this served as the only weather data repository,
	but over time the web-based system took over due to its greater flexibility, power, reach, and automation.
	Samples of the <?php Html::a('DailyData2011.xlsx', 'daily', 'Daily data 2011 spreadsheet'); ?> and
	<?php Html::a('MonthlyData2011.xlsx', 'monthly', 'Monthly data 2011 apreadsheet'); ?> spreadsheets for 2011 are provided for interest.
</p>

<h3>Site Traffic</h3>
These figures are approximations based on my site logs, and are for interest only.
<!--<table class="table1 aboutTbl">
	<thead>
		<tr class="table-head">
			<td class="td12" style="padding: 0.5em" colspan="2">Nw3weather Site Traffic History</td>
		</tr>
		<tr class="table-top">
			<td>Date</td>
			<td>Median Daily Visits</td>
		</tr>
	</thead>
	<tfoot>  Yes, it is meant to go here!
		<tr>
			<td>Jun 2012 - Current</td>
			<td>~150</td>
		</tr>
	</tfoot>
	<tbody>
		<tr class="rowdark">
			<td>Sep 2010 - Mar 2011</td>
			<td>~10</td>
		</tr>
		<tr class="rowlight">
			<td>Apr 2011 - Sep 2011</td>
			<td>~30</td>
		</tr>
		<tr class="rowdark">
			<td>Oct 2011 - Jan 2012</td>
			<td>~60</td>
		</tr>
		<tr class="rowlight">
			<td>Feb 2012 - May 2012</td>
			<td>~100</td>
		</tr>
	</tbody>
</table>-->

<?php
$traffic = new helper\Traffic();
$traffic->prepare_annual_data_table();
?>
<table class="table1 aboutTbl">
	<thead>
		<tr class="table-head">
			<td class="td12" style="padding: 0.5em" colspan="6">Nw3weather Annual Traffic</td>
		</tr>
		<tr class="table-top">
			<td>Year</td>
			<td>Total Visits</td>
			<td>Mean</td>
			<td>Median</td>
			<td>Max</td>
			<td>Min</td>
		</tr>
	</thead>
	<tfoot> <!-- Yes, it is meant to go here! -->
		<tr>
			<td>Total</td>
			<td><?php echo number_format($traffic->annual_summary['sum']) ?></td>
			<td><?php echo $traffic->annual_summary['mean'] ?></td>
			<td></td>
			<td><?php echo $traffic->annual_summary['max'] ?></td>
			<td><?php echo $traffic->annual_summary['min'] ?></td>
		</tr>
	</tfoot>
	<tbody>
		<?php foreach (Traffic::$annual as $year => $data): ?>
		<tr>
			<td><?php echo $year ?></td>
			<td><?php echo number_format($data['sum']) ?></td>
			<td><?php echo $data['mean'] ?></td>
			<td><?php echo $data['median'] ?></td>
			<td><?php echo $data['max'] ?> (<?php echo $data['max_date'] ?>)</td>
			<td><?php echo $data['min'] ?> (<?php echo $data['min_date'] ?>)</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>
<p>Additionally, it is found that, as expected, traffic is higher on days of precipitation - particularly snowfall, which can drive traffic up by more than 500%;
	record site traffic was ~1500 visits on <a href='/wxhistday.php?year=2013&amp;month=1&amp;day=18' title='daily weather breakdown'>18th Jan 2013</a>.
</p>

<h2>Acknowledgements</h2>
<p>
	Thanks go to TNET weather and Wildwood Weather for script ideas which helped in the development of the historical data tables.<br />
	Thanks also to carterlake for CSS and HTML templates that I used in nw3weather's early years (pre-2013).<br />
	Finally, enormous thanks to any frequent visitors, and especially those have contacted me with kind words -
	I really appreciate this as it makes all the effort and resources I devote to building and maintaining this website especially worthwhile.
</p>

<h2>Other Notes and Thoughts</h2>
<a name="Forecasting"></a>
<h3>Forecasting</h3>
<p>
	The accuracy of the local forecast on the home page is considered to be low, since it is based entirely on pressure
	trends - falling pressure means cloud, and if it continues to fall, then rain;
	rising pressure signals sunny intervals then clear conditions. This is obviously very simplistic. The forecasting
	done by national meteorological offices requires the amassing of a huge amount of data from observation sites on land,
	on the sea, in the air and even from satellites in space. The data is then fed through supercomputers running numerical models
	that aim to simulate extremely complex atmospheric processes.
	The model outputs then need processing and interpreting before a forecast is produced.
	The whole operation costs many millions of pounds to set up and run. Barometric prediction, on the other hand, is very cheap but has
	severe limitations: it cannot offer regional guidance, it is only relevant for the next ~24 hours, and it breaks down
	completely in more complex atmospheric set-ups.
</p>

<a name='data'></a>
<h3>Data Quality, Differences, and Requests</h3>
<p>
	As discussed, I take steps to ensure data quality, but this is not guaranteed and I accept that my data probably does not hold up
	to international standards for an urban site. However, I am active in comparing my data to, and ensuring consistency with,
	data produced by London's "official" stations, these being at all airports near-ish nw3 (Northolt, Heathrow, City),
	as well as St James's Park, and one at Whitestone Pond in Hampstead.
	A <a href='news#rainfall'>detailed discussion</a> on my blog exists about rainfall differences, and similar (unpublished) analysis has been done for Temperature.
	As a general comment, it should be stated that there can be many reasons for differences in weather across the large metropolis that is London.
	Permanent reasons include elevation (<a href='http://en.wikipedia.org/wiki/Lapse_rate'>lapse rate</a>), hills (frost pockets, orographic lift),
	buildings (<a href='http://en.wikipedia.org/wiki/Urban_heat_island'>UHI</a> effect, turbulence).
	Day-to-day causes are down to the localised nature of cloud and rain cells (a thunderstorm in 2009 dropped 36 mm on East Finchley, whilst Heathrow remained dry).
	Thus, differences can be perfectly natural (though averaged out are usually small).<br />
	At any rate, my analysis seems to support the conclusion that as far my own data is concerned, its accuracy is within
	the deviations due to the given natural reasons in the majority of cases. The notable exception is wind speeds, which under-report by 25-50% at nw3weather. <br />
	I will continue to strive to maintain good quality data as far as reasonably possible for a hobbyist site.
</p>
<p>
	I am happy to share my raw data files with anyone who <a href='contact'>requests</a> them from me.
	I can provide most data from Jan 2009, and temperature data from March 2008, at minutely or greater intervals, in CSV format.
	Additionally I am happy to answer questions and provide analysis of this data to individuals or groups with non-commercial interests.
	However, due to limited resources, I cannot provide extensive or repeated analysis, and you should seek professional guidance (from the Met Office, probably).
</p>

<h3>Rain Gauge - Trace Rain, and Snowfall</h3>
<p>
	The rain gauge works by water falling through a meshed funnel and tipping an internal bucket.
	The mesh serves to filter out grit and leaves, but also means snow gets trapped until it melts or is blown away by the wind.
	The equipment is sensitive to 0.3mm of rain - so an hour's worth of drizzle may not register,
	though I will manually record the day as having had a trace of rainfall (seen as 0.1mm).
</p>
<p>
	Consequently, I measure snowfall by a combination of snow depth (1cm of snow approx. corresponds to 1mm of liquid precipitation)
	and high-resolution radar which can pick up all types of precipitation. To my knowledge, this is also how the Met Office do it.
	In any case, once I've gathered this information after a snowfall, I manually enter the approximate rain-equivalent figure into my records,
	so it does show up in the daily and monthly totals, but not immediately (typically by 9am the following day).
</p>


<h2>About me</h2>

<p>
	I am a weather fanatic with a particular interest in data analysis,
	and have been since around 2007 when I first started to collect data (using spreadsheets) from a very basic weather station I had.
	I enjoy any weather which is interesting from a data point-of-view - whether that be extreme cold, heat, snow or dullness -
	though I always appreciate convective events (storms, hail and heavy showers) - rare though these are in London.
</p>
<p>
	My background is in Physics and Computer Science, both of which I was largely motivated to study by my love of the weather.<br />
	I currently live and work in London.
</p>

<script type="text/javascript" src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDwThmJT7UIczMkMIEq9CkaZ-mYoEhur58&amp;sensor=false"></script>
<script type="text/javascript">
	//<![CDATA[
	$(document).ready(initialise);
	function initialise() {
		var mapOptions = {
			center: new google.maps.LatLng(<?php echo Station::LAT ?>, <?php echo Station::LNG ?>),
			zoom: 12,
			mapTypeId: google.maps.MapTypeId.ROADMAP
		};
		map = new google.maps.Map(document.getElementById("map"),
			mapOptions);

		var homeMarker = new google.maps.Marker({
			position: map.getCenter(),
			map: map,
			title: 'nw3weather home',
			icon: '<?php echo ASSET_PATH ?>favicon.ico'
		});
		var nw3Bubble = new google.maps.InfoWindow({
			content: '<div><h2 style="margin:0.2em">nw3weather base!</h2><img src="<?php echo ASSET_PATH ?>/img/gmaps3.jpg" alt="pic"></div>'
		});
		google.maps.event.addListener(homeMarker, 'click', function() {
			nw3Bubble.open(map, homeMarker);
			map.setCenter(homeMarker.getPosition());
		});
	}
	//]]>
</script>
