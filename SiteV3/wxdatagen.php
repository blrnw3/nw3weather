<?php
$year = isset($_SESSION['year']) ? $_SESSION['year'] : $yr_yest;
$month = isset($_SESSION['month']) ? $_SESSION['month'] : 0;

$ranknumOptions = array(5,10,15,20,25,50,100);
$rankLimit = isset($_SESSION['rankLimit']) ? $_SESSION['rankLimit'] : 20;
if(!in_array($rankLimit, $ranknumOptions)) {
	$rankLimit = 25;
}

if(isset($_SESSION['vartype']) && in_array($_SESSION['vartype'], $types_alltogether)) {
	$type = $_SESSION['vartype'];
} else {
	$type = 'rain';
}

if($showMonth && $month > 0) {
	$datgenHeading .= ' for ' . date('F', mkdate($month, 1));
}

$typeNum = $types_all[$type];
$hasToday = !(in_array($type, $types_m_original) || in_array($type, ['sunhra','sunhrp', 'wethra', 'wethrp']));
$typeconvNum = $typeconvs_all[$typeNum];
$isSum = $sumq_all[$typeNum];
$isAnom = $anomq_all[$typeNum];
$typevalcolNum = $wxtablecols_all[$typeNum];
$description = $descriptions_all[$types_all[$type]];
$isNotSummarisable = in_array($type, array('wdir', 'cloud', 'hail', 'thunder'));

$CVARS = array( $typeNum, $hasToday, $typeconvNum, $isSum, $isAnom, $typevalcolNum, $description, $isNotSummarisable);
class CurrVar {
	public $typeNum;
	public $hasToday;
	public $typeconvNum;
	public $isSum;
	public $isAnom;
	public $typevalcolNum;
	public $description;
	public $isNotSummarisable;

	function __construct($vars) {
		$this->typeNum = $vars[0];
		$this->hasToday = $vars[1];
		$this->typeconvNum = $vars[2];
		$this->isSum = $vars[3];
		$this->isAnom = $vars[4];
		$this->typevalcolNum = $vars[5];
		$this->description = $vars[6];
		$this->isNotSummarisable = $vars[7];
	}
}


//Logic for cycling of drop-downs
$catNum = array_search($type, $flatCats);
$wxvarCount = count($flatCats);
$prevType = $types_all[$flatCats[mod( $catNum-1, $wxvarCount)]];
$nextType = $types_all[$flatCats[($catNum+1) % $wxvarCount]];
$prevYear = mod( $year-1-$startYear, $dyear - $startYear+1 ) + $startYear;
$nextYear = ( ($year+1-$startYear) % ($dyear - $startYear+1) ) + $startYear;
$prevMonth = ($month == 0) ? 12 : mod( $month-1, 12 + 1 );
$nextMonth = $month % 12 + 1;
//echo "<br />CAT NUM: $catNum<br />";

$currfilea = explode('/',$_SERVER['PHP_SELF']);
$currfile = $currfilea[count($currfilea)-1];
$currfildat = substr($currfile,-6,6);
$currfilname = str_replace($currfildat, '', $currfile);

//valcol function setup
$tempvals = array(-5,0,5, 10,15,20, 25,30,35);
$humivals = array(30,40,50, 60,70,80, 90,97);
$presvals = array(970,980,990, 1000,1010,1015, 1020,1030,1040);
$windvals = array(1,2,4, 7,10,15, 20,30,40);
$degrvals = array(45,90,135, 180,225,270, 315);
$rainvals = array(0,0.1,0.6, 1,2,5, 10,15,20, 25,50);
$rtmxvals = array(0.1,1,2, 3,5,10, 30,60,100, 150,300);
$tchgvals = array(0.3,0.6,1, 1.5,2,2.5, 3,4,5);
$hchgvals = array(2,5,10, 15,20,30, 40,50);
$prngvals = array(1,2,3, 5,7,10, 15,20,25);
$dhrsvals = array(0,0.3,0.5, 1,2,3, 5,7,9, 12,15);
$pmaxvals = array(0,10,20, 25,35,50, 65,75,85, 90,95);
$tanmvals = array(-10,-5, -2,0,2, 5,10,15,20);
$percvals = array(25,50,75, 90,100,110, 125,150,175, 200,250);

if($imperial) {
	$tempvals = array(10,20,30, 40,50,60, 70,80,90);
	$presvals = array(28.5,28.75,29, 29.25,29.5,29.75, 30,30.25,30.5);
	$rainvals = array(0,0.004,0.02, 0.04,0.08,0.2, 0.4,0.6,0.8, 1,2);
	$rtmxvals = array(0.04,0.08,0.2, 0.4,0.8,1.2, 2,3,4, 6,10);
	$prngvals = array(0.03,0.06,0.1, 0.15,0.17,0.2, 0.25,0.3,0.35);
}
elseif($metric) {
	$windvals = array(2,3,5, 10,15,20, 30,45,70);
}

$valcol = array($tempvals,$humivals,$presvals, $windvals,$degrvals,$rainvals, $rtmxvals,$tchgvals, //7
	$hchgvals, $prngvals,$dhrsvals,$pmaxvals,$tanmvals,$percvals); //13
$col_descrip = array('temp','humi','press', 'wind','degr','rain', 'rtmax','tchg',
	'hchg','prng','dhrs','dhrs','temp','dhrs');

$valcolSumOffset = 250 / $valcol[$typevalcolNum][count($valcol[$typevalcolNum])-1];

echo '<h1>'. $datgenHeading .' - ', $description,
	 ' / ', $std_units[ $units_all[$types_all[$type]] ], '<br /></h1>';

$valcolConvert = !in_array($type, array('hail', 'thunder', 'wdir')); //don't convert textual values before entering valcol function

//Main form for data type (and year)
$isDailyC1 = $isDaily ? disableHTML: '';
$isDailyC2 = !$isDaily ? disableHTML : '';
echo '<div style="padding:10px">
	<form action="./'.$linkToOther.'.php">
		<input type="submit" value="Daily" '.$isDailyC1.' style="padding:0.4em" />
	</form>
	<form action="./'.$linkToOther.'.php">
		<input type="submit" value="Monthly" ' .$isDailyC2.' style="padding:0.4em" />
	</form>
	<span class="test" style="padding-left:20px;padding-right:4px;">Weather Variable:</span>
	<form method="get" action="">';
	echo '<select name="vartype" onchange="this.form.submit()">
	';
//Weather variable form
foreach ($categories as $cat => $subCats) {
	echo '<optgroup label="'.$cat.'">';
	foreach ($subCats as $subCat) {
		if(!in_array($subCat, $badCats) || $subCat === $type) { //only show bad categories if selected
			$i = $types_all[$subCat];
			$selected = ($type == $subCat) ? selectHTML : '';
			echo '<option value="'. $subCat .'" '. $selected .'>'. $descriptions_all[$i] .'
				</option>';
		}
	}
	echo '</optgroup>';
}
echo '</select>';
	dropdownCycle(false, "year=$year&vartype=". $types_alltogether[$prevType], $descriptions_all[$prevType] );
dropdownCycle(true, "year=$year&vartype=". $types_alltogether[$nextType], $descriptions_all[$nextType] );

if($showYear) {
	echo '<span style="padding-left:25px;padding-right:3px;" class="rep">Year</span>';
	dropdownCycle(false, 'year='. $prevYear ."&vartype=$type", $prevYear );
	echo '<select name="year" onchange="this.form.submit()">';
	for($i = $startYear; $i <= $dyear; $i++) {
		echo '<option value="' . $i . '"';
		if($i == $year) { echo ' selected="selected"'; }
		echo '>', $i, '</option>
			';
	}
	echo '</select>';
	dropdownCycle(true, 'year='. $nextYear ."&vartype=$type", $nextYear );
}
if($showMonth) {
	echo '<span style="padding-left:25px;padding-right:3px;" class="rep">Month</span>';
	dropdownCycle(false, 'month='. $prevMonth ."&vartype=$type", $months[$prevMonth-1] );
	echo '<select name="month" onchange="this.form.submit()">
		<option value="0" ' . $isallCheck . '>All</option>';
	for($i = 1; $i <= 12; $i++) {
		echo '<option value="' . $i . '"';
		if($i == $month) { echo ' selected="selected"'; }
		echo '>', $months3[$i-1], '</option>
			';
	}
	echo '</select>';
	dropdownCycle(true, 'month='. $nextMonth ."&vartype=$type", $months[$nextMonth-1] );
}
if($showNum) {
	echo '<span style="padding-left:25px" class="rep">Limit</span>
		<select name="rankLimit" onchange="this.form.submit()">';
	for($i = 0; $i < count($ranknumOptions); $i++) {
		echo '<option value="' . $ranknumOptions[$i] . '"';
		if($ranknumOptions[$i] == $rankLimit) { echo ' selected="selected"'; }
		echo '>', $ranknumOptions[$i], '</option>
			';
	}
	echo '</select>';
}
echo '</form>';

echo '</div>
	<a name="start"> </a>';


function valcolr($value, $num, $countable = false) {
	global $valcol, $col_descrip;
	$values = $countable ? array(0,1,3, 5,7,10, 15,20,25, 30,31) : $valcol[$num];
	$level_type = $col_descrip[$num];

	for($i = 0; $i < count($values); $i++){
		if($value <= $values[$i]) { return 'level'.$level_type.'_'.$i;	}
	}
	return 'level'.$level_type.'_'.$i;
}

function finalConv($val, $type) {
	if($type == 'hail') {
		return hailname($val);
	} else {
		return thundername($val);
	}
}

function yr_togg($value, $year) {
	global $hide, $m, $r;
	if($hide == 1) { echo '<b>', $value, '</b><br />', $year, '</a>'; }
	else { echo '<acronym style="border-bottom-width: 0" title="',$year,'">
	',	$value, '</a>'; }
}

function rankTable($values, $dates, $conv, $colour, $absfix, $rankNum, $title, $alignLeft, $showToday, $showFoot, $isDaily = true, $sumfix = 1, $isCount = false) {
	$align = $alignLeft ? "left" : "center";
	table("table1", '49%" align="'.$align);
	tableHead($title);

	if($isCount) {
		$conv = false;
	}

	tr();
	td("Rank");
	td("Value");
	td("Date");
	tr_end();

	for($i = 1; $i <= $rankNum; $i++) {
		tr( "row". colcol($i) );
		td($i);
		td( conv($values[$i], $conv, false), valcolr( conv($values[$i] / $sumfix, $conv, false, false, false, $absfix), $colour, $isCount) );
		td( $dates[$i] );
		tr_end();
	}

	if($showFoot) {
		if($isDaily) {
			$today = 'Today';
			$yest = 'Yesterday';
		} else {
			$today = 'Current Month';
			$yest = 'Last Month';
		}
		if($showToday) {
			tr('tblfoot" style="border-top:3px solid #6F7;');
			td($dates['today']);
			td( conv($values['today'], $conv, false), valcolr( conv($values['today'] / $sumfix, $conv, false, false, false, $absfix), $colour, $isCount) );
			td($today);
			tr_end();

			$yestRank = $dates['yest'];
			$yestVal = $values['yest'];
		} else {
			$yestRank = $dates['today'];
			$yestVal = $values['today'];
		}
		tr("tblfoot");
		td($yestRank);
		td( conv($yestVal, $conv, false), valcolr( conv($yestVal / $sumfix, $conv, false, false, false, $absfix), $colour, $isCount) );
		td($yest);
		tr_end();
	}

	table_end();
}
?>
