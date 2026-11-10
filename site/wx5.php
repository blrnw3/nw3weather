<?php

require("Page.php");
require_once __DIR__ . '/Forecast.php';
Page::init([
	"fileNum" => 5,
	"title" => "Forecast | Latest Maps",
	"description" => 'London forecast discussion, Hampstead meteogram and latest UK weather maps from NW3 weather.'
]);
Page::Start();

require Site::$rareTags;
?>
<h1>Latest Forecasts and Weather Maps</h1>

<p><b>NB:</b> NW3 is an observation site, so detailed forecasts are gratefully pulled from external providers.
	High-quality forecasting relies on running sophisticated computer models on large supercomputers using data from a wide array of sources, including land-based observation sites like this one.
	A fuller discussion is on the <a href="wx8.php#Forecasting" title="Found on the about page">about page</a>.
</p>

<?php Forecast::renderDiscussion(); ?>

<?php Forecast::renderFavouriteLinks(); ?>

<div id="widgets" class="fc-widgets">
	<div class="fc-meteogram-wrap">
		<h2>Hampstead 10-day meteogram</h2>
		<p class="hm-break-note">Temperature &amp; dew point, precipitation amount &amp; chance, cloud cover and wind. Data: Open-Meteo (falls back to Yr.no if needed).</p>
		<?php Charts::meteogram(null, ['height' => 620]); ?>
	</div>

	<div>
		<h2>Forecast for NW3</h2>
		<?php Forecast::renderDailyTable(); ?>
		<?php Forecast::renderPeriodTable(8); ?>
	</div>

	<?php Forecast::renderPressureChart(); ?>
</div>

<?php
// Windy's own windy_map_async.js loader reads manifest.json with a cross-origin fetch
// that their server answers without a CORS header, so it dies before injecting anything.
// cron_main.php resolves the hashed bundle for us; fall back to their loader when the
// cache is missing or stale so the widget self-heals if they fix the header.
$windyFile = V5_CACHE_ROOT . 'windy_widget.txt';
$windyBundle = file_exists($windyFile) ? trim(file_get_contents($windyFile)) : '';
if (!preg_match('/^windy_map\.[a-z0-9]+\.js$/i', $windyBundle)) { $windyBundle = ''; }
$windySrc = ($windyBundle !== '')
	? 'https://windy.app/widget3/' . $windyBundle
	: 'https://windy.app/widget3/windy_map_async.js?v415';
?>
<div>
	<h2>Area Weather Map</h2>
	<div id="wx-map"
		data-windywidget="map"
		data-thememode="white"
		data-spotid="5980477"
		data-appid="widgets_2467d6af6b">
	</div>
	<script async="true" data-cfasync="false" type="text/javascript" src="<?php echo htmlspecialchars($windySrc); ?>"></script>
</div>

<?php Page::End(); ?>
