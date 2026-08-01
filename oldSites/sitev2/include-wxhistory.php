<?php
$website = $webdir;

if ( !isset($wxh_graphs_path) ) {
$wxh_graphs_path = $webdir;
}

$parts = array();
$extracts = array();
$LEVEL = 0;
$DAY = 0;

$month = '';
$monthnum = 0;
$yearnum = 0;

// Arrays to Format Things with
$level1 = array(
			array('Average temperature',3),
			array('Average humidity',3),
			array('Average dewpoint',3),
			array('Average barometer',3),
			array('Average windspeed',3),
			array('Average gustspeed',3),
			array('Average direction',3),
			array('Rainfall for month',4),
			array('Rainfall for year',4),
			array('Minimum pressure',3),
			array('Maximum dewpoint',3),
			array('Minimum dewpoint',3),
			array('Maximum pressure',3),
			array('Maximum windspeed',3),
			array('Maximum humidity',4),
			array('Minimum humidity',3),
			array('Avg daily min temp',5),
			array('Avg daily max temp',5),
			array('Frost days',3),
			);

$level2 = array(
			array(',ET', 2),
			array('Sunshine hours month to date',6),
			array('Sunshine hours year to date',6),
			array('in. on day',0),
			array('in.  on day',0),
			array('mm on day',0),
			array('mm  on day',0),
			);
			
$level3 = array(array('Maximum temperature',4),);
$level4 = array(array('Minimum temperature',4),);
$level5 = array(array('Rainfall for day',4));
$level6 = array(array('Maximum gust speed',4),);

function display_data($date_to_process){
	global $website, $wxh_graphs_path;
	global $level1, $level2, $level3, $level4, $level5, $level6, $LEVEL, $DAY, $month, $monthnum, $yearnum;

	$mnthname = array('January', 'February', 'March', 'April', 'May', 'June',
	'July', 'August', 'September', 'October', 'November', 'December');

	$mnthnameshort = array('Jan', 'Feb', 'March', 'April', 'May', 'June',
	'July', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');

	if ( strlen($date_to_process) == 0 ) {
		# defaults to this year month
		$date_to_process = date('Y') . date('m');
	}
	$monthnum = substr($date_to_process,4,2);
	$yearnum = substr($date_to_process,0,4);
	if($yearnum < date('Y')) { $wxh_graphs_path = './' . $yearnum . '/'; }
	$datafile = $website . $mnthname[$monthnum -1 ] . $yearnum . ".htm";

	$fd = fopen($datafile,'r');

	$LEVEL = 0;
	$DAY = 0;
	$RAIN = 0;
	$even_odd = 0;
	$ignore = 0;
	$special_msg = '';

	if ($fd ) {
		while ( !feof($fd) ){
			$gotraw = fgets($fd,8192);
			$value = trim($gotraw);

			if ( preg_match("|Any Special weather conditions for the day:|i",$gotraw)) {  // Ignore special weather conditions
				$ignore = 1;                                                              // entered by users
				$value = $gotraw = $special_msg = '';
			} else {
				if (($ignore == 1) AND preg_match("|<FONT COLOR|i",$gotraw)){
					$ignore = 0;
					$special_class = array('class="column-light"','class="column-dark"');
					$special_class = $special_class[$DAY % 2];
					echo '<tr><td colspan="2" ',$special_class,'>'.$special_msg.'</td></tr>';
				} else {
					if ($ignore == 1){
						if (!preg_match("|<img |i",$gotraw)){
						$special_msg = $special_msg.$gotraw;
						}
					$value = $gotraw = '';
					}
				}
			}

			if ($LEVEL == 0 ) {
				if ( preg_match("|Daily report for |i",$value)) {
					# mchallis page title consistancy fix ....
					# January2007.htm page title was "Daily Report for the month of January 2007"
					# and January2008.htm page title was "Daily Report for the month of 01 2007"
#                     preg_match("/Daily report for (.*)\</U",$value,$found);
					#$month = $found[1];
					$month = $mnthname[$monthnum -1 ] . " $yearnum";
					echo "<h2>Breakdown for " . $month . "</h2>\n";
					$LEVEL = 1;
				}
			}

			if ($LEVEL == 1 ) {
				// Check for Day String
				if ( strpos ($value,"Extremes for day :") !== false ) {

					// Must already have been processing a day so close table
					if ( $DAY != 0 ) {
						echo "</table>\n";

						$this_img = $yearnum . $monthnum . $DAY;
						
						if (is_file("$wxh_graphs_path$this_img.gif")){
						echo '
<p onclick="toggleDisplay(\'img_\' + '.$this_img.');">
<u>Click here to toggle the 24 Hour Graph of this day</u></p>
<img src="'. $wxh_graphs_path . $this_img .'.gif" id="img_'.$this_img .'" style="display: none" onclick="toggleDisplay(\'img_\' + '.$this_img.');" alt="24 Hour Graph for Day '.$DAY.'" title="24 Hour Graph for Day '.$DAY.'" />' . "\n";
						} else {
							echo "24 Hour Graph of this day is not available <br/>" . "\n";
						}
					}
					
					preg_match("/Extremes for day :(\d{1,2})\</",$value,$found);
					$DAY = $found[1];

				echo '<br /> <a name="', round($DAY), '"></a>
					<table width="600" cellpadding="3" cellspacing="0" border="0">
					<tr class="table-top">
					<td>', datefull($DAY), ' ', $mnthnameshort[$monthnum -1 ], ' Averages and Extremes</td><td align=right><a href="#header">Top</a></td>
					</tr>
					';

				}
				// Check for Month Recap
				if ( strpos ($value, "Extremes for the month of ") !== false ) {
					echo "</table>\n";

						$this_img = $yearnum . $monthnum . $DAY;
						# mchallis added if is_file check
						if (is_file("$wxh_graphs_path$this_img.gif")){
						echo '
<p onclick="toggleDisplay(\'img_\' + '.$this_img.');">
<u>Click here to toggle the 24 Hour Graph of this day</u></p>
<img src="'. $wxh_graphs_path . $this_img .'.gif" id="img_'.$this_img .'" style="display: none" onclick="toggleDisplay(\'img_\' + '.$this_img.');" alt="24 Hour Graph for Day '.$DAY.'" title="24 Hour Graph for Day '.$DAY.'" />' . "\n";

						} else {
							echo "24 Hour Graph of this day is not available <br/> <br/>" . "\n";
						}
						
					echo '<br /> <hr> <a name="summary"></a> <br />
					<table width="600" cellpadding="3" cellspacing="0" border="0">
					<tr class="table-top">
					<td>', $month, ' summary</td><td align=right><a href="#header">Top</a></td>
					</tr>
					';
				}

				// Check for Daily Rain Totals (in case there was no Sunshine)
				if ( strpos ($value, "Daily rain totals" ) !== false ) {
					echo "</table>\n";

					echo '<br /><br />
					<table width="300" cellpadding="3" cellspacing="0" border="0">
					<tr class="table-top">
					<td colspan="2">Daily Rain Totals</td>
					</tr>
					';
					$LEVEL = 2;
					$RAIN = 1;
				}

				if(preg_match('|Avg daily \S+ temp :|is',$value)) {
					$value = preg_replace('|\:|','  =',$value);
				}
				output_data($value, $LEVEL);
			}

			if ($LEVEL == 2 ) {
				// Check for Daily Rain Totals (in case there was no Sunshine)

				if ( strpos ($value, "Daily rain totals" ) !== false  && ! $RAIN ) {
					echo "</table>\n";

					echo '<br /><br />
					<table width="300" cellpadding="3" cellspacing="0" border="0">
					<tr class="table-top">
					<td colspan="2">Daily Rain Totals</td>
					</tr>
					';
				}

				if ( strpos (strtolower($value), "</body>" ) !== false ) {
					echo "</table>\n";
					$LEVEL = 3;
				}
				// Check for data lines
				output_data($value, $LEVEL);
			}
		}
	}
}

function output_data($invalue, $LEVEL ) {
	global $website, $even_odd;
	global $level1, $level2, $level3, $level4, $level5, $level6, $LEVEL, $DAY, $month, $monthnum, $yearnum;

	$row_class = '';
	$even_class = 'class="column-light"';
	$odd_class = 'class="column-dark"';

	// Remove ALL html tag info...
		$newval = preg_replace("'<[/!]*?[^<>]*?>'si","",$invalue);

	if ($LEVEL == 1 ) {

		foreach ($level1 as $key => $kvalue ) {
			if ( strpos ( strtolower($newval), strtolower($kvalue[0]) ) !== false ) {
				list ($left, $right ) = getparts($newval,$kvalue[1]);
				$left = preg_replace('/=/','',$left);
				$right = preg_replace('/=/','',$right);
				# mjc added for testing
#                  if (preg_match("/Maximum gust speed/i",$left)){
#                      $left = "<b>$left</b>";
#                      $right = "<b>$right</b>";
#                  }
				# eof

			if ($even_odd % 2){
					$row_class = $odd_class;
			}
			else {
					$row_class = $even_class;
			}

				echo '<tr '.$row_class.'>
				<td>'.$left .'</td><td>' .$right ."</td>
				</tr>\n";

				$even_odd++;
			}
		}
		
		foreach ($level3 as $key => $kvalue ) {
			if ( strpos ( strtolower($newval), strtolower($kvalue[0]) ) !== false ) {
				list ($left3, $right3 ) = getparts($newval,$kvalue[1]);
				$left3 = preg_replace('/=/','',$left3);
				$right3 = preg_replace('/=/','',$right3);

			if ($even_odd % 2){
					$row_class = $odd_class;
			}
			else {
					$row_class = $even_class;
			}

				echo '<tr '.$row_class.'>
				<td class=tdTmax>'.$left3 .'</td><td class=tdTmax>' .$right3 ."</td>
				</tr>\n";

				$even_odd++;
			}
		}
		
		foreach ($level4 as $key => $kvalue ) {
			if ( strpos ( strtolower($newval), strtolower($kvalue[0]) ) !== false ) {
				list ($left4, $right4 ) = getparts($newval,$kvalue[1]);
				$left4 = preg_replace('/=/','',$left4);
				$right4 = preg_replace('/=/','',$right4);

			if ($even_odd % 2){
					$row_class = $odd_class;
			}
			else {
					$row_class = $even_class;
			}

				echo '<tr '.$row_class.'>
				<td class=tdTmin>'.$left4 .'</td><td class=tdTmin>' .$right4 ."</td>
				</tr>\n";

				$even_odd++;
			}
		}
		
		foreach ($level5 as $key => $kvalue ) {
			if ( strpos ( strtolower($newval), strtolower($kvalue[0]) ) !== false ) {
				list ($left5, $right5 ) = getparts($newval,$kvalue[1]);
				$left5 = preg_replace('/=/','',$left5);
				$right5 = preg_replace('/=/','',$right5);

			if ($even_odd % 2){
					$row_class = $odd_class;
			}
			else {
					$row_class = $even_class;
			}

				echo '<tr '.$row_class.'>
				<td class=tdRain>'.$left5 .'</td><td class=tdRain>' .$right5 ."</td>
				</tr>\n";

				$even_odd++;
			}
		}
		
		foreach ($level6 as $key => $kvalue ) {
			if ( strpos ( strtolower($newval), strtolower($kvalue[0]) ) !== false ) {
				list ($left6, $right6 ) = getparts($newval,$kvalue[1]);
				$left6 = preg_replace('/=/','',$left6);
				$right6 = preg_replace('/=/','',$right6);

			if ($even_odd % 2){
					$row_class = $odd_class;
			}
			else {
					$row_class = $even_class;
			}

				echo '<tr '.$row_class.'>
				<td class=tdWind>'.$left6 .'</td><td class=tdWind>' .$right6 ."</td>
				</tr>\n";

				$even_odd++;
			}
		}
	}

	if ($LEVEL == 2 ) {

			// Strip out = sign from line
			if (strpos($newval,'Sunshine hours ') !== false) {
					$newval = preg_replace('/=/','',$newval);
			}

		foreach ($level2 as $key => $kvalue ) {
			if ( strpos ( strtolower($newval), strtolower($kvalue[0]) ) !== false ) {

			if ($even_odd % 2){
					$row_class = $odd_class;
			}
			else {
					$row_class = $even_class;
			}
			if (strpos($newval,',ET') !== false) {
				$sunshine_vals = explode(',', $newval);
				echo '<tr '.$row_class.'>
				<td>'.$sunshine_vals[0] .'</td>
				<td>'.$sunshine_vals[1] .'</td>
				<td>'.$sunshine_vals[2] .'</td>
				<td>'.$sunshine_vals[3] ."</td>
				</tr>\n";
			}else if(strpos($newval,'Sunshine hours ') !== false){
				echo '<tr '.$row_class.'>
				<td colspan="4">'.$newval ."</td>
				</tr>\n";
			}
			else{
				echo '<tr '.$row_class.'>
				<td>'.$newval ."</td>
				</tr>\n";
			}
				$even_odd++;
			}
		}
	}
}

function getparts($val, $number) {
	$retval = array();
	$collect = '';

	$splits = explode(" ", $val, $number);

	for ($i = 0 ; $i < ($number -1) ; $i++ ) {
		$collect .= $splits[$i] . ' ';
	}
	$retval[0] = trim($collect);
	$retval[1] = ltrim($splits[$i]);
	return($retval);
}
?>