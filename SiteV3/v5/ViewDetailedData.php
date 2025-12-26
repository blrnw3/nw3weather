<?php

/**
* This depends heavily on precomputed data in cron_tags.php to make it fast.
 */
class ViewDetailedData {

	private $group;
	private $groupName;
	private $conv;
	private $getAnom;

	private $datMins;
	private $datMaxs;
	private $datMeans;

	public static $periods = array('latest_7d','curr_month','latest_31d','curr_year','latest_365d','alltime','all_this_month','all_this_date');
	public static $measuresGeneric = array('Lowest Min','Highest Max','Highest Min','Lowest Max','Lowest Mean','Highest Mean','Averages','Mean','Avg Low','Avg High');

	public $periods_values;
	public $periods_all;
	public static $periodCnt;


	function __construct($groupName) {
		$groups = [
			"temp" => [
				"name" => "Temperature",
				"unit" => Wx::Temperature,
				"var_min" => Wx::$daily["tmin"],
				"var_max" => Wx::$daily["tmax"],
				"var_mean" => Wx::$daily["tmean"],
				"superlativeLo" => "Coldest",
				"superlativeHi" => "Warmest",
				"letter" => "t",
				"class" => 14
			],
			// TODO more

		];
		$this->groupName = $groupName;
		$this->group = $groups[$groupName];
		$this->conv = $this->group["unit"];
		$this->getAnom = array_key_exists("anomaly", $this->group);

		$this->datMins = new DataSummarizer($this->group["var_min"]);
		$this->datMaxs = new DataSummarizer($this->group["var_max"]);
		$this->datMeans = new DataSummarizer($this->group["var_max"]);

		// $daytypes = array('temp' => 6, 'hum' => 7, 'dew' => 9, 'rain' => 10, 'baro' => 8, 'wdir' => 11, 'gust' => 4, 'wind' => 3);
		// $index = $daytypes[$type];
		// $tconv = array(false,false,false, Wx::Wind,Wx::Wind,false, Wx::Temperature,Wx::Humidity,Wx::Pressure, Wx::Temperature,Wx::Rain);
		// $classes = array(0,0,0, 13,13,13, 14,10,16, 10,12);
		// $names = array('temp' => 'Temperature', 'hum' => 'Humidity', 'dew' => 'Dew Point', 'rain' => 'Rainfall', 'baro' => 'Pressure',
		// 	'wdir' => 'Wind Direction', 'gust' => 'Gust', 'wind' => 'Wind Speed');
		// $superlativeHighs = array('','','', 'Windiest','Gustiest','', 'Warmest','Most Humid','Highest', 'Most Humid','Wettest');
		// $superlativeLows = array('','','', 'Calmest','Calmest','', 'Coldest','Least Humid','Lowest', 'Least Humid','Driest');

		// $this->letter = ($index == 8) ? 'p' : substr($type, 0, 1);

		// $this->conv = $tconv[$index];
		// $this->cssClass = 'td'.$classes[$index];
		// $this->label = $names[$type];

		// $this->superlativeHigh = $superlativeHighs[$index];
		// $this->superlativeLow = $superlativeLows[$index];

		// $periods_keys = array('d','b','7','m','31','y','365','a','mr','dr','7cum','Ma','Mmr','31cum','Ya','365cum');
		// $periods_values = array('Today','Yesterday','7-day','Month','31-day','Year','365-day','Overall',
		// 	Date::$months[Date::$dmonth-1], Date::datefull(Date::$dday).' ' .Date::monthfull(Date::$dmonth), '7-day', 'Month', Date::$months[Date::$dmonth-1], '31-day', 'Year', '365-day');
		$this->periods_all = [
			"today" => "Today",
			"yest" => "Yesterday",
			"curr_month" => Date::$months[Date::$dmonth-1],
			// TODO  etc.
		];
		self::$periodCnt = count(self::$periods);
	}

	/**
	 * Makes a "current/latest" table
	 * @param int $num css class number for tdx
	 * @param array $measures names of vars
	 * @param array $values vars
	 * @param array $convs conv types
	 * @param int $wid [= 42] table width
	 */
	public function currentLatest($measures, $values, $convs) {
		$cnt = count($measures);
		echo "<h2>Current/Latest conditions</h1>";
		
		echo '<div class="detail-grid">';
		echo ' <div class="kv-table">';
		for ($r = 0; $r < $cnt; $r++) {
			echo '<div class="'. Html::colcol($r) .'">';
			echo '<div>'. $measures[$r] .'</div>';
			echo '<div>'. Wx::conv($values[$r], $convs[$r]) .'</div>';
			echo '</div>';
		}
		echo ' </div>';
		echo '<div class="detail-graph">';
		echo '  <img style="margin:5px;" src="/graphdayA.php?type='.$this->groupName.'&amp;x=600&amp;y=300&amp;ts=12&amp;nofooter" width="600" height="300" alt="Last 12hrs London nw3 '.$this->label.'" />';
		echo '</div>';
		echo '</div>';

		$data = [
			[
				'label' => "Today's Low",
				'value' => Wx::Conv($this->datMins["period_summaries"]["today"]["val"], $this->conv),
				'time' => $this->datMins["period_summaries"]["today"]["time"],
				'anomaly' => $this->getAnom ? Wx::conv($datToday[2]['min'], Wx::AbsTemp,0,1) : null,
			],
			[
				'label' => "Today's High",
				'value' => Wx::Conv($this->datMaxs["period_summaries"]["today"]["val"], $this->conv),
				'time' => $this->datMaxs["period_summaries"]["today"]["time"],
				'anomaly' => $this->getAnom ? Wx::conv($datToday[2]['max'], Wx::AbsTemp,0,1) : null,
			],
			[
				'label' => "Today's Mean",
				'value' => Wx::Conv($this->datMeans["period_summaries"]["today"]["val"], $this->conv),
				'time' => null,
				'anomaly' => $this->getAnom ? Wx::conv($datToday[2]['mean'], Wx::AbsTemp,0,1) : null,
			],
			[
				'label' => "Yesterday's Low",
				'value' => Wx::Conv($datYest[0]['min'], $this->conv),
				'time' => $datYest[1][0],
				'anomaly' => $this->letter == 't' ? Wx::conv($datYest[2]['min'], Wx::AbsTemp,0,1) : null,
			],
			[
				'label' => "Yesterday's High",
				'value' => Wx::Conv($datYest[0]['max'], $this->conv),
				'time' => $datYest[1][1],
				'anomaly' => $this->letter == 't' ? Wx::conv($datYest[2]['max'], Wx::AbsTemp,0,1) : null,
			],
			[	
				'label' => "Yesterday's Mean",	
				'value' => Wx::Conv($datYest[0]['mean'], $this->conv),
				'time' => $datYest[1][2],
				'anomaly' => $this->letter == 't' ? Wx::conv($datYest[2]['mean'], Wx::AbsTemp,0,1) : null,
			]
		];
		echo '<div class="detail-grid">';
		echo ' <div class="kv-table"">';
		foreach ($data as $r => $row) {
			echo '<div class="'. Html::colcol($r) .'">';
			echo '<div>'. $row['label'] .'</div>';
			$val = $row['value'];
			if($row['time']) {
				$val .= ' @ ' . $row['time'];
			}
			if($row['anomaly'] !== null) {
				$val .= '&nbsp; (' . $row['anomaly'] . ')';
			}
			echo '<div>'. $val .'</div>';
			echo '</div>';
		}
		echo ' </div>';

		echo ' <div class="detail-graph">';
		echo '   <img src="/graph31.php?type='.$this->letter.'mean&amp;x=600&amp;y=300&amp;length=31" width="600" height="300" alt="31-day chart of mean London '.$this->label.'" />';
		echo ' </div>';
		echo '</div>';

		// 31-day min and max charts
		echo '
			<div class="detail-grid">
				<div class="detail-graph">
					<img src="/graph31.php?type='.$this->letter.'min&amp;x=600&amp;y=350" width="600" height="350" alt="31-day chart of min London '.$this->label.'" />
				</div>
				<div class="detail-graph">
					<img src="/graph31.php?type='.$this->letter.'max&amp;x=600&amp;y=350" width="600" height="350" alt="31-day chart of max London '.$this->label.'" />
				</div>
		</div>';
	}

	function avgsExtrmsRecs($measures = null, $wid = 99) {
		$dat =  $GLOBALS[$this->letter.'dat'];
		$measures = is_null($measures) ? self::$measuresGeneric : $measures;
		$values = array($dat['min'][0],$dat['max'][1],$dat['min'][1],$dat['max'][0],$dat['mean'][0],$dat['mean'][1],'---',$dat['mean'][2],$dat['min'][2],$dat['max'][2]);

		$splitOne = self::$periodCnt-3;
		echo "<h2>Averages, Extremes, and Records</h2>";
		echo "<div class='detail-grid'>";

		echo "<div>";
		echo "<h3>Recent</h3>";

		Html::table(null, $wid . '%" align="center', 6);
		Html::tr();
		Html::td($this->label, $this->cssClass, "10%");
		for($h = 0; $h < $splitOne; $h++) {
			Html::td($this->periods_all[self::$periods[$h]], $this->cssClass, '18%');
		}
		Html::tr_end();

		for($r = 0; $r < count($measures); $r++) {
			Html::tr( ($r === 6) ? 'table-top' : Html::colcol($r) );
			Html::td(str_ireplace(' ','<br />',$measures[$r]), $this->cssClass);

			for($c = 0; $c < $splitOne; $c++) {
				if($r != 6) {
					if($this->letter == 't') {
						$anom = '<br />(' . Wx::conv($values[$r][self::$periods[$c].'anom'], Wx::AbsTemp,0,1) . ')';
					} else {
						$anom = '';
					}
					if(array_key_exists(self::$periods[$c].'date', $values[$r])) {
						$date = '<br />'. $values[$r][self::$periods[$c].'date'];
					} else {
						$date = '';
					}
					Html::td( '<b>'. Wx::conv($values[$r][self::$periods[$c]], $this->conv) .'</b>'. $anom . $date, $this->cssClass );
				} else {
					Html::td('&nbsp;', $this->cssClass);
				}
			}
			Html::tr_end();
		}
		Html::table_end();
		echo "</div>";

		echo "<div>";
		echo "<h3>Station Lifetime (2009-". Date::$dyear .")</h3>";

		Html::table(null, $wid . '%" align="center', 6);
		Html::tr();
		Html::td($this->label, $this->cssClass, "10%");
		for($h = $splitOne; $h < self::$periodCnt; $h++) {
			Html::td($this->periods_all[self::$periods[$h]], $this->cssClass, '18%');
		}
		Html::tr_end();

		for($r = 0; $r < count($measures); $r++) {
			Html::tr( ($r === 6) ? 'table-top' : Html::colcol($r) );
			Html::td(str_ireplace(' ','<br />',$measures[$r]), $this->cssClass);

			for($c = $splitOne; $c < self::$periodCnt; $c++) {
				if($r != 6) {
					if($this->letter == 't') {
						$anom = '<br />(' . Wx::conv($values[$r][self::$periods[$c].'anom'], Wx::AbsTemp,0,1) . ')';
					} else {
						$anom = '';
					}
					if(array_key_exists(self::$periods[$c].'date', $values[$r])) {
						$date = '<br />'. $values[$r][self::$periods[$c].'date'];
					} else {
						$date = '';
					}
					Html::td( '<b>'. Wx::conv($values[$r][self::$periods[$c]], $this->conv) .'</b>'. $anom . $date, $this->cssClass );
				} else {
					Html::td('&nbsp;', $this->cssClass);
				}
			}
			Html::tr_end();
		}
		Html::table_end();
		echo "</div>";
		echo "</div>";

		$this->recordPeriodAvgs();
	}

	function pastYearAvgsExtrms($measures = null, $wid = 99) {
		// Daily graphs for past year
		echo '
			<h3>Current year vs last year daily trends for '. $this->label.'</h3>
			<img width="1200" height="500" src="/graph_daily_trend.php?type='.$this->letter.'mean&amp;x=1200&amp;y=500&amp;multiyr=last" alt="Daily London mean '.$this->label.' vs climate normals" />
			<h3>This year min/max daily trends in detail for '. $this->label.'</h3>
			<div class="detail-grid">
				<div><img width="600" height="350" src="/graph_daily_trend.php?type='.$this->letter.'min&amp;x=600&amp;y=350&amp;year='.Date::$yr_yest.'" alt="Current year daily London min '.$this->label.' vs climate normals" /></div>
				<div><img width="600" height="350" src="/graph_daily_trend.php?type='.$this->letter.'max&amp;x=600&amp;y=350&amp;year='.Date::$yr_yest.'" alt="Current year daily London max '.$this->label.' vs climate normals" /></div>
			</div>
		';

		$dat = $GLOBALS[$this->letter.'datMM'];
		$measures = is_null($measures) ? self::$measuresGeneric : $measures;
		$values = array($dat['min'][0],$dat['max'][1],$dat['min'][1],$dat['max'][0],$dat['mean'][0],$dat['mean'][1],'---',$dat['mean'][2],$dat['min'][2],$dat['max'][2]);

		echo "<h2>Past Year Monthly Averages and Extremes (". date('M') ." ". (Date::$dyear-2001) . ' - ' . date('M y',Date::mkdate(Date::$dmonth-1,15)) .")</h2>";
		Html::table(null, $wid . '%" align="center', 6);
		Html::tr();
		Html::td("Month", $this->cssClass);
		for($r = 0; $r < count($measures); $r++) {
			if($r != 6) {
				Html::td(str_ireplace(' ','<br />',$measures[$r]), $this->cssClass);
			}
		}
		Html::tr_end();

		for($m = 11; $m >= 0; $m--) {
			$mt = date('n',Date::mkdate(Date::$dmonth-$m-1,15))-1;
			Html::tr(Html::colcol($m));
			Html::td(Date::$months3[$mt], $this->cssClass);

			for($r = 0; $r < count($measures); $r++) {
				if($r != 6) {
					if($values[$r][0][$mt] == $values[$r]['extr'][1]) { $colour = '" style="color:#DF7401'; }
					elseif($values[$r][0][$mt] == $values[$r]['extr'][0]) { $colour = '" style="color:#0101DF'; }
					else { $colour = ''; }
					if($this->letter == 't') {
						$anom = '<br />(' . Wx::conv($values[$r][2][$mt], Wx::AbsTemp,0,1) . ')';
					} else {
						$anom = '';
					}
					if(array_key_exists(1, $values[$r]) && array_key_exists($mt, $values[$r][1])) {
						$date = '<br />'. Date::datefull($values[$r][1][$mt]);
					} else {
						$date = '';
					}
					Html::td('<b>'. Wx::conv($values[$r][0][$mt], $this->conv) .'</b>'. $anom . $date, $this->cssClass . $colour);
				}
			}
			Html::tr_end();
		}

		Html::table_end();

		echo '<p>View daily tables of
			<a href="wxdataday.php?vartype='.$this->letter.'min">min</a> /
			<a href="wxdataday.php?vartype='.$this->letter.'max">max</a> /
			<a href="wxdataday.php?vartype='.$this->letter.'mean">mean</a>
			 '.$this->label.' data for the past year <br />View monthly tables of
			<a href="TablesDataMonth.php?vartype='.$this->letter.'min">min</a> /
			<a href="TablesDataMonth.php?vartype='.$this->letter.'max">max</a> /
			<a href="TablesDataMonth.php?vartype='.$this->letter.'mean">mean</a>
			 '.$this->label.' data for all months in the station history.
			</p>';

		$this->seasonalAvgs();

		echo '
			<h3>Past 24hrs and past 12 months trends for '. $this->label.'</h3>
			<div class="detail-grid">
				<div><img width="600" height="330" src="/graphdayA.php?type='.$this->type.'&amp;x=600&amp;y=330" alt="Last 24hrs London '.$this->label.'" /></div>
				<div><img width="600" height="330" src="/graph12.php?type='.$this->letter.'mean&amp;x=600&amp;y=330&amp;lta" alt="12 month mean London '.$this->label.'" /></div>
			</div>
			<div class="detail-grid">
				<div><img width="600" height="330" src="/graph12.php?type='.$this->letter.'min&amp;x=600&amp;y=330" alt="12month min London '.$this->label.'" /></div>
				<div><img width="600" height="330" src="/graph12.php?type='.$this->letter.'max&amp;x=600&amp;y=330" alt="12month max London '.$this->label.'" /></div>
			</div>
			<p><a href="charts.php">View more '.$this->label.' charts</a></p>
		';

		
	}

	private function seasonalAvgs($wid = 75) {
		$dat = $GLOBALS[$this->letter.'datSS'];
		$datAnom = $this->letter == 't' ? $GLOBALS[$this->letter.'datSSanom'] : [];

		echo "<h2>Past Year Seasonal Averages</h2>";
		Html::table(null, $wid . '%" align="center', 6, true);

		Html::tr();
		Html::td("Season", $this->cssClass, "22%");
		Html::td("Daily Min", $this->cssClass, "26%");
		Html::td("Daily Max", $this->cssClass, "26%");
		Html::td("Mean", $this->cssClass, "26%");
		Html::tr_end();

		for($i = 0; $i < 4; $i++) {
			$dfo1 = Date::$dyear-2001; $dfo2 = Date::$dyear-2000; $dfo3 = Date::$dyear-2002;
			$nwint = ($i+1 < Date::$season || Date::$dmonth == 12) ? Date::$dyear : Date::$dyear-1;
			$wint = (Date::$dmonth > 2) ? $dfo1 .'/' .$dfo2 : $dfo3 .'/' .$dfo1;
			$yr3 = array($wint, $nwint, $nwint, $nwint);
			$hlite = ($i+1 == Date::$season-1) ? 'border-bottom:3px solid #8181F7' : '';


			Html::tr(Html::colcol($i));
			Html::td( Date::$snames[$i] . ' ' . $yr3[$i], $this->cssClass . '" style="' . $hlite );

			for($j = 0; $j < 3; $j++) {
				$anom = $this->letter == 't' ? ' (' . Wx::conv($datAnom[Wx::$mmm[$j]][$i], Wx::AbsTemp,1,1) . ')' : '';
				Html::td( Wx::conv($dat[Wx::$mmm[$j]][$i], $this->conv) . $anom .'<br />', $this->cssClass . '" style="' . $hlite );
			}
			Html::tr_end();
		}

		Html::table_end();

		
	}

	private function recordPeriodAvgs($wid = 98) {
		$dat =  $GLOBALS[$this->letter.'dat'];
		$values = array($dat['mean'][0],$dat['mean'][1],$dat['min'][0],$dat['min'][1],$dat['max'][0],$dat['max'][1]);

		$periods = array('7cum','Ma','Mmr','31cum','Ya','365cum');
		$measures = array($this->superlativeLow,$this->superlativeHigh, 'Lowest Mean Daily-Min','Highest Mean Daily-Min', 'Lowest Mean Daily-Max','Highest Mean Daily-Max');

		echo "<h2>Record Period Averages</h2>";
		Html::table(null, $wid . '%" style="margin-bottom:28px;', 6);

		Html::tr();
		Html::td("Measure", $this->cssClass, "8%");
		for($h = 0; $h < count($periods); $h++) {
			Html::td($this->periods_all[$periods[$h]], $this->cssClass, '15%');
		}
		Html::tr_end();

		for($r = 0; $r < count($measures); $r++) {
			Html::tr( Html::colcol($r) );
			Html::td(str_ireplace(' ','<br />',$measures[$r]), $this->cssClass);

			for($c = 0; $c < count($periods); $c++) {
				Html::td( '<b>'. Wx::conv($values[$r][$periods[$c]], $this->conv) .'</b><br />'. $values[$r][$periods[$c].'date'], $this->cssClass );
			}
			Html::tr_end();
		}

		Html::table_end();
	}

	private function rankTablePair($rankArray, $rankNum, $type, $title, $label) {
		echo "<div class='detail-grid'>";

		echo "<div>";
		echo "<h3>". $this->superlativeHigh ." ". $title ."</h3>";
		Html::table("table1", '99%');
		Html::tr();
		Html::td("Rank", $this->cssClass);
		Html::td($label ." Low", $this->cssClass);
		Html::td($label ." High", $this->cssClass);
		Html::td($label ." Mean", $this->cssClass);
		Html::tr_end();

		for($i = 1; $i <= $rankNum; $i++) {
			Html::tr( Html::colcol($i) );
			Html::td($i, $this->cssClass);
			for($j = 0; $j < 3; $j++) {
				Html::td( Wx::conv($rankArray[$j][$type][1][0][$i], $this->conv) . '<br />' . $rankArray[$j][$type][1][1][$i], $this->cssClass );
			}
			Html::tr_end();
		}
		Html::table_end();
		echo "</div>";

		echo "<div>";
		echo "<h3>". $this->superlativeLow ." ". $title ."</h3>";
		Html::table("table1", '99%');
		Html::tr();
		Html::td("Rank", $this->cssClass);
		Html::td($label ." Low", $this->cssClass);
		Html::td($label ." High", $this->cssClass);
		Html::td($label ." Mean", $this->cssClass);
		Html::tr_end();

		for($i = 1; $i <= $rankNum; $i++) {
			Html::tr( Html::colcol($i) );
			Html::td($i, $this->cssClass);
			for($j = 0; $j < 3; $j++) {
				Html::td( Wx::conv($rankArray[$j][$type][0][0][$i], $this->conv) . '<br />' . $rankArray[$j][$type][0][1][$i], $this->cssClass );
			}
			Html::tr_end();
		}
		Html::table_end();
		echo "</div>";

		echo "</div>";
		echo "<p>
			 <a href='RankDay.php?vartype=".$this->letter."mean'>View more ".$this->label." rankings</a>
		</p>";
	}

	function rankTables($rankNum = 10, $rankNumM = 10, $rankNumCM = 5) {
		$tranks = $GLOBALS[$this->letter . 'ranks'];
		echo '<h2>Ranked Historical '.$this->label.' Data</h2>';

		self::rankTablePair($tranks, $rankNum, 'daily', "Days", "Daily");
		self::rankTablePair($tranks, $rankNumM, 'monthly', "Months", "Monthly");
		self::rankTablePair($tranks, $rankNumCM, 'dailyCM', "Days in ". Date::$monthname, "Daily");
	}
}
?>
