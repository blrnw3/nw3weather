<?php
require 'Page.php';
require 'ViewDetailedData.php';
Page::init([
	"fileNum" => 13,
	"title" => "Wind Detail",
	"description" => 'Detailed latest wind speed and direction data and records from NW3 weather station.'
]);
Page::Start();
?>

<h1>Detailed Wind Data</h1>

<?php
$mainTables = new ViewDetailedData("wind");

$measures = ['Gust', 'Max Gust Last Hour', 'Speed', '10-min Speed', '24hr Mean Speed', 'Beaufort',
	'Direction', '24hr Mean Direction'];
$values = [Live::$gust, Live::$maxgsthr, Live::$wind, Live::$w10m, Live::$NOW['mean']['wind'], Wx::bft(Live::$wind),
	Live::$wdir, Live::$NOW['mean']['wdir']];
$conv = [Wx::Wind, Wx::Wind, Wx::Wind, Wx::Wind, Wx::Wind, Wx::None,
	Wx::Direction, Wx::Direction];

$mainTables->currentLatest($measures, $values, $conv);

$measures2 = array('Lowest Daily Gust', 'Highest Daily Speed', 'Highest Daily Gust', 'Lowest Daily Speed',
	'Calmest Day', 'Windiest Day', 'Averages', 'Mean Speed', 'Mean Daily Gust', 'Mean Daily Speed');
$mainTables->avgsExtrmsRecs($measures2);
$mainTables->pastYearAvgsExtrms($measures2);
$mainTables->rankTables();
?>

<hr />
<h2>Wind roses</h2>
<div class="detail-grid">
	<div><?php Charts::rose(['en' => 'month']); ?></div>
	<div><?php Charts::rose(['en' => 'year', 'st' => Date::$dyear . '0101']); ?></div>
</div>
<p><a href="/windrose_viewer.php">See wind roses for all months, days and years</a></p>

<h3>Latest wind charts</h3>
<?php
Charts::intradayPanel(['num' => 1], ['wind' => 'Speed &amp; gust', 'wdir' => 'Direction'], ['height' => 400]);
Charts::daily(['type' => 'wmean', 'mode' => 'daily', 'year' => Date::$yr_yest, 'month' => 0, 'lta' => 1], ['height' => 440]);
?>

<h3>All-time wind rose for NW3</h3>
<?php Charts::rose(['en' => 'now', 'st' => '20090101'], ['height' => 640]); ?>

<h2>Notes</h2>
<ul>
	<li>Valid wind records began in August 2009</li>
	<li>Figures in brackets refer to departure from
		<a href="/wxaverages.php" title="Long-term NW3 climate averages">average conditions</a></li>
	<li><b>"Speed"</b> is the windspeed sampled over a one minute period;
		<b>"Gust"</b> is the windspeed sampled over a 14 second period.</li>
</ul>

<?php Page::End(); ?>
