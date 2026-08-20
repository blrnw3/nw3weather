<?php
require 'Page.php';
require 'ViewDetailedData.php';
Page::init([
	"fileNum" => 14,
	"title" => "Temperature Detail",
	"description" => 'Detailed latest temperature data/information and records from NW3 weather station.'
]);
Page::Start();
?>

<h1>Detailed Temperature Data</h1>

<?php
$mainTables = new ViewDetailedData("temp");

// Air-frost days: overnight min < 0 °C. Use current-year daily data only —
// avoid a full DataSummarizer rebuild just for these two counts.
$nmMonth = Data::getMonthlyData('nightmin', Date::$dyear, Date::$dmonth);
$monthFrosts = Util::cond_count(is_array($nmMonth) ? $nmMonth : [], false, 0);
$nmYear = Data::getYearlyData('nightmin', Date::$dyear);
$yearFrosts = Util::cond_count(is_array($nmYear) ? Data::MDtoZ($nmYear) : [], false, 0);

$tchangehour = Wx::conv(Live::$HR24['changeHr']['temp'], Wx::AbsTemp, 1, 1);
$measures = ['Temperature', 'Temperature Trend / hr', 'Feels-like', 'Night Minimum (21-09)', 'Day Maximum (09-21)', '24hr Average',
	'Daily Air Frost Hours', 'Monthly Air Frosts', 'Annual Air Frosts'];
$values = [Live::$temp, $tchangehour, Live::$feel, Live::$NOW['min']['night'], Live::$NOW['max']['day'], Live::$HR24['mean']['temp'],
	Live::$NOW['misc']['frosthrs'], $monthFrosts, $yearFrosts];
$conv = [Wx::Temperature, Wx::None, Wx::Temperature, Wx::Temperature, Wx::Temperature, Wx::Temperature,
	Wx::Hours, Wx::Days, Wx::Days];

$mainTables->currentLatest($measures, $values, $conv);

$measures2 = array('Lowest Min', 'Highest Max', 'Highest Min', 'Lowest Max', 'Coldest Day', 'Warmest Day', 'Averages', 'Mean', 'Mean Minimum', 'Mean Maximum');
$mainTables->avgsExtrmsRecs($measures2);
$mainTables->pastYearAvgsExtrms($measures2);
$mainTables->rankTables();
?>

<p class="report-var-about">Air-temperature extremes for each calendar day run midnight to midnight (local time), when daily extremes are reset. Night minimum (21–09) and day maximum (09–21) use those fixed windows instead.</p>
<h2>Notes</h2>
<ul>
	<li>Station temperature records began on 1st Jan 2009. A long-term series from nearby sites is available from 1881 (use Start year above).</li>
	<li>Figures in brackets refer to departure from
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	</li>
	<li>An air frost requires the <b>overnight</b> (21-09) temperature to fall <i>below</i> freezing</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset.</li>
</ul>

<b>Definition of 'Feels-like' temperature:</b> This displays either the Wind Chill index or the
'Humidex', depending on the temperature and humidity.
These indices are attempts to depict what the air actually feels like on a human's skin -
 either from the warming effect of high humidity, or the cooling effect of the wind.
 However, they have little physical meaning and their valid use is debatable, so are provided for interest only.

<?php Page::End(); ?>
