<?php
require("Page.php");
Page::init(array(
	"fileNum" => 20,
	"isSubFile" => true,
	"title" => "LTA temperature detail",
	"description" => "Long-term climate/weather average/mean temperatures day-by-day for London, NW3"
));
Page::Start();

$unitT = (Page::$units === UNIT_US) ? 'F' : 'C';
$ltaVars = array('tmin', 'tmax', 'tmean', 'trange', 'maxsun');
$ltaTypes = array(12, 14, 16, 10, 18);
$ltaUnits = array(Wx::Temperature, Wx::Temperature, Wx::Temperature, Wx::AbsTemp, Wx::Hours);
?>

<h1>Daily long-term temperature averages / <?php echo $unitT; ?> &nbsp; (and Max Sun Hrs)</h1>

<p>These figures are based on the monthly values, but the detail of the intra-month progression is derived from analysis of
<acronym title="Central England Temperature: The CET series is the longest continuous temperature record in the world">CET</acronym>
figures from the last hundred years. The data has been deliberately smoothed to provide a more realistic basis for anomalies.<br />
The raw CET data can be viewed here: <a href="/CETanalysis.xls" title="Excel 2003 spreadsheet with raw CET data from 1900-2010">CETanalysis</a> (.xls, 2.70 MB).
</p>

<p class="clim365-graphs" style="margin: 10px 0px;">
<a id="graph"></a>
<?php
Charts::daily(array('mode' => 'climate', 'types' => 'tmin,tmax', 'type' => 'tmin'), array('height' => 320));
Charts::daily(array('mode' => 'climate', 'types' => 'tmean,trange', 'type' => 'tmean'), array('height' => 320));
Charts::daily(array('mode' => 'climate', 'types' => 'maxsun', 'type' => 'maxsun'), array('height' => 300));
?>
</p>

<div class="table-scroll">
<table class="table1" width="500" cellpadding="1" cellspacing="0">

<tr class="table-top">
<td rowspan="2" colspan="2" width="15%" class="td4">Date</td>
<td colspan="4" width="72%" class="td14C">Temperature / &deg;<?php echo $unitT; ?></td>
<td rowspan="2" width="13%" class="td18C">Max <br />Sun Hrs</td>
</tr>
<tr class="table-top">
<td class="td12C" width="18%">Min</td>
<td class="td14C" width="18%">Max</td>
<td class="td16C" width="18%">Mean</td>
<td class="td10C" width="18%">Range</td>
</tr>
<?php
for ($m = 0; $m < 12; $m++) {
	$daysInMonth = Date::get_days_in_month($m + 1);
	echo '<tr><td rowspan="' . $daysInMonth . '" class="td4"><b>' . Date::$months[$m] . '</b></td>';
	for ($d = 0; $d < $daysInMonth; $d++) {
		$style = ($d % 2 == 0) ? 'light' : 'dark';
		if ($d > 0) {
			echo '<tr class="row' . $style . '">';
		}
		echo '<td class="td4">' . ($d + 1) . '</td>';
		for ($v = 0; $v < 5; $v++) {
			$dpa = ($v == 4) ? 1 : 0;
			$raw = LTA::getDailyAnom($ltaVars[$v], $m + 1, $d + 1, 2009);
			$disp = ($raw === null) ? '' : Wx::conv($raw, $ltaUnits[$v], false, false, $dpa);
			echo '<td class="td' . $ltaTypes[$v] . 'C">' . $disp . '</td>';
		}
		echo '</tr>';
	}
}
?>
</table>
</div>

<?php Page::End(); ?>
