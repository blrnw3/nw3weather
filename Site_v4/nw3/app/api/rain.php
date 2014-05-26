<?php
namespace nw3\app\api;

use nw3\app\model\Rain as Rn;
use nw3\app\model\Variable as Vari;

/**
 * All rain stats n stuff
 */
class Rain extends \nw3\app\core\Api {

	private $rain;

	function __construct() {
		parent::__construct();
		$this->rain = new Rn();
	}

	/**
	 * TODO: split out period tots and days into into own table
	 * @return type
	 */
	function current_latest() {
		$now = $this->rain->live;
		$totals = $this->rain->totals();
		$days = $this->rain->days();

		$data = array(
			'rn' => array(
				'descrip' => 'Daily Rain (00-00)',
				'val' => $now->rain,
			),
			'rn10' => array(
				'descrip' => 'Rain Last 10 mins',
				'val' => $now->change('rain', 10),
			),
			'rnhr' => array(
				'descrip' => 'Rain Last Hour',
				'val' => $now->change('rain', '1h'),
			),
			'rn3h' => array(
				'descrip' => 'Rain Last 3 hrs',
				'val' => $now->change('rain', '3h'),
			),
			'rn6h' => array(
				'descrip' => 'Rain Last 6 hrs',
				'val' => $now->change('rain', '6h'),
			),
			'rn24h' => array(
				'descrip' => 'Rain Last 24 hrs',
				'val' => $now->change('rain', '24h'),
			),
			'rndur' => array(
				'descrip' => 'Rain Duration',
				'val' => $now->hr24->rnduration,
				'type' => Vari::Hours
			),
			'rnrate' => array(
				'descrip' => 'Rain Rate',
				'val' => $now->hr24->rnrate,
				'type' => Vari::RainRate
			),
			'rndur24' => array(
				'descrip' => 'Past 24hrs Duration',
				'val' => $now->hr24->wethrs,
				'type' => Vari::Hours
			),
			'rnlast' => array(
				'descrip' => 'Most Recent Rain',
				'val' => $now->hr24->rnlast,
				'type' => Vari::Laststamp
			),
			'rnyest' => array(
				'descrip' => 'Yesterday\'s Rain',
				'val' => $totals[Rn::YESTERDAY]['val'],
			),
		);

		foreach (Rn::$periods as $n) {
			$data["rn$n"] = array(
				'descrip' => "Last $n day's Rain"
			) + $totals[$n];
		}

		$data += array(
			'rnmon' => array(
				'descrip' => 'Month Rain',
			) + $totals[Rn::NOWMON],
			'rnyr' => array(
				'descrip' => 'Annual Rain',
			) + $totals[Rn::NOWYR],
			'rnseas' => array(
				'descrip' => D_seasonname.' Rain',
			) + $totals[Rn::NOWSEAS],
		);

		$currspell = $this->rain->curr_spell($now->rain > 0);
		$currspell_type = ($now->rain > 0) ? 'Wet' : 'Dry';
		$data += array(
			'currspell' => array(
				'descrip' => "Consecutive $currspell_type Days",
				'val' => $currspell,
				'type' => Vari::Days
			)
		);

		$data += array(
			'daysmon' => array(
				'descrip' => 'Month Rain Days',
				'type' => Vari::Days
			) + $days[Rn::NOWMON],
			'daysyr' => array(
				'descrip' => 'Annual Rain Days',
				'type' => Vari::Days
			) + $days[Rn::NOWYR],
			'daysseas' => array(
				'descrip' => D_seasonname .' Rain Days',
				'type' => Vari::Days
			) + $days[Rn::NOWSEAS]
		);
		foreach (Rn::$periods as $n) {
			$data["rn{$n}d"] = array(
				'descrip' => "Last $n days Rain Days",
				'type' => Vari::Days
			) + $days[$n];
		}

		$data += array(
			'rnyrago' => array(
				'descrip' => 'Daily Rain Last Year',
			) + $totals[Rn::DAY_YR_AGO],
			'rtdmonago' => array(
				'descrip' => 'Last Month Rain-to-date',
			) + $totals[Rn::CUM_MON_AGO],
			'rtdyrago' => array(
				'descrip' => 'Last Year Rain-to-date',
			) + $totals[Rn::CUM_YR_AGO],
		);

		//Default type
		foreach ($data as &$dat) {
			if(!key_exists('type', $dat)) {
				$dat['type'] = Vari::Rain;
			}
		}
		return $data;
	}
}

?>
