<?php require('unit-select.php');
	$ranking = 1; ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php include("phptags.php");
	$file = 54; ?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Old(v2) - Temperature Rankings</title>

	<meta name="description" content="Old v2 - Highest and lowest ranked min/max/mean daily temperatures" />

	<?php require('chead.php'); ?>
	<link rel="stylesheet" type="text/css" href="wxreports2.css" media="screen" title="screen" />
<?php include_once("ggltrack.php") ?>
</head>

<body>
	<!-- For non-visual user agents: -->
	<div id="top"><a href="#main-copy" class="doNotDisplay doNotPrint">Skip to main content.</a></div>

	<!-- ##### Header ##### -->
	<? require('header.php'); ?>

	<!-- ##### Left Sidebar ##### -->
	<? require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->

<div id="main-copy">

	<div align="center" id="report">
		<h1>Daily Ranked Temperatures (&deg;<?php echo $unitT; ?>)</h1>
	
		<?php $self = 'rankhist14.php';
			include("wxrepgen.php");

$monthshort = array('Measure', 'Jan', 'Feb', 'March', 'April', 'May', 'June', 'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

$temprange_start = -5; //Set up colour scheme
$temprange_increment = 5;
if($unitT == 'F') { $temprange_start = 20; $temprange_increment = 10; }
$increments = 8;
for ( $i = 0; $i < $increments; $i++ ) {
	$tempvalues[$i] = $temprange_start + $temprange_increment*$i;
}

//Start Data collection
for($y = 2009; $y <= $date_year; $y++) { //Add past data to the array
	for($m = 1; $m <= 12; $m++) {
		if(date('n') == $m && date('Y') == $y && date('j') != 1) { $filename = "dailynoaareport".".htm"; $daysvalid = date('j')-1; }
		elseif(date('n') == $m && date('Y') == $y && date('j') == 1) { $filename = 'nothing'; }
		else { $filename = "dailynoaareport".date("nY", mktime(0,0,0,$m,1,$y)).".htm"; $daysvalid = date('t',mktime(0,0,0,$m,1,$y)); }
		if(file_exists($filename)) {
			$raw[$m][$y] = getnoaafile($filename);
			for($d = 1; $d <= $daysvalid; $d++) {
				$dd = fullify($d); $mm = fullify($m,2);
				$dmyID = mktime(1,1,1,$m,$d,$y)/mktime()/1000;
				
				$rawtmin[$dd.$mm.$y] = $rawtmin2[date('d M Y',mktime(1,1,1,$m,$d,$y))] = $raw[$m][$y][$d-1][4] + $dmyID;
				$rawtminM[$m][$dd.$mm.$y] = $rawtminM2[$m][date('d M Y',mktime(1,1,1,$m,$d,$y))] = $rawtmin[$dd.$mm.$y];
				
				$rawtmax[$dd.$mm.$y] = $rawtmax2[date('d M Y',mktime(1,1,1,$m,$d,$y))] = $raw[$m][$y][$d-1][2] + $dmyID;
				$rawtmaxM[$m][$dd.$mm.$y] = $rawtmaxM2[$m][date('d M Y',mktime(1,1,1,$m,$d,$y))] = $rawtmax[$dd.$mm.$y];
				
				$rawtmean[$dd.$mm.$y] = $rawtmean2[date('d M Y',mktime(1,1,1,$m,$d,$y))] = $raw[$m][$y][$d-1][1] + $dmyID;
				$rawtmeanM[$m][$dd.$mm.$y] = $rawtmeanM2[$m][date('d M Y',mktime(1,1,1,$m,$d,$y))] = $rawtmean[$dd.$mm.$y];
				
				$rawtrange[$dd.$mm.$y] = $rawtrange2[date('d M Y',mktime(1,1,1,$m,$d,$y))] = $rawtmax[$dd.$mm.$y] - $rawtmin[$dd.$mm.$y] + $dmyID;
				$rawtrangeM[$m][$dd.$mm.$y] = $rawtrangeM2[$m][date('d M Y',mktime(1,1,1,$m,$d,$y))]= $rawtrange[$dd.$mm.$y];
			}
		}
	}
}

$dd = date('d'); $mm = date('m'); $yy = date('Y'); $d = date('j'); $m = date('n'); //Add today's values to the array
$t_tmin = floatval($mintemp) + mktime(1,1,1,$mm,$dd,$yy)/mktime()/1000;
$rawtmin[$dd.$mm.$yy] = $rawtmin2[date('d M Y',mktime(1,1,1,$mm,$dd,$yy))] = $t_tmin;
$rawtminM[$m][$dd.$mm.$yy] = $rawtminM2[$m][date('d M Y',mktime(1,1,1,$mm,$dd,$yy))] = $t_tmin;
$t_tmax = floatval($maxtemp) + mktime(1,1,1,$mm,$dd,$yy)/mktime()/1000;
$rawtmax[$dd.$mm.$yy] = $rawtmax2[date('d M Y',mktime(1,1,1,$mm,$dd,$yy))] = $t_tmax;
$rawtmaxM[$m][$dd.$mm.$yy] = $rawtmaxM2[$m][date('d M Y',mktime(1,1,1,$mm,$dd,$yy))] = $t_tmax;
$t_tmean = floatval($avtempsincemidnight) + mktime(1,1,1,$mm,$dd,$yy)/mktime()/1000;
$rawtmean[$dd.$mm.$yy] = $rawtmean2[date('d M Y',mktime(1,1,1,$mm,$dd,$yy))] = $t_tmean;
$rawtmeanM[$m][$dd.$mm.$yy] = $rawtmeanM2[$m][date('d M Y',mktime(1,1,1,$mm,$dd,$yy))] = $t_tmean;
$t_trange = $t_tmax - $t_tmin + mktime(1,1,1,$mm,$dd,$yy)/mktime()/1000;
$rawtrange[$dd.$mm.$yy] = $rawtrange2[date('d M Y',mktime(1,1,1,$mm,$dd,$yy))] = $t_trange;
$rawtrangeM[$m][$dd.$mm.$yy] = $rawtrangeM2[$m][date('d M Y',mktime(1,1,1,$mm,$dd,$yy))] = $t_trange;

$oldt = file($absRoot.'extraTdata.csv'); // Special addition of available 2008 data
for($l = 0; $l < count($oldt); $l++) {
	$oldtl = explode(',', $oldt[$l]);
	$dd = fullify($oldtl[1]); $mm = fullify($oldtl[0],2); $yy = $y = 2008; $dmyID = mktime(1,1,1,$m,$d,$y)/mktime()/1000; $d = intval($dd); $m = intval($mm);
	if(intval($oldtl[0]) != 1) {
		$rawtmin[$dd.$mm.$yy] = $rawtmin2[date('d M Y',mktime(1,1,1,$m,$d,$y))] = floatval($oldtl[2]) + $dmyID;
		$rawtminM[$m][$dd.$mm.$yy] = $rawtminM2[$m][date('d M Y',mktime(1,1,1,$m,$d,$y))] = $rawtmin[$dd.$mm.$yy];
		$rawtmax[$dd.$mm.$yy] = $rawtmax2[date('d M Y',mktime(1,1,1,$m,$d,$y))] = floatval($oldtl[3]) + $dmyID;
		$rawtmaxM[$m][$dd.$mm.$yy] = $rawtmaxM2[$m][date('d M Y',mktime(1,1,1,$m,$d,$y))] = $rawtmax[$dd.$mm.$yy];
		$rawtmean[$dd.$mm.$yy] = $rawtmean2[date('d M Y',mktime(1,1,1,$m,$d,$y))] = floatval($oldtl[4]) + $dmyID;
		$rawtmeanM[$m][$dd.$mm.$yy] = $rawtmeanM2[$m][date('d M Y',mktime(1,1,1,$m,$d,$y))] = $rawtmean[$dd.$mm.$yy];
		$rawtrange[$dd.$mm.$yy] = $rawtrange2[date('d M Y',mktime(1,1,1,$m,$d,$y))] = $rawtmax[$dd.$mm.$yy] - $rawtmin[$dd.$mm.$yy] + $dmyID;
		$rawtrangeM[$m][$dd.$mm.$yy] = $rawtrangeM2[$m][date('d M Y',mktime(1,1,1,$m,$d,$y))] = $rawtrange[$dd.$mm.$yy];
	}
}
//End data collection

$length = array(count($rawtmin), count($rawtmax), count($rawtmean), count($rawtrange)); //Count and sort arrays
sort($rawtmin); sort($rawtmax); sort($rawtmean); sort($rawtrange);
for($m = 1; $m <= 12; $m++) {
	$lengthM[$m] = array(count($rawtminM[$m]), count($rawtmaxM[$m]), count($rawtmeanM[$m]), count($rawtrangeM[$m]));
	sort($rawtminM[$m]); sort($rawtmaxM[$m]); sort($rawtmeanM[$m]); sort($rawtrangeM[$m]);
}

$all_ranks = 'View All';
$rank_lengths = array(10,50,100,250,500,1000,$all_ranks); //Customise number of records to display
if(isset($_GET['length'])) { $cust_length = $_GET['length']; } elseif(isset($_COOKIE['length'])) { $cust_length = $_COOKIE['length']; } else { $cust_length = $rank_lengths[1]; }
$rank_lengthsM = array(5,10,25,50,75,100,$all_ranks);
if(isset($_GET['lengthM'])) { $cust_lengthM = $_GET['lengthM']; } elseif(isset($_COOKIE['lengthM'])) { $cust_lengthM = $_COOKIE['lengthM']; } else { $cust_lengthM = $rank_lengthsM[1]; }

$t_quantity = array($t_tmin, $t_tmax, $t_tmean, $t_trange); //Set up column headings (min, max, mean, range)
$quantity = array($rawtmin, $rawtmax, $rawtmean, $rawtrange);
$quantity2 = array($rawtmin2, $rawtmax2, $rawtmean2, $rawtrange2);
for($m = 1; $m <= 12; $m++) {
	$quantityM[$m] = array($rawtminM[$m], $rawtmaxM[$m], $rawtmeanM[$m], $rawtrangeM[$m]);
	$quantity2M[$m] = array($rawtminM2[$m], $rawtmaxM2[$m], $rawtmeanM2[$m], $rawtrangeM2[$m]); 
}
$quantity_name = array('Minima', 'Maxima', 'Means', 'Ranges');
$quantity_expl = array('Lowest temperature', 'Highest temperature', '24hr Mean temperature', 'Diurnal temperature range - the maximum minus minimum',
	' between midnight and midnight on the day in question');

$o_n = 0; $order_name = array('Highest','Lowest'); $ac_adj = $length[0]; //Customise rank up or down
$o_t = 1; $order_type = array('Descending','Ascending'); $d_a = 0; $disable = array('', 'disabled="disabled"');
if(isset($_GET['order'])) { if($_GET['order'] == 'Ascending') { $o_n = 1; $o_t = 0; $d_a = 1; $ac_adj = -1; } }
elseif(isset($_COOKIE['order'])) { if($_COOKIE['order'] == 'Ascending') { $o_n = 1; $o_t = 0; $d_a = 1; $ac_adj = -1; } }
echo '<table align="center" cellpadding="3"><tr><td align="center" class="rep">Settings</td></tr><tr><td align="center">
	<form method="get" action="">';
	for($f = 0; $f < 2; $f++) { echo '<input name="order" type="submit" value="', $order_type[$f],'" ', $disable[($d_a+1+$f)%2], ' />'; }
echo '</form></td></tr>';

$rank_type = array('Unsplit', 'Split by Month'); $d_a2 = 0; $monthly = 0; //Customise - allow monthly split
if(isset($_GET['rank_type'])) { if($_GET['rank_type'] == $rank_type[1]) { $monthly = 1; $d_a2 = 1; } }
elseif(isset($_COOKIE['rank_type'])) { if($_COOKIE['rank_type'] == $rank_type[1]) { $monthly = 1; $d_a2 = 1; } }
echo '<tr><td align="center"><form method="get" action="">';
	for($r = 0; $r < 2; $r++) { echo '<input name="rank_type" type="submit" value="', $rank_type[$r],'" ', $disable[($d_a2+1+$r)%2], ' /> '; }
echo '</form></td></tr>';

echo '<tr><td align="center"><form method="get" action="">';

if($monthly == 1) { //Rank by month
	if($cust_lengthM == $lengthM[12][0]) { $cust_lengthM = $all_ranks; }
	for($l = 0; $l < count($rank_lengthsM); $l++) {
		if($cust_lengthM == $rank_lengthsM[$l]) { $disable2 = 'disabled="disabled"'; } else { $disable2 = ''; }
		echo '<input name="lengthM" type="submit" value="', $rank_lengthsM[$l],'" ', $disable2, ' /> ';
	}
	echo '</form></td></tr><tr><td><b>Jump to: &nbsp; </b>';
	for($m = 1; $m <= 12; $m++) { echo ' <a href="#', $monthshort[$m], '">', $monthshort[$m], '</a> &nbsp; '; }
	echo '</td></tr></table><br />
		<b>NB:</b> Hover over a heading for an explanation of its meaning<br />Hover over a value for its rank within all the records';
	for($m = 1; $m <= 12; $m++) {
		if($cust_lengthM == $all_ranks || $cust_lengthM > $rank_lengthsM[count($rank_lengthsM)-1]) { $cust_lengthM = $lengthM[$m][0]; }
		echo '<h2>Records for ', $monthshort[$m],
			'</h2>Total number of records = <b>', $lengthM[$m][1],
			'</b> &nbsp; <a name="', $monthshort[$m], '" href="#top">Jump to top</a>',
			'<table align="center" class="table1" width="1000" cellpadding="3">
			<tr><th width="2%" class="labels">Rank</th>';
		for($t = 0; $t < 4; $t++) {
			if($m == 1) { $quantity_expl[$t] .= $quantity_expl[4]; }
			echo '<th width="12%" class="labels">', $order_name[$o_n], ' ',  acronym($quantity_expl[$t],$quantity_name[$t],1), '</th><th width="12%" class="labels">Date</th>';
		}
		echo '<th width="2%" class="labels">', acronym('The proportion of all data existing at a lower rank. 
 e.g.  95% for a maximum means that this maximum is '. str_replace('st', 'r', $order_name[$o_n]).' than 95% of all maxima recorded in that month', '%', 1), '</th></tr>';
		for($i = 0; $i < $cust_lengthM; $i++) {
			if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
			echo '<tr class="', $style, '"><td>', $i+1; if($i == 100) { echo '<a name="99"></a>'; } echo '</td>'; 
			for($t = 0; $t < 4; $t++) {
				$order = $lengthM[$m][$t]-$i-1; if(isset($_GET['order'])) { if($_GET['order'] == 'Ascending') { $order = $i; } }
				elseif(isset($_COOKIE['order'])) { if($_COOKIE['order'] == 'Ascending') { $order = $i; } }
				if($quantityM[$m][$t][$order] == $t_quantity[$t]) { $style2 = 'style="font-size:120%; font-weight:bold"'; } else { $style2 = ''; }
				echo '<td ', $style2, ' class="', ValueColor3(conv($quantityM[$m][$t][$order],1,0)), '">',
					acronym(abs(-array_search($quantityM[$m][$t][$order], $quantity[$t])+$ac_adj), conv($quantityM[$m][$t][$order],1,0)), '</td>
					<td ', $style2, '>', array_search($quantityM[$m][$t][$order], $quantity2M[$m][$t]), '</td>';
			}
			echo '<td style="border-left: 2px solid #2A0A1B">', conv3(1-(($i+1)/$lengthM[$m][1]),6,0), '</td></tr>';
		}
		echo '</table>';
	}
}

else { //Rank all together
	for($l = 0; $l < count($rank_lengths); $l++) {
		if($cust_length == $rank_lengths[$l]) { $disable2 = 'disabled="disabled"'; } else { $disable2 = ''; }
		echo '<input name="length" type="submit" value="', $rank_lengths[$l],'" ', $disable2, ' /> ';
	}
	echo '</form></td></tr></table><br /><b>NB:</b> Hover over a heading for an explanation of its meaning';
	if($cust_length == $all_ranks) { $cust_length = $length[0]; }
	echo '<h2>Records</h2>Total number of records = <b>', $length[1], ' </b> (20 Mar 2008 - ', date('d M Y',mktime()),')';
	echo '<table align="center" class="table1" width="1000" cellpadding="3">
		<tr><th width="2%" class="labels">Rank</th>';
	for($t = 0; $t < 4; $t++) {
		$quantity_expl[$t] .= $quantity_expl[4];
		echo '<th width="12%" class="labels">', $order_name[$o_n], ' ',  acronym($quantity_expl[$t],$quantity_name[$t],1), '</th><th width="12%" class="labels">Date</th>';
	}
	echo '<th width="2%" class="labels">', acronym('The proportion of all data existing at a lower rank. 
 e.g.  95% for a maximum means that this maximum is '. str_replace('st', 'r', $order_name[$o_n]).' than 95% of all maxima recorded', '%', 1), '</th></tr>';
	for($i = 0; $i < $cust_length; $i++) {
		if($i % 2 == 0) { $style = 'column-light'; } else { $style = 'column-dark'; }
		echo '<tr class="', $style, '"><td>', $i+1; if($i == 100) { echo '<a name="99"></a>'; } echo '</td>'; 
		for($t = 0; $t < 4; $t++) {
			$order = $length[$t]-$i-1; if(isset($_GET['order'])) { if($_GET['order'] == 'Ascending') { $order = $i; } }
			elseif(isset($_COOKIE['order'])) { if($_COOKIE['order'] == 'Ascending') {$order = $i; } }
			if($quantity[$t][$order] == $t_quantity[$t]) { $style2 = 'style="font-size:120%; font-weight:bold"'; } else { $style2 = ''; }
			echo '<td ', $style2, ' class="', ValueColor3(conv($quantity[$t][$order],1,0)), '">', conv($quantity[$t][$order],1,0), '</td>
				<td ', $style2, '>', array_search($quantity[$t][$order], $quantity2[$t]), '</td>';
		}
		echo '<td style="border-left: 2px solid #2A0A1B">', conv3(1-(($i+1)/$length[1]),6,0), '</td></tr>';
	}
	echo '</table>';
}
//print_r($rawtmin);
function ValueColor3($value) {
	global $tempvalues;
	$limit = count($tempvalues);
	if ($value < $tempvalues[0]) {
	return 'level3_1';
	}
	for ($i = 1; $i < $limit; $i++){
		if ($value < $tempvalues[$i]) {
		return 'level3_'.($i+1);
		}
	}
	return 'level3_'.($limit+1);
}
?>
</div></div>

<!-- ##### Footer ##### -->
<? require('footer.php'); ?>
</body>
</html>