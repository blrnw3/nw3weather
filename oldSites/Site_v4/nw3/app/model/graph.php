<?php
namespace nw3\app\model;

use nw3\app\util\Date;

/**
 * jpgraph
 *
 * @author Ben LR
 */
abstract class Graph extends \Graph {

	const FOOTER_COLOUR = '#555';
	const FOOTER_COLOUR_LIGHT = '#888';
	const FOOTER_COPYRIGHT = 'nw3weather.co.uk';

	const LABELS_DATE_MONTHS = 'lab_dat_months';

	private static $REQUIRED = '__required_opt__';

	private $opts;
	protected $title_from_data;

	public function __construct($opts, $timer=null) {
		$this->opts = $opts;
		$x = $this->optget('x', 850);
		$y = $this->optget('y', 400);

		if($timer !== null) {
			//Non-graphing time
			$timer->stop();
			$pre_graphing_time = $timer->executionTimeMs();
			//Graph rendering time
			$gtimer = new \JpgTimer();
			$gtimer->Push();
		}

		parent::__construct($x, $y);

		if($timer !== null) {
			$this->footer->right->Set($pre_graphing_time .' + ');
			$this->footer->SetTimer($gtimer, 'ms rendering');
		}

		$this->setup();
		$this->Stroke();
	}

	abstract function set_data($data);

	private function setup() {
		$this->SetScale($this->optget('scale', 'textint'));
		$this->set_labelsx();
		$this->SetTickDensity(TICKD_SPARSE);

		$this->set_data($this->optget('data', self::$REQUIRED));

		$this->set_title();

		$this->set_footer($this->optget('footer', 'missing footer'));

		$this->set_legend();
	}

	private function set_labelsx() {
		$labels = $this->optget('labelsx');
		if($labels === self::LABELS_DATE_MONTHS) {
			$labels = Date::$months3;
		}
		$this->xaxis->SetTickLabels($labels);

	}

	public function set_title() {
		$title = $this->optget('title', 'Graph');
		if($this->title_from_data) {
			$title .= $this->title_from_data;
		}
		$this->title->Set($title);

		$this->title->SetFont(FF_FONT1, FS_BOLD);
//		$this->yaxis->title->SetFont(FF_FONT1, FS_BOLD);
//		$this->xaxis->title->SetFont(FF_FONT1, FS_BOLD);
	}

	public function set_footer($footer) {
		//Main footer
		$this->footer->center->Set($footer);
		//Copyright
		$this->footer->left->Set(\SymChar::Get('copy') .' '. self::FOOTER_COPYRIGHT);

		$this->footer->center->SetColor(self::FOOTER_COLOUR);
		$this->footer->right->SetColor(self::FOOTER_COLOUR_LIGHT);
		$this->footer->left->SetColor(self::FOOTER_COLOUR_LIGHT);
	}

	public function set_legend() {
		//See theme classes (e.g. UniversalTheme.class.php, the default)
	}

	/**
	 * Get for opts
	 */
	protected function optget($key, $default=null) {
		if(isset($this->opts[$key])) {
			return $this->opts[$key];
		}
		if($default !== self::$REQUIRED) {
			return $default;
		}
		throw new \Exception("Option '$key' is required");
	}
	/**
	 * Like Python's dict.get, for opts
	 * @param type $dict
	 * @param type $key
	 * @param type $default
	 * @return type
	 */
	protected function get(&$dict, $key, $default=null) {
		return isset($dict[$key]) ? $dict[$key] : $default;
	}

}

?>
