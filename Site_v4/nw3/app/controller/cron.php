<?php
namespace nw3\app\controller;

class Cron {

	public function __construct($path) {
		require __DIR__.'/../../cron/'. $path[0] .'.php';
	}

}

?>
