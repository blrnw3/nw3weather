<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\migrate;

/**
 * Admin tasks
 *
 * @author Ben LR
 */
class Admin extends core\Controller {

	public function __construct($path) {
		parent::__construct(__CLASS__, $path);
	}

	public function index() {
		die('Unimplemented');
	}

	public function css() {
		$this->raw('css');
	}

	public function cron() {
		$cron_class = 'nw3\cron\\'. $_GET['path'];
		try {
			class_exists($cron_class);
			$abstract_cron = new \ReflectionClass($cron_class);
			$cron = $abstract_cron->newInstance();
			$cron->execute();
		} catch (\LogicException $e) {
			var_dump($e->getTrace());
			die("No such cron path - $cron_class - exists");
		}
	}

	public function migrate() {
		if($_GET['script'] === 'daily') {
			$migration = new migrate\Importdailylogs($_GET['start_date'], $_GET['end_date']);
			if($_GET['type'] === 'migrate') {
				$migration->migrate($this->timer);
			} elseif($_GET['type'] === 'sanitise') {
				$migration->sanitise_raw_logs();
			} else {
				$migration->validate_raw_logs();
			}
		} elseif($_GET['script'] === 'wd') {
			$wd_parser = new migrate\Wdlogstodaily($_GET['start_date'], $_GET['end_date']);
			$wd_parser->parse();
		} else {
			die('Not a valid script');
		}
	}

	public function windfix() {
		$test = new migrate\Importdailylogs($_GET['start_date'], $_GET['end_date']);
		$test->legacy_wind_speed_fix();
	}
	public function logdiff() {
		$test = new migrate\Importdailylogs($_GET['start_date'], $_GET['end_date']);
		$test->raw_log_diff();
	}
	public function deformat() {
		$test = new migrate\Importdailylogs($_GET['start_date'], $_GET['end_date']);
		$test->deformat();
	}

}

?>
