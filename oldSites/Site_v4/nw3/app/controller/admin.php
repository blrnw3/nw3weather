<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\migrate;
use nw3\app\util\String;
use nw3\app\util\Http;

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
		$path = $this->sub_path(1);
		$cron_class = 'nw3\cron\\'. $path;
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
		$script = $this->url_args[1];
		if($script === 'daily') {
			$type = $this->sub_path(2);
			$migration = new migrate\Importdailylogs($_GET['start_date'], $_GET['end_date']);
			if($type === 'migrate') {
				$migration->migrate($this->timer);
			} elseif($type === 'sanitise') {
				$migration->sanitise_raw_logs();
			} else {
				$migration->validate_raw_logs();
			}
		} elseif($script === 'wd') {
			$this->check_correct_subpath_length(2);
			$wd_parser = new migrate\Wdlogstodaily($_GET['start_date'], $_GET['end_date']);
			$wd_parser->parse();
		} elseif($script === 'monthly') {
			$yr = (int)$this->sub_path(2);
			$migration = new migrate\Importmonthlylogs();
			if($_GET['upto']) {
				$migration->import_upto($yr);
			} else {
				$migration->import($yr);
			}
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
