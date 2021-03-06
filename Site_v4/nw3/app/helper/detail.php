<?php
namespace nw3\app\helper;

use nw3\app\model\Variable;
use nw3\app\model\Detail as mD;
use nw3\app\util\Html;
use nw3\app\util\Date;
use nw3\config\Admin;

/**
 * Generic helper for Detailed pages
 *
 * @author Ben LR
 */
abstract class Detail {

	const MULTI = 0;
	const SINGLE = 1;

	static function of_final_exp($data, $type=null) {
		$conv_type = is_null($type) ? $data['type'] : $type;
		$cur = Variable::conv_anom($data['anom'], $conv_type);
		$fin = Variable::conv_anom($data['anom_f'], $conv_type);
		return Html::tip("Of final expected total: $fin", $cur, true);
	}

	static function record_yr_range() {
		$now_yr = D_yest_year;
		return "(2009 - $now_yr)";
	}

	static function date($dt_string, $period, $rec_type) {
		// Override
		if($rec_type === false) {
			return $dt_string;
		}
		// Time, no date format required
		if($period && !mD::$periods[$period]['multi']) {
			return $dt_string;
		}
		if($rec_type === mD::YEARLY) {
			return substr($dt_string, 0, 4);
		}

		try {
			$dt = new \DateTime($dt_string);
		} catch (\Exception $ex) { // Probably an integer
			$dt = \DateTime::createFromFormat('U', $dt_string);
		}

		if($rec_type === mD::SEASONAL) {
			$mon = (int)$dt->format('n');
			$yr = (int)$dt->format('Y');
			return Date::season_name_from_month($mon) .' '. $yr;
		}

		if($period && $rec_type === mD::MONTHLY) {
			$format = key_exists('mon_format', mD::$periods[$period]) ? mD::$periods[$period]['mon_format'] : 'M Y';
		} elseif($period && $rec_type === mD::DAILY) {
			$format = key_exists('format', mD::$periods[$period]) ? mD::$periods[$period]['format'] : 'jS M Y';
		} elseif($rec_type) { // Custom format
			$format = $rec_type;
		} else {
			$format = "[Y-m-d] ($period, $rec_type)";
		}
		return $dt->format($format);
	}

	static function date_custom($dt_string, $format) {
		try {
			$dt = new \DateTime($dt_string);
		} catch (\Exception $ex) { // Probably an integer
			$dt = \DateTime::createFromFormat('U', $dt_string);
		}
		return $dt->format($format);
	}

	static function filter_data(&$data, $field, $cond) {
		foreach ($data as &$dat) {
			foreach ($dat['data'] as $p => $d) {
				if(mD::$periods[$p][$field] == $cond) {
					unset($dat['data'][$p]);
				}
			}
		}
		return $data;

	}
	static function filter_recent($data) {
		return self::filter_data($data, 'record', true);
	}

	static function filter_records($data) {
		return self::filter_data($data, 'record', false);
	}

//	static function headers($type) {
//		if($type === self::MULTI) {
//			$periods = mD::get_periods('multi');
//		} else {
//			$periods = mD::get_periods('multi', true, true);
//		}
//		foreach ($periods as $p) {
//			$name = mD::$periods[$p]['descrip'];
//			echo "<td>$name</td>
//			";
//		}
//	}

}

?>
