<?php
namespace nw3\data\weather;

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
			'group' => 'temperature',
			'colour' => '#FFD750' #for graphs
		),
		'tmax' => array(
			'description' => 'Maximum Temperature',
			'group' => 'temperature',
			'colour' => 'orange'
		),
		'tmean' => array(
			'description' => 'Mean Temperature',
			'group' => 'temperature',
			'colour' => 'tan3'
		),

		'hmin' => array(
			'description' => 'Minimum Humidity',
			'group' => 'humidity',
			'colour' => 'chartreuse'
		),
		'hmax' => array(
			'description' => 'Maximum Humidity',
			'group' => 'humidity',
			'colour' => 'darkolivegreen'
		),
		'hmean' => array(
			'description' => 'Mean Humidity',
			'group' => 'humidity',
			'colour' => 'chartreuse3'
		),

		'pmin' => array(
			'description' => 'Minimum Pressure',
			'group' => 'pressure',
			'colour' => 'darkorchid4'
		),
		'pmax' => array(
			'description' => 'Maximum Pressure',
			'group' => 'pressure',
			'colour' => 'orchid1'
		),
		'pmean' => array(
			'description' => 'Mean Pressure',
			'group' => 'pressure',
			'colour' => 'purple'
		),

		'wmean' => array(
			'description' => 'Mean Wind Speed',
			'group' => 'wind',
			'colour' => 'red'
		),
		'wmax' => array(
			'description' => 'Maximum Wind Speed',
			'group' => 'wind',
			'colour' => 'firebrick1'
		),
		'gust' => array(
			'description' => 'Maximum Gust Speed',
			'group' => 'wind',
			'colour' => 'firebrick2'
		),
		'wdir' => array(
			'description' => 'Mean Wind Direction',
			'group' => 'wind',
			'colour' => 'firebrick3'
		),

		'rain' => array(
			'description' => 'Rainfall',
			'group' => 'rain',
			'colour' => 'royalblue'
		),
		'hrmax' => array(
			'description' => 'Maximum Hourly Rain',
			'group' => 'rain',
			'colour' => 'royalblue1'
		),
		'10max' => array(
			'description' => 'Maximum 10-min Rain',
			'group' => 'rain',
			'colour' => 'royalblue2'
		),
		'rate' => array(
			'description' => 'Maximum Rain Rate',
			'group' => 'rain',
			'colour' => 'royalblue3'
		),

		'dmin' => array(
			'description' => 'Minimum Dew Point',
			'group' => 'dew',
			'colour' => 'darkseagreen'
		),
		'dmax' => array(
			'description' => 'Maximum Dew Point',
			'group' => 'dew',
			'colour' => 'darkslategray'
		),
		'dmean' => array(
			'description' => 'Mean Dew Point',
			'group' => 'dew',
			'colour' => 'darkseagreen4'
		),

		't24min' => array(
			'description' => '24hr Min Temperature (00-00)',
			'group' => 'temperature',
			'colour' => 'darkseagreen4'
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
			'anomable' => true, #Whether a corresponding climate variable exists (i.e. an anomaly can be computed
			'colour' => 0 #For CSS
		)
	);
}
?>
