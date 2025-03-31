<?php
require("Page.php");
Page::init([
	"fileNum" => 8,
	"title" => "About",
	"description" => 'NW3 Weather station and Website Information, accuracy, and comments, set-up, location, pictures, traffic, and history.'
]);
Page::Start();

class Traffic {
	public $annual = [
		2011 => [ // Based on 01 Sep - End
			'sum' => 7000,
			'mean' => 57,
			'median' => 54,
			'min' => null,
			'max' => 240,
			'min_date' => null,
			'max_date' => null
		],
		2012 => [
			'sum' => 49000,
			'mean' => 133,
			'median' => 127,
			'min' => 39,
			'max' => 667,
			'min_date' => '10 Jan',
			'max_date' => '04 Feb'
		],
		2013 => [
			'sum' => 63000,
			'mean' => 174,
			'median' => 152,
			'min' => 67,
			'max' => 1562,
			'min_date' => '03 Sep',
			'max_date' => '18 Jan'
		],
		2014 => [
			'sum' => 75000,
			'mean' => 206,
			'median' => 188,
			'min' => 106,
			'max' => 599,
			'min_date' => '06 Sep',
			'max_date' => '14 Feb'
		],
		2015 => [
			'sum' => 84000,
			'mean' => 231,
			'median' => 210,
			'min' => 116,
			'max' => 679,
			'min_date' => '17 Oct',
			'max_date' => '01 Jul'
		],
		2016 => [
			'sum' => 85000,
			'mean' => 232,
			'median' => 213,
			'min' => 121,
			'max' => 813,
			'min_date' => '25 Dec',
			'max_date' => '23 Jun'
		],
		2017 => [
			'sum' => 98000,
			'mean' => 269,
			'median' => 240,
			'min' => 126,
			'max' => 1486,
			'min_date' => '24 Dec',
			'max_date' => '10 Dec'
		],
		2018 => [
			'sum' => 118000,
			'mean' => 324,
			'median' => 282,
			'min' => 173,
			'max' => 1256,
			'min_date' => '22 Dec',
			'max_date' => '28 Feb'
		],
		2019 => [
			'sum' => 143000,
			'mean' => 393,
			'median' => 338,
			'min' => 193,
			'max' => 1661,
			'min_date' => '23 Mar',
			'max_date' => '25 Jul'
		],
		2020 => [
			'sum' => 184000,
			'mean' => 504,
			'median' => 429,
			'min' => 224,
			'max' => 1917,
			'min_date' => '12 Sep',
			'max_date' => '04 Oct'
		],
		2021 => [
			'sum' => 183000,
			'mean' => 503,
			'median' => 435,
			'min' => 215,
			'max' => 1552,
			'min_date' => '25 Sep',
			'max_date' => '18 Jun'
		],
		2022 => [
			'sum' => 209000,
			'mean' => 573,
			'median' => 453,
			'min' => 240,
			'max' => 3329,
			'min_date' => '28 May',
			'max_date' => '19 Jul'
		],
		2023 => [
			'sum' => 287000,
			'mean' => 787,
			'median' => 683,
			'min' => 352,
			'max' => 2594,
			'min_date' => '04 Mar',
			'max_date' => '02 Nov'
		],
		2024 => [
			'sum' => 324000,
			'mean' => 885,
			'median' => 737,
			'min' => 370,
			'max' => 2822,
			'min_date' => '25 Dec',
			'max_date' => '23 Sep'
		],
	];

	public $annual_summary = [
		'sum' => 0,
		'mean' => 0,
		'min' => PHP_INT_MAX,
		'max' => PHP_INT_MIN,
	];

	function prepare_annual_data_table() {
		foreach ($this->annual as $yr => $data) {
			if($yr > 2012) {
				$last = $this->annual[$yr - 1]['sum'];
				$diff = $data['sum'] - $last;
				$this->annual[$yr]['yoy'] = (($diff > 0) ? "+ " : "") . round($diff/ $last * 100) ."%";
			} else {
				$this->annual[$yr]['yoy'] = "";
			}
			$this->annual_summary['sum'] += $data['sum'];
			$this->annual_summary['mean'] += $data['mean'];
			if($data['min'] < $this->annual_summary['min']) {
				$this->annual_summary['min'] = $data['min'];
			}
			if($data['max'] > $this->annual_summary['max']) {
				$this->annual_summary['max'] = $data['max'];
			}
		}
		$this->annual_summary['mean'] =  "";
		$this->annual_summary['median'] = "";
		$this->annual_summary['yoy'] = "";
	}
}
?>
	<h1>About nw3weather</h1>

	<h2>Weather Station</h2>

	<h3>Technical</h3>
	<p>
		I run a Davis Vantage Pro 2 station and use Weather Display software to collect the data in real time from the sensors,
		which is then broadcast to this website. Each sensor polls for data every 3-20s, and is available live on nw3weather with a delay of approx 5s, with
		aggregated data (e.g. daily mean, monthly max, graphs) calculated and cached every 1-5 minutes. At midnight, daily aggregations of the data are saved to the
		permanent historical data-sets using high-tech csv databases.
	</p>
	<p>
		It is, however, important to note that these various updates will not always be carried out due to the large number of
		potential failure nodes in the system - sensors (power, wireless signal), local PC (HDD failure, software crash, internet connection),
		and the web server. I have been lucky not to suffer any serious hardware failures (most failures result in no physical data loss), but undoubtedly the time will come.<br />
		The current uptime status can view on the <a href="wx15.php">system page</a>.
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
		A more in-depth guide to siting weather stations can be found <a href="http://www.weatherstations.co.uk/gooddata.htm" title="Optimal siting of a PWS">here.</a> </p>
	<p> It should also be noted that at no point do I quote figures for the errors associated with my readings, though of course they technically do exist.
		This is partly due to conventions in displaying weather data, but also because it can be very difficult to ascertain such values, especially for urban sites:
		For a given sensor the errors can vary widely from day to day and are sensitive to quite specific factors.
		All I can say is that I have tried to minimise and correct for errors where possible, but some large errors may well exist.
	</p>

	<a name="location"></a>
	<h2>Location</h2>
	<p>
		The nw3weather station is situated on the south-eastern edge of London's Hampstead Heath,
		a 790 acre ancient park just a few miles north of central London.
		Nearby areas include Hampstead village to the west, Highgate to the north, Belsize Park to the south-west, and Kentish Town to the south.</p>
	<div id="map">
		<?php Html::img("/static-images/nw3wxhq.jpg", "Map of nw3 hq within London", 0.5, "Wind Sensors close-up", 784, 816); ?>
	</div>
	<p>The station co-ordinates are approx. <a href="https://www.google.com/maps/@51.556,-0.155,15z" title="Google Maps">51.556, -0.155</a>, which is ~57m (195ft) above mean sea level.</p>

	<h2>Setup</h2>
	The station comprises several sensors:
	<ul>
		<li>A combined thermometer/hygrometer housed in a home-built Stevenson Screen (a radiation shield)</li>
		<li>A combined anemometer &amp; wind vane, located atop a 6m pole attached to the chimney of the house</li>
		<li>A self-tipping rain gauge with a tip resolution of 0.2mm, which is attached to the roof of the Stevenson Screen</li>
		<li>A barometer integrated into the receiving unit (indoors).</li>
	</ul>
	<?php
		Html::img("/photos/wxstn/box.jpg", "Thermo/Hygro and Rain Gauge", 0.2, "Thermo/Hygro in radiation shield, Rain Gauge on top", 430, 338);
		Html::img("/static-images/P1010074.JPG", "Wind Sensors", 0.2, "Anemometer &amp; Wind Vane on top of pole", 394, 338);
		echo '<p>The external sensors are wired to a solar-powered transmission unit (which sits atop the radiation shield box).
			This wirelessly transmits the data from all sensors to the internal console every few seconds.
			Battery-backup and a data-logger ensure continuous operation</p>';
		Html::img("/static-images/PICT2516.JPG", "Thermo/Hygro close-up", 0.2, "Thermo/Hygro close-up inside radiation shield", 420, 315);
		Html::img("/static-images/P1010076.JPG", "Wind Sensors close-up", 0.2, "Wind Sensors close-up", 420, 315);
	?>
	<p><a href="./wx7.php#wx-albums">Weather station albums</a> are also available,
		with more complete and detailed pictures of past and present setups (note that the above pics are of a previous station).</p>
<!--		<a href='/static-images/P1010070.JPG' title='Enlarge'>
			<img src='/static-images/P1010070.JPG' alt='Thermo/Hygro and Rain Gauge' width='430' height='338' title='Thermo/Hygro in radiation shield, Rain Gauge on top'   />
		</a>
	<a href='/static-images/P1010074.JPG' title='Enlarge'><img src='/static-images/P1010074.JPG' alt='Wind Sensors'  width='394' height='338'   title='Anemometer &amp; Wind Vane on top of pole'   />
	</a>
	<p>All the sensors are automatic and send data wirelessly to the receiving unit every 14-60s.</p>
	<img src='/static-images/PICT2516.JPG' alt='Thermo/Hygro clos-up'  width='448' height='336'   title='Thermo/Hygro close-up inside radiation shield'   />
	<a href='/static-images/P1010076.JPG' title='Enlarge'>
		<img src='/static-images/P1010076.JPG' alt='Wind Sensors close-up'  width='448' height='336'   title='Wind Sensors close-up'   />
	</a>-->
	<p>
		Additionally, there are two webcams used to monitor sky and ground conditions, which along with traditional techniques (pending AI assistance) I use
		to collect data for extra weather variables - sun hours, cloud cover, wet hours, snowfall, lying snow, fog, hail, and thunder(storms).
	</p>


	<a name="History"></a>

	<h2>History</h2>

	<p>
		I started collecting data at this site on 28th July 2010.
		Prior to that I had been running the same setup in East Finchley, a town 5 km north of here, since February 2009,
		but I have kept daily temperature records since March 2008 (albeit not integrated into this website).
		Consequently, any records from before July 2010 are for East Finchley, though given the small distance involved, any data comparisons are largely valid.
	</p>
	<p>
		The sky-facing webcam (Logitech C300) was added to the station on 1st August 2010; The ground-facing one on 19th December 2010.
	</p>
	<p> In 2016 the original Oregon Scientific WMR-200 sensors began to fail due to age.
		In Feb '16 the wind sensor failed, followed by the T/H sensor in Sept '16 (replaced a month later),	and the rain sensor in March '17.
		In Sept '17 I therefore <a href="./news.php#post-20170906" title="Full details in blog post">replaced the entire station</a> with a Davis VP2, a better model.
	</p>
	<p>In June 2018 I <a href="./news.php#post-20180621" title="Full details in blog post">installed a new high-resolution network camera</a> to replace the original webcam.</p>

	<h3>Technical history</h3>
	<ul>
		<li type="disc">The rain gauge was modified in March 2009 - the original had a resolution of only 1mm.
			This involved fitting a funnel to the collecting channel so that less rain is needed to cause a 'tip' in the internal bucket
			(each tip of 10ml results in a signal being sent to the receiving unit, which registers the rain).</li>
		<li type="disc"> In July 2009 I built the radiation shield for the temp/hum sensor to improve the accuracy of the readings:
			The walls are double louvred hardwood, the roof is double insulated plywood with a cavity for air flow, and
			the whole thing is painted in white gloss. Maintenance is carried out twice a year, mainly to keep the paint glossy for maximal radiation reflection.</li>
		<li type="disc">The combined wind sensor unit was mounted on a 6.1m aluminium-alloy aerial pole in August 2009.</li>
		<li type="disc">A manual rain gauge <a href="./news.php#post-20120107" title="Details in blog post">was installed</a> on 4th September 2011 - this is for the verification and calibration of the automatic gauge data. </li>
		<li type="disc">The automatic rain gauge was <a href="./news.php#post-20140330" title="Full details in blog post">further modified</a> on 30th March 2014 to a new resolution of 0.18 mm. </li>
		<li type="disc">The base and roof of the radiation shield <a href="./news.php#post-20140330" title="Full details in blog post">were replaced</a> on 30th March 2014 owing to decay of the originals. </li>
		<li type="disc">The entire weather station <a href="./news.php#post-20170906" title="Full details in blog post">was replaced</a> on 5th September 2017 due to failure of the original. </li>
	</ul>


	<h2>Website</h2>

	<p>
		The site was developed in Firefox but other modern browsers have been tested and found to display most things correctly.
		<br /><code>nw3weather.co.uk</code> launched on 10th September 2010, I having acquired the domain name soon after moving here in July.
		The most recent version (v3) launched on 22nd May 2013. <br />
		Within a month or so, I submit manual observations for prior days to the system, as well as fixing glitches and correcting misreads.
		These changes are reflected on the site almost immediately.
	</p>

	<h3>Technical</h3>
	<p>
		The process involved in converting readings from the sensors to the data viewable on this website can be summarised thus: <br />
		<a style="display:block; margin:0.8em" href="/static-images/Sensors_to_WWW.jpg">
			<img src="/static-images/Sensors_to_WWW.jpg" alt="Data flow chart" title="Click for full-size image" width="820" />
		</a>
		<br /> Each sensor wireless sends data to the base station, which transmits it to my home PC (a dedicated low-power machine running Windows 7).
		Weather Display software renders and uploads this data via
		<abbr title="file transfer protocol">FTP</abbr> to a web server -
		which broadcasts the PHP-rendered web pages to the World Wide Web so anyone making a valid HTTP request can view them.
		The web server runs on a Linux VM which I manage, but is provisioned by Digital Ocean.
		All web pages and data procedures are written in PHP (inc. graphs powered by the JPGraph library),
		with some Javascript used to provide a basic level of dynamism.
		Data storage is a combination of <abbr title='comma separated value'>CSV</abbr> (long-term),
		<a href='https://en.wikipedia.org/wiki/Serialisation'>PHP-serialised</a> (short-term), and PHP variable-exported.
	</p>
	<ul>
		<li><a href='/dat2012.csv' title='Daily data from 2012'>Sample csv</a></li>
		<li><a href='/sample.xhtml' title='Source-code for monthly data tables webpage'>Sample script</a></li>
		<li><a href='/TemperatureTags.php?BLRdebugTags' title='Temperature variables'>Sample variable export</a></li>
	</ul>

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
		A 90s throwback. It was simply a webpage pre-generated by Weather Display and hosted on
		a free, ad-ridden web server. I only set it up so I could view my data away from home.
	</p>

	<h3 id="site-traffic">Site Traffic</h3>
	These figures are approximations based on my site logs, and are for interest only. Figures for 2011 are from 01 Sep onwards.


	<?php
$traffic = new Traffic();
$traffic->prepare_annual_data_table();
//var_dump($traffic->annual);
?>
	<table class="table1 aboutTbl">
		<thead>
			<tr class="table-head">
				<td class="td12" style="padding: 0.5em" colspan="7">Nw3weather Annual Traffic</td>
			</tr>
			<tr class="table-top">
				<td>Year</td>
				<td>Total Visits</td>
				<td>growth</td>
				<td>Mean</td>
				<td class="hide-small">Median</td>
				<td>Max</td>
				<td class="hide-small">Min</td>
			</tr>
		</thead>
		<tfoot> <!-- Yes, it is meant to go here! -->
			<tr>
				<td>Total</td>
				<td><?php echo number_format($traffic->annual_summary['sum']) ?></td>
				<td><?php echo $traffic->annual_summary['yoy'] ?></td>
				<td><?php echo $traffic->annual_summary['mean'] ?></td>
				<td class="hide-small"><?php echo $traffic->annual_summary['median'] ?></td>
				<td><?php echo $traffic->annual_summary['max'] ?></td>
				<td class="hide-small"><?php echo $traffic->annual_summary['min'] ?></td>
			</tr>
		</tfoot>
		<tbody>
			<?php foreach ($traffic->annual as $year => $data): ?>
			<tr>
				<td><?php echo $year ?></td>
				<td><?php echo number_format($data['sum']); if($year == Date::$dyear) { echo "+ (to ${data['TO']})"; } ?></td>
				<td><?php echo $data['yoy'] ?></td>
				<td><?php echo $data['mean'] ?></td>
				<td class="hide-small"><?php echo $data['median'] ?></td>
				<td><?php echo $data['max'] ?> (<?php echo $data['max_date'] ?>)</td>
				<td class="hide-small"><?php echo $data['min'] ?> (<?php echo $data['min_date'] ?>)</td>
			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	<p>Additionally, it is found that, as expected, traffic is higher on days of precipitation - particularly snowfall, which can drive traffic up by more than 500% -
		record site traffic for a long time was over 1500 visits on <a href='/wxhistday.php?year=2013&amp;month=1&amp;day=18' title='daily weather breakdown'>18th Jan 2013</a>.
		This was only surpassed in 2019 with  <a href='/wxhistday.php?year=2019&amp;month=7&amp;day=25' title='weather for 25 jul 2019'>a day of record-breaking heat</a> bringing 1661 visits,
		then again in 2020 with a record-breaking 3-day rain storm in early Oct.
	</p>

	<?php Html::img("/sessions.jpg", "nw3weather site traffic - sessions", 0.2, "nw3weather daily site visits, 2012-2023", 851, 517); ?>

	<h2>Acknowledgements</h2>
	<p>
		Thanks go to TNET weather and Wildwood Weather for script ideas which helped in the development of the historical data tables.<br />
		Thanks also to carterlake for CSS and HTML templates that I used in nw3weather's early years (pre-2013).<br />
		Special thanks to my weather assistant Juliet, and the various other family members who have helped keep the station running over the years<br />
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
		done by national meteorological offices requires the amassing of a huge amount of data from observation sites on land (like this one),
		on the sea, in the air and even from satellites in space. The data is then fed through supercomputers running numerical models
		that aim to simulate extremely complex atmospheric processes.
		The model outputs then need processing and interpreting before a forecast is produced.
		The whole operation costs many millions of pounds to set up and run. Barometric prediction, on the other hand, is very cheap but has
		severe limitations: it cannot offer regional guidance, it is only relevant for the next ~24-48 hours, and it breaks down
		completely in more complex atmospheric set-ups.
	</p>

	<a name='data'></a>
	<h3>Data Quality, Differences, and Requests</h3>
	<p>
		As discussed, I take steps to ensure data quality, but this is not guaranteed and I accept that my data probably does not hold up
		to international standards for an urban site. However, I am active in comparing my data to, and ensuring consistency with,
		data produced by London's "official" stations, these being at all airports near-ish nw3 (Northolt, Heathrow, City),
		as well as St James's Park, and one at Whitestone Pond in Hampstead.
		A <a href='news.php#rainfall'>detailed discussion</a> on my blog exists about rainfall differences, and similar (unpublished) analysis has been done for Temperature.
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
		I am happy to share my raw data files with anyone who <a href='contact.php'>requests</a> them from me.
		I can provide most data from Jan 2009, and temperature data from March 2008, at minutely or greater intervals, in CSV format.
		Additionally I am happy to answer questions and provide analysis of this data to individuals or groups with non-commercial interests.
		However, due to limited resources, I cannot provide extensive or repeated analysis; for this you should seek professional guidance (from the Met Office, probably).
	</p>

	<h3>Rain Gauge - Trace Rain, and Snowfall</h3>
	<p>
		The rain gauge works by water falling through a meshed funnel and tipping an internal bucket.
		The mesh serves to filter out grit and leaves, but also means snow gets trapped until it melts or is blown away by the wind.
		The equipment is sensitive to approx. 0.2mm of rain - so an hour's worth of light drizzle or a brief shower may not register,
		though I will manually record the day as having had a trace of rainfall (seen as 0.1mm).
	</p>
	<img src="/photos/wxstn/gauge_decon.jpg" title="Deconstructed view of rain gauge - funnel and internals atop the station roof" />
	<img src="/photos/wxstn/gauge_internals.jpg" title="Internal view of the gauge, showing tipping bucket" />
	<p>
		Consequently, I measure snowfall by a combination of snow depth (1cm of snow approx. corresponds to 1mm of liquid precipitation)
		and high-resolution radar which can pick up all types of precipitation. To my knowledge, this is also how the Met Office do it.
		In any case, once I've gathered this information after a snowfall, I manually enter the approximate rain-equivalent figure into my records,
		so it does show up in the daily and monthly totals, but not immediately (typically by 9am the following day).
	</p>


	<h2>About me</h2>

	<p>
		I am a weather fanatic with a particular interest in data analysis,
		and have been since around 2007 when I first started to collect data (in Excel) from a very basic weather station I had.
		I enjoy extremes of weather, whether that be cold, heat, rain, dry, sun, cloud, snow, storms, hail, heavy showers, sleet, fog...
	</p>
	<p>
		I studied Physics and Computer Science, both of which I was largely motivated to study by my love of the weather, and went on
		to work as a software engineer.
		In 2015 I left London and <a href="/news.php#post-20151107">moved to New York</a>. I currently live and work in the San Francisco Bay Area, California.
		The weather station back home in London is lovingly cared for by my mother and other relatives, and me when I come back every year or so (pandemics-permitting).
	</p>
	<p>
		For more insight into the world of a weather hobbyist,
		<a href="news.php#post-20141116">take a look at the article on me in the local paper</a>. <br />
		I was featured <a href="https://www.hamhigh.co.uk/news/environment/hampstead-weather-station-owner-ben-lee-rodgers-1-6192043">
			in another article</a>, about the July 2019 heatwave, five years later.
			And <a href="https://www.camdennewjournal.co.uk/article/you-can-see-it-from-the-heath-enthusiasts-own-weather-station-charts-record-temperatures"> again</a> for the record-breaking July 2022 heatwave.

	</p>


<?php Page::End(); ?>
