<?php 
	if(!isset( $_SESSION ) ) {
		session_start();
	}
	$unitT = 'C'; $unitR = 'mm'; $unitW = 'mph'; $unitP = 'hPa';
	$expTime = 3600*24*100;
	if (isset($_GET['unit'])) {
		if($_GET['unit'] == 'US') { $unitT = 'F'; $unitR = 'in'; $unitW = 'mph'; $unitP = 'inHg'; setcookie("SetUnits", "US", time()+$expTime); }
		elseif($_GET['unit'] == 'UK') { $unitT = 'C'; $unitR = 'mm'; $unitW = 'mph'; $unitP = 'hPa'; setcookie("SetUnits", "UK", time()+$expTime); }
		else { $unitT = 'C'; $unitR = 'mm'; $unitW = 'km/h'; $unitP = 'mb'; setcookie("SetUnits", "EU", time()+$expTime); }
	}
	elseif(isset($_COOKIE['SetUnits'])) {
		if($_COOKIE['SetUnits'] == 'US') { $unitT = 'F'; $unitR = 'in'; $unitW = 'mph'; $unitP = 'inHg'; }
		if($_COOKIE['SetUnits'] == 'UK') { $unitT = 'C'; $unitR = 'mm'; $unitW = 'mph'; $unitP = 'hPa'; }
		if($_COOKIE['SetUnits'] == 'EU') { $unitT = 'C'; $unitR = 'mm'; $unitW = 'km/h'; $unitP = 'mb'; }
	}
	
function conv($tag, $type, $unit) { 	//unit=1 displays the units
	global $unitT, $unitW, $unitR, $unitP;
	$dat = floatval($tag);
	if($unit == 1) { $uarr = array(' &deg;'.$unitT,' '.$unitR,' '.$unitP,' '.$unitW); }
	$un = '&deg;'.$uarr;
	if ($type == 1) {
		if($unitT == 'F') { $conv = $dat*9/5+32; $clean = sprintf("%01.1f", $conv).$uarr[0]; } // C => F
		else { $clean = sprintf("%01.1f", $dat).$uarr[0]; }
	}
	if ($type == 2) {
		if($unitR == 'in') { $conv = $dat/25.4; $clean = sprintf("%01.2f", $conv).$uarr[1]; } // mm => in
		else { $clean = sprintf("%01.1f", $dat).$uarr[1]; }
	}
	if ($type == 3) {
		if($unitP == 'inHg') { $conv = $dat/33.864; $clean = sprintf("%01.2f", $conv).$uarr[2]; } // hPa => mmHg
		else { $clean = sprintf("%01.0f", $dat).$uarr[2]; }
	}
	if ($type == 4) {
		if($unitW == 'km/h') { $conv = $dat*1.6093; $clean = sprintf("%01.1f", $conv).$uarr[3]; } // mph => kmh
		elseif($unitW == 'm/s') { $conv = $dat*0.44704; $clean = sprintf("%01.1f", $conv).$uarr[3]; } // mph => mps
		elseif($unitW == 'knots') { $conv = $dat*0.86898; $clean = sprintf("%01.1f", $conv).$uarr[3]; } // mph => knot
		else { $clean = sprintf("%01.1f", $dat).$uarr[3]; }
	}
	if ($type == 0) { $clean = sprintf("%01.0f", $dat); }
	if ($type == 6) { $clean = sprintf("%01.0f", $dat). ' days'; }
	return $clean;
}

$path = $_SERVER['DOCUMENT_ROOT'];
$path .= "/main_tags.php";

include($path);
	if((date("s") % 10) % 2 == 1):
	echo 'Temperature: ',conv($temp,1,1), '<br />Wind Speed: ', conv($avgspd,4,1); else: echo 'Daily Rain: ', conv($dayrn,2,1), '<br />Pressure: ', conv($baro,3,1); endif;
?>