<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\model\Detail;
use nw3\app\model\Store;
use nw3\app\util\String;
use Config;

/**
 * API stuff
 * @author Ben LR
 */
class Api extends core\Controller {

	private $do_raw = false;

	public function __construct($path) {
		parent::__construct(__CLASS__, $path);
		Detail::initialise();
	}

	public function index() {
		$non_calls = ['validate_arg', 'subpath', 'index'];
		$calls = [];

		$api_reflect = new \ReflectionClass('nw3\app\controller\api');
		$public = $api_reflect->getMethods(\ReflectionMethod::IS_PUBLIC);

		foreach ($public as $pubcall) {
			if($pubcall->class === 'nw3\app\controller\Api'
				&& !String::starts_with($pubcall->name, '__')
				&& !in_array($pubcall->name, $non_calls)
			) {
				$calls[] = $pubcall->name;
			}
		}

		$api_calls_raw = scandir(Config::ROOT . 'nw3\app\api');
		foreach ($api_calls_raw as $raw_call) {
			if(String::contains($raw_call, 'php')) {
				$bits = explode('.', $raw_call);
				$calls[] = $bits[0];
			}
		}
		sort($calls);
		$this->calls = $calls;
		$this->base_api = '';
		$this->build('API');
		$this->render();
	}

	public function now() {
		$dat = get_object_vars(Store::g());
		unset($dat['today']);
		unset($dat['hr24']);
		$this->json($dat);
	}
	public function today() {
		$dat = Store::g()->today->json();
		$this->json($dat);
	}
	public function hr24() {
		$dat = Store::g()->hr24->json();
		$this->json($dat);
	}

	public function validate_arg($path) {
		$api_class = 'nw3\app\api\\'. $path;
		try {
			class_exists($api_class);
		} catch (\LogicException $e) {
			# TODO - do a 404 but still call index() in some way...
			$this->redirect('api');
		}
		$api_reflect = new \ReflectionClass($api_class);
		if ($api_reflect->isAbstract()) {
			$this->redirect('api');
		}

		$call = $this->sub_path(1);
		$poss_calls = array_filter($api_reflect->getMethods(\ReflectionMethod::IS_PUBLIC),
			function($c){return !String::starts_with($c->name, '__');});
		if($this->valid_call($call, $poss_calls)) {
			$api = $api_reflect->newInstance();
			$dat = $api->{$call}();
			$this->json($dat);
		} else {
			$calls = [];
			foreach ($poss_calls as $pc) {
				$calls[] = $pc->name;
			}
			sort($calls);
			$this->do_raw = true;
			$this->calls = $calls;
			$this->base_api = $path .'/';
		}
		return true;
	}

	public function subpath($path) {
		if($this->do_raw) {
			$this->build('API', null, 'index');
			$this->render();
		}
	}

	private function valid_call($call, $poss_calls) {
		if(!$call) {
			return false;
		}
		foreach ($poss_calls as &$pc) {
			if($call === $pc->name) {
				return true;
			}
		}
		return false;
	}

}

?>
