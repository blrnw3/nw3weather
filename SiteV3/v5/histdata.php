<?php
/**
 * JSON data endpoint for historical charts (daily / monthly / annual / climate).
 *
 * Replaces the legacy server-rendered jpgraph endpoints (graph31, graph12,
 * graph_annual, graph_daily_trend, graphclim) by returning category labels and
 * one or more value series. The Highcharts front-end (wxcharts.js) renders them.
 *
 * Common params:
 *   type         variable name (key of Wx::$daily), e.g. tmin, rain, gust
 *   mode         daily | monthly | annual | climate   (default daily)
 * Daily:
 *   length       last-N days (default 31)              [no year set]
 *   year, month  single month (month 1-12) or whole year (month 0)
 *   lta          1 to overlay the daily climate normal (whole-year only)
 *   cume         1 for a cumulative running total (whole-year only)
 *   multiyr      "last" or comma-list of years to overlay (whole-year line)
 * Monthly:
 *   length       last-N months (default 12)            [no year set]
 *   year         a single year (12 months)
 *   summary_type 0 mean,1 sum,2 count,3 min,4 max
 *   lta          1 to overlay the monthly climate normal
 * Annual:
 *   start, end   year range (defaults BASE_YEAR..current)
 *   summary_type as above
 */
require __DIR__ . '/Page.php';
ini_set('display_errors', '0');

// Resolve display units from the same cookie Page uses, without a full page init.
$unitPref = isset($_GET['unit']) ? $_GET['unit'] : (isset($_COOKIE['SetUnits']) ? $_COOKIE['SetUnits'] : 'UK');
Page::$units = ($unitPref === 'US') ? UNIT_US : (($unitPref === 'EU') ? UNIT_EU : UNIT_UK);
LTA::init();

header('Content-Type: application/json; charset=utf-8');

$type = isset($_GET['type']) ? preg_replace('/[^a-z0-9]/', '', $_GET['type']) : 'tmin';
if (!isset(Wx::$daily[$type])) {
	echo json_encode(['error' => 'unknown type']);
	exit;
}
$mode = isset($_GET['mode']) ? $_GET['mode'] : 'daily';
$meta = Wx::$daily[$type];
$convType = Data::typeToConvType($type);
$summable = isset($meta['summable']) && $meta['summable'];
$summaryType = isset($_GET['summary_type']) ? (int)$_GET['summary_type'] : ($summable ? Data::SUMMARY_SUM : Data::SUMMARY_MEAN);
if ($summaryType < 0 || $summaryType > 4) { $summaryType = Data::SUMMARY_MEAN; }
$colour = Wx::colourHex(isset($meta['colour']) ? $meta['colour'] : null);
$precision = isset(Wx::$UNITS[$convType]['precision']) ? Wx::$UNITS[$convType]['precision'] : 1;

/** Convert a daily/monthly value to user units (raw counts stay unconverted). */
function cv($v, $isCount = false) {
	global $convType, $precision;
	if ($v === null || $v === '' || $v === '-' || !is_numeric($v)) { return null; }
	if ($isCount) { return round((float)$v, 0); }
	return Wx::convNum($v, $convType, $precision);
}

$out = [
	'type'        => $type,
	'description' => $meta['description'],
	'colour'      => $colour,
	'unit'        => strip_tags(Wx::getUnits($convType)),
	'precision'   => $precision,
	'yMinZero'    => ($convType !== Wx::Pressure),
	'mode'        => $mode,
	'chartType'   => 'column',
	'xType'       => 'category',
	'title'       => '',
	'categories'  => [],
	'series'      => [],
];

/** Map a variable to its LTA monthly normal array, or null. */
function ltaMonthly($type) {
	if (isset(LTA::$vars[$type]) && is_array(LTA::$vars[$type]) && isset(LTA::$vars[$type]['monthly'])) {
		return LTA::$vars[$type]['monthly'];
	}
	// Mean temperature normal isn't stored directly; derive it from tmin/tmax.
	if ($type === 'tmean'
		&& isset(LTA::$vars['tmin']['monthly']) && isset(LTA::$vars['tmax']['monthly'])) {
		$mean = array();
		for ($m = 0; $m < 12; $m++) {
			$mean[$m] = (LTA::$vars['tmin']['monthly'][$m] + LTA::$vars['tmax']['monthly'][$m]) / 2;
		}
		return $mean;
	}
	return null;
}
/** Map a variable to its LTA daily (day-of-year) normal array, or null. */
function ltaDaily($type) {
	if (isset(LTA::$vars[$type]) && is_array(LTA::$vars[$type]) && isset(LTA::$vars[$type]['daily']) && count(LTA::$vars[$type]['daily'])) {
		return LTA::$vars[$type]['daily'];
	}
	return null;
}

if ($mode === 'annual') {
	$start = isset($_GET['start']) ? (int)$_GET['start'] : Site::BASE_YEAR;
	$end   = isset($_GET['end']) ? (int)$_GET['end'] : (int)Date::$dyear;
	$isCount = ($summaryType === Data::SUMMARY_COUNT);
	$annual = Data::getAnnualData($type, $summaryType, $start, $end);
	$data = [];
	foreach ($annual as $yr => $v) {
		$out['categories'][] = (string)$yr;
		$data[] = cv($v, $isCount);
	}
	$out['title'] = 'Annual ' . Data::$SUMMARY_NAMES[$summaryType] . ' ' . $meta['description'];
	$out['series'][] = ['name' => $meta['description'], 'data' => $data, 'color' => $colour, 'type' => 'column'];
	if ($isCount) { $out['unit'] = 'days'; $out['precision'] = 0; }

} elseif ($mode === 'monthly') {
	$isCount = ($summaryType === Data::SUMMARY_COUNT);
	if (isset($_GET['year'])) {
		$yr = (int)$_GET['year'];
		$ms = Data::getMonthlySummary($type, $summaryType, $yr, $yr);
		$vals = isset($ms[$yr]) ? $ms[$yr] : [];
		$data = [];
		for ($m = 1; $m <= 12; $m++) {
			$out['categories'][] = Date::$months3[$m - 1];
			$data[] = isset($vals[$m]) ? cv($vals[$m], $isCount) : null;
		}
		$out['title'] = $yr . ' monthly ' . Data::$SUMMARY_NAMES[$summaryType] . ' ' . $meta['description'];
		$out['series'][] = ['name' => $meta['description'], 'data' => $data, 'color' => $colour, 'type' => 'column'];
		$ltaEndMon = 12;
	} else {
		$length = isset($_GET['length']) ? (int)$_GET['length'] : 12;
		$startYr = (int)Date::$dyear - intval($length / 12) - 1;
		$ms = Data::getMonthlySummary($type, $summaryType, $startYr, (int)Date::$yr_yest);
		$flatVals = []; $flatLabels = [];
		foreach ($ms as $yr => $months) {
			foreach ($months as $m => $v) {
				$flatVals[] = cv($v, $isCount);
				$flatLabels[] = Date::$months3[$m - 1] . '-' . substr($yr, 2);
			}
		}
		$flatVals = array_slice($flatVals, -$length);
		$flatLabels = array_slice($flatLabels, -$length);
		$out['categories'] = $flatLabels;
		$out['title'] = "Last $length months " . Data::$SUMMARY_NAMES[$summaryType] . ' ' . $meta['description'];
		$out['series'][] = ['name' => $meta['description'], 'data' => $flatVals, 'color' => $colour, 'type' => 'column'];
		$ltaEndMon = (int)Date::$dmonth;
	}
	if ($isCount) { $out['unit'] = 'days'; $out['precision'] = 0; }
	// LTA overlay
	$lta = ltaMonthly($type);
	if (isset($_GET['lta']) && $lta && $summaryType <= Data::SUMMARY_SUM) {
		$n = count($out['categories']);
		$climvals = [];
		for ($i = 0; $i < $n; $i++) {
			$offset = (($ltaEndMon - $n + $i) % 12 + 12) % 12;
			$climvals[] = cv($lta[$offset]);
		}
		$out['series'][] = ['name' => 'Normal', 'data' => $climvals, 'color' => '#999999', 'type' => 'column'];
	}

} elseif ($mode === 'climate') {
	// Monthly climate normals (+ daily if available)
	$lta = ltaMonthly($type);
	$data = [];
	for ($m = 0; $m < 12; $m++) {
		$out['categories'][] = Date::$months3[$m];
		$data[] = $lta ? cv($lta[$m]) : null;
	}
	$out['title'] = 'Climate normal ' . $meta['description'];
	$out['series'][] = ['name' => 'Normal', 'data' => $data, 'color' => '#999999', 'type' => 'column'];

} else {
	// ----- daily -----
	$cumeable = $summable;
	$cume = isset($_GET['cume']) && $cumeable;
	$out['chartType'] = 'line';

	if (isset($_GET['year'])) {
		$mon = isset($_GET['month']) ? (int)$_GET['month'] : 0;
		$yr  = (int)$_GET['year'];
		if ($yr > (int)Date::$dyear || ($yr == (int)Date::$dyear && $mon > (int)Date::$dmonth)) {
			echo json_encode(['error' => 'invalid period']); exit;
		}
		if ($mon === 0) {
			// Whole year (day-of-year aligned), with optional cume + lta + multiyr overlays
			$ltaDaily = ltaDaily($type);
			$buildYear = function($y) use ($type, $cume) {
				$md = Data::getDailyDataForYear($type, $y);
				$flat = Data::MDtoZ($md);
				$series = []; $run = 0;
				foreach ($flat as $v) {
					$val = cv($v);
					if ($cume) { $run += ($val === null ? 0 : $val); $series[] = round($run, 2); }
					else { $series[] = $val; }
				}
				return $series;
			};
			$thisYear = $buildYear($yr);
			// x labels: day-of-year as 'd M'
			for ($z = 0; $z < count($thisYear); $z++) {
				$out['categories'][] = date('d M', Date::mkz($z, $yr));
			}
			$out['series'][] = ['name' => (string)$yr, 'data' => $thisYear, 'color' => $colour, 'type' => 'line'];

			if (isset($_GET['multiyr'])) {
				$overlayYrs = $_GET['multiyr'] === 'last'
					? [(int)Date::$dyear - 1]
					: array_map('intval', array_filter(explode(',', $_GET['multiyr'])));
				$palette = ['#2f9e44', '#ffb473', '#555577', '#138086', '#333366', '#aac', '#349'];
				foreach ($overlayYrs as $i => $oy) {
					if ($oy == $yr) { continue; }
					$out['series'][] = ['name' => (string)$oy, 'data' => $buildYear($oy),
						'color' => $palette[$i % count($palette)], 'type' => 'line'];
				}
			}
			if (isset($_GET['lta']) && $ltaDaily) {
				$ltaSeries = []; $run = 0;
				for ($z = 0; $z < count($thisYear); $z++) {
					$lv = isset($ltaDaily[$z]) ? cv($ltaDaily[$z]) : null;
					if ($cume) { $run += ($lv === null ? 0 : $lv); $ltaSeries[] = round($run, 2); }
					else { $ltaSeries[] = $lv; }
				}
				$out['series'][] = ['name' => 'Normal', 'data' => $ltaSeries, 'color' => '#999999',
					'type' => 'line', 'dashStyle' => 'Dash'];
			}
			$out['title'] = $yr . ($cume ? ' cumulative ' : ' ') . $meta['description'];
		} else {
			// Single month (one bar/line per day)
			$md = Data::getDailyDataForYear($type, $yr);
			$days = isset($md[$mon]) ? $md[$mon] : [];
			$data = [];
			foreach ($days as $d => $v) {
				$out['categories'][] = (string)$d;
				$data[] = cv($v);
			}
			$out['chartType'] = 'column';
			$out['title'] = Date::$months3[$mon - 1] . ' ' . $yr . ' ' . $meta['description'];
			$out['series'][] = ['name' => $meta['description'], 'data' => $data, 'color' => $colour, 'type' => 'column'];
		}
	} else {
		// Last-N days
		$length = isset($_GET['length']) ? (int)$_GET['length'] : 31;
		$startYr = (int)Date::$dyear - intval($length / 365) - 1;
		$daily = Data::getDailyData($type, $startYr);
		$flatVals = []; $flatLabels = [];
		$fmt = ($length < 50) ? 'd' : (($length < 500) ? 'd M' : 'M-y');
		foreach ($daily as $y => $months) {
			foreach ($months as $m => $days) {
				foreach ($days as $d => $v) {
					$flatVals[] = cv($v);
					$flatLabels[] = date($fmt, Date::mkdate($m, $d, $y));
				}
			}
		}
		$length = min($length, count($flatVals));
		$out['categories'] = array_slice($flatLabels, -$length);
		$out['series'][] = ['name' => $meta['description'], 'data' => array_slice($flatVals, -$length),
			'color' => $colour, 'type' => ($length > 90 ? 'line' : 'column')];
		$out['chartType'] = ($length > 90 ? 'line' : 'column');
		$out['title'] = "Last $length days " . $meta['description'];
	}
}

echo json_encode($out);
