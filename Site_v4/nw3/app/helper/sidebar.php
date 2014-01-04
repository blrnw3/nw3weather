<?php
namespace nw3\app\helper;

use nw3\config\Admin;

/**
 * Sidebar generation helper
 * @author Ben LR
 */
class Sidebar {

	private $page;
	private $subfile = false;

	function __construct($page, $subfile) {
		$this->page = $page;
		$this->subfile = $subfile;
	}

	function group($items_name) {
		foreach (Admin::$sidebar[$items_name] as $name => $item) {
			$active = ($this->page === $item['map']);

			$new = $item['new'];
			if($new && D_now - $new['last'] < Admin::LENGTH_NEWNESS) {
				$name .= ' <sup title="Last '. $new['name'] .': '. date('jS M Y'. $new['last']) . '" style="color:#382">new</sup>';
			}

			$class = $active ? ' class="curr"' : '';
			echo "<li$class>";

			if (!($active && !$this->subfile)) { //need link
				echo '<a href="'. \Config::HTML_ROOT . $item['map']. '/" title="'. $item['title']. '">'. $name. '</a>
					';
			} else {
				echo $name;
			}
			echo '</li>';
		}
	}

	function subheading($title, $colour) {
		echo '<li><hr /></li>
			<li><span class="sideBarText" style="color:#'.$colour.'">'.$title.'</span></li>
		';
	}
}

?>
