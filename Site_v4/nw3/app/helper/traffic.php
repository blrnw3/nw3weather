<?php
namespace nw3\app\helper;

use nw3\data;
use nw3\app\util\Maths;
/**
 * Website traffic helper
 * @author Ben LR
 */
class Traffic {

	public $annual_summary = array(
		'sum' => 0,
		'mean' => 0,
		'min' => INT_MAX,
		'max' => INT_MIN,
	);

//		<tr class="rowdark">
//			<td>2011</td>
//			<td>~12,000</td>
//		</tr>
	/**
	 * Generates the tfoot and tbody summary table from the annual data
	 */
	function prepare_annual_data_table() {
		foreach (data\Traffic::$annual as $year => $data) {
			$this->annual_summary['sum'] += $data['sum'];
			$this->annual_summary['mean'] += $data['mean'];
			if($data['min'] < $this->annual_summary['min']) {
				$this->annual_summary['min'] = $data['min'];
			}
			if($data['max'] > $this->annual_summary['max']) {
				$this->annual_summary['max'] = $data['max'];
			}
		}
		$this->annual_summary['mean'] = round($this->annual_summary['mean'] / count(data\Traffic::$annual));
	}

}

?>
