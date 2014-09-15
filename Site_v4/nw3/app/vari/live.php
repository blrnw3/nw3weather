<?php
namespace nw3\app\vari;

use nw3\app\core\Db;
use nw3\app\core\Alias;

abstract class Live extends \nw3\app\model\Detail {

	public $live;
	protected $live_var_sql;

	function __construct($livename) {
		parent::__construct($livename);
		$this->live_var_sql = $this->live_var;
	}

	public function current() {
		return $this->now->{$this->live_var};
	}

	public function live() {
		return [$this->main_live()];
	}

	public function trend_avgs($avg_periods) {
		$data = [];
		foreach($avg_periods as $period) {
			$val = $this->trend_avg($period);
			$data[$period] = $val;
		}
		return $data;
	}

	public function trend_diffs($diff_periods) {
		$data = [];
		foreach($diff_periods as $period) {
			$val = $this->now->change($this->live_var, $period);
			$data[$period] = $val;
		}
		return $data;
	}

	protected function main_live() {
		return [
			'val' => $this->now->{$this->live_var},
			'descrip' => $this->live['name'],
			'type' => $this->live['group']
		];
	}

	protected function value_today() {
		return ['val' => $this->now->today->mean[$this->live_var]];
	}
	protected function value_hr24() {
		return ['val' => $this->now->hr24->mean[$this->live_var]];
	}

	protected function trend_avg($duration) {
		$q_inner = $this->db->query(new Alias($this->live_var_sql, 'stuff'))->tbl('live')
			->limit($duration)->order(MAX, 't');
		return $this->db->query(Db::avg('stuff'))->nest($q_inner)->scalar();
	}


	/**
	 * TODO - this is hideously similar to the one in self::parent
	 */
	function record_24hr() {
		$var = $this->live_var;
		$all = $this->db->query('t', $var)->tbl('live')->filter(Db::not_null($var))->all();
		$min = INT_MAX;
		$max = INT_MIN;
		$n = 1440;
		$cum = 0.0;

		foreach ($all as $i => $db_val) {
			$dt = $db_val['t'];
			$cum += $db_val[$var];
			if ($i >= $n) {
				$cum -= $all[$i - $n][$var];
				if ($cum < $min) {
					$min = $cum;
					$min_end = $dt;
				}
				if ($cum > $max) {
					$max = $cum;
					$max_end = $dt;
				}
			}
		}
		if($this->summable) $n = 1;
		return [
			'min' => [
				'val' => $min / $n,
				'dt' => $min_end
			],
			'max' => [
				'val' => $max / $n,
				'dt' => $max_end
			],
		];
	}
}

?>
