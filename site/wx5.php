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
		<?php
		$mg = Forecast::meteogramSeries();
		$mgNote = ($mg['source'] === 'yr')
			? 'Temperature &amp; dew point, precipitation amount &amp; chance, cloud cover and wind. Data: Yr.no / MET Norway; precip chance and gusts: Open-Meteo.'
			: 'Temperature &amp; dew point, precipitation amount &amp; chance, cloud cover and wind. Data: Open-Meteo.';
		?>
		<p class="hm-break-note"><?php echo $mgNote; ?></p>
		<div class="fc-mg-scroll">
			<?php Charts::meteogram($mg, ['height' => 860]); ?>
		</div>
	</div>

	<div>
		<h2>Forecast for NW3</h2>
		<?php Forecast::renderPeriodTable(); ?>
	</div>

	<?php Forecast::renderPressureChart(); ?>
</div>

<?php Page::End(); ?>
