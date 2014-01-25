<?php
namespace nw3\app\model;

/**
 * jpgraph
 *
 * @author Ben LR
 */
class Graph extends \Graph {

	const FOOTER_COLOUR = '#555';
	const FOOTER_COLOUR_LIGHT = '#888';
	const FOOTER_COPYRIGHT = 'nw3weather.co.uk';

	public function __construct($timer = null) {
		$x = isset($_GET['x']) ? $_GET['x'] : 850;
		$y = isset($_GET['y']) ? $_GET['y'] : 400;

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
	}

	public function set_title($title) {
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

}

?>
