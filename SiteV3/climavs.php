<?php
$clim_descrip = array('Min Temp','Max Temp','Mean Temp','Temp Range', 'Rainfall','Rain Days > 1mm', 'Wind Speed', 'Air Frosts','Thunder Days','Days Of Lying Snow',
					'Days Of Falling Snow', 'Wet Hours','Sun Hours','Max Sun Hours');
$clim_unit = array(1,1,1,1, 2,false, 4, false,false,false,false, false,false,false);
$clim_colours = array('blue','orange','blueviolet','green', 'cadetblue3','cadetblue4', 'brown3', 'darkblue','darkgoldenrod1','cyan','cyan3', 'aquamarine4','gold','gold3');

$sunav = array(46,75,105,137,190,191,185,182,138,106,68,48);
$maxsun = array(233,249,331,376,440,452,454,410,342,295,237,219);
$wetav = array(67,52,53,46,40,37,34,38,41,49,63,62);
$rainav = array(55,40,44,49,51,55,42,53,57,65,56,56);
$rdaysav = array(11,9,10,10,9,9,7,8,9,10,10,11);
$windav = array(5.2,5.1,5.2,4.9,4.7,4.4,4.3,4.0,3.9,4.1,4.6,5.1);
$tdatav['min'] = array(2.4,2.2,3.6,5.0,8.3,11.1,13.4,13.2,11.1,8.8,5.0,3.2);
$tdatav['max'] = array(7.0,7.6,10.4,12.8,16.7,20.2,22.4,22.2,18.7,14.6,10.2,7.8);
for($m = 0; $m < 12; $m++) {
	$tdatav['mean'][$m] = ($tdatav['min'][$m]+$tdatav['max'][$m])/2;
	$tdatav['range'][$m] = $tdatav['max'][$m]-$tdatav['min'][$m];
	$rateav[$m] = $rainav[$m] / $wetav[$m];
}
$AFav = array(7,7,3,1,0.1,0,0,0,0,0.2,2,6);
$TSav = array(0.4,0.3,0.6,1.0,2.0,3.0,2.5,2.5,2.0,1.0,0.4,0.3);
$LSav = array(2.5,2.5,0.4,0.2,0,0,0,0,0,0,0.3,1);
$FSav = array(5,5,4,2,0,0,0,0,0,0,1,3);

$vars = array($tdatav['min'], $tdatav['max'], $tdatav['mean'], $tdatav['range'],
	$rainav,$rdaysav,$windav, $AFav,$TSav,$LSav, $FSav,$wetav,$sunav, $maxsun,$rateav); //14
$sumorno = array(false,false,false,false,
	true,true,false, true,true,true, true,true,true, true,true);

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

//365-day clim avs
$dtfanomcc = file(ROOT . 'tminmaxav.csv');
$dsuncc = file(ROOT . 'maxsun.csv');
for($z = 0; $z < 365; $z++) {
	$dtanomcc = explode(',', $dtfanomcc[$z]);
	$lta[0][$z] = $dtanomcc[0];
	$lta[1][$z] = $dtanomcc[1];
	$lta[2][$z] = ($dtanomcc[1] + $dtanomcc[0])/2;
	$lta[3][$z] = $dtanomcc[1] - $dtanomcc[0];
	$lta[4][$z] = $dsuncc[$z];
}
$lta_type = array(12,14,16,10,18);
$lta_unit = array(1,1,1,1.1,0);
$lta_descrip = array('Min Temp','Max Temp','Mean Temp','Temp Range','Max Sun');
$lta_colours = array('blue','orange','blueviolet','green','gold3');
$unitconvs = array('',$unitT,$unitR,$unitP,$unitW);

$dbi = count($types);
$mdi = $dbi + count($types_derived);
$maptoClimavs = array_flip( array(0,1,2,$dbi, 13,99,9, 99,99,99, 99,$mdi+1,$mdi, 99,$dbi+3) );

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