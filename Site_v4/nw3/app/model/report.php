<?php
namespace nw3\app\model;

use nw3\app\model\Variable;

/**
 * Helper for generation of historical reports for a partiular variable
 *
 * @author Ben LR
 */
abstract class Report {

	const BANDING_MONTHLY = 1;
	const BANDING_COUNTS = 2;
	const BANDING_CUMULATIVE = 3;

	public $categories = array();

	function __construct() {
		//Generate the categories
		foreach (Variable::$daily as $varname => $var) {
			$cat = $var['category'];
			if($cat) {
				if(key_exists($cat, $this->categories)) {
					$this->categories[$cat][] = $varname;
				} else {
					$this->categories[$cat] = array($varname);
				}
			}
		}


	}
}

?>
