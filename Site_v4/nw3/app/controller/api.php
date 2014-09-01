<?php
namespace nw3\app\controller;

use nw3\app\core;
use nw3\app\model\Detail;
use nw3\app\util\Html;
use nw3\app\util\String;

/**
 * API stuff
 * @author Ben LR
 */
class Api extends core\Controller {

	public function __construct($path) {
		parent::__construct(__CLASS__, $path);
		Detail::initialise();
	}

	public function index() {
		foreach(get_declared_classes() as $cls) {
			Html::out($cls);
		}
	}

	public function validate_arg($path) {
		$api_class = 'nw3\app\api\\'. $path;
		try {
			class_exists($api_class);
		} catch (\LogicException $e) {
			return $this->invalid_subpath($path, 'No such class');
		}
		$api_reflect = new \ReflectionClass($api_class);
		if ($api_reflect->isAbstract()) {
			return $this->invalid_subpath($path, 'Class is abstract. Dummy');
		}

		$call = $this->sub_path(1);
		$poss_calls = array_filter($api_reflect->getMethods(\ReflectionMethod::IS_PUBLIC),
			function($c){return !String::starts_with($c->name, '__');});
		if($this->valid_call($call, $poss_calls)) {
			$api = $api_reflect->newInstance();
			$dat = $api->{$call}();
			$this->json($dat);
		} else {
			$resp = String::isBlank($call) ? 'Possible calls...': "Could not find call '$call'";
			Html::out($resp);
			Html::out('Try one of:');
			foreach($poss_calls as $method) {
				Html::out("<a href='./$method->name'>$method->name</a>");
			}
		}
		return true;
	}

	public function subpath($path) {
//		echo $path;
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
