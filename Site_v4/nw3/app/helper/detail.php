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

	static function date($dt_string) {
		$dt = new \DateTime($dt_string);
		# TODO - format based on period type
		return $dt->format('d M Y');
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
