<?php
namespace nw3\app\model;

use nw3\app\model\Variable;
use nw3\app\model\Store;

abstract class Varilive {

	const TREND_STEADY = 'trend_steady';
	const TREND_RISING = 'trend_rising';
	const TREND_FALLING = 'trend_falling';
	const TREND_RISING_BIG = 'trend_rising_big';
	const TREND_FALLING_BIG = 'trend_falling_big';

	public $meta;
	public $name;
	public $type;
	public $abs_type;

	protected $now;
	protected $today;
	protected $hr24;

	protected $trend_thresh_short_val;
	protected $trend_thresh_short_time = 15;
	protected $trend_thresh_long_val;
	protected $trend_thresh_long_time = 30;

	public static function get_instance($varname) {
		$cls = new \ReflectionClass("nw3\\app\\varilive\\$varname");
		return $cls->newInstance();
	}

	function __construct($livename) {
		$this->now = Store::g();
		$this->today = $this->now->today;
		$this->hr24 = $this->now->hr24;

		$this->name = $livename;
		$this->meta = Variable::$live[$livename];
		if(!isset($this->type)) {
			$this->type = $livename;
		}
		$this->abs_type = $this->type;
	}

	public function current() {
		return $this->now->{$this->name};
	}

	public function min() {
		return [
			'val' => $this->today->min[$this->name],
			'dt' => $this->today->timeMin[$this->name],
		];
	}

	public function max() {
		return [
			'val' => $this->today->max[$this->name],
			'dt' => $this->today->timeMax[$this->name],
		];
	}

	public function mean() {
		return $this->today->mean[$this->name];
	}

	public function range() {
		return $this->today->max[$this->name] - $this->today->min[$this->name];
	}

	public function mean_period($period) {
		if($period === '24hr') {
			return $this->hr24->mean[$this->name];
		}
		throw new \Exception('Not yet implemented for period !== `24hr`');
	}

	public function rate($period) {
		// Proxy for Stor::change()
		return $this->now->change($this->name, $period);
	}

	public function conv($val, $show_unit=true, $show_sign=false) {
		// Proxy for Variable::conv()
		if($show_sign) {
			return Variable::conv($val, $this->abs_type, $show_unit, true);
		}
		return Variable::conv($val, $this->type, $show_unit);
	}

	public function trend_ternary() {
		$vartrS = $this->now->change($this->name, $this->trend_thresh_short['time']);
		$vartrL = $this->now->change($this->name, $this->trend_thresh_long['time']);

		//small short-term trend, or short/long trends opposite, or long trend too small
		if( abs($vartrS) == 0 || ( ($vartrS > 0) != ($vartrL > 0) ) || abs($vartrL) < $this->trend_thresh_short['val'] ) {
			return self::TREND_STEADY;
		} else {
			$isSmallTrend = abs($vartrL) >= $this->trend_thresh_short['val'] && abs($vartrL) <= $this->trend_thresh_long['val'];
			if($vartrL > 0) {
				return $isSmallTrend ? self::TREND_RISING : self::TREND_RISING_BIG;
			}
			return $isSmallTrend ? self::TREND_FALLING : self::TREND_FALLING_BIG;
		}
	}

}

?>
