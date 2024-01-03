<?php
$allDataNeeded = true;
require('unit-select.php'); ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<?php
	$file = 40.1;
	$linkToOther = 'wxdataday';
	$needValcolStyle = true;
	$datgenHeading = 'Monthly Data Tables';
?>

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>

<title>NW3 Weather - Monthly Data Tables</title>

<meta name="description" content="Detailed historical monthly summary data reports with all-time summary." />

<?php require('chead.php'); ?>
<?php include('ggltrack.php') ?>
</head>

<body>
	<!-- ##### Header ##### -->
	<?php require('header.php'); ?>
	<!-- ##### Left Sidebar ##### -->
	<?php require('leftsidebar.php'); ?>

	<!-- ##### Main Copy ##### -->
	<div id="main">

<?php require('wxdatagen.php');

if($type == 'wdir' || $type == 'cloud') {
	echo "<p>$description has no meaningfull monthly summary.</p></div>";
	require('footer.php');
	echo '</body></html>';
	die();
}

class MonthlySummaryDataTables {

	const edgeStyle = "border-left: 5px solid #565";
	private $DAT;
	private $CVAR;
	private $valcolSumOffset;
	private $CLIMATE;
	private $climateConvType;
	private $climateMapping;
	private $countable;

	function __construct($data, $cvar, $valcolSumOffset, $climData, $climMap) {
		$this->DAT = $data;
		$this->valcolSumOffset = $valcolSumOffset;
		$this->CVAR = new CurrVar($cvar);
		$this->CLIMATE = new Climate($climMap, $climData);

		$this->climateConvType = ($this->CVAR->typeconvNum == 1) ? 1.1 : $this->CVAR->typeconvNum;
		$this->climateMapping = $this->CLIMATE->mapping[$this->CVAR->typeNum];
	}

	/**
	 *
	 * @param enum $t table type (0: min, 1: max; 2: mean, 3: count)
	 * @param string $heading table heading
	 */
	public function makeTable($t, $heading) {
		global $yr_yest, $mon_yest;

		$isAnom = $this->CVAR->isAnom;
		$sumfix = ($this->CVAR->isSum && $t === 2) ? $this->valcolSumOffset : 1;
		$isCount = ($t === 3);
		$this->countable = $isCount;

		table('table1" style="margin-bottom: 15px;', null, 6);
		tableHead($heading . ' ' . $this->CVAR->description, 14);

		tr();
		td('Month', 'td4C', '7%');
		for ($m = 1; $m <= 12; $m++) {
			td(DateConsts::$months3[$m - 1], 'td4C', '7%');
		}
		td('Year', 'td4" style="' . self::edgeStyle, '9%');
		tr_end();

		for ($y = date('Y'); $y >= DateConsts::$startYear; $y--) {
			tr(null);
			td("<a href='./wxdataday.php?year=$y' title='View full data for year'>$y</a>", 'row' . colcol($y) . '" style="text-align:center; font-weight:bold');

			for ($m = 1; $m <= 12; $m++) {
				$cnt = count($this->DAT[$y][$m]);

				//initalise to default for month not arrived
				$anom = '';
				$value = '-';
				$class = 'reportday';

				if ($cnt >= 1) { //month arrived
					$val = mom($this->DAT[$y][$m], $t);

					if (isBlank($val)) {
						$value = '';
						$class = 'invalid';
					} else {
						$extreme[$y][$m] = $val;
						$extreme[$m][$y] = $val;

						if ($this->CVAR->isSum && $t === 2) { //convert mean to sum and amend valuecolour setting
							$extreme[$y][$m] *= $cnt;
							$extreme[$m][$y] *= $cnt;
						}

						$value = $isCount ? $extreme[$m][$y] : conv($extreme[$m][$y], $this->CVAR->typeconvNum, false);
						$class = isClassless($value) ? 'invalid' : self::getColourClass($extreme[$m][$y] / $sumfix);

						$anom = ($isAnom && $t === 2) ?
							'<br />(' . self::anomMonth($extreme[$m][$y], $m - 1) . ')' : '';
					}
				}
				$lnk = "/wxhistmonth.php?year=$y&month=$m";
				$linked_val = ($y < $yr_yest || $m <= $mon_yest) ? '<a class="hidden-link" href="'. $lnk .'" title="View detailed report for month">'. $value .'</a>' : $value;
				td($linked_val . $anom, $class);
			}

			//Annual summary
			$yrMin[$y] = $isCount ? mean($extreme[$y]) : mom($extreme[$y], $t);
			if ($this->CVAR->isSum && $t >= 2) { //convert mean to sum and amend valuecolour setting
				$cntM = count($extreme[$y]);
				$yrMin[$y] *= $cntM;
			} else {
				$cntM = 1;
			}
			$anom = ($isAnom && $t === 2) ?
				'<br />(' . self::anomYear($yrMin[$y]) . ')' : '';

			$valyr = $isCount ? $yrMin[$y] : conv($yrMin[$y], $this->CVAR->typeconvNum, false);
			td($valyr . $anom, self::getColourClass($yrMin[$y] / $sumfix / $cntM) . '" style="' . self::edgeStyle);
			tr_end();
		}

		//Monthly summary
		for($mm = 0; $mm < 3; $mm++) {
			$extra = ($mm === 0) ? '" style="border-top:10px solid #cdc' : '';
			$mmmedgeStyle = ($mm === 0) ? "; " : '" style="';
			tr(null);
			td(GenConsts::$lhm[$mm], 'reportttl'.$extra );

			for($m = 1; $m <= 12; $m++) {
				$extremeM = mom($extreme[$m], $mm);
				$value = $isCount ? conv($extremeM, 9, false) : conv( $extremeM, $this->CVAR->typeconvNum, false );
					$anom = ($isAnom && $t === 2) ?
						'<br />('. self::anomMonth($extremeM, $m-1) .')' : '';
				$class = self::getColourClass($extremeM / $sumfix);
				td($value . $anom, $class . $extra);
			}

			$yrMinM = mom($yrMin, $mm);
			$valall = $isCount ? conv($yrMinM, 9, false) : conv( $yrMinM, $this->CVAR->typeconvNum, false );
				$anom = ($isAnom && $t === 2) ?
							'<br />('. self::anomYear($yrMinM) .')' : '';
			td($valall . $anom, self::getColourClass($yrMinM / $sumfix / $cntM) . $extra . $mmmedgeStyle . self::edgeStyle );
			tr_end();
		}

		table_end();
	}

	private function getColourClass($val) {
		return valcolr($val, $this->CVAR->typevalcolNum, $this->countable);
	}

	private function anomMonth($value, $month) {
		$climateVal = $this->CLIMATE->monthlyData[$this->climateMapping][$month];
		if($this->CVAR->isSum) {
			return percent( $value, $climateVal, 0, true, false );
		}
		return conv($value - $climateVal, $this->climateConvType, false, true);
	}

	private function anomYear($value) {
		if($this->CVAR->isSum) {
			return percent( $value, $this->CLIMATE->annualSums[$this->climateMapping], 0, true, false );
		}
		return conv($value - $this->CLIMATE->annualAvs[$this->climateMapping], $this->climateConvType, false, true);
	}

}

if($isSum) {
	$lhmFull[2] = 'Total';
}

$DAT = varNumToDatArray($typeNum);

$tables = new MonthlySummaryDataTables($DAT, $CVARS, $valcolSumOffset, $vars, $maptoClimavs);

if(!$isNotSummarisable && $type != 'fog') { // mean and count are indistinguisable for fog (binary daily quantity)
	$tables->makeTable(2, 'Monthly-'. $lhmFull[2]);
}
if($isSum) {
	$tables->makeTable(3, "Days of (non-zero)");
}
if(!$isSum) {
	$tables->makeTable(0, "Lowest");
}
$tables->makeTable(1, "Highest");

echo '<p>';
echo $description .' data for every available month in the weather station history, along with overall
	(lowest, highest, and mean), and annual (min/max or mean) summaries.
	<br />';
if($isAnom) {
	echo 'Figures in brackets refer to departure from
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>';
	if($endyr == $dyear) {
		echo " (note that the anomaly for the current month is unadjusted for the month's degree of completeness)";
	}
	echo '.';
}
echo '</p>';

function isClassless($val) {
	return isBlank($val) || $val === 'null';
}
?>
	</div>

	<!-- ##### Footer ##### -->
	<?php require('footer.php'); ?>

</body>
</html>