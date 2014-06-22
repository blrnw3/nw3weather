<?php
namespace nw3\app\api;

use nw3\app\model\Rain as Rn;
use nw3\app\model\Detail;
use nw3\app\model\Store;
use nw3\app\model\Variable as Vari;

/**
 * All rain stats n stuff
 */
class Rain extends \nw3\app\core\Api {

	private $rain;
	private $ratemax, $r10max, $hrmax;
	private $wettest, $ratest, $hrmaxest, $r10maxest;

	function __construct() {
		parent::__construct();
		$this->rain = new Rn();
		$this->ratemax = new Detail('ratemax');
		$this->r10max = new Detail('r10max');
		$this->hrmax = new Detail('hrmax');

		$this->wettest = $this->rain->extremes();
		$this->ratest = $this->ratemax->extremes();
		$this->hrmaxest = $this->hrmax->extremes();
		$this->r10maxest = $this->r10max->extremes();
	}

	function current_latest() {
		$now = Store::g();

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
		);
		// Spell
		$currspell = $this->rain->curr_spell($now->rain > 0);
		$currspell_type = ($now->rain > 0) ? 'Wet' : 'Dry';
		$data += array(
			'currspell' => array(
				'descrip' => "Consecutive $currspell_type Days",
				'val' => $currspell,
				'type' => Vari::Days
			)
		);
		//Default type
		foreach ($data as &$dat) {
			if(!key_exists('type', $dat)) {
				$dat['type'] = Vari::Rain;
			}
		}
		return $data;
	}

	function recent_values() {
		$now = Store::g();
		return array(
			'rain' => $this->rain->values() + array(Rn::TODAY => array(
				'val' => $now->rain
			)),
			'ratemax' => $this->ratemax->values() + array(Rn::TODAY => array(
				'val' => $now->today->max['rate'],
				'dt' => $now->today->timeMax['rate']
			)),
			'hrmax' => $this->hrmax->values() + array(Rn::TODAY => array(
				'val' => $now->today->max['rnhr'],
				'dt' => $now->today->timeMax['rnhr']
			)),
			'r10max' => $this->r10max->values() + array(Rn::TODAY => array(
				'val' => $now->today->max['rn10'],
				'dt' => $now->today->timeMax['rn10']
			))
		);
	}


	function extremes() {
		return array(
			'rain' => $this->rain->totals() + array(
				'descrip' => 'Rainfall',
				'type' => Vari::Rain
			),
			'rain_days' => $this->rain->days() + array(
				'descrip' => 'Rain Days',
				'type' => Vari::None
			),
			'wettest' => $wettest['max'] + array(
				'descrip' => 'Wettest Day',
				'type' => Vari::Rain
			),
			'ratemax' => $ratest['max'] + array(
				'descrip' => 'Max Rain Rate',
				'type' => Vari::RainRate
			),
			'hrmax' => $hrmaxest['max'] + array(
				'descrip' => 'Max Hr Rn',
				'type' => Vari::Rain
			),
			'r10max' => $r10maxest['max'] + array(
				'descrip' => 'Max 10m Rn',
				'type' => Vari::Rain
			)
		);
	}
}

?>
