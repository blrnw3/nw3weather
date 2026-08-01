<?php
require('unit-select.php');

require_once ($root.'jpgraph/src/jpgraph.php');
require_once ($root.'jpgraph/src/jpgraph_bar.php');
include('functions.php');

$dtype = $_GET['type'];
$test_type = $types_all[$dtype];
if($test_type === null) {
	$dtype = 'rain';
}

$typeNum = $types_all[$dtype];
$hasToday = !(in_array($dtype, $types_m_original) || in_array($dtype, ['sunhra','sunhrp', 'wethra', 'wethrp']));
$typeconvNum = $typeconvs_all[$typeNum];
$isSum = $sumq_all[$typeNum];
$isAnom = $anomq_all[$typeNum];
$typevalcolNum = $wxtablecols_all[$typeNum];
$description = $descriptions_all[$types_all[$dtype]];
$startYear = $start_year_all[$typeNum];

$summary_type = isset($_GET['summary_type']) ? (int)$_GET['summary_type'] : 0;
if($summary_type < 0 || $summary_type > 4) {
	$summary_type = 0;
}
if($isSum && $summary_type === 0) {
	$summary_type = 1;
}

if(isset($_GET['x'])) { $dimx = ($_GET['x'] > 2000) ? 2000 : (int)$_GET['x']; } else { $dimx = 400; }
if(isset($_GET['y'])) { $dimy = ($_GET['y'] > 1500) ? 1500 : (int)$_GET['y']; } else { $dimy = 200; }


// SHARED GRAPH DATA FUNCS
/**
 *
 * @param type $type
 * @param type $len
 * @return type
 */
function graphDaily($type, $len = 31) {
	$daily_data = getDailyData($type, $GLOBALS["dyear"] - intval($len / 365) - 1);
	$format = ($len < 50) ? 'd' : ( ($len < 500) ? 'd-M' : 'M-y' );
	$graph = $labels = [];
	foreach ($daily_data as $y => $months) {
		foreach ($months as $m => $days) {
			foreach ($days as $d => $v) {
				$graph[] = floatval( conv($v, typeToConvType($type), 0, 0, 1, 0, 0, true) );
				$labels[] = date($format, mkdate($m, $d, $y));
			}
		}
	}
	return [array_slice($graph, -$len), array_slice($labels, -$len)];
}
/**
 *
 * @param type $type
 * @param type $year
 * @param type $lta_all
 * @param type $cume
 * @return type
 */
function graphDailyYear($type, $year, $lta_all = false, $cume = false) {
	$data = MDtoZ(getDailyDataForYear($type, $year));
	$format = 'd-M';
	$ltas = [];
	$c = 0;
	$clta = 0;
	for($d = 0; $d < count($data); $d++) {
		$val = floatval( conv($data[$d], typeToConvType($type), 0, 0, 1, 0, 0, true) );
		$c += $val;
		$graph[$d] = $cume ? $c : $val;
		$labels[$d] = date( $format, mkz($d, $year) );
		if($lta_all) {
			$clta += $lta_all[$d];
			$ltas[$d] = $cume ? $clta : $lta_all[$d];
		}
	}
	return array($graph, $labels, $ltas);
}
/**
 *
 * @param type $type
 * @param type $month
 * @param type $year
 * @return type
 */
function graphDailyMonth($type, $month, $year) {
	$datay = getDailyDataForYear($type, $year);
	$data = $datay[$month]; unset($datay);
	$len = count($data);
	for($d = 0; $d < $len; $d++) {
		$graph[$d] = conv($data[$d+1], typeToConvType($type), 0, 0, 1, 0, 0, true);
		$labels[$d] = $d+1;
	}
	return array($graph, $labels);
}
?>
