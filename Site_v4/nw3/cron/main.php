<?php
namespace nw3\cron;

use nw3\app\model\Day;
use nw3\app\util\File;
use nw3\app\util\Date;
use nw3\app\model\Store;

class Main implements \nw3\app\core\Cron {

	public function execute() {
		$day = new Day();
		$dat24 = $day->summary(Day::LATEST);
		File::live_data(Store::DAT24_NAME, serialize($dat24), true);
		var_dump($dat24);
//		$datday = $day->summary(Date::mkdate(3, 18, 2013));
//		File::live_data('datday.sphp', serialize($datday), true);
//		var_dump($datday);
	}

}


?>
