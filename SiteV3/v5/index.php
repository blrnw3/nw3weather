<?php
require 'Page.php';
require 'live-body.php';
Page::init([
	"fileNum" => 1,
	"title" => "Live and historical weather from Hampstead, London",
	"description" => 'Live weather data from a personal automatic weather station located in Hampstead, North London.'
]);
Page::Start();

// Live console/forecast text (written by cron)
include Site::$rareTags;

$HR24 = Live::$HR24;
$rnrt = nw3_live_get($HR24, 'misc', 'rnrate');
$nw3Raining = ((nw3_live_get($HR24, 'trendRn', 0) - nw3_live_get($HR24, 'trendRn', 1)) > 0);
$METAR = file_exists(ROOT . 'METAR.txt') ? file_get_contents(ROOT . 'METAR.txt') : '';

// ----- Weather report (current conditions decode) -----
$metarRaining = $foggy = $snowing = false;
$weather = 'Dry';
if ($nw3Raining) {
	$isShower = (nw3_live_get($HR24, 'trend', 0) !== null
		&& ($HR24['trend'][0]['temp'] - $HR24['trend'][30]['temp'] <= -0.3
			|| $HR24['trend'][0]['hum'] - $HR24['trend'][30]['hum'] >= 5));
	$rnType = $isShower ? 'Shower' : 'Rain';
	$intensities = array('', 'Slight', 'Light', 'Moderate', 'Heavy', 'Very Heavy', 'Torrential');
	$intensityThresholds = array(0.1, 0.5, 2, 8, 35, 60, 500);
	$intensity = '';
	for ($i = 0; $i < count($intensityThresholds); $i++) {
		if ($rnrt < $intensityThresholds[$i]) {
			$intensity = $intensities[$i];
			break;
		}
	}
	$lastrnThresh = $isShower ? 12 : 20;
	$rn_ago = (time() - strtotime(nw3_live_get($HR24, 'misc', 'prevRn')));
	if ($rn_ago > ($lastrnThresh * 60)) {
		$intensity = 'Recent';
	}
	$weather = trim($intensity . ' ' . $rnType);
} else {
	$metarRaining = Util::strContains($METAR, array('RA', 'DZ'));
	$foggy = Util::strContains($METAR, array('FG', 'BR'));
	$snowing = Util::strContains($METAR, array('SN', 'SG'));
	$showery = Util::strContains($METAR, 'SH');
	$stormy = Util::strContains($METAR, 'TS');

	$METARactives = array($snowing, $metarRaining, $foggy, $stormy);
	$METARdescrips = array('Snow', 'Rain', 'Mist/Fog', 'Thunderstorm');
	foreach ($METARactives as $i => $wxMetar) {
		if ($wxMetar) {
			$weather = $METARdescrips[$i];
			if ($showery) {
				$weather .= ' Showers';
			}
			$weather .= ' Nearby';
			break;
		}
	}
}

$cloud = ($nw3Raining || $metarRaining || $foggy || $snowing) ? 'Cloudy' : 'Clear';
$METARcloudTypes = array('OVC', 'BKN', 'SCT', 'FEW', 'NSC');
$METARcloudDescrips = array('Overcast', 'Mostly cloudy', 'Partly cloudy', 'Mostly clear', 'Cloudy');
foreach ($METARcloudTypes as $i => $cloudSrch) {
	if (Util::strContains($METAR, $cloudSrch)) {
		$cloud = $METARcloudDescrips[$i];
		break;
	}
}

// ----- 2-day forecast (Open-Meteo, refreshed by cron into forecast_v5.json) -----
$forecast = array();
$fcFile = ROOT . 'forecast_v5.json';
if (file_exists($fcFile)) {
	$fcData = json_decode(file_get_contents($fcFile), true);
	if (isset($fcData['days'])) {
		$forecast = $fcData['days'];
	}
}
?>

<div class="home-head">
	<h1>Hampstead NW3, London - Current Weather</h1>
	<div class="home-summary">
		<div class="home-report">
			<b><span style="color:#610B0B">Right now:</span></b>
			<b><?php echo $weather; ?></b>, <?php echo HTML::acronym("Raw METAR: " . $METAR, $cloud); ?>.
			<div class="live-status">
				<span class="live-dot" id="live-dot" aria-hidden="true"></span>
				<span class="live-meta">Updated <b id="live-time"><?php echo date('H:i:s', Live::$unix); ?></b> &middot; <span id="live-age">just now</span></span>
			</div>
		</div>
	</div>
</div>

<div class="wx-cards">
	<div id="live-wx-body" class="wx-live-cards">
		<?php nw3_render_cards(); ?>
	</div>
	<div class="wx-card wx-card-sun">
		<div class="wx-card-head"><img class="wx-card-icon" src="<?php echo Site::IMG_ROOT; ?>clear.png" alt="" width="36" height="36" /><a class="hidden-link" href="wx6.php" title="Sun, moon and astronomy detail">Sun &amp; Moon</a></div>
		<div class="wx-card-rows">
			<div><span class="k">Sunrise</span><span class="v"><b><?php echo Date::$sunrise; ?></b></span></div>
			<div><span class="k">Sunset</span><span class="v"><b><?php echo Date::$sunset; ?></b></span></div>
			<div><span class="k">Daylight</span><span class="v"><?php echo isset($hoursofpossibledaylight) ? $hoursofpossibledaylight : '-'; ?></span></div>
			<div><span class="k">Moonrise</span><span class="v"><?php echo isset($moonrise) ? $moonrise : '-'; ?></span></div>
			<div><span class="k">Moonset</span><span class="v"><?php echo isset($moonset) ? $moonset : '-'; ?></span></div>
			<div><span class="k">Illumination</span><span class="v"><?php echo isset($moonphase) ? $moonphase : '-'; ?></span></div>
		</div>
	</div>
</div>
<noscript><p><b>Note:</b> Javascript must be enabled for live updates to function</p></noscript>

<a class="home-cam-link" href="wx2.php" title="Full webcam image and timelapses"><img id="cam" name="refresh-home" src="/skycam_small.jpg" title="Click to enlarge" alt="Web cam" width="864" height="576" /></a>

<script type="text/javascript">
	//<![CDATA[
	function refreshLiveBody() {
		if (!document.hidden && window.fetch) {
			fetch('/v5/ajaxwxbody.php', { cache: 'no-store' })
				.then(function (r) { return r.text(); })
				.then(function (html) { document.getElementById('live-wx-body').innerHTML = html; })
				.catch(function () {});
		}
		setTimeout(refreshLiveBody, 20000);
	}
	setTimeout(refreshLiveBody, 20000);

	// Live-data freshness: count up the age of the latest reading and flag if stale.
	(function () {
		var dot = document.getElementById('live-dot');
		var ageEl = document.getElementById('live-age');
		var timeEl = document.getElementById('live-time');
		if (!dot || !ageEl) { return; }

		var tzFmt = null;
		try {
			tzFmt = new Intl.DateTimeFormat('en-GB', {
				hour: '2-digit', minute: '2-digit', second: '2-digit',
				hour12: false, timeZone: 'Europe/London'
			});
		} catch (e) {}

		var wdTime = 0, baseServer = 0, t0 = Date.now();

		function sync() {
			var w = document.getElementById('WDtime');
			var s = document.getElementById('Servertime');
			if (!w || !s) { return; }
			var wd = parseInt(w.value, 10);
			var sv = parseInt(s.value, 10);
			if (isNaN(wd) || isNaN(sv)) { return; }
			if (wd !== wdTime || sv > baseServer) {
				wdTime = wd;
				baseServer = sv;
				t0 = Date.now();
				if (timeEl && tzFmt) { timeEl.textContent = tzFmt.format(new Date(wd * 1000)); }
			}
		}

		function fmtAge(sec) {
			sec = Math.max(0, Math.round(sec));
			if (sec < 60) { return sec + 's ago'; }
			var m = Math.floor(sec / 60), s = sec % 60;
			if (m < 60) { return m + 'm ' + (s < 10 ? '0' : '') + s + 's ago'; }
			var h = Math.floor(m / 60); m = m % 60;
			return h + 'h ' + (m < 10 ? '0' : '') + m + 'm ago';
		}

		function tick() {
			sync();
			if (!wdTime) { return; }
			var age = baseServer + (Date.now() - t0) / 1000 - wdTime;
			ageEl.textContent = fmtAge(age);
			var ok = age < 180;
			dot.className = 'live-dot ' + (ok ? 'live-dot-ok' : 'live-dot-stale');
			dot.title = 'Live data ' + (ok ? 'is current' : 'may be stale') + ' \u2013 ' + fmtAge(age);
		}

		tick();
		setInterval(tick, 1000);
	})();
	//]]>
</script>

<h2>Recent Trends</h2>
<div class="home-graphs">
	<img id="graph1" src="/graphdayA.php?type1=temp&amp;type2=rain&amp;ts=12&amp;x=400&amp;y=160&amp;nofooter&amp;currid=<?php echo time(); ?>" alt="Last 12-hours temperature and rain" width="400" height="160" />
	<img id="graph2" src="/graphdayA.php?type1=hum&amp;type2=dew&amp;ts=12&amp;x=400&amp;y=160&amp;currid=<?php echo time(); ?>" alt="Last 12-hours humidity and dew point" width="400" height="160" />
	<img id="graph3" src="/graphdayA.php?type1=baro&amp;ts=12&amp;x=400&amp;y=160&amp;currid=<?php echo time(); ?>" alt="Last 12-hours pressure" width="400" height="160" />
	<img id="graph4" src="/graphdayA.php?type1=wind&amp;type2=wdir&amp;ts=12&amp;x=400&amp;y=160&amp;currid=<?php echo time(); ?>" alt="Last 12-hours wind" width="400" height="160" />
</div>
<p><a href="/charts.php" title="All NW3 weather charts">See all charts</a></p>

<?php if ($forecast): ?>
<h2>Forecast</h2>
<div class="home-forecast">
	<?php foreach ($forecast as $day): ?>
	<div class="fcast-day">
		<img src="<?php echo Site::IMG_ROOT . $day['icon']; ?>_lg.png" width="60" alt="<?php echo htmlspecialchars($day['desc']); ?>" title="<?php echo htmlspecialchars($day['desc']); ?>" />
		<div class="fcast-label"><?php echo htmlspecialchars($day['label']); ?></div>
		<div class="fcast-temps"><b><?php echo Wx::conv($day['tmax'], Wx::Temperature, true, false, -1); ?></b> / <?php echo Wx::conv($day['tmin'], Wx::Temperature, true, false, -1); ?></div>
		<?php if (isset($day['pop']) && $day['pop'] !== null && $day['pop'] !== ''): ?>
		<div class="fcast-pop"><?php echo intval($day['pop']); ?>% rain</div>
		<?php endif; ?>
	</div>
	<?php endforeach; ?>
	<div class="fcast-more"><a href="wx5.php" title="5-Day Local Forecast and Maps">Full forecast &amp; maps &rarr;</a></div>
</div>
<?php endif; ?>

<div class="home-links">
	<b>Dive deeper:</b>
	<a href="wx4.php">Records</a>
	<a href="/wxdataday.php">Daily data</a>
	<a href="/RankDay.php">Rankings</a>
	<a href="/charts.php">Charts</a>
	<a href="/wxaverages.php">Climate averages</a>
	<a href="wx7.php">Photos</a>
	<a href="wx8.php">About the station</a>
</div>

<div class="home-about">
	<p><b>NW3 Weather</b> is a meteorological observation site located near Hampstead, in North London, UK.
	The site was established with an <a href="wx8.php" title="Detailed station and website information">automatic personal weather station</a>
	in July 2010 and runs continuously, with updates at least every 60s.</p>
</div>

<?php $pond = nw3_live_get($HR24, 'misc', 'pondTemp'); ?>
<?php if ($pond !== null): ?>
<p>Latest Hampstead Heath pond temperature: <b><?php echo Wx::conv($pond, Wx::Temperature); ?></b>
	&nbsp; <a href="/wxdataday.php?vartype=pond">see temperature history</a></p>
<?php endif; ?>

<h1>Latest Monthly Weather Report</h1>
<?php
$repStamp = Date::mkdate(Date::$dmonth - 1, 1);
displayMonthlyReport(date('n', $repStamp), date('Y', $repStamp));
?>

<?php
$nowStamp = Date::mkdate(Date::$dmonth, Date::$dday, Date::$dyear);
$runYears = Date::$dyear - 2009;
if ($nowStamp < Date::mkdate(2, 1, Date::$dyear)) { $runYears--; }
$runDays = intval(($nowStamp - Date::mkdate(2, 1, 2009 + $runYears)) / (24 * 3600));
$runStr = $runYears . ' year' . ($runYears === 1 ? '' : 's')
	. ' and ' . $runDays . ' day' . ($runDays === 1 ? '' : 's');
?>
<p style="margin-top: 2em;">This weather station has been recording data for
<b><?php echo $runStr; ?></b> (Since 1st Feb 2009)
</p>

<?php
Page::End();

function displayMonthlyReport($mon, $yr) {
	$repFile = ROOT . $yr . "/report$mon.php";
	if (!file_exists($repFile)) {
		$repStamp = Date::mkdate($mon - 1, 1, $yr);
		$repFile = ROOT . date('Y', $repStamp) . "/report" . date('n', $repStamp) . ".php";
	}
	if (!file_exists($repFile)) {
		echo '<p>Report not available.</p>';
		return null;
	}
	include $repFile;
	if (!isset($export)) {
		echo '<p>Report not available.</p>';
		return null;
	}

	$repMonth = $export['date'][0];
	$repYear = $export['date'][1];

	$tempComparator = $export['temp'][0];
	$tempAv = Wx::conv($export['temp'][1], Wx::Temperature);
	$tempAnom = Wx::conv($export['temp'][2], Wx::AbsTemp, true, true);
	$tempLo = Wx::conv($export['temp'][3], Wx::Temperature);
	$tempHi = Wx::conv($export['temp'][4], Wx::Temperature);

	$rainComparator = $export['rain'][0];
	$rainAv = Wx::conv($export['rain'][1], Wx::Rain);
	$rainAnom = $export['rain'][2];
	$rainCnt = $export['rain'][3];
	$rainHi = Wx::conv($export['rain'][4], Wx::Rain);
	$rainYr = Wx::conv($export['rain'][5], Wx::Rain);
	$rainYrAnom = $export['rain'][6];
	$rainYrCnt = $export['rain'][7];

	$sunComparator = $export['sun'][0];
	$sunAv = $export['sun'][1];
	$sunAnom = $export['sun'][2];
	$sunMax = $export['sun'][3];
	$sunCnt = $export['sun'][4];
	$sunHi = $export['sun'][5];

	$notWintry = ($export['winter'][0] == 0 && $export['winter'][1] == 0);
	$fallSnow = $export['winter'][2];
	$fallSnowAnom = $export['winter'][3];
	$fallSnowAnom2 = $export['winter'][4];
	$AFsFull = $export['winter'][5];
	$AFavr = $export['winter'][6];
	$lySnow = $export['winter'][7];
	$LSavr = $export['winter'][8];
	$maxDepth = $export['winter'][9];

	$hail = $export['other'][0];
	$thunder = $export['other'][1];
	$fog = $export['other'][2];
	$bigRnsFull = $export['other'][3];
	$mm10 = Wx::conv($export['other'][4], Wx::Rain, true, false, -1);
	$bigGusts = $export['other'][5];
	$mph30 = Wx::conv($export['other'][6], Wx::Wind, true, false, -1);

	echo "<h2>" . date('F Y', Date::mkdate($repMonth, 1, $repYear)) . "</h2>
		<dl>
		<dt class='temp'>Temperature</dt>
		<dd>Overall, the month was $tempComparator average, with a mean of <b>$tempAv</b> ($tempAnom from the <abbr title='Long-term average'>LTA</abbr>).
			<br />The absolute low was <b>$tempLo</b>, and the highest <b>$tempHi</b>.</dd>
		<dt class='rain'>Rainfall</dt>
		<dd>Came in $rainComparator the long-term average, at <b>$rainAv</b> ($rainAnom%) across <b>$rainCnt</b> days of <abbr title='&ge;0.2mm'>recordable rain</abbr>.
			The most rainfall recorded in a single day (starting at midnight) was <b>$rainHi</b>.
			The cumulative annual total for $repYear now stands at <b>$rainYr</b> ($rainYrAnom%) from <b>$rainYrCnt</b> rain days.</dd>
		<dt class='sun'>Sunshine</dt>
		<dd>A $sunComparator month, with <b>$sunAv hrs</b> ($sunAnom%) from a possible $sunMax.<br />
			<b>$sunCnt</b> days had more than a minute of sunshine, the maximum being <b>$sunHi hrs</b>.</dd>
		<dt class='snow'>Winter Events</dt>
		<dd>";
	echo $notWintry ?
		"No snow or frost observed." :
		"There $fallSnow of falling snow or sleet ($fallSnowAnom $fallSnowAnom2 the <abbr title='Long-term average'>LTA</abbr>),
			and $AFsFull ($AFavr). <br />
		$lySnow of lying snow at 09z were observed ($LSavr), with a max depth of <b>$maxDepth cm</b>.";
	echo "</dd>
		<dt>Other Events</dt>
		<dd>There $hail of hail, <b>$thunder</b> of thunder, <b>$fog</b> with fog at 09z.
			$bigRnsFull had &gt;$mm10 of rain, and <b>$bigGusts</b> with gusts &gt;$mph30.</dd>
		</dl>
		<p>All long-term <a href='/wxaverages.php' title='Long-term NW3 climate averages'>climate averages</a>
		are with respect to the period 1971-2000. &nbsp;
		<a href='/wxhistmonth.php'>View full report</a></p>";
}
?>
