<?php
namespace nw3\app\api;

use nw3\app\util\Date;
use nw3\app\util\Time;
use nw3\app\model\Store;
use nw3\app\model\Variable;
use nw3\app\model\Varilive;
use nw3\app\varilive\Sky;
use nw3\config\Station;

class Latest {

	private $vars;
	private $sky;

	public function __construct() {
		$varnames = ['temp', 'rain', 'wind', 'humi', 'pres',
			'dewp', 'gust', 'feel', 'wdir', 'rate'];
		foreach ($varnames as $varname) {
			$this->vars[$varname] = Varilive::get_instance($varname);
		}
		$this->sky = new Sky();
	}

	public function weather_report() {
		return [
			'forecast' => $this->sky->forecast(),
			'conditions' => $this->sky->condition(),
			'station_stats' => $this->station_stats(),
			'graph' => 'TODO',
			'webcam' => 'TODO',
		];
	}

	public function live_metadata() {
		$data = [];
		foreach ($this->vars as $varname => $var) {
			$data[$varname] = $var->meta;
		}
		$data['wind']['name'] = 'Wind';
		return $data;
	}

	public function live($raw=false) {
		$now = Store::g();
		$data = [];
		foreach($this->vars as $varname => $var) {
			$dat = [
				'now' => $var->current(),
				'trend' => $var->trend_ternary(),
				'min' => $var->min(),
				'max' => $var->max(),
				'mean' => $var->mean_period('24hr'),
				'rate_hr' => $var->rate(60),
				'rate_24hr' => $var->rate('24hr'),
			];
			// This call is used by ajax so until I do front-end formatting, have to do this
			if(!$raw) {
				$dat['now'] = $var->conv($dat['now']);
				if($dat['min']['val']) {
					$dat['min']['val'] = $var->conv($dat['min']['val']);
				}
				if($dat['max']['val']) {
					$dat['max']['val'] = $var->conv($dat['max']['val']);
				}
				$dat['mean'] = $var->conv($dat['mean']);
				$dat['rate_hr'] = $var->conv($dat['rate_hr'], true, true);
				$dat['rate_24hr'] = $var->conv($dat['rate_24hr'], true, true);
			}
			$data[$varname] = $dat;
		}
		$misc = [
			'max_hr_gust' => $now->hr24->maxhrgst,
			'bft_wind_now' => Variable::bft($now->wind),
			'rn_last_10' => $this->vars['rain']->rate(10),
			'today_temp_range' => $this->vars['temp']->range(),
			'last_rn' => $now->hr24->rnlast,
			'max_hour_rn' => $now->hr24->max['rnhr'],
			'10m_wind' => $now->w10m,
			'10m_wdir' => 'TODO',
			'month_rn' => 'TODO'
		];
		if(!$raw) {
			$misc['max_hr_gust'] = Variable::conv($misc['max_hr_gust'], Variable::Wind);
			$misc['rn_last_10'] = Variable::conv($misc['rn_last_10'], Variable::Rain);
			$misc['max_hour_rn'] = Variable::conv($misc['max_hour_rn'], Variable::Rain);
			$misc['today_temp_range'] = Variable::conv($misc['today_temp_range'], Variable::AbsTemp);
			$misc['last_rn_pretty'] = Time::secsToReadable(D_now - $misc['last_rn']) . ' ago';
			$misc['last_rn'] = Variable::conv($misc['last_rn'], Variable::Timestamp);
			$misc['10m_wind'] = Variable::conv($misc['10m_wind'], Variable::Wind);

			$misc['last_updated_pretty'] = date('H:i:s', $now->updated);
		}
		$data['misc'] = $misc;
		return $data;
	}

	function monthly_report($datetime) {
		$export = null;
		$rep_path = $this->get_monthly_report($datetime);
		if(!$rep_path) {
			$rep_path = $this->get_monthly_report($datetime - Date::secs_DAY);
			if(!$rep_path) {
				return "Report for $datetime not available.";
			}
		}
		include $rep_path; // defines $export

		$repMonth = $export['date'][0];
		$repYear = $export['date'][1];

		$tempComparator = $export['temp'][0];
		$tempAv = Variable::conv($export['temp'][1], Variable::Temperature);
		$tempAnom = Variable::conv($export['temp'][2], Variable::AbsTemp, true, true);
		$tempLo = Variable::conv($export['temp'][3], Variable::Temperature);
		$tempHi = Variable::conv($export['temp'][4], Variable::Temperature);

		$rainComparator = $export['rain'][0];
		$rainAv = Variable::conv($export['rain'][1], Variable::Rain);
		$rainAnom = $export['rain'][2];
		$rainCnt = $export['rain'][3];
		$rainHi = Variable::conv($export['rain'][4], Variable::Rain);
		$rainYr = Variable::conv($export['rain'][5], Variable::Rain);
		$rainYrAnom = $export['rain'][6];
		$rainYrCnt = $export['rain'][7];

		$sunComparator = $export['sun'][0];
		$sunAv = Variable::conv($export['sun'][1], Variable::Hours);
		$sunAnom = $export['sun'][2];
		$sunMax = $export['sun'][3];
		$sunCnt = $export['sun'][4];
		$sunHi = $export['sun'][5];

		$notWintry = ($export['winter'][0] == 0 && $export['winter'][1] == 0);
		$fallSnow = $export['winter'][2];
		$fallSnowAnom = $export['winter'][3];
		$fallSnowAnom2 = $export['winter'][4];
		$AFsFull = $export['winter'][5];
		$AFavr = $export['winter'][6];
		$lySnow = $export['winter'][7];
		$LSavr = $export['winter'][8];
		$maxDepth = Variable::conv($export['winter'][9], Variable::Snow);

		$hail = $export['other'][0];
		$thunder = $export['other'][1];
		$fog = $export['other'][2];
		$bigRnsFull = $export['other'][3];
		$mm10 = Variable::conv($export['other'][4], Variable::Rain, true, false, -1);
		$bigGusts = $export['other'][5];
		$mph30 = Variable::conv($export['other'][6], Variable::Wind, true, false, -1);

		$output = "<h2>".date('F Y', Date::mkdate($repMonth, 1, $repYear)) ."</h2>
			<dl>
			<dt class='temp'>Temperature</dt>
			<dd>Overall, the month was $tempComparator average, with a mean of <b>$tempAv</b> ($tempAnom from the <abbr title='Long-term average'>LTA</abbr>).
				<br />The absolute low was <b>$tempLo</b>, and the highest <b>$tempHi</b>.
			</dd>
			<dt class='rain'>Rainfall</dt>
			<dd>Came in $rainComparator the long-term average, at <b>$rainAv</b> ($rainAnom%) across <b>$rainCnt</b> days of <abbr title='&gt;0.25mm'>recordable rain</abbr>.
				The most rainfall recorded in a single day (starting at midnight) was <b>$rainHi</b>.
				The cumulative annual total for $repYear now stands at <b>$rainYr</b> ($rainYrAnom%) from <b>$rainYrCnt</b> rain days.
			</dd>
			<dt class='sun'>Sunshine</dt>
			<dd>A $sunComparator month, with <b>$sunAv</b> ($sunAnom%) from a possible $sunMax. <br />
				<b>$sunCnt</b> days had more than a minute of sunshine, the maximum being <b>$sunHi hrs</b>.
			</dd>
			<dt class='snow'>Winter Events</dt>
			<dd>";
		$output .= $notWintry ?
			"No snow or frost observed." :
			"There $fallSnow of falling snow or sleet
			($fallSnowAnom $fallSnowAnom2 the <abbr title='Long-term average'>LTA</abbr>),
				and $AFsFull ($AFavr). <br />
			$lySnow of lying snow at 09z were observed ($LSavr), with a max depth of <b>$maxDepth</b>.
			";
		$output .= "</dd>
			<dt>Other Events</dt>
			<dd>There $hail of hail, <b>$thunder</b> of thunder, <b>$fog</b> with fog at 09z.
				$bigRnsFull had &gt;$mm10 of rain, and <b>$bigGusts</b> with gusts &gt;$mph30.
			</dd>
			</dl>
			<p>
			All long-term <a href='wxaverages.php' title='Long-term NW3 climate averages'>climate averages</a>
			are with respect to the period 1971-2000. &nbsp;
			<a href='wxhistmonth.php'>View full report</a>
			</p>
			";

		return $output;
	}

	private function station_stats() {
		$st = strtotime(Station::START_DATE);
		$st_nw3 = strtotime(Station::START_DATE_NW3);
		return [
			'days_running' => (int)((D_now - $st)/Date::secs_DAY),
			'days_running_nw3' => (int)((D_now - $st_nw3)/Date::secs_DAY)
		];
	}

	private function get_monthly_report($timestamp) {
		$root = __DIR__.'/../../data/reports/';
		$repMonth = date('n', $timestamp);
		$repYear = date('Y', $timestamp);
		$repFile = $root.$repYear."/report$repMonth.php";
		if(file_exists($repFile)) {
			return $repFile;
		}
		return false;
	}

}

?>

