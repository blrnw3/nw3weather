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

$tmin = new DataSummarizer("tmin");
$monthFrosts = Util::cond_count($tmin->currentMonth, false, 0);
$yearFrosts = Util::cond_count($tmin->currentYear, false, 0);

$tchangehour = Wx::conv(Live::$HR24['changeHr']['temp'], Wx::AbsTemp, 1, 1);
$measures = ['Temperature', 'Temperature Trend / hr', 'Feels-like', 'Night Minimum (21-09)', 'Day Maximum (09-21)', '24hr Average',
	'Daily Air Frost Hours', 'Monthly Air Frosts', 'Annual Air Frosts'];
$values = [Live::$temp, $tchangehour, Live::$feel, Live::$NOW['min']['night'], Live::$NOW['max']['day'], Live::$NOW['mean']['temp'],
	Live::$NOW['misc']['frosthrs'], $monthFrosts, $yearFrosts];
$conv = [Wx::Temperature, Wx::None, Wx::Temperature, Wx::Temperature, Wx::Temperature, Wx::Temperature,
	Wx::Hours, Wx::Days, Wx::Days];

$mainTables->currentLatest($measures, $values, $conv);

$measures2 = array('Lowest Min', 'Highest Max', 'Highest Min', 'Lowest Max', 'Coldest Day', 'Warmest Day', 'Averages', 'Mean', 'Mean Minimum', 'Mean Maximum');
$mainTables->avgsExtrmsRecs($measures2);
$mainTables->pastYearAvgsExtrms($measures2);
$mainTables->rankTables();
?>

<h2>Notes</h2>
<ul>
	<li>Temperature records began on 1st Jan 2009</li>
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
