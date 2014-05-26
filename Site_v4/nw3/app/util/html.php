<?php
namespace nw3\app\util;

/**
 *
 * @author Ben LR
 */
class Html {

	const check = ' checked="checked"';
	const disable = ' disabled="disabled"';
	const select = ' selected="selected"';

	static function href($path) {
		echo \Config::HTML_ROOT . $path .'/';
	}

	 static function tr_() {
		 echo '</tr>';
	 }

	 static function table_() {
		 echo '</table>';
	 }

	 static function print_m($var) {
		echo '<pre>';
		var_dump($var);
		echo '</pre>';
	}

	static function out($str) {
		echo $str . '<br />';
	}

	/**
	 * Make tooltip
	 * TODO: do this properly
	 * @param type $title
	 * @param type $value
	 * @param type $show
	 * @return string
	 */
	static function tip($title, $value, $show = false) {
		$tag = '<acronym style="border-bottom'; if($show) { $tag .= ': 1px dotted'; } else { $tag .= '-width: 0'; }
		$tag .= '" title="' . $title. '">'. $value. '</acronym>';
		return $tag;
	}

	/**
	 * makes an opening html table tag. Null-pass enabled.
	 * @param string $class [= table1]
	 * @param string $width [=99%]
	 * @param number $cellpadding [=3]
	 * @param bool $centre [=false] position table centrally (auto margin)
	 * @param number $cellspacing [=0]
	 */
	static function table( $class = null, $width = null, $cellpadding = null, $centre = false, $cellspacing = 0 ) {
		if(is_null($class)) { $class = 'table1'; }
		if(is_null($width)) { $width = '99%'; }
		if(is_null($cellpadding)) { $cellpadding = 3; }
		$centrality = $centre ? 'style="margin:auto;"' : '';
		echo '<table '.$centrality.' class="'.$class.'" width="'.$width.'" cellpadding="'.$cellpadding.'" cellspacing="'.$cellspacing.'">
			';
	}
	static function table_end() {
		echo '</table>
			';
	}

	/**
	 * makes a table row with set class. Pass null to give straight &lt;tr&gt;
	 * @param type $class [=table-top]
	 */
	static function tr( $class = 'table-top' ) {
		$class2 = ( !is_null($class) ) ? ' class="'.$class.'"' : '';
		echo '<tr'.$class2.'>
		';
	}
	static function tr_end() {
		echo '</tr>
			';
	}

	/**
	 * make table data cell
	 * @param type $value
	 * @param string $class [=null] defaults to td4
	 * @param int $width in percent [=false]
	 * @param int $colspan [=false]
	 * @param int $rowspan [=false]
	 */
	static function td($value, $class = null, $width = false, $colspan = false, $rowspan = false) {
		if(is_null($class)) { $class = 'td4'; }
		if($width) { $wid = ' width="'.$width.'%"'; }
		if($colspan) { $csp = ' colspan="'.$colspan.'"'; }
		if($rowspan) { $rsp = ' rowspan="'.$rowspan.'"'; }
		echo '<td class="'.$class.'"'.$wid.$csp.$rsp.'>'.$value.'</td>
			';
	}

	static function tableHead($text, $colspan = 3) {
		echo '<tr class="table-head"><td class="td12" style="padding:0.5em" colspan="'.$colspan.'">'.$text.'</td></tr>
				';
	}

	/**
	 * Produces an html img, with optional surrounding anchor<br />
	 * Null-pass enabled.
	 * @param string $src required
	 * @param string $title [=null] doubles-up as alt
	 * @param string $class [=null] class name(s)
	 * @param string $extras [=null] any other attributes
	 * @return void echoes the well-formed xhtml
	 */
	static function img($src, $title = null, $class = null, $extras = null) {
		$_alt = ($title === null) ? 'image' : $title;
		$_title = ($title === null) ? '' : " title='$title' ";
		$_class = ($title === null) ? '' : " class='$class' ";
		$mores = ($extras === null) ? '' : $extras;
		echo "<img src='". ASSET_PATH ."img/$src' alt='$_alt' $_class $_title $mores />";
	}

	/**
	 * For anchors to internal documents only (e.g. xls)
	 * @param type $path
	 * @param type $content
	 * @param type $_title
	 */
	static function a($path, $content, $_title=null) {
		$title = ($_title === null) ? '' : " title='$_title' ";
		echo "<a href='". ASSET_PATH ."doc/$path' $title>$content</a>" ;
	}

	/**
	 * Light or Dark alternating odd/even
	 * @param type $i
	 * @return type light or dark
	 */
	static function colcol($i) {
		return ($i % 2 == 0) ? 'light' : 'dark';
	}

	/**
	 * Produces an html form for inputting year, month and, optionally, day
	 * @param int $yproc
	 * @param int $mproc
	 * @param int $dproc [= 0] leave blank if only month/year selector
	 */
	static function dateFormMaker($yproc, $mproc, $dproc = 0) {
		global $dyear, $months;
		echo '<select name="year">';
		for($i = 2009; $i <= $dyear; $i++) {
			$selected = ($i == $yproc) ? 'selected="selected"' : '';
			echo '<option value="', $i, '" ', $selected, '>', $i, '</option>
				';
		}
		echo '</select>
			<select name="month">';
		for($i = 1; $i <= 12; $i++) {
			$selected = ($i == $mproc) ? 'selected="selected"' : '';
			echo '<option value="', $i, '" ', $selected, '>', $months[$i-1], '</option>
				';
		}

		if($dproc) {
			echo '</select>
				<select name="day">';
			for($i = 1; $i <= 31; $i++) {
				$selected = ($i == $dproc) ? 'selected="selected"' : '';
				echo '<option value="', $i, '" ', $selected, '>', zerolead($i), '</option>
					';
			}
		}
		echo '</select>';
	}
}

?>
