<?php
ini_set("memory_limit","64M");
if(isset($_GET['ts'])) { $stt = $_GET['ts']; } else { $stt = false; }
if(isset($_GET['te'])) { $end = $_GET['te']; } else { $end = false; }
if(isset($_GET['num'])) { $num = ($_GET['num'] > 100) ? 100 : (int)$_GET['num']; } else { $num = 1; }
if(isset($_GET['x'])) { $dimx = ($_GET['x'] > 2000) ? 2000 : (int)$_GET['x']; } else { $dimx = 850; }
if(isset($_GET['y'])) { $dimy = ($_GET['y'] > 1500) ? 1500 : (int)$_GET['y']; } else { $dimy = 450; }
if(isset($_GET['type'])) { $dtype = $_GET['type']; } else { $dtype = 'temp'; }
if(isset($_GET['nofooter'])) { $nofooter = true; $fmarge = 20; } else { $fmarge = 45; }

//cron settings
$fileName = $argv[1];
if($argv[2] == 's' || $argv[3] == 's') {
	$dimx = $argv[4];
	$dimy = $argv[3];
	$legendBoxWeight = 0;
	$skipYaxis = true;
} elseif(!isset($_GET['small'])) {
	$legendBoxWeight = 2;
	$skipYaxis = false;
} else {
	$skipYaxis = true;
	$legendBoxWeight = 0;
}
if($argv[2] == 'wdir') { //main (wx3) graph
	$dtype = 'wdir';
	$dimy = ($argv[3] == 's') ? 150 : 220;
}
if($argc > 5) { //main-page mini-graphs
	$_GET['type1'] = $argv[2];
	$_GET['type2'] = $argv[3];
	$nofooter = $argv[4];
	$fmarge = $nofooter ? 25 : 45;
	$dimx = 400;
	$dimy = 160;
	$stt = 12;
}
if($argc > 0 && date('Hi') == '0000') {
	//show the correct footer string for saved daily graphs
	$_GET['date'] = date('Ymd', time() - 60);
}

if(isset($_GET['date'])) {
	$procday = $_GET['date'];
	if($num > 1) { $message = $num.'-day graph of '; $conj = 'ending: end of '; }
	else { $message = 'Daily graph of '; $conj = 'for: '; }
	$footerstring = 'Graph '.$conj.date('jS F Y',mkdate(substr($_GET['date'],4,2),substr($_GET['date'],6,2),substr($_GET['date'],0,4)));
}
else {
	$procday = date('Ymd',time()-100);
	$rem = true;
	$hrs = 24*($num)-$stt;
	if($num > 1) { $hrs += date('H'); }
	$message = 'Last ';
	if($num > 3) { $message .= $num . ' days '; }
	else { $message .= $hrs.' hrs '; }
}

$namea = explode('/', $_SERVER['SCRIPT_NAME']);
$cachedURL = 'imgCache/'. substr( end($namea), 0, -4 ) .'_'. $procday;
$cachedURL .= isset($_GET['small']) ? '_s' : '';
$cachedURL .= '.png';
$cacheName = ROOT . $cachedURL;
if(false && file_exists($cacheName)) {
	$expires = 25920; //8-hr cache
	header("Pragma: public");
	header("Cache-Control: maxage=".$expires);
	header('Expires: ' . gmdate('D, d M Y H:i:s', time()+$expires) . ' GMT');
	header("Content-Type: image/png");
	header("Pragma: public");
	header("Expires: public");
	header("Cache-Control: public, max-age=".$expires);
	//header("Location: http://nw3weather.co.uk/". $cachedURL);
	readfile($cacheName);
	log_events("cacheReturn.txt", $cacheName);
	die();
}

if($autoscale) { // see graphdayA. Need here to optimise data collection
	for($i = 1; $i < 9; $i++) {
		if(isset($_GET['type'.$i])) { $dextra[$i] = $_GET['type'.$i]; $dble[$i] = true; $more = true; } else { $dble[$i] = false; }
		if($dextra[$i] == 'wdir') { $dtype = 'wdir'; unset($dextra[$i]); $dble[$i] = false; }
		if(!$dble[$i]) { unset($dble[$i]); }
	}
	if($more) { $dextra = array_merge($dextra); $dble = array_merge($dble); if($dtype == 'temp') { $dtype = $dextra[count($dble)-1]; $dble[count($dble)-1] = false; } }
}

$data = graphdaydata($procday, $stt, $end, $num, $dtype == 'wdir');
for($i = 0; $i <= 11; $i++) {
	$data[$i] = array_merge($data[$i]);
}
$data['lab'] = array_merge($data['lab']);

if($rem) {
	$footerstring = 'Last Updated: '.$data['enddate'];
}
if($imperial) {
	$unitcorr = 1/25;
	$unitcorr2 = 0.1;
	$rnlin = 'lin';
	$lineAF = 32;
} else {
	$unitcorr = 1;
	$unitcorr2 = 5;
	$rnlin = 'int';
	$lineAF = 0;
}
$unitcorr2 += $data['rnmax'];
$yAdjust = 450 / $dimy;
$rnShamt = (0.03 * $yAdjust * $unitcorr) + (0.003 * $data['rnmax'] * $yAdjust);

//$spike = array(99,99,99,9,9,360,0.5,5,0.5,2,9);
//$tconv = array(false,false,false,4,4,false,1,false,3,1,2);
//$depth = ceil(5/$num);
$baroSmoothConst = 5;
$baroSmoothDiff = ($imperial ? 0.02 : 0.5) * pow($num, 0.5) / $baroSmoothConst;

$cnt1 = count($data[1]);
for($i = 1; $i < $cnt1; $i++) {
	if($data[7][$i] == 98) { $data[7][$i] = 97.8; }
	if($data[3][$i] < 0.1) { $data[3][$i] = 0.1; }
	$data[10][$i] += $rnShamt;
	if($num > 48) { $data['lab'][$i] = date('d M',mkdate(substr($procday,4,2),substr($procday,6,2)-round((1440-$i)*$num/1440))); }
	$data['af'][$i] = $lineAF;

	//essential to prevent spiky baro line
	// NO LONGER NEEDED WITH VP2 SENSOR
	$diff = $data[8][$i] - $data[8][$i-1];
	if(abs($diff) > $baroSmoothDiff) {
		$data[8][$i] = $data[8][$i-1] + $diff / $baroSmoothConst;
	}

	//legacy smoothing method (works for all params but is expensive)
//	for($t = 8; $t <= 8; $t++) {
//		if($num < 60) {
//			$c = 1;
//			for($y = 1; $y <= $depth; $y++) {
//				for($s = 1; $s <= $y; $s++) { if(abs($data[$t][$i-$s] - $data[$t][$i-$y-1]) > conv($spike[$t],$tconv[$t],0)) { $c++; } }
//				if($c == $s) { for($x = 1; $x <= $y; $x++) { $data[$t][$i-$x] = $data[$t][$i]; } }
//			}
//		}
//	}
}
//rain smoothing alpha testing
//Not keen, deprecate.
if(false && $me) {
	for($i = 1; $i < $cnt1; $i++) {
		$diff2 = $data[10][$i] - $data[10][$i-1];
		if($diff2 > $baroSmoothDiff) {
			$data[10][$i] = $data[10][$i-1] + $diff2 / $baroSmoothConst;
		}
	}
}

for($i = $cnt1-1; $i < $cnt1+60; $i++) { //fix issue with last label
	$data['lab'][$i] = $data['lab'][count($data[1])-1];
}

$marg3 = 10; $labu = 60 * ceil(800/$dimx);
if($num > 3) { $labu = round(24*60 / $num ) * ceil($num/$dimx*30); }
if($num > 48) { $labu *= 2; }
$shift = $data['startmin'];

//footer string
$phpload = microtime(get_as_float) - $scriptbeg;
$footerstring .= '; Load time: ' . myround($phpload, 2) . ' s';


//######## functions  ##########
function graphdaydata($logf, $stt, $end, $multi, $wdirNeeded) {
	$filtot = array();
	$tconv = array(false,false,false,4,4,false,1,false,3,1,2);

	$wdirAvgTime = 15;
	$wdirs = new rollingMean($wdirAvgTime);

	$w10mAv = 0;
	$oldWinds = array();
	$pWind = 0; //position in circular buffer

	for($d = $multi-1; $d >= 0; $d--) {
		$log[$d] = date('Ymd',mkdate(substr($logf,4,2),substr($logf,6,2)-$d,substr($logf,0,4)));
		if($multi > 1 && $log[$d] == date('Ymd',mkdate($GLOBALS['dmonth'],$GLOBALS['dday'],$GLOBALS['dyear']))) { $log[$d] = 'today'; }
		if(file_exists(ROOT.'logfiles/daily/'.$log[$d].'log.txt')) {
			$filcust = file(ROOT.'logfiles/daily/'.$log[$d].'log.txt');
			$filtot = array_merge($filtot, $filcust);
			unset($filcust);
		}
	}
	if(!$end) { $end = count($filtot); } else { $end *= 60; }
	if(!$stt) { $stt = 1; } else { $stt = round(60*$stt); }

	for($i = $stt; $i < $end; $i+=$multi) {
		$custl = explode(',', $filtot[$i]);
		for($t = 0; $t <= 10; $t++) {
			$dat[$t][$i] = $custl[$t]; // gives a massive 3x saving over applying conv() on everything!
		}

		$w10mAv += $dat[3][$i];
		$oldWinds[$pWind % 10] = $dat[3][$i];
		$pWind++;
		if($pWind >= 10) {
			$dat[3][$i] = $w10mAv / 10;
			$w10mAv -= $oldWinds[$pWind % 10];
		}

		//now, apply conv as little as possible (we are in a nested loop so big factor)
		if($GLOBALS['imperial']) {
			for($t = 6; $t <= 10; $t++) {
				$dat[$t][$i] = conv($custl[$t], $tconv[$t], 0);
			}
		} elseif($GLOBALS['metric']) {
			for($t = 3; $t <= 4; $t++) {
				$dat[$t][$i] = conv($custl[$t], $tconv[$t], 0);
			}
		}

		if($wdirNeeded) {
			$wdirs->add($dat[5][$i]);
			$dat[11][$i] = ($dat[3][$i] > 0.5) ? $wdirs->mean() : -999;
		} else {
			$dat[11][$i] = 0;
		}

		$dat['lab'][$i] = ($multi > 3) ? $custl[2] : date('H', mktime($custl[0], $custl[1]+1));
	}

	if(is_array($dat[0])) {
		$dat['startmin'] = $dat[1][$stt]; if($multi > 1) { $dat['startmin'] = 0; }
		$dat['rnmax'] = mymax($dat[10]);
		$dat['enddate'] = date('H:i, jS M', mktime($dat[0][$i-$multi],$dat[1][$i-$multi],0,$GLOBALS['dmonth'],$dat[2][$i-$multi]));
		return $dat;
	} else {
		error_log("non-array when getting graphday data!");
		die();
	}
}


/// ###### Classes  #######
class rollingMean {
	private $size;
	public $items = null;
	private $pointer = 0;
	private $curr = 0;
	private $oldMean = -99;

	function __construct($size) {
		$this->items = array();
		$this->size = $size;
	}

	function add($item) {
		$this->curr = $item;
		$pos = $this->pointer % $this->size;
		$this->pointer++;
		$this->items[$pos] = $item;

	}

	const bitifier = 120; //constant - the quantisation level to convert 360 degrees into a bitier signal
	function mean() {

		//don't average until buffer is full
		if ($this->pointer <= $this->size) {
			return $this->curr;
		}

		$freqs = array();
		for($i = 0; $i <= 360/self::bitifier; $i++) {
			$freqs[$i] = 0;
		}

		//get frequencies for each bitified angle
		for($i = 0; $i < $this->size; $i++) {
			$freqs[round($this->items[$i] / self::bitifier)]++;
		}

		//choose a pivot
		$minfreq = min($freqs);
		$pivot = array_search($minfreq, $freqs);
		$pivot *= self::bitifier;

		//calculate the mean
		$sum = 0;
		for($i = 0; $i < $this->size; $i++) {
			$sum += $this->items[$i];
			if($this->items[$i] > $pivot) {
				$sum -= 360;
			}
		}
		//clean-up
		$mean = $sum / $this->size;
		if($mean < 0) {
			$mean += 360;
		}

		$oldieMean = $this->oldMean;
		$this->oldMean = $mean;

		// don't show heavily drifting mean
		if($oldieMean !== -99 && abs($mean - $oldieMean) > 5) {
			return -100;
		}

		return $mean;
	}
}
?>