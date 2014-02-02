<?php
namespace nw3\config;
/**
 * Website admin config
 *
 * @author Ben LR
 */

class Admin {
	/** * Spits out useful debug info in case of errors */
	const DEBUG = true;

	/** * Name of the Controller to use by default, i.e. for the Site root */
	const HOME_ROOT = 'home';

	const STATUS_MESSAGE = 'Site version 4 coming soon!';
	const SHOW_MESSAGE = false;

	const FAULT_MESSAGE = "A local hardware fault has been detected and is preventing data updates -
			the site administrator has been notified and will investigate ASAP.";
	const MAINTENANCE_MESSAGE = 'Planned system maintenance is taking place - updates will resume shortly';
	const MAINTENANCE_PLANNED = false;

	const LAST_BLOG_POST = "2013-05-22";
	const LAST_ALBUM_UPLOAD = "2013-08-22";

	/** Length in days, during which to consider a blog post/album upload to be new */
	const LENGTH_NEWNESS = 3;

	/** Email addy for contact */
	const EMAIL_CONTACT = 'blr@nw3weather.co.uk';
	/** Email addy for alerts */
	const EMAIL_ALERT = 'alerts@nw3weather.co.uk';

	const FIRST_YEAR_REPORTS = 2009;

	static $sidebar = array(
		'main' => array(
			'Home' => array(
				'title' => 'Return to main page',
				'map' => 'home'
			),
			'Webcam' => array(
				'title' => 'Live Webcam and Timelapses',
				'map' => 'webcam'
			),
			'Graphs' => array(
				'title' => 'Latest Daily and Monthly Graphs &amp; Charts',
				'map' => 'graphs'
			),
			'Data Summary' => array(
				'title' => 'Extremes and Trends, and Averages',
				'map' => 'data'
			),
			'Forecast' => array(
				'title' => 'Local Forecasts and Latest Maps',
				'map' => 'forecast'
			),
			'Astronomy' => array(
				'title' => 'Sun and Moon Data',
				'map' => 'astronomy'
			),
			'Photos' => array(
				'title' => 'My Weather Photography',
				'map' => 'photos',
				'new' => array(
					'name' => 'Upload',
					'last' => LAST_ALBUM_UPLOAD
				)
			),
			'About' => array(
				'title' => 'About the Weather Station and Website',
				'map' => 'about'
			)
		),
		'detail' => array(
			'Rain' => array(
				'title' => 'Detailed Rain Data',
				'map' => 'rain'
			),
			'Temperature' => array(
				'title' => 'Detailed Temperature Data',
				'map' => 'temperature'
			),
			'Wind' => array(
				'title' => 'Detailed Wind Data',
				'map' => 'wind'
			),
			'Humidity' => array(
				'title' => 'Detailed Rain Data',
				'map' => 'humidity'
			),
			'Charts' => array(
				'title' => '31-day and 12-month Data Charts',
				'map' => 'charts'
			),
			'Climate' => array(
				'title' => 'Long-term Climate Averages for NW3',
				'map' => 'climate'
			)
		),
		'historical' => array(
			'Data Tables' => array(
				'title' => 'Tables of monthly and daily data by type',
				'map' => 'datareport'
			),
			'Rankings' => array(
				'title' => 'Daily and monthly ranked data by type',
				'map' => 'ranking_tables'
			),
			'Reports' => array(
				'title' => 'Weather Reports - daily and monthly',
				'map' => 'reports'
			),
			'Graphs' => array(
				'title' => 'Customisable multi-variable line graphs',
				'map' => 'custom_graphs'
			)
		),
		'other' => array(
			'Blog' => array(
				'title' => 'Website and weather station blog and news',
				'map' => 'blog',
				'new' => array(
					'name' => 'Post',
					'last' => LAST_BLOG_POST
				)
			),
			'System' => array(
				'title' => 'System Status and Miscellaneous',
				'map' => 'system'
			),
			'External' => array(
				'title' => 'My Site on the Web and Useful Weather Links',
				'map' => 'external'
			)
		)
	);

}

?>
