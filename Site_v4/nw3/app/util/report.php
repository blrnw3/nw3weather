<?php
namespace nw3\app\util;

/**
 *
 * @author Ben LR
 */
class Report {

	const START_YEAR = 2009;

	static $rank_num_to_show = array(
		'daily' => 10,
		'monthly' => 10,
		'c_monthly' => 5
	);

	static $lhm = array('Low','High','Mean');
	static $lhmFull = array('Lowest','Highest','Mean');
	static $mmm = array('min', 'max', 'mean');
	static $mmmFull = array('Minimum','Maximum','Mean');
	static $mmmr = array('Min', 'Max', 'Mean', 'Range');
	static $meanOrTotal = array('Mean', 'Total');

	static $pgather = array(7,31,365);

}

?>
