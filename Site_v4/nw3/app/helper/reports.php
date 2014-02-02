<?php
namespace nw3\app\helper;

use nw3\app\model\Variable;
use nw3\app\util\Html;
use nw3\app\util\Date;
use nw3\config\Admin;

/**
 * Generic helper for reports pages
 *
 * @author Ben LR
 */
abstract class Reports {

	static function var_dropdown($categories, $curCat) {
		echo '<select id="variable_select" name="vartype" onchange="this.form.submit()">';

		foreach ($categories as $cat => $subCats) {
			echo '<optgroup label="'.$cat.'">';
			foreach ($subCats as $subCat) {
				$subcat_var = Variable::$daily[$subCat];
				$selected = ($curCat == $subCat) ? Html::select : '';
				echo '<option value="'. $subCat .'"'. $selected .'>'. $subcat_var['description'] .'
					</option>';
			}
			echo '</optgroup>';
		}
		echo '</select>';
	}

	static function year_dropdown($curYear) {
		echo '<select id="year_select" name="year" onchange="this.form.submit()">';
		for($i = Admin::FIRST_YEAR_REPORTS; $i <= D_year; $i++) {
			echo '<option value="' . $i . '"';
			if($i == $curYear) { echo Html::select; }
			echo '>', $i, '</option>
				';
		}
		if(D_month !== 12 || $curYear === 0) {
			$selected = ($curYear === 0) ? Html::select : '';
			echo '<option value="0"'. $selected .'>Last 12m</option>';
		}
		echo '</select>';
	}

	static function months_head($months, $noday=false) {
		$day = $noday ? '' : 'Day';
		echo '<thead>
			<tr>
			<td>'. $day .'</td>
			';
		foreach ($months as $month) {
			echo '<td>'. Date::$months3[$month-1] .'</td>
			';
		}
		echo '</tr>
			</thead>';
	}

}

?>
