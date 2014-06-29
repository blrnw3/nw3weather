<?php
namespace nw3\data;

class Traffic {

	static $annual = [
		2011 => [ //less accurate for this year due to partial reliance on awstats
			'sum' => 12000,
			'mean' => 33,
			'median' => null,
			'min' => 5,
			'max' => 240,
			'min_date' => '13 Jan',
			'max_date' => '09 Oct'
		],
		2012 => [
			'sum' => 48000,
			'mean' => 133,
			'median' => 127,
			'min' => 39,
			'max' => 667,
			'min_date' => '10 Jan',
			'max_date' => '04 Feb'
		],
		2013 => [
			'sum' => 63000,
			'mean' => 174,
			'median' => 152,
			'min' => 67,
			'max' => 1562,
			'min_date' => '03 Sep',
			'max_date' => '18 Jan'
		]
	];
}
?>