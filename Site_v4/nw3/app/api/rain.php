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

		$this->wettest = $this->rain->extremes()['max'];
		$this->ratest = $this->ratemax->extremes()['max'];
		$this->hrmaxest = $this->hrmax->extremes()['max'];
		$this->r10maxest = $this->r10max->extremes()['max'];
	}

	function current_latest() {
		$now = Store::g();

		$data = [
			'rn' => [
				'descrip' => 'Daily Rain (00-00)',
				'val' => $now->rain,
			],
			'rn10' => [
				'descrip' => 'Rain Last 10 mins',
				'val' => $now->change('rain', 10)
			],
			'rnhr' => [
				'descrip' => 'Rain Last Hour',
				'val' => $now->change('rain', '1h')
			],
			'rn3h' => [
				'descrip' => 'Rain Last 3 hrs',
				'val' => $now->change('rain', '3h')
			],
			'rn6h' => [
				'descrip' => 'Rain Last 6 hrs',
				'val' => $now->change('rain', '6h'),
			],
			'rn24h' => [
				'descrip' => 'Rain Last 24 hrs',
				'val' => $now->change('rain', '24h'),
			],
			'rndur' => [
				'descrip' => 'Rain Duration',
				'val' => $now->hr24->rnduration,
				'type' => Vari::Hours
			],
			'rnrate' => [
				'descrip' => 'Rain Rate',
				'val' => $now->hr24->rnrate,
				'type' => Vari::RainRate
			],
			'rndur24' => [
				'descrip' => 'Past 24hrs Duration',
				'val' => $now->hr24->wethrs,
				'type' => Vari::Hours
			],
			'rnlast' => [
				'descrip' => 'Most Recent Rain',
				'val' => $now->hr24->rnlast,
				'type' => Vari::Laststamp
			],
		];
		// Spell
		$currspell = $this->rain->curr_spell($now->rain > 0);
		$currspell_type = ($now->rain > 0) ? 'Wet' : 'Dry';
		$data += [
			'currspell' => [
				'descrip' => "Consecutive $currspell_type Days",
				'val' => $currspell,
				'type' => Vari::Days
			]
		];
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
		$data = [
			'rain' => $this->rain->values() + [Rn::TODAY => [
				'val' => $now->rain
			]],
			'ratemax' => $this->ratemax->values() + [Rn::TODAY => [
				'val' => $now->today->max['rate'],
				'dt' => $now->today->timeMax['rate']
			]],
			'hrmax' => $this->hrmax->values() + [Rn::TODAY => [
				'val' => $now->today->max['rnhr'],
				'dt' => $now->today->timeMax['rnhr']
			]],
			'r10max' => $this->r10max->values() + [Rn::TODAY => [
				'val' => $now->today->max['rn10'],
				'dt' => $now->today->timeMax['rn10']
			]]
		];
		foreach($data as $k => &$dat) {
			$dat = [
				'data' => $dat,
				'type' => $k,
				'descrip' => Vari::$daily[$k]['description']
			];
		}
		return $data;
	}


	function extremes() {
		$data = [
			'rain' => ['data' => $this->rain->totals()],
			'rdays' => ['data' => $this->rain->days()],
			'wettest' => ['data' => $this->wettest, 'type' => Vari::Rain, 'descrip' => 'Wettest Day']
//			'ratemax' => $ratest['max'] + [
//				'descrip' => 'Max Rain Rate',
//				'type' => Vari::RainRate
//			],
//			'hrmax' => $hrmaxest['max'] + [
//				'descrip' => 'Max Hr Rn',
//				'type' => Vari::Rain
//			],
//			'r10max' => $r10maxest['max'] + [
//				'descrip' => 'Max 10m Rn',
//				'type' => Vari::Rain
//			)
		];
		foreach($data as $k => $dat) {
			if(!key_exists('type', $dat)) {
				$data[$k]['type'] = $k;
			}
			if(!key_exists('descrip', $dat)) {
				$data[$k]['descrip'] = Vari::$daily[$k]['description'];
			}
		}
		xdebug_break();
		return $data;
	}
}

?>
