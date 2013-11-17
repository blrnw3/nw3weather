<?php
namespace data;

/*
 * Read-only, static, fixed properties of weather variables
 */
class Variable {

	public static $live = array(
		'temp' => array(
			'name' => 'Temperature',
			'group' => 'Temperature'
		)
	);


	public static $daily = array(
		'tmin' => array(
			'description' => 'Minimum Temperature',
			'group' => 'Temperature',
			'colour' => '#FFD750' #for graphs
		),
		'tmax' => array(
			'description' => 'Maximum Temperature',
			'group' => 'Temperature',
			'colour' => 'orange'
		),
		'tmin' => array(
			'description' => 'Mean Temperature',
			'group' => 'Temperature',
			'colour' => 'tan3'
		),
	);

	/**
	 * A variable (daily, monthly etc.) can belong to a group and thus inherit
	 * common properties such as units. Also enables view-based grouping
	 * @var Object
	 */
	public static $groups = array(
		'Temperature' => array(
			'unit' => 'C',
			'summable' => false,
			'anomable' => true, #Whether a climate variable exists (i.e. an anomaly can be computed
			'colour' => 0 #For CSS
		)
	);
}
?>
