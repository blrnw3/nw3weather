<?php

class Live {
	// Live Data
	public static $unix;
	public static $temp;
	public static $humi;
	public static $pres;
	public static $rain;
	public static $wind;
	public static $gust;
	public static $gustRaw;
	public static $w10m;
	public static $wdir;
	public static $dewp;
	public static $feel;
	public static $pm25; // Air quality: latest raw PM2.5 (ug/m3), null if unavailable

	// 24hr / today
	public static $NOW;
	public static $HR24;

	// Other multi-use weather vars
	public static $maxgsthr;
	public static $maxgstToday;

	public static $diff = 0;
	public static $updated;
	public static $outage = false;

	public static function init() {
		self::$NOW = unserialize(file_get_contents(ROOT . 'serialised_datNow.txt'));
		self::$HR24 = unserialize(file_get_contents(ROOT . 'serialised_datHr24.txt'));

		// TODO get from $NOW
		// Air quality: latest PM2.5 reading (polled every 5 min by cron), null if absent
		$pm25File = ROOT . 'pm25_latest.txt';
		if(file_exists($pm25File)) {
			$pm25Raw = trim(file_get_contents($pm25File));
			self::$pm25 = ($pm25Raw !== '' && is_numeric($pm25Raw)) ? (float)$pm25Raw : null;
		} else {
			self::$pm25 = null;
		}

		$crsizeFinal = filesize(Site::LIVE_DATA_PATH);

		//Select appropriate file to use
		$usePath = Site::LIVE_DATA_PATH;
		$badCRdata = false;
		if($crsizeFinal === 0) {
			$usePath = Site::MAIN_ROOT.'clientrawBackup.txt';
			$badCRdata = true;
		}

		$client = file($usePath);
		$mainData = explode(" ", $client[0]);

		if($badCRdata) {
			Page::log_events('clientrawBad.txt', $crsizeFinal ."B ");
		}

		$kntsToMph = 1.152;
		// Main current weather variables
		self::$temp = $mainData[4];
		self::$humi = $mainData[5];
		self::$pres = $mainData[6];
		self::$rain = $mainData[7];
		self::$wind = $mainData[1] * $kntsToMph;
		self::$gust = $mainData[140] * $kntsToMph; //actually the max 1-min gust
		self::$gustRaw = $mainData[2] * $kntsToMph; //true 14s gust
		self::$w10m = $mainData[158] * $kntsToMph;
		self::$wdir = $mainData[3];

		// Time variables
		self::$unix = mktime(intval($mainData[29]), intval($mainData[30]), intval($mainData[31]),
				intval($mainData[36]), intval($mainData[35]), intval($mainData[141]));

		self::$diff = time() - self::$unix;
		self::$outage = self::$diff > 3600;

		// Other multi-use weather vars
		// Floored at the live gust below, once any fallback source has settled it - the
		// per-minute log's last-hour max can momentarily lag a fresh live gust.
		self::$maxgsthr = self::$HR24['misc']['maxhrgst'];
		self::$maxgstToday = self::$NOW['max']['gust'];


		// Synoptic data from James park
		if(self::$outage && false) {
			$mod_james = filemtime(ROOT.'EXT_james.json');
			$alt_age = time() - $mod_james;
			if($alt_age < 600) {
				self::$unix = $mod_james;
				$james_data = json_decode(file_get_contents(ROOT."EXT_james.json"), true);
				self::$temp = $james_data["STATION"][0]["OBSERVATIONS"]["air_temp_value_1"]["value"] - 0.5;
				self::$dewp = $james_data["STATION"][0]["OBSERVATIONS"]["dew_point_temperature_value_1"]["value"] - 0.5;
				// https://www.omnicalculator.com/physics/relative-humidity
				self::$humi = intval(100 * exp((17.625 * self::$dewp) / (243.04 + self::$dewp)) / exp((17.625 * self::$temp) / (243.04 + self::$temp)));
			}
		}

		$DOWN = (self::$temp < -9 || self::$humi < 20 || (self::$temp == -19.3 && self::$humi == 80));

		// CWOP Islington data
		$mainBackupOk = false;
		if(($DOWN || self::$outage) && file_exists(ROOT."EXT_islington.json")) {
			$isl_data = json_decode(file_get_contents(ROOT."EXT_islington.json"), true);
			$isl_wx = $isl_data["entries"][0];
			$isl_unix = intval($isl_wx["time"]);
			if((time() - $isl_unix) < 3600) {
				$mainBackupOk = true;
				self::$unix = $isl_unix;
				self::$temp = (float)$isl_wx["temp"];
				self::$humi = $isl_wx["humidity"];
				self::$rain = (float)$isl_wx["rain_mn"]; // rain since midnight (meteoglance field)
				self::$pres = (float)$isl_wx["pressure"];
				self::$wind = (float)$isl_wx["wind_speed"];
				self::$gust = (float)$isl_wx["wind_gust"];
				self::$gustRaw = self::$gust + 1;
				self::$w10m = self::$wind;
			}
		}

		// CWOP Potters
		if(($DOWN || self::$outage) && file_exists(ROOT."EXT_potters.json")) {
			$pot_data = json_decode(file_get_contents(ROOT."EXT_potters.json"), true);
			$pot_wx = $pot_data["entries"][0];
			$pot_unix = intval($pot_wx["time"]);
			if((time() - $pot_unix) < 3000) {
				if(!$mainBackupOk) {
					self::$unix = $pot_unix;
					self::$temp = (float)$pot_wx["temp"];
					self::$humi = $pot_wx["humidity"];
				}
				self::$rain = (float)$pot_wx["rain_24h"];
			}
		}

		// Derived current weather variables
		self::$feel = Wx::feelsLike(self::$temp, self::$gust, self::$dewp);
		self::$dewp = Wx::dewPoint(self::$temp, self::$humi);

		// Max gust in the last hour can never be below the current gust.
		self::$maxgsthr = max((float)self::$maxgsthr, (float)self::$gust, (float)self::$gustRaw);
	}
}


class DataSummarizer {
	public $varName;
	public $startYear;

	public static $periods = [7, 31, 365];

	// Daily vals for different periods
	public $vals;  // Y-m-d indexed daily vals
	public $currentMonth;
	public $currentMonthAll; // current month, all years
	public $currentDateAll; // current month-day, all years
	public $currentYear;
	public $pastNDays = [];

	// Monthly/yearly summaries for different periods
	public $monthSummaries;	
	public $yearSummaries;
	public $seasonSummaries;
	// Re-indexed
	public $monthSummariesFlat;
	public $currentMonthSummariesFlat; // current monthname only

	private $monthIdx;
	private $dayIdx;
	private $summable;
	private $summaryKey;
	private $anomable;

	public function __construct($varName, $startYear = null) {
		$this->varName = $varName;
		$this->summable = array_key_exists("summable", Wx::$daily[$this->varName]);
		$this->anomable = array_key_exists("anomaly", Wx::$daily[$this->varName]);
		$this->summaryKey = $this->summable ? "sum" : "mean";

		$this->startYear = $startYear === null ? Site::BASE_YEAR : $startYear;
		$this->monthSummaries = $this->getMonthlySummaries();
		$this->yearSummaries = $this->getYearlySummaries();
		$this->seasonSummaries = $this->getSeasonSummaries();

		$this->monthIdx = date('m');  // 01-12
		$this->dayIdx = date('d');  // 01-31

		// Re-index vals by year-month-day
		$this->vals = [];
		foreach (Data::getAllData($varName) as $year => $arr1) {
			foreach ($arr1 as $month => $arr2) {
				$monthIdx = Util::zerolead($month);
				foreach ($arr2 as $day => $val) {
					$dayIdx = Util::zerolead($day);
					$this->vals["$year-$monthIdx-$dayIdx"] = $val;
				}
			}
		}
		$this->currentMonth = Util::array_filter_keys($this->vals, function($key) {
			return substr($key, 0, 7) === Date::$dyear . '-' . $this->monthIdx;
		});

		$this->currentMonthAll = Util::array_filter_keys($this->vals, function($key) {
			return substr($key, 5, 2) === $this->monthIdx;
		});

		$this->currentDateAll = Util::array_filter_keys($this->vals, function($key) {
			return substr($key, 5, 5) === $this->monthIdx . '-' . $this->dayIdx;
		});

		$this->currentYear = Util::array_filter_keys($this->vals, function($key) {
			return substr($key, 0, 4) === Date::$dyear;
		});

		foreach (self::$periods as $period) {
			$cutoff = date('Y-m-d', mktime(12, 0, 0, Date::$dmonth, Date::$dday - $period, Date::$dyear));
			$this->pastNDays[$period] = Util::array_filter_keys($this->vals, function($key) use ($cutoff) {
				return $key > $cutoff;
			});
		}

		// Re-index month summaries by year-month-day
		$this->monthSummariesFlat = [];
		$this->currentMonthSummariesFlat = [];
		foreach ($this->monthSummaries as $year => $monthlySummary) {
			foreach ($monthlySummary as $month => $summary) {
				$this->monthSummariesFlat["$year-$month-01"] = $summary;
				if ($month == $this->monthIdx) {
					$this->currentMonthSummariesFlat["$year-$month-01"] = $summary;
				}
			}
		}
	}

	public function summarize() {
		$period_summaries = $this->getFixedPeriodSummaries() + $this->getRecentNdaySummaries() + $this->getRecordFixedPeriodMeans() + $this->getRecordNdayMeans();
		return [
			"period_summaries" => $period_summaries,
			'year_summaries' => $this->yearSummaries,
			'month_summaries' => $this->monthSummaries,
			"season_summaries" => $this->seasonSummaries,
			'ranks' => $this->getRanks()
		];
	}

	private function getBaseSummary($arr) {
		list($min, $minDate) = Util::extremeValAndKey($arr, 0);
		list($max, $maxDate) = Util::extremeValAndKey($arr, 1);
		$summary = [
			'count' => Util::mycount($arr),
			'count_nonzero' => Util::cond_count($arr, true, 0),
			'count_internal' => count($arr),
			'sum' => array_sum($arr),
			'min' => $min,
			'minDate' => $minDate,
			'max' => $max,
			'maxDate' => $maxDate,
		];
		$summary['mean'] =  $summary['count'] > 0 ? $summary['sum'] / $summary['count'] : null;
		return $summary;
	}

	private function getEmptyBaseSummary() {
		$summary = $this->getBaseSummary([]);
		$summary["min"] = PHP_INT_MAX;
		$summary["max"] = -1 * PHP_INT_MAX;
		return $summary;
	}

	private function mergeSummaries($summaries) {
		$summary = $this->getEmptyBaseSummary();
		foreach($summaries as $ms) {
			$summary['count'] += $ms['count'];
			$summary['count_nonzero'] += $ms['count_nonzero'];
			$summary['count_internal'] += $ms['count_internal'];
			$summary['sum'] += $ms['sum'];
			if ($ms['min'] < $summary['min']) {
				$summary['min'] = $ms['min'];
				$summary['minDate'] = $ms['minDate'];
			}
			if ($ms['max'] > $summary['max']) {
				$summary['max'] = $ms['max'];
				$summary['maxDate'] = $ms['maxDate'];
			}
		}
		$summary['mean'] = $summary['count'] > 0 ? $summary['sum'] / $summary['count'] : null;
		return $summary;
	}

	private function addAnomalies(&$summary, $anom) {
		if($this->anomable && is_numeric($anom)) {
			$summary['anom'] = $summary[$this->summaryKey] - $anom;
			$summary['anom_pct'] = $anom != 0 ? $summary['anom'] / $anom * 100 : null;
		}
	}

	private function getMonthlySummaries() {
		$allMonthSummary = [];
		for ($year = $this->startYear; $year <= Date::$dyear; $year++) {
			foreach (Data::getYearlyData($this->varName, $year) as $month => $dailyData) {
				$month = strval(Util::zerolead($month));
				$summary = $this->getBaseSummary($dailyData);
				$summary['minDate'] = $year . '-' . $month . '-' . Util::zerolead($summary['minDate']);
				$summary['maxDate'] = $year . '-' . $month . '-' . Util::zerolead($summary['maxDate']);
				$this->addAnomalies($summary, LTA::getMonthlyAnom($this->varName, $month));
				$allMonthSummary[$year][$month] = $summary;
			}
		}
		return $allMonthSummary;
	}

	private function getYearlySummaries() {
		$allYearSummary = [];
		foreach ($this->monthSummaries as $year => $monthlySummaries) {
			$allYearSummary[$year] = $this->mergeSummaries($monthlySummaries);
			$this->addAnomalies($allYearSummary[$year], LTA::getYearlyAnom($this->varName));
		}
		return $allYearSummary;
	}

	private function getSeasonSummaries() {
		$seasons = [];
		foreach ($this->monthSummaries as $year => $monthlySummaries) {
			foreach(Date::$snums as $i => $monthIdxs) {
				$vals = [];
				foreach($monthIdxs as $mi) {
					$k = Util::zerolead($mi + 1);
					if($mi === 11 && $year > 2009) {
						$vals[] = $this->monthSummaries[$year-1]["12"];
					} elseif(array_key_exists($k, $monthlySummaries)) {
						$vals[] = $monthlySummaries[$k];
					}
				}
				if(count($vals) > 0) {
					$seasonSummary = $this->mergeSummaries($vals);
					$this->addAnomalies($seasonSummary, LTA::getSeasonAnom($this->varName, $i));
					$seasons[Date::$snames[$i] ."_". $year] = $seasonSummary;
				}
			}
		}
		return $seasons;
	}
	
	private function getRecentNdaySummaries() {
		$periodSummaries = [];
		foreach(DataSummarizer::$periods as $period) {
			$k = "latest_$period". "d";
			$periodSummaries[$k] = $this->getBaseSummary($this->pastNDays[$period]);
			$periodSummaries[$k]['minDateFmt'] = Date::today(null, $period > 31, true, null, $periodSummaries[$k]['minDate']);
			$periodSummaries[$k]['maxDateFmt'] = Date::today(null, $period > 31, true, null, $periodSummaries[$k]['maxDate']);
		}
		return $periodSummaries;
	}

	private function getRecordNdayMeans() {
		$periodSummaries = [];
		foreach(DataSummarizer::$periods as $period) {
			for ($i = 0; $i < 2; $i++) { // hi and lo
				$endDt = null;
				$buff = new CircularBuffer($period);
				$extremeVal = $i === 0 ? PHP_INT_MAX : -1 * PHP_INT_MAX;
				foreach ($this->vals as $dt => $val) {
					if (!Util::isBlank($val)) {
						$buff->add(floatval($val));
					}
					if ($buff->isFull) {
						$currSum = array_sum($buff->items);
						if (Util::opmom($currSum, $extremeVal, $i)) {
							$extremeVal = $currSum;
							$endDt = $dt;
						}
					}
				}
				$periodSummaries[$period . 'd_' . ($i === 0 ? 'lo' : 'hi') . '_mean_alltime'] = [
					'count' => $period,
					'count_nonzero' => Util::cond_count($buff->items, true, 0),
					'count_internal' => count($buff->items),
					'sum' => $extremeVal,
					'mean' => $extremeVal / $period,
					'endDate' => $endDt,
					'endDateFmt' => Date::today(true, true, true, null, $endDt),
				];
			}
		}
		return $periodSummaries;
	}

	private function getFixedPeriodSummaries() {
		return [
			'today' => [
				'val' => Data::get($this->varName, Date::$dyear, Date::$dmonth, Date::$dday),
				'time' => Data::getTime($this->varName, Date::$dyear, Date::$dmonth, Date::$dday),
			],
			'yest' => [
				'val' => Data::get($this->varName, Date::$yr_yest, Date::$mon_yest, Date::$day_yest),
				'time' => Data::getTime($this->varName, Date::$yr_yest, Date::$mon_yest, Date::$day_yest),
			],
			'curr_month' => $this->getBaseSummary($this->currentMonth),
			'curr_year' => $this->getBaseSummary($this->currentYear),
			'alltime' => $this->getBaseSummary($this->vals),
			'all_this_month' => $this->getBaseSummary($this->currentMonthAll),
			'all_this_date' => $this->getBaseSummary($this->currentDateAll),
		];
	}

	private function getRecordFixedPeriodMeans() {
		return [
			'month_mean_alltime' => $this->getBaseSummary(Util::array_pluck($this->monthSummariesFlat, $this->summaryKey)),
			'month_mean_all_this_month' => $this->getBaseSummary(Util::array_pluck($this->currentMonthSummariesFlat, $this->summaryKey)),
			'year_mean_alltime' => $this->getBaseSummary(Util::array_pluck($this->yearSummaries, $this->summaryKey)),
		];
	}

	private function reformat_ranks($assoc) {
		$result = [];
		foreach ($assoc as $k => $v) {
			$result[] = array('dt' => $k, 'val' => $v);
		}
		return $result;
	}

	private function extractHighLow($arr, $n) {
		asort($arr);  // NB: this is in-place
		// Get bottom N
		$bottom = array_slice($arr, 0, $n, true);
		// Get top N (last N elements of the sorted array, reversed)
		$top = array_slice($arr, -$n, $n, true);
		$top = array_reverse($top, true);
	
		return [
			'lo' => $this->reformat_ranks($bottom),
			'hi' => $this->reformat_ranks($top)
		];
	}

	private function getRanks() {
		// NB: must be called last as it sorts the arrays in place
		return [
			"daily_alltime" => $this->extractHighLow($this->vals, 10),
			"daily_all_this_month" => $this->extractHighLow($this->currentMonthAll, 10),
			"daily_all_this_date" => $this->extractHighLow($this->currentDateAll, 5),
			"month_mean_alltime" => $this->extractHighLow(Util::array_pluck($this->monthSummariesFlat, $this->summaryKey), 10),
			"month_mean_all_this_month" => $this->extractHighLow(Util::array_pluck($this->currentMonthSummariesFlat, $this->summaryKey), 10),
		];
	}
}

class CircularBuffer {
	private $size;
	public $items = null;
	private $pointer = 0;
	public $isFull = false;

	function __construct($size) {
		$this->items = array();
		$this->size = $size;
	}

	function add($item) {
		$pos = $this->pointer % $this->size;
		$this->items[$pos] = $item;
		$this->pointer++;
		if ($this->pointer === $this->size) {
			$this->isFull = true;
		}
	}

}

class Data {

	public static $CACHE_DAT = [];
	public static $CACHE_DAT_TIMES = null;
	public static $CACHE_DAT_HIST = [];

	const SUMMARY_MEAN = 0;
	const SUMMARY_SUM = 1;
	const SUMMARY_COUNT = 2;
	const SUMMARY_MIN = 3;
	const SUMMARY_MAX = 4;

	public static $SUMMARY_NAMES = ["mean", "total", "count", "lowest", "highest"];
	public static $SUMMARY_EXPLAIN = ["monthly average", "monthly total", "number of non-zero days",  "lowest <b>daily</b> value in each month",  "highest <b>daily</b> value in each month"];

	public static function init() {
	}

	public static function getAllData($name) {
		if (array_key_exists($name, self::$CACHE_DAT)) {
			return self::$CACHE_DAT[$name];
		}
		return unserialize(file_get_contents(ROOT . "serialised_dat_new_$name.txt"));
	}
	public static function getYearlyData($name, $year) {
		return self::getAllData($name)[$year];
	}
	public static function getMonthlyData($name, $year, $month) {
		return self::getYearlyData($name, $year)[$month];
	}
	public static function get($name, $year, $month, $day) {
		return self::getMonthlyData($name, $year, $month)[$day];
	}

	public static function getTime($name, $year, $month, $day) {
		if (self::$CACHE_DAT_TIMES === null) {
			self::$CACHE_DAT_TIMES = unserialize(file_get_contents(ROOT . "serialised_datt_new.txt"));
		}
		// BUG TODO: if for current day, it returns the value as cached at midnight, which is the only time the time file is updated
		if (!isset(self::$CACHE_DAT_TIMES[$name][$year][$month][$day])) {
			return null;
		}
		return self::$CACHE_DAT_TIMES[$name][$year][$month][$day];
	}

	public static function datDerived($varName, $include_historic) {
		global $types_all;

		$srcMap = ["ratemean" => ["rain", "wethr"], "trange" => ["tmin", "tmax"], "hrange" => ["hmin", "hmax"], "prange" => ["pmin", "pmax"]];
		$src = $srcMap[$varName];
		$var1 = varNumToDatArray($types_all[$src[0]], $include_historic);
		$var2 = varNumToDatArray($types_all[$src[1]], $include_historic);

		$res = [];
		foreach ($var1 as $year => $arr1) {
			foreach ($arr1 as $month => $arr2) {
				foreach ($arr2 as $day => $val) {
					if($varName === "ratemean") {
						$res[$year][$month][$day] = ($var2[$year][$month][$day]  > 0.4 && $val > 0.3) ?
							$val / $var2[$year][$month][$day] : null;
					}
					else {
						$res[$year][$month][$day] = $var2[$year][$month][$day] - $val;
					}
				}
			}
		}
		return $res;
	}


	/**
	 * Gets data from the right global ALL array (DATA, DATAM), or derives it (anoms, ranges, rates).
	 * @param int $varNum
	 * @param mixed $include_historic false to exclude, int to set start year for historic data, true to include all
	 * @return mixed
	 */
	// public static function varNameToDatArray($varName, $include_historic = false) {
	// 	$isAnom = in_array($varName, $types_anom);
	// 	if($isAnom) {
	// 		$anomVarName = $varName;
	// 		$varNum = $types_all[substr($varName, 0, strlen($varName)-1)];  // e.g. tmina -> 0
	// 		$varName = $types_alltogether[$varNum];
	// 	}
	// 	if(in_array($varName, $types_derived)) {
	// 		return datDerived($varName, $include_historic);
	// 	}
	// 	if($varNum < count($types)) {
	// 		if (!array_key_exists($varName, $CACHE_DAT)) {
	// 			$CACHE_DAT[$varName] = unserialize(file_get_contents(ROOT . "serialised_dat_$varNum.txt"));
	// 		}
	// 	} else {
	// 		if (!array_key_exists($varName, $CACHE_DAT)) {
	// 			$idx = $varNum - $types_all['sunhr'];
	// 			$CACHE_DAT[$varName] = unserialize(file_get_contents(ROOT . "serialised_datm_$idx.txt"));
	// 		}
	// 	}
	// 	$arr = $CACHE_DAT[$varName];
	// 	if($include_historic !== false && $start_year_all[$varNum] < 2009) {
	// 		if($include_historic < 2009) {
	// 			// Populate cache
	// 			if (!array_key_exists($varName, $CACHE_DAT_HIST)) {
	// 				$CACHE_DAT_HIST[$varName] = unserialize(file_get_contents(ROOT."serialised_historical_$varName.txt"));
	// 			}
	// 			$arr = $CACHE_DAT_HIST[$varName] + $arr;
	// 		}
	// 	}
	// 	if($isAnom) {
	// 		// NB: passing the original varname
	// 		return datAnom($anomVarName, $arr, $varNum);
	// 	}
	// 	return $arr;
	// }


	public static function summarize($arr, $summary_type) {
		if($summary_type === Data::SUMMARY_MEAN) {
			return Util::mean($arr);
		}
		if($summary_type === Data::SUMMARY_SUM) {
			return array_sum($arr);
		}
		if($summary_type === Data::SUMMARY_COUNT) {
			return Util::mycount($arr);
		}
		if($summary_type === Data::SUMMARY_MIN) {
			return Util::mymin($arr);
		}
		if($summary_type === Data::SUMMARY_MAX) {
			return Util::mymax($arr);
		}
	}
	public static function summarize2D($arr2D, $summary_type) {
		$summary = [];
		foreach($arr2D as $k => $arr) {
			$summary[$k] = summarize($arr, $summary_type);
		}
		return $summary;
	}


	// GLOBAL DATA ACCESS FUNCTIONS
	/**
	 * Returns array[year][month][day] = val
	 * @param type $var
	 * @param type $start_year
	 * @return type
	 */
	public static function getDailyData($var, $start_year) {
		$data = [];
		foreach( varNumToDatArray($GLOBALS["types_all"][$var], $start_year) as $y => $dat ) {
			if($y >= $start_year) {
				$data[$y] = $dat;
			}
		}
		return $data;
	}

	public static function typeToConvType($type) {
		return $GLOBALS['typeconvs_all'][$GLOBALS['types_all'][$type]];
	}

	/**
	 * Good implementation of calculating the mean wind direction from an array of wdirs and speeds
	 * @param array $wdir raw array
	 * @param array $speed so calm times can be ignored
	 * @return int
	 */
	public static function wdirMean($wdir, $speed) {
		$bitifier = 36; //constant - the quantisation level to convert 360 degrees into a bitier signal
		$calmThreshold = 1; //constant - values when the wind speed was below this are ignored

		$end = count($wdir);

		$freqs = array();
		for($i = 0; $i <= 360/$bitifier; $i++) {
			$freqs[$i] = 0;
		}

		//get frequencies for each bitified angle
		for($i = 0; $i < $end; $i++) {
			if($speed[$i] > $calmThreshold) { // pivot not to be affected by calm times
				$freqs[round($wdir[$i] / $bitifier)]++;
			}
		}

		//choose a pivot
		$minfreq = min($freqs);
		$pivot = array_search($minfreq, $freqs);
		$pivot *= $bitifier;

		//calculate the mean
		$sum = 0;
		$count = 0;
		for($i = 0; $i < $end; $i++) {
			//values from calm times or near pivot are anomalous => ignore
			if(abs($wdir[$i] - $pivot) >= $bitifier && $speed[$i] > $calmThreshold) {
				$sum += $wdir[$i];
				$count++;
				if($wdir[$i] > $pivot) {
					$sum -= 360;
				}
			}
		}
		//clean-up
		$mean = ($count === 0) ? 0 : roundToDp($sum / $count, 0);
		if($mean < 0) {
			$mean += 360;
		}

		return $mean;
	}

	/**
	 * Processes a daily logfile into useful data - max, mins, means etc.
	 * @param string $procfil [=today] Ymd format for the day to process
	 * @return array of data for the chosen daily logfile
	 */
	public static function dailyData($procfil = 'today') {
		$datt = $dat = array();
		for($t = 6; $t < 10; $t++) {
			$datt[$t]['max'] = -99999999;
			$datt[$t]['min'] = 99999999;
		}
		$round_pt = array(0,0,0,1,0,0, 2,1,1,2);
		$trendKeys = array('wind', 'gust', 'wdir', 'temp', 'humi', 'pres', 'dewp');
		$daytypes = array_flip(array('temp' => 6, 'humi' => 7, 'dewp' => 9, 'rain' => 10, 'pres' => 8, 'wdir' => 5, 'gust' => 4, 'wind' => 3));
		$rntipmm = 0.24; //constant
		$RATE_THRESH = 0.4; //Two tips' worth

		$daymax1 = $daymax2 = -99;
		$nightmin1 = $nightmin2 = $nightmin1T = $nightmin2T = 99;
		$frostMins = 0;
		$lineLength = 11;
		$trends = $rnCums = $rncumArr = array();
		$rncum = $w10 = 0;
		$mins = $maxs = $means = $timesMin = $timesMax = array();

		// PM2.5 is an optional trailing column (11); tracked separately and guarded,
		// since older/partial logs may not contain it.
		$pm25Sum = 0; $pm25Count = 0; $pm25Min = null; $pm25Max = null;
		$pm25MinTimes = array(); $pm25MaxTimes = array();

		$windDirs = [];

		$filcust = file(ROOT. "logfiles/daily/" . $procfil . 'log.txt');
		$end = count($filcust); //should be 1440

		for($i = 0; $i < $end; $i++) {
			$custl = explode(',', $filcust[$i]);
			$custmin[$i] = intval($custl[1]);
			$custhr[$i] = intval($custl[0]);

			for($t = 0; $t < $lineLength; $t++) {
				$dat[$t][$i] = floatval($custl[$t]);
				if($t > 5 && $t < 10) {
					$custl[$t] = floatval($custl[$t]);
					// Set max/min, and find _every_ time of max/min
					if($custl[$t] > $datt[$t]['max']) {
						$datt[$t]['max'] = $custl[$t];
						$datt[$t]['timesMax'] = array(mktime($custhr[$i],$custmin[$i]));
					}
					if($custl[$t] === $datt[$t]['max']) {
						$datt[$t]['timesMax'][] = mktime($custhr[$i],$custmin[$i]);
					}
					if($custl[$t] < $datt[$t]['min']) {
						$datt[$t]['min'] = $custl[$t];
						$datt[$t]['timesMin'] = array(mktime($custhr[$i],$custmin[$i]));
					}
					if($custl[$t] === $datt[$t]['min']) {
						$datt[$t]['timesMin'][] = mktime($custhr[$i],$custmin[$i]);
					}
				}
			}

			// PM2.5 - trailing column 11, only present from launch onward
			if(isset($custl[11]) && trim($custl[11]) !== '') {
				$pm25V = floatval($custl[11]);
				$dat[11][$i] = $pm25V;
				$pm25Sum += $pm25V; $pm25Count++;
				$tPm25 = mktime($custhr[$i], $custmin[$i]);
				if($pm25Max === null || $pm25V > $pm25Max) { $pm25Max = $pm25V; $pm25MaxTimes = array($tPm25); }
				elseif($pm25V === $pm25Max) { $pm25MaxTimes[] = $tPm25; }
				if($pm25Min === null || $pm25V < $pm25Min) { $pm25Min = $pm25V; $pm25MinTimes = array($tPm25); }
				elseif($pm25V === $pm25Min) { $pm25MinTimes[] = $tPm25; }
			}

			$feels[$i] = feelsLike($custl[6], $custl[4], $custl[9]);

			//cumulative rain
			if($i > 0) {
				$rnChange = $dat[10][$i] - $dat[10][$i-1];
				// account for potential glitches where rain decreases
				$rncum += ($rnChange > 0) ? $rnChange : 0;
			}
			$rncumArr[$i] = $rncum;

			//Frost hours
			if($custl[6] < 0) {
				$frostMins++;
			}
			//Day max
			if($custhr[$i] >= 9 && $custhr[$i] < 21) {
				if($custl[6] >= $daymax1) { $daymax1 = $custl[6]; $daymaxt1 = mktime($custhr[$i],$custmin[$i]); }
				if($custl[6] > $daymax2) { $daymax2 = $custl[6]; $daymaxt2 = mktime($custhr[$i],$custmin[$i]); }
			}
			//Night Min
			if($custhr[$i] < 9) {
				if($custl[6] <= $nightmin1) { $nightmin1 = $custl[6]; $nightmint1 = mktime($custhr[$i],$custmin[$i]); }
				if($custl[6] < $nightmin2) { $nightmin2 = $custl[6]; $nightmint2 = mktime($custhr[$i],$custmin[$i]); }
			}
			//Night Min Tomorrow
			if($custhr[$i] >= 21) {
				if($custl[6] <= $nightmin1T) { $nightmin1T = $custl[6]; $nightmint1T = mktime($custhr[$i],$custmin[$i]); }
				if($custl[6] < $nightmin2T) { $nightmin2T = $custl[6]; $nightmint2T = mktime($custhr[$i],$custmin[$i]); }
			}
			//Max rain rate
			for($r=1; $r<60; $r++) {
				if($i > $r) {
					$rnr[$i] = $dat[10][$i] - $dat[10][$i-$r];
					if($rnr[$i] > $RATE_THRESH) {
						if($r === 1) { $rr[$i] = 60*$rnr[$i]; }
						else { $rr[$i] = round(60/($r-1)*$rntipmm, 1); }
						break;
					}
				}
			}
			$w10 += $dat[3][$i];
			//10-min trend extremes
			if($i >= 10) {
				$w10 -= $dat[3][$i-10];
				$wind10[$i] = $w10 / 10;
				$rn10[$i] = $dat[10][$i] - $dat[10][$i-10];
				$t10[$i] = $dat[6][$i] - $dat[6][$i-10];
			}
			//		$w60 += $dat[3][$i]/60;
			//		$wind60[$i] = $w60;
			//hour trend extremes
			if($i > 60) {
				$tchangehr[$i] = $dat[6][$i] - $dat[6][$i-60];
				$hchangehr[$i] = $dat[7][$i] - $dat[7][$i-60];
			//			$w60 -= $dat[3][$i-60]/60;
				$rn60[$i] = $dat[10][$i] - $dat[10][$i-60];
			}

			// Wdir
			$dir_quantised = floor(($dat[5][$i] + 11.25) / 22.5) % 16;
			$windDirs[$dir_quantised][floor($dat[3][$i])]++;
		}

		//Trends
		if($end > 400) {
			$rnCums['10m'] = $rncumArr[$end-11];
			for($i = 1; $i <= 361; $i += 60) { //last 1-6hrs rain
				$rnCums[] = $rncumArr[$end-$i];
			}

			$trendLen = count($trendKeys);
			for($i = 1; $i <= 121; $i += 5) {
				for($j = 0; $j < $trendLen; $j++) {
					$trends[$i-1][$trendKeys[$j]] = $dat[$j+3][$end-$i];
				}
				$trends[$i-1]['rain'] = $rncumArr[$end-$i];
			}
		}

		if($daymax1 == -99) {
			$daymax1 = $timesMax['day'] = '-';
		} else {
			$timesMax['day'] = date( 'H:i', ($daymaxt1 + $daymaxt2) / 2 );
		}
		$mins['night'] = $nightmin1;
		$mins['nightTomoz'] = $nightmin1T;
		$maxs['day'] = $daymax1;
		$timesMin['night'] = date( 'H:i', ($nightmint1 + $nightmint2) / 2 );
		$timesMin['nightTomoz'] = date( 'H:i', ($nightmint1T + $nightmint2T) / 2 );

		$maxs['wind'] = max($dat[3]); $timesMax['wind'] = timeFromMM($maxs['wind'], $dat[3], $custhr, $custmin);
		$maxs['gust'] = max($dat[4]); $timesMax['gust'] = timeFromMM($maxs['gust'], $dat[4], $custhr, $custmin);

		$minFeel = min($feels); $timesMin['feel'] = timeFromMM($minFeel, $feels, $custhr, $custmin);
		$maxFeel = max($feels); $timesMax['feel'] = timeFromMM($maxFeel, $feels, $custhr, $custmin);
		$mins['feel'] = round($minFeel, 1);
		$maxs['feel'] = round($maxFeel, 1);

		if(is_array($rn60)) {
			$maxs['rnhr'] = max($rn60); if($maxs['rnhr'] > 0.2) { $timesMax['rnhr'] = timeFromMM($maxs['rnhr'], $rn60, $custhr, $custmin); }
			$maxs['tchangehr'] = max($tchangehr); $timesMax['tchangehr'] = timeFromMM($maxs['tchangehr'], $tchangehr, $custhr, $custmin);
			$maxs['hchangehr'] = max($hchangehr); $timesMax['hchangehr'] = timeFromMM($maxs['hchangehr'], $hchangehr, $custhr, $custmin);
			$tchhr = min($tchangehr); $timesMin['tchangehr'] = timeFromMM($tchhr, $tchangehr, $custhr, $custmin);
			$hchhr = min($hchangehr); $timesMin['hchangehr'] = timeFromMM($hchhr, $hchangehr, $custhr, $custmin);
			$mins['tchangehr'] = -1 * $tchhr;
			$mins['hchangehr'] = -1 * $hchhr;

		}
		if(is_array($t10)) {
			$w10max = max($wind10); $timesMax['w10m'] = timeFromMM($w10max, $wind10, $custhr, $custmin);
			$maxs['w10m'] = round($w10max, 1);
			$maxs['rn10'] = max($rn10); if($maxs['rn10'] > 0.2) { $timesMax['rn10'] = timeFromMM($maxs['rn10'], $rn10, $custhr, $custmin); }
			$t10min = min($t10); $timesMin['tchange10'] = timeFromMM($t10min, $t10, $custhr, $custmin);
			$mins['tchange10'] = -1 * $t10min;
			$maxs['tchange10'] = max($t10); $timesMax['tchange10'] = timeFromMM($maxs['tchange10'], $t10, $custhr, $custmin);
		}
		if(is_array($rr)) {
			$maxs['rate'] = max($rr);
			$timesMax['rate'] = timeFromMM($maxs['rate'], $rr, $custhr, $custmin);
			$maxs['rate'] = $maxs['rate'];
		}
		for($t = 6; $t < 10; $t++) {
			// Time of max/min is the mean time of the longest continuous period at that value
			$timesMin[$daytypes[$t]] = date('H:i', midpoint_of_longest($datt[$t]['timesMin'], 120));
			$timesMax[$daytypes[$t]] = date('H:i', midpoint_of_longest($datt[$t]['timesMax'], 120));
			$mins[$daytypes[$t]] = $datt[$t]['min'];
			$maxs[$daytypes[$t]] = $datt[$t]['max'];
			$means[$daytypes[$t]] = round( mean($dat[$t]), $round_pt[$t] );

			if($end > 61) {
				$hrChanges[$daytypes[$t]] = $dat[$t][$end-1] - $dat[$t][$end-61];
				$hr24Changes[$daytypes[$t]] = $dat[$t][$end-1] - $dat[$t][1];
			}
		}

		$hrChanges['wind'] = $dat[3][$end-1] - $dat[3][$end-61];
		$hr24Changes['wind'] = $dat[3][$end-1] - $dat[3][1];

		// PM2.5 summary - only when data was present this period
		if($pm25Count > 0) {
			$mins['pm25'] = $pm25Min;
			$maxs['pm25'] = $pm25Max;
			$means['pm25'] = round($pm25Sum / $pm25Count, 1);
			$timesMin['pm25'] = date('H:i', midpoint_of_longest($pm25MinTimes, 120));
			$timesMax['pm25'] = date('H:i', midpoint_of_longest($pm25MaxTimes, 120));
			if($end > 61 && isset($dat[11][$end-1]) && isset($dat[11][$end-61])) {
				$hrChanges['pm25'] = $dat[11][$end-1] - $dat[11][$end-61];
			}
			if($end > 1 && isset($dat[11][$end-1]) && isset($dat[11][1])) {
				$hr24Changes['pm25'] = $dat[11][$end-1] - $dat[11][1];
			}
		}

		$means['wind'] = round(mean($dat[3]), 1);
		$means['w10m'] = round(mean($wind10), 1);
		$means['wdir'] = wdirMean($dat[5], $dat[3]);
		$means['feel'] = round(mean($feels), 1);
		$means['rain'] = $rncum;
		if($means['rain'] < 0.2) {
			$maxs['rnhr'] = $maxs['rn10'] = null;
		}
		$rnCums[0] = $rncum;

		//rain duration
		if($rncum > 0 && $rnCums[0] - $rnCums[1] != 0) {
			$duration = 0;
			$lastTip = 1;
			for($i = 0; $i < $end; $i++) {
				if($rncumArr[$end-$i-1] == $rncumArr[$end-$i-2]) {
					$lastTip++;
				} else {
					$duration += $lastTip;
					$lastTip = 1;
				}
				if($lastTip >= 60) {
					break;
				}
			}
		}

		//wet hours rough estimate
		$wetmins = 0;
		if($rncum > 0) {
			$notRained = 0;
			$raining = false;
			for($i = 1; $i < $end-1; $i++) {
				$notRained++;
				if($rncumArr[$i] != $rncumArr[$i+1]) {
					$notRained = 0;
					$raining = true;
				}
				if($raining) {
					$wetmins++;
				}
				if($notRained > 30) {
					$raining = false;
				}
			}
		}
		$wethrs = ceil($wetmins / 60);

		//current rain rate guess (based on last rain tip - so inaccurate when tipped after long break -> revert to max rate
		if($rnCums[0] - $rnCums[1] != 0) {
			$last = 60;
			for($i = 1; $i < 61; $i++) {
				if($rncumArr[$end-$i-1] != $rncum) {
					$last = $i;
					break;
				}
			}
			$tipQuantity = ($last === 1) ? round(($rncum - $rncumArr[$end-2])/$rntipmm) : 1;
			$currRateGuess = round(60/$last*$rntipmm*$tipQuantity, 1);
			$currRate = ($currRateGuess > $maxs['rate']) ? $maxs['rate'] : $currRateGuess;
		} else {
			$currRate = 0;
		}

		if($procfil == date('Ymd')) {
			//last rain
			$prevRnOld = file_get_contents("lastrn");
			if($rncum > 0) {
				//Only look at recent values, since this script is meant to be run every minute anyway,
				// so in ideal conditions only really need to check most recent two rnCumArr values.
				//Also, this fixes an awkward bug that presents itself 24hrs after rain, ie. in rnCumArr[0] territory,
				// so it is best to avoid this
				$limitRnLook = 300;
				for($i = 1; $i < $limitRnLook; $i++) {
					if($rncumArr[$end-$i-1] != $rncum) {
						$prevRn = mktime($custhr[$end-1], $custmin[$end-1] - $i, 0);
						if($prevRn != $prevRnOld) {
							file_put_contents("lastrn", $prevRn);
						}
						break;
					}
				}
				if($i === $limitRnLook) {
					$prevRn = $prevRnOld;
				}
			} else {
				$prevRn = $prevRnOld;
			}

			$diff = time() - $prevRn;
			$ago = secsToReadable($diff);
			$dateAgo = date('jS M', $prevRn);
			if(date('Ymd') == date('Ymd', $prevRn)) {
				$dateAgo = 'Today';
			} elseif(date('Ymd', Date::mkdate(date('n'), date('j')-1)) == date('Ymd', $prevRn)) {
				$dateAgo = 'Yesterday';
			}
			$lastRnFull = HTML::acronym(date('H:i ', $prevRn) .' '. $dateAgo, $ago . ' ago', true);
		}

		//maxhr gust
		$maxhrgst = 0;
		for($i = 1; $i <= 60; $i++) {
			if($dat[4][$end-$i] > $maxhrgst) {
				$maxhrgst = $dat[4][$end-$i];
			}
		}

		// Pond temp
		if($procfil === "today" || $procfil == date('Ymd')) {
			$fildatm = file(ROOT."datm". Date::$yr_yest .".csv");
			$last_line_raw = $fildatm[count($fildatm) - 1];
			$last_line = explode(',', $last_line_raw);
			$pond_temp = $last_line[12];
		} else {
			$pond_temp = null;
		}

		$frosthrs = round($frostMins / 60, (int)($frostMins < 10) + 1);
		$rnDuration = roundToDp($duration / 60, 1);

		return array("min" => $mins, "max" => $maxs, "mean" => $means, "timeMin" => $timesMin, "timeMax" => $timesMax,
					"trend" => $trends, "trendRn" => $rnCums, "changeHr" => $hrChanges, "changeDay" => $hr24Changes,
					"misc" => array("frosthrs" => $frosthrs, "rnrate" => $currRate, "rnduration" => $rnDuration,
									"rnlast" => $lastRnFull, "wethrs" => $wethrs, "maxhrgst" => $maxhrgst, "cnt" => $end,
									"prevRn" => date('r', $prevRn), "prevRnOld" => date('r', $prevRnOld),
									"pondTemp" => $pond_temp
								),
					"windDirs" => $windDirs
				);
	}

	public static function timeFromMM($mm, $arr, $hrs, $mins) {
		$line = array_search($mm, $arr);
		return Util::zerolead($hrs[$line]).':'.Util::zerolead($mins[$line]);
	}

	public static function midpoint_of_longest($arr, $max_gap) {
		$curr_period = 0;
		$longest_period = 0;
		$longest_p_end = 0;
		$arrlen = count($arr);
		$arr[-1] = $arr[0];

		for($i = 0; $i < $arrlen; $i++) {
			if(abs($arr[$i] - $arr[$i - 1]) > $max_gap) {
				$curr_period = 0;
			}
			$curr_period++;
			if($curr_period > $longest_period) {
				$longest_period = $curr_period;
				$longest_p_end = $i;
			}
		}
		return $arr[$longest_p_end - floor($longest_period / 2)];
	}

	
}

?>