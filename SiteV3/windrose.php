<?php // content="text/plain; charset=utf-8"
include('/var/www/html/basics.php');
require_once (ROOT.'jpgraph/src/jpgraph.php');
require_once (ROOT.'jpgraph/src/jpgraph_windrose.php');
include($root.'unit-select.php');
include($root.'functions.php');

// https://jpgraph.net/download/manuals/chunkhtml/ch21s07.html
// (and ch21s02, ...03 etc.

// URL settings
$st_raw = filter_input(INPUT_GET, "st", FILTER_SANITIZE_STRING);
$en_raw = filter_input(INPUT_GET, "en", FILTER_SANITIZE_STRING);
if(isset($_GET['x'])) { $dimx = ($_GET['x'] > 2000) ? 2000 : (int)$_GET['x']; } else { $dimx = 700; }
if(isset($_GET['y'])) { $dimy = ($_GET['y'] > 1500) ? 1500 : (int)$_GET['y']; } else { $dimy = 700; }

// cron settings
if($argc > 0 || isset($_GET["cron_test"])) {
	$en_raw = $argv[1];
	$file_name = $argv[2];
	$st_raw = ($en_raw === "year") ? date("Ymd", mkdate(1, 1, $yr_yest)) : null;
	$dimx = 432;
	$dimy = 460;
	if($en_raw === "now") {
		$st_raw = "20110101";
		$dimx = 800;
		$dimy = 820;
	}
}
$sz = ($dimx < 500) ? 0.75: 0.75;
if(isset($_GET['sz'])) { $sz = (float)$_GET['sz']; }

$z = 0.1;
$buckets = array(1,3,5,8,11,15,20,50);
$weights = array(8,15,20,23,25,27,29,30);
if($dimx < 500) {
	$weights = array(5,10,14,17,19,21,23,25);
	$buckets = array(1,3,5,8,11,15,50);
	$z = 0.12;
}

$st = ($st_raw === null || $st_raw === false) ? mkdate($mon_yest, 1, $yr_yest): datestamp_to_ts($st_raw);
if($en_raw === null) {
	$en_raw = "month";
}
if($en_raw == "month") {
	$en = datestamp_to_ts(date('Ym', $st) . zerolead(get_days_in_month(date('m', $st), date('Y', $st))));
	$roseDate = date('M Y', $st);
}
elseif($en_raw == "year") {
	$en = datestamp_to_ts(date('Y', $st) . '1231');
	$roseDate = date('Y', $st);
}
elseif($en_raw == "now") {
	$en = time();
	$roseDate = date('d M Y', $st) . " to present";
}
elseif($en_raw == "24hrs") {
	$st = time();
	$en = time();
	$roseDate = "past 24 hours";
}
else {
	$en = datestamp_to_ts($en_raw);
	if($en === $st) {
		$roseDate = date('d M Y', $st);
	} else {
		$roseDate = date('d M Y', $st) . " to " . date('d M Y', $en);
	}
}

$st_stamp = date('Ymd', $st);
$en_stamp = date('Ymd', $en);

$plot_data = getRosePlotData($st_stamp, $en_stamp, $buckets);
//print_m($plot_data);

$graph = new WindroseGraph($dimx, $dimy);
// Setup title
$graph->title->Set("Wind rose for $roseDate");
$graph->title->SetFont(FF_VERDANA,FS_BOLD,15);
$graph->title->SetColor("#222");

// Create the windrose plot.
$wp = new WindrosePlot($plot_data);
$wp->SetSize($sz);
$wp->SetRadialGridStyle('dashed');
$wp->SetRangeColors(array('#999','yellow', 'orange', 'red','blue','purple', 'black'));
$wp->SetRangeWeights($weights);
$wp->SetZCircleSize($z);
$wp->scale->SetZeroLabel("Calm");
//$wp->scale->SetLabelFillColor("#fbb","black");
//$wp->scale->Set(30, 10);
$wp->SetFontColor('#666');
$wp->SetLabelMargin(10);
$wp->SetGridColor('#777','#555');
$wp->SetGridWeight(1, 2);
$wp->legend->SetText('mph');
$wp->legend->SetMargin(18,0);
$wp->setRanges($buckets);
//$wp->setRangeStyle(RANGE_DISCRETE);
$wp->SetDataKeyEncoding(KEYENCODING_CLOCKWISE);

$phpload = microtime(get_as_float) - $scriptbeg;
$footerstring = "@nw3weather. Generated: $date $time; Load time: " . myround($phpload, 2) . ' s';
$graph->footer->center->Set($footerstring);

$graph->Add($wp);
$graph->Stroke($file_name);

function combineRoses($roses) {
	$tot_cnt = 0;
	$overall = [];
	$size = count($roses);
	foreach($roses as $i => $rose) {
		foreach($rose as $dir => $speeds) {
			if(!is_array($speeds)) {
				continue;
			}
			foreach($speeds as $spd => $cnt) {
				if($cnt > 1300 && $size > 7) {
					continue;
//					echo("Suspect: $dir $cnt {$rose['dt']}<br />");
				}
				$overall[$dir][$spd] += $cnt;
				$tot_cnt += $cnt;
			}
		}
	}
	$overall["tot_cnt"] = $tot_cnt;
//	print_m($overall);
	return $overall;
}

function extract_roses($st, $en) {
	$today = date('Ymd');
	if($GLOBALS["en_raw"] === "24hrs") {
		return [$GLOBALS["HR24"]["windDirs"]];
	}
	if($st === $en && $st === $today) {
		return [$GLOBALS["NOW"]["windDirs"]];
	}
	$lines = file(ROOT."datwdirdaily.dat");
	$roses = [];
	foreach($lines as $i => $line) {
		$rose = unserialize($line);
		$dt = $rose["dt"];
		if($dt > $en) {
			break;
		}
		if($dt >= $st) {
			$roses[$i] = $rose;
		}
	}
	if($en >= date('Ymd') && $st <= date('Ymd')) {
		$roses[] = $GLOBALS["NOW"]["windDirs"];
	}
	return $roses;
}

function roseToPlotData($rose, $buckets) {
	$plot_rose = [];
	foreach($rose as $dir => $speeds) {
		if($dir === "tot_cnt") {
			continue;
		}
		// I have 0 as N, they use NNE for 0
		$adj_dir = ($dir + 15) % 16;
		$prev = 0;
		foreach($buckets as $i => $bucket) {
			$plot_rose[$adj_dir][$i] = 0;
			foreach($speeds as $spd => $cnt) {
				if($spd >= $prev && $spd < $bucket) {
					$plot_rose[$adj_dir][$i] += $cnt / $rose["tot_cnt"] * 100;
				}
			}
			$prev = $bucket;
		}
	}
	return $plot_rose;
}

function getRosePlotData($st, $en, $buckets) {
	return roseToPlotData(combineRoses(extract_roses($st, $en)), $buckets);
}

?>
