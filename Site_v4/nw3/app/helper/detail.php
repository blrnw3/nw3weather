<?php
namespace nw3\app\helper;

use nw3\app\model\Variable;
use nw3\app\util\Html;
use nw3\app\util\Date;
use nw3\config\Admin;

/**
 * Generic helper for Detailed pages
 *
 * @author Ben LR
 */
abstract class Detail {

	static function of_final_exp($data) {
		$cur = Variable::conv_anom($data['anom'], $data['type']);
		$fin = Variable::conv_anom($data['anom_f'], $data['type']);
		return Html::tip("Of final expected total: $fin", $cur, true);
	}

}

?>
