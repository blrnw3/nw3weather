<?php
/**
 * Hand overrides for the "Best webcam shots" gallery on wx2 (WebcamBest.php). Edits show on the next page load.
 *
 * months      calendar month (1-12) => ['Y-m-d HH:MM', 'caption']   all-time best for that month; wins over the
 *             automatic pick. The frame must be from that month. Optional third value: camera ('hik' default).
 * past_month  ['Y-m-d HH:MM', 'caption'] or null                     wins while the frame is within the last 30 days
 * past_year   ['Y-m-d HH:MM', 'caption'] or null                     wins while the frame is within the last 365 days
 * exclude     frames the automatic picks must never use: 'Y-m-d HH:MM', a whole day 'Y-m-d',
 *             or a day range 'Y-m-d..Y-m-d'
 * Times are UK local, as in the archive file names (/camchive/hik/Y/m/d/HHMMhik.jpg).
 */
return array(
	'months' => array(
		1 => array('2023-01-17 10:00', 'Winter cirrus, −3.3 °C'),
		6 => array('2022-06-16 04:30', 'Midsummer sunrise'),
		9 => array('2026-09-15 06:30', 'Sunrise'),
		12 => array('2022-12-15 12:00', 'Snow and blue sky, 5 cm lying'),
	),
	'past_month' => null,
	'past_year' => null,
	'exclude' => array(
		'2019-04-10..2019-06-14', // scaffolding in front of the camera
		'2018-12-30',             // reflections / lights in front of the lens
	),
);
