<?php
namespace nw3\data;

class Traffic {

	static $annual = [
		2011 => [ // Based on 01 Sep - End
			'sum' => 7000,
			'mean' => 57,
			'median' => 54,
			'min' => null,
			'max' => 240,
			'min_date' => null,
			'max_date' => null
		],
		2012 => [
			'sum' => 49000,
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
		],
		2014 => [
			'sum' => 75000,
			'mean' => 206,
			'median' => 188,
			'min' => 106,
			'max' => 599,
			'min_date' => '06 Sep',
			'max_date' => '14 Feb'
		],
		2015 => [
			'sum' => 84000,
			'mean' => 231,
			'median' => 210,
			'min' => 116,
			'max' => 679,
			'min_date' => '17 Oct',
			'max_date' => '01 Jul'
		],
		2016 => [
			'sum' => 64000,
			'TO' => "24 Sep",
			'mean' => 237,
			'median' => 217,
			'min' => 128,
			'max' => 813,
			'min_date' => '13 Aug',
			'max_date' => '23 Jun'
		]
	];
}
?>