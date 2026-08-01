<?php
namespace nw3\app\util;

/**
 *
 * @author Ben LR
 */
class Report {

	const START_YEAR = 2009;

	static $rank_num_to_show = [
		'daily' => 10,
		'monthly' => 10,
		'c_monthly' => 5
	];

	static $lhm = ['Low','High','Mean'];
	static $lhmFull = ['Lowest','Highest','Mean'];
	static $mmm = ['min', 'max', 'mean'];
	static $mmmFull = ['Minimum','Maximum','Mean'];
	static $mmmr = ['Min', 'Max', 'Mean', 'Range'];
	static $meanOrTotal = ['Mean', 'Total'];

	static $pgather = [7,31,365];

}

?>
