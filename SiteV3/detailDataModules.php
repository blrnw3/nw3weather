<?php

class DetailedDataModule {

	private $type;
	private $letter;
	private $conv;
	private $cssClass;
	private $label;

	public static $periods = array('7','m','31','y','365','a','mr','dr');
	public static $measuresGeneric = array('Lowest Min','Highest Max','Highest Min','Lowest Max','Lowest Mean','Highest Mean','Averages','Mean','Mean Minimum','Mean Maximum');

	public $periods_values;
	public $periods_all;
	public static $periodCnt;

	private $superlativeHigh;
	private $superlativeLow;

	function __construct($type) {
		global $dmonth, $dday, $months;

		$this->type = $type;

		$daytypes = array('temp' => 6, 'hum' => 7, 'dew' => 9, 'rain' => 10, 'baro' => 8, 'wdir' => 11, 'gust' => 4, 'wind' => 3);
		$index = $daytypes[$type];
		$tconv = array(false,false,false, 4,4,false, 1,5,3, 1,2);
		$classes = array(0,0,0, 13,13,13, 14,10,16, 10,12);
		$names = array('temp' => 'Temperature', 'hum' => 'Humidity', 'dew' => 'Dew Point', 'rain' => 'Rainfall', 'baro' => 'Pressure',
			'wdir' => 'Wind Direction', 'gust' => 'Gust', 'wind' => 'Wind Speed');
		$superlativeHighs = array('','','', 'Windiest','Gustiest','', 'Warmest','Most Humid','Highest', 'Most Humid','Wettest');
		$superlativeLows = array('','','', 'Calmest','Calmest','', 'Coldest','Least Humid','Lowest', 'Least Humid','Driest');

		$this->letter = ($index == 8) ? 'p' : substr($type, 0, 1);

		$this->conv = $tconv[$index];
		$this->cssClass = 'td'.$classes[$index];
		$this->label = $names[$type];

		$this->superlativeHigh = $superlativeHighs[$index];
		$this->superlativeLow = $superlativeLows[$index];

		$periods_keys = array('d','b','7','m','31','y','365','a','mr','dr','7cum','Ma','Mmr','31cum','Ya','365cum');
		$periods_values = array('Today','Yesterday','7-day','Month','31-day','Year','365-day','Overall',
			$months[$dmonth-1], datefull($dday).' ' .monthfull($dmonth), '7-day', 'Month', $months[$dmonth-1], '31-day', 'Year', '365-day');
		$this->periods_all = array_combine($periods_keys, $periods_values);
		self::$periodCnt = count(self::$periods);
	}

	/**
	 * Alternate background row-colouring in a table
	 * @param type $i
	 * @return string row light or dark
	 */
	private static function rowColour($i) {
		$type = ($i % 2 === 0) ? 'light' : 'dark';
		return "row".$type;
	}

	/**
	 * Makes a "current/latest" table
	 * @param int $num css class number for tdx
	 * @param array $measures names of vars
	 * @param array $values vars
	 * @param array $convs conv types
	 * @param int $wid [= 42] table width
	 */
	public function currentLatest($measures, $values, $convs, $wid = 42) {
		$cnt = count($measures);

		table(null, $wid . '%" align="left', 5);
		tableHead("Current", 2);

		tr();
		td("Measure", $this->cssClass, "59%");
		td("Value", $this->cssClass, "41%");
		tr_end();

		for ($r = 0; $r < $cnt; $r++) {
			tr(self::rowColour($r));
			td($measures[$r], $this->cssClass);
			td(conv($values[$r], $convs[$r]), $this->cssClass);
			tr_end();
		}

		table_end();
	}

	function recentAvgsExtrms($wid = 56) {
		global $dnm, $mmmr, $NOW, $mappingsToDailyDataKey, $lta, $dz, ${$this->letter.'datYest'}, ${$this->letter.'datToday'};

		$key = $mappingsToDailyDataKey[$this->letter];

		table(null, $wid . '%" align="center', 6);
		tableHead("Recent Averages and Extremes");

		tr();
		td("Measure", $this->cssClass, "30%");
		td("Today", $this->cssClass, "35%");
		td("Yesterday", $this->cssClass, "35%");
		tr_end();

		for ($r = 0; $r < 3; $r++) {
			$currVal = $NOW[$dnm[$r]][$key];

			$anom = $this->letter == 't' ? ' (' . conv($tdatToday[2][$dnm[$r]], 1.1,1,1) . ')' : '';
			$anomY = $this->letter == 't' ? ' (' . conv($tdatYest[2][$dnm[$r]], 1.1,1,1) . ')' : '';

			tr(self::rowColour($r));
			td($GLOBALS['mmmFull'][$r], $this->cssClass);
			td( '<b>'. conv($currVal, $this->conv) .'</b>'. $anom .'<br />'. $NOW['time'.$mmmr[$r]][$key], $this->cssClass );
			td( '<b>'. conv(${$this->letter.'datYest'}[0][$dnm[$r]], $this->conv) .'</b>'. $anomY .'<br />'. ${$this->letter.'datYest'}[1][$r], $this->cssClass );
			tr_end();
		}

		table_end();

		echo '<img style="margin:5px;" src="graphdayA.php?type='.$this->type.'&amp;x=495&amp;y=150&amp;ts=18&amp;nofooter" width="495" height="150" alt="Last 6hrs  London'.$this->label.'" />';
	}

	function avgsExtrmsRecs($measures = null, $wid = 99) {
		$dat =  $GLOBALS[$this->letter.'dat'];
		$measures = is_null($measures) ? self::$measuresGeneric : $measures;
		$values = array($dat['min'][0],$dat['max'][1],$dat['min'][1],$dat['max'][0],$dat['mean'][0],$dat['mean'][1],'---',$dat['mean'][2],$dat['min'][2],$dat['max'][2]);

		table(null, $wid . '%" align="center', 5);
		tableHead("Averages, Extremes and Records", self::$periodCnt + 1);

		tr();
		td("<b>Current</b>", $this->cssClass, null, 6);
		td('<b>Station Lifetime (09-'. ($GLOBALS['dyear']-2000) .	')</b>', $this->cssClass, null, 3);
		tr_end();

		tr();
		td("Measure", $this->cssClass, "8%");
		for($h = 0; $h < self::$periodCnt; $h++) {
			td($this->periods_all[self::$periods[$h]], $this->cssClass, '12%');
		}
		tr_end();

		for($r = 0; $r < count($measures); $r++) {
			tr( ($r === 6) ? 'table-top' : self::rowColour($r) );
			td(str_ireplace(' ','<br />',$measures[$r]), $this->cssClass);

			for($c = 0; $c < self::$periodCnt; $c++) {
				if($r != 6) {
					$anom = $this->letter == 't' ? '<br />(' . conv($values[$r][self::$periods[$c].'anom'], 1.1,0,1) . ')' : '';
					$date = ($values[$r][self::$periods[$c].'date'] != '') ? '<br />'. $values[$r][self::$periods[$c].'date'] : '';
					td( '<b>'. conv($values[$r][self::$periods[$c]], $this->conv) .'</b>'. $anom . $date, $this->cssClass );
				} else {
					td('&nbsp;', $this->cssClass);
				}
			}
			tr_end();
		}

		table_end();
	}

	function graph31dump() {
		echo '<img src="graph31.php?type='.$this->letter.'mean&amp;x=820&amp;y=275" width="820" height="275" alt="31-day chart of mean London'.$this->label.'" />
			<img src="graph31.php?type='.$this->letter.'min&amp;x=430&amp;y=275" width="430" height="275" alt="31-day chart of min London'.$this->label.'" />
			<img src="graph31.php?type='.$this->letter.'max&amp;x=430&amp;y=275" width="430" height="275" alt="31-day chart of max London'.$this->label.'" />';
	}

	function pastYearAvgsExtrms($measures = null, $wid = 99) {
		global $dmonth, $monthname, $dyear, $months3;

		$dat = $GLOBALS[$this->letter.'datMM'];
		$measures = is_null($measures) ? self::$measuresGeneric : $measures;
		$values = array($dat['min'][0],$dat['max'][1],$dat['min'][1],$dat['max'][0],$dat['mean'][0],$dat['mean'][1],'---',$dat['mean'][2],$dat['min'][2],$dat['max'][2]);

		table(null, $wid . '%" align="center', 6);
		tableHead("Past Year Monthly Averages and Extremes ($monthname " . ($dyear-1) . ' - ' . date('F Y',mkdate($dmonth-1,15)) .")", 13);

		tr();
		td("Extremes", $this->cssClass);
		for($h = 11; $h >= 0; $h--) {
			$ht = date('n',mkdate($dmonth-$h-1,15))-1;
			td($months3[$ht], $this->cssClass, '8%');
		}
		tr_end();

		for($r = 0; $r < count($measures); $r++) {
			tr( ($r === 6) ? 'table-top' : self::rowColour($r) );
			td(str_ireplace(' ','<br />',$measures[$r]), $this->cssClass);

			for($m = 11; $m >= 0; $m--) {
					$mt = date('n',mkdate($dmonth-$m-1,15))-1;
				if($r != 6) {
					if($values[$r][0][$mt] == $values[$r]['extr'][1]) { $colour = '" style="color:#DF7401'; }
					elseif($values[$r][0][$mt] == $values[$r]['extr'][0]) { $colour = '" style="color:#0101DF'; }
					else { $colour = ''; }

					$anom = $this->letter == 't' ? '<br />(' . conv($values[$r][2][$mt], 1.1,0,1) . ')' : '';
					$date = ($values[$r][1][$mt] != '') ? '<br />'. datefull($values[$r][1][$mt]) : '';
					td( '<b>'. conv($values[$r][0][$mt], $this->conv) .'</b>'. $anom . $date, $this->cssClass . $colour );
				} else {
					td($months3[$mt], $this->cssClass);
				}
			}
			tr_end();
		}

		table_end();
	}

	function seasonalAvgs($wid = 60) {
		global $dnm, $season, $dmonth, $dyear, $snames;

		$dat = $GLOBALS[$this->letter.'datSS'];
		$datAnom = $GLOBALS[$this->letter.'datSSanom'];

		table(null, $wid . '%" align="center', 6, true);
		tableHead("Past Year Seasonal Averages", 4);

		tr();
		td("Season", $this->cssClass, "22%");
		td("Daily Min", $this->cssClass, "26%");
		td("Daily Max", $this->cssClass, "26%");
		td("Mean", $this->cssClass, "26%");
		tr_end();

		for($i = 0; $i < 4; $i++) {
			$dfo1 = $dyear-2001; $dfo2 = $dyear-2000; $dfo3 = $dyear-2002;
			$nwint = ($i+1 < $season || $dmonth == 12) ? $dyear : $dyear-1;
			$wint = ($dmonth > 2) ? $dfo1 .'/' .$dfo2 : $dfo3 .'/' .$dfo1;
			$yr3 = array($wint, $nwint, $nwint, $nwint);
			$hlite = ($i+1 == $season-1) ? 'border-bottom:3px solid #8181F7' : '';


			tr(self::rowColour($i));
			td( $snames[$i] . ' ' . $yr3[$i], $this->cssClass . '" style="' . $hlite );

			for($j = 0; $j < 3; $j++) {
				$anom = $this->letter == 't' ? ' (' . conv($datAnom[$dnm[$j]][$i], 1.1,1,1) . ')' : '';
				td( conv($dat[$dnm[$j]][$i], $this->conv) . $anom .'<br />', $this->cssClass . '" style="' . $hlite );
			}
			tr_end();
		}

		table_end();
	}

	function graph12Dump() {
		global $yr_yest;
		echo '<p align="center">View daily tables of
			<a href="wxdataday.php?vartype='.$this->letter.'min">min</a> /
			<a href="wxdataday.php?vartype='.$this->letter.'max">max</a> /
			<a href="wxdataday.php?vartype='.$this->letter.'mean">mean</a>
			 '.$this->label.' data for the past year <br />View monthly tables of
			<a href="TablesDataMonth.php?vartype='.$this->letter.'min">min</a> /
			<a href="TablesDataMonth.php?vartype='.$this->letter.'max">max</a> /
			<a href="TablesDataMonth.php?vartype='.$this->letter.'mean">mean</a>
			 '.$this->label.' data for all months in the station history.
			</p>

			<h2>Latest charts and graphs of '. $this->label.'</h2>

			<h4>Past 24hrs and past 12 months trends for '. $this->label.'</h4>
			<img width="430" height="275" src="graphdayA.php?type='.$this->type.'&amp;x=430&amp;y=275" alt="Last 24hrs London '.$this->label.'" />
			<img width="430" height="275" src="graph12.php?type='.$this->letter.'mean&amp;x=430&amp;y=275&amp;lta" alt="12 month mean London '.$this->label.'" />
			<img width="430" height="275" src="graph12.php?type='.$this->letter.'min&amp;x=430&amp;y=275" alt="12month min London '.$this->label.'" />
			<img width="430" height="275" src="graph12.php?type='.$this->letter.'max&amp;x=430&amp;y=275" alt="12month max London '.$this->label.'" />
			<h4>Current year vs last year daily trends for '. $this->label.'</h4>
			<img width="865" height="475" src="graph_daily_trend.php?type='.$this->letter.'mean&amp;x=865&amp;y=475&amp;multiyr=last" alt="Daily London mean '.$this->label.' vs climate normals" />
			<h4>This year min/max daily trends in detail for '. $this->label.'</h4>
			<img width="430" height="290" src="graph_daily_trend.php?type='.$this->letter.'min&amp;x=430&amp;y=290&amp;year='.$yr_yest.'" alt="Current year daily London min '.$this->label.' vs climate normals" />
			<img width="430" height="290" src="graph_daily_trend.php?type='.$this->letter.'max&amp;x=430&amp;y=290&amp;year='.$yr_yest.'" alt="Current year daily London max '.$this->label.' vs climate normals" />
			<p><a href="charts.php">View more '.$this->label.' charts</a></p>
';

	}

	function recordPeriodAvgs($wid = 98) {
		$dat =  $GLOBALS[$this->letter.'dat'];
		$values = array($dat['mean'][0],$dat['mean'][1],$dat['min'][0],$dat['min'][1],$dat['max'][0],$dat['max'][1]);

		$periods = array('7cum','Ma','Mmr','31cum','Ya','365cum');
		$measures = array($this->superlativeLow,$this->superlativeHigh, 'Lowest Mean Daily-Min','Highest Mean Daily-Min', 'Lowest Mean Daily-Max','Highest Mean Daily-Max');

		table(null, $wid . '%" style="margin-bottom:28px;', 6);
		tableHead("Record Period Averages", 7);

		tr();
		td("Measure", $this->cssClass, "8%");
		for($h = 0; $h < count($periods); $h++) {
			td($this->periods_all[$periods[$h]], $this->cssClass, '15%');
		}
		tr_end();

		for($r = 0; $r < count($measures); $r++) {
			tr( self::rowColour($r) );
			td(str_ireplace(' ','<br />',$measures[$r]), $this->cssClass);

			for($c = 0; $c < count($periods); $c++) {
				td( '<b>'. conv($values[$r][$periods[$c]], $this->conv) .'</b><br />'. $values[$r][$periods[$c].'date'], $this->cssClass );
			}
			tr_end();
		}

		table_end();
	}

	private function rankTable($rankArray, $rankNum, $type, $highLow, $title, $alignLeft) {
		$align = $alignLeft ? "left" : "center";
		table("table1", '49%" align="'.$align);
		tableHead($title, 4);

		tr();
		td("Rank", $this->cssClass);
		td("Min", $this->cssClass);
		td("Max", $this->cssClass);
		td("Mean", $this->cssClass);
		tr_end();

		for($i = 1; $i <= $rankNum; $i++) {
			tr( self::rowColour($i) );
			td($i, $this->cssClass);
			for($j = 0; $j < 3; $j++) {
				td( conv($rankArray[$j][$type][$highLow][0][$i], $this->conv) . '<br />' . $rankArray[$j][$type][$highLow][1][$i], $this->cssClass );
			}
			tr_end();
		}
		table_end();

		$isEnd = $rankNum < 10;

		if(!$alignLeft && !$isEnd) {
			echo "<br /><br />";
		}
	}

	function rankTables() {
		$tranks = $GLOBALS[$this->letter . 'ranks'];
		echo '<h2>Ranked Historical '.$this->label.' Data</h2>';

		self::rankTable($tranks, $GLOBALS['rankNum'], 'daily', 1, $this->superlativeHigh ." Days", true);
		self::rankTable($tranks, $GLOBALS['rankNum'], 'daily', 0, $this->superlativeLow ." Days", false);
		self::rankTable($tranks, $GLOBALS['rankNumM'], 'monthly', 1, $this->superlativeHigh ." Months", true);
		self::rankTable($tranks, $GLOBALS['rankNumM'], 'monthly', 0, $this->superlativeLow ." Months", false);
		self::rankTable($tranks, $GLOBALS['rankNumCM'], 'dailyCM', 1, $this->superlativeHigh ." Days in ". $GLOBALS['monthname'], true);
		self::rankTable($tranks, $GLOBALS['rankNumCM'], 'dailyCM', 0, $this->superlativeLow ." Days in ". $GLOBALS['monthname'], false);
	}
}
?>