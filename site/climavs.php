<?php
$clim_descrip = array('Min Temp','Max Temp','Mean Temp','Temp Range', 'Rainfall','Rain Days > 1mm', 'Wind Speed', 'Air Frosts','Thunder Days','Days Of Lying Snow',
					'Days Of Falling Snow', 'Wet Hours','Sun Hours','Max Sun Hours');
$clim_unit = array(1,1,1,1, 2,false, 4, false,false,false,false, false,false,false);
$clim_colours = array('blue','orange','blueviolet','green', 'cadetblue3','cadetblue4', 'brown3', 'darkblue','darkgoldenrod1','cyan','cyan3', 'aquamarine4','gold','gold3');

$sunav = array(69,78,113,161,189,194,195,185,147,118,84,65);
$maxsun = array(233,249,331,376,440,452,454,410,342,295,237,219);
$wetav = array(67,52,53,46,40,37,34,38,41,49,63,62);
$rainav = array(59,45,39,42,46,47,46,54,50,65,67,57);
$rdaysav = array(11,9,10,10,9,9,8,8,9,11,10,11);
$windav = array(5.2,5.1,5.2,4.9,4.7,4.4,4.3,4.0,3.9,4.1,4.6,5.1);
$tdatav['min'] = array(3.0,3.0,4.3,6.0,9.0,12.0,14.2,14.1,11.5,8.6,5.5,3.6);
$tdatav['max'] = array(7.8,8.2,10.9,14.3,17.8,20.9,23.0,22.5,19.1,14.8,10.7,8.2);
for($m = 0; $m < 12; $m++) {
	$tdatav['mean'][$m] = ($tdatav['min'][$m]+$tdatav['max'][$m])/2;
	$tdatav['range'][$m] = $tdatav['max'][$m]-$tdatav['min'][$m];
	$rateav[$m] = $rainav[$m] / $wetav[$m];
}
$AFav = array(6,6,3,0.7,0.0,0,0,0,0,0.3,2,5);
$TSav = array(0.4,0.3,0.6,1.0,2.0,3.0,2.5,2.5,2.0,1.0,0.4,0.3);
$LSav = array(2.5,2.5,0.4,0.2,0,0,0,0,0,0,0.3,1);
$FSav = array(5,5,4,2,0,0,0,0,0,0,1,3);

$vars = array($tdatav['min'], $tdatav['max'], $tdatav['mean'], $tdatav['range'],
	$rainav,$rdaysav,$windav, $AFav,$TSav,$LSav, $FSav,$wetav,$sunav, $maxsun,$rateav); //14
$vars_to_climav = [
	"tmin" => $tdatav['min'],
	"tmax" => $tdatav['max'],
	"tmean" => $tdatav['mean'],
	"trange" => $tdatav['range'],
	"rain" => $rainav,
	"rdays" => $rdaysav,
	"wmean" => $windav,
	"sunhr" => $sunav
];
$sumorno = array(false,false,false,false,
	true,true,false, true,true,true, true,true,true, true,true);

$annualsum = array();
$annualav = array();
$annualrange = array();
for($v = 0; $v < count($vars); $v++) {
	//Seasonal
	if($sumorno[$v]) { $divorno = 1; } else { $divorno = 3; }
	for($s = 0; $s < 4; $s++) {
		for($s2 = 0; $s2 < 3; $s2++) {
			$seasonav[$v][$s] += $vars[$v][$snums[$s][$s2]] / $divorno;
		}
	}
	//Annual
	$annualsum[$v] = array_sum($vars[$v]);
	$annualav[$v] = round(mean($vars[$v]),1);
	$annualrange[$v] = max($vars[$v]) - min($vars[$v]);
}
$vars_to_climav_annual = [
	"tmin" => $annualav[0],
	"tmax" => $annualav[1],
	"tmean" => $annualav[2],
	"trange" => $annualav[3],
	"rain" => $annualsum[4],
	"rdays" => $annualsum[5],
	"wmean" => $annualav[6],
	"sunhr" =>$annualsum[12],
];

//365-day clim avs
$dtfanomcc = file(ROOT . 'tminmaxav.csv');
$dsuncc = file(ROOT . 'maxsun.csv');
for($z = 0; $z <= 365; $z++) {
	$dtanomcc = explode(',', $dtfanomcc[$z]);
	$lta[0][$z] = floatval($dtanomcc[0]);
	$lta[1][$z] = floatval($dtanomcc[1]);
	$lta[2][$z] = ($dtanomcc[1] + $dtanomcc[0])/2;
	$lta[3][$z] = $dtanomcc[1] - $dtanomcc[0];
	$lta[4][$z] = $dsuncc[$z];
	$month_idx = date('n', mkdate(1, min($z+1, 365), 2023)) - 1;
	$month_days = date('t', mkdate(1, min($z+1, 365), 2023));
	$lta["rain"][$z] = $rainav[$month_idx] / $month_days;
	$lta["sunhr"][$z] = $sunav[$month_idx] / $month_days;
	$lta["wmean"][$z] = $windav[$month_idx];
}
$lta_type = array(12,14,16,10,18);
$lta_unit = array(1,1,1,1.1,0);
$lta_descrip = array('Min Temp','Max Temp','Mean Temp','Temp Range','Max Sun');
$lta_colours = array('blue','orange','blueviolet','green','gold3');
$unitconvs = array('',$unitT,$unitR,$unitP,$unitW);

$dbi = count($types);
$mdi = $dbi + count($types_derived);
$maptoClimavs = array_flip( array(0,1,2,$dbi, 13,99,9, 99,99,99, 99,$mdi+1,$mdi, 99,$dbi+3) );

$vars_to_climav_daily = [
	"tmin" => $lta[0],
	"tmax" => $lta[1],
	"tmean" => $lta[2],
	"trange" => $lta[3],
	"sunhr" => $lta["sunhr"],
	"rain" => $lta["rain"],
	"wmean" => $lta["wmean"],
];

/**
 * Long-term climate averages for all available types, by month, season and year (and day if temp or sun)
 */
class Climate {
	public $monthlyData;
	public $mapping;
	public $annualSums = array();
	public $annualAvs = array();

	function __construct($map, $data) {
		$this->mapping = $map;
		$this->monthlyData = $data;

		for($v = 0; $v < count($data); $v++) {
			$this->annualSums[$v] = array_sum($data[$v]);
			$this->annualAvs[$v] = round(mean($data[$v]),1);
		}
	}
}
?>