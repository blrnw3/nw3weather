<?php
require 'Page.php';

Page::init(array(
	'fileNum' => 0,
	'title' => 'Data Input',
	'description' => 'Weather data input and modification.'
));
Page::Start();

function dataModEscape($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function dataModSunnyRanges($jsonPath, $returnAsString) {
	if (!is_string($jsonPath) || $jsonPath === '' || !file_exists($jsonPath)) {
		return $returnAsString ? '' : array();
	}
	$data = json_decode(file_get_contents($jsonPath), true);
	if (!is_array($data) || !isset($data['preds']) || !is_array($data['preds'])) {
		return $returnAsString ? '' : array();
	}

	$frequency = isset($data['freq']) ? intval($data['freq']) : 5;
	$predictions = $data['preds'];
	ksort($predictions, SORT_STRING);
	$ranges = array();
	$currentStart = null;
	$previous = null;

	foreach ($predictions as $hhmm => $isSunny) {
		$hhmm = str_pad((string)$hhmm, 4, '0', STR_PAD_LEFT);
		$current = intval(substr($hhmm, 0, 2)) * 60 + intval(substr($hhmm, 2, 2));
		if ($isSunny) {
			if ($currentStart === null) {
				$currentStart = $current;
			} elseif ($previous !== null && $current > $previous + $frequency) {
				$ranges[] = array($currentStart, $previous + $frequency);
				$currentStart = $current;
			}
			$previous = $current;
		} elseif ($currentStart !== null) {
			$ranges[] = array($currentStart, $previous === null ? $current : $previous + $frequency);
			$currentStart = null;
			$previous = null;
		}
	}
	if ($currentStart !== null) {
		$ranges[] = array($currentStart, $previous === null ? $currentStart : $previous + $frequency);
	}

	$formatted = array();
	foreach ($ranges as $range) {
		$start = sprintf('%02d:%02d', floor($range[0] / 60), $range[0] % 60);
		$end = sprintf('%02d:%02d', floor($range[1] / 60), $range[1] % 60);
		$formatted[] = $start . '-' . $end . ' (' . round(($range[1] - $range[0]) / 60, 1) . 'h)';
	}
	return $returnAsString ? implode(', ', $formatted) : $formatted;
}

function dataModWriteCsv($path, $lines, $stripQuotes) {
	$file = fopen($path, 'w');
	if ($file === false) {
		return false;
	}
	foreach ($lines as $line) {
		$row = explode(',', $line);
		if ($stripQuotes) {
			$row = str_ireplace('"', '', $row);
		}
		$last = count($row) - 1;
		$row[$last] = intval($row[$last]);
		fputcsv($file, $row);
	}
	fclose($file);
	return true;
}

if (!Page::$me && !Page::$nw3) {
	echo Page::getStatusDiv('Not allowed from this IP. Admin only!', true);
	Page::End();
	exit;
}

/*
 * Only the metadata used by this editor is defined here. Loading legacy
 * unit-select.php/datfuncdef.php would redeclare ROOT, Date and other v5 names.
 */
$types_original = array(
	'tmin','tmax','tmean','hmin','hmax','hmean','pmin','pmax','pmean',
	'wmean','wmax','gust','wdir','rain','hrmax','10max','ratemax',
	'dmin','dmax','dmean','nightmin','daymax','tc10max','tchrmax','hchrmax',
	'tc10min','tchrmin','hchrmin','w10max','fmin','fmax','fmean','afhrs',
	'aqmin','aqmax','aqmean'
);
$types = array_flip($types_original);
$data_num = array(
	4,4,4,0,0,0,6,6,6,3,3,3,3,2,2,2,2,0,0,0,4,4,4,4,0,4,4,0,3,4,4,4,4,2,2,2
);
$nums_all = $data_num;
$types_m_original = array(
	'sunhr','wethr','cloud','snow','lysnw','hail','thunder','fog',
	'comms','extra','issues','away','pond','spare'
);
$data_m_num = array(8,9,10,-6,-6,-6,-6,-6,-6,-6,-6,-6,4,-6);

$dtm = isset($_GET['dtm']) ? intval($_GET['dtm']) : 1;
if ($dtm < 1) {
	$dtm = 1;
}
$modTimestamp = Date::mkdate(Date::$dmonth, Date::$dday - $dtm, Date::$dyear);
$year = date('Y', $modTimestamp);
$datPath = ROOT . 'dat' . $year . '.csv';
$dattPath = ROOT . 'datt' . $year . '.csv';
$datmPath = ROOT . 'datm' . $year . '.csv';

$sunriseTime = date_sunrise($modTimestamp, SUNFUNCS_RET_STRING, Site::LATITUDE, Site::LONGITUDE, Site::ZENITH, date('I', $modTimestamp));
$sunsetTime = date_sunset($modTimestamp, SUNFUNCS_RET_STRING, Site::LATITUDE, Site::LONGITUDE, Site::ZENITH, date('I', $modTimestamp));
?>

<div class="admin-page">
	<h1>Weather data modification</h1>
	<p>Sunrise: <?php echo dataModEscape($sunriseTime); ?>. Sunset: <?php echo dataModEscape($sunsetTime); ?>.<br />
		<a href="/highreswebcam.php?camtype=sky&amp;light=day&amp;width=6&amp;freq=10">High-resolution camera for today</a>
	</p>

<?php
$datmCheckTime = '0603';
if (date('Hi') < $datmCheckTime) {
	echo Page::getStatusDiv('<b>WARNING: TOO EARLY!</b><br />Cannot make edits until after ' . $datmCheckTime, true);
}

echo '<p><a href="https://www.wunderground.com/history/daily/gb/london/EGLC/date/'
	. dataModEscape(date('Y-n-d', $modTimestamp)) . '">EGLC history</a><br />';
if (is_file($dattPath)) {
	echo 'datt size in B: ' . dataModEscape(filesize($dattPath)) . '<br />';
}
if (!isset($_POST['pwd']) && is_readable(ROOT . 'maxsun.csv')) {
	$maximumSun = file(ROOT . 'maxsun.csv');
	$dayOfYear = intval(date('z', $modTimestamp));
	if (isset($maximumSun[$dayOfYear])) {
		echo 'Max sun for this day: ' . dataModEscape(trim($maximumSun[$dayOfYear])) . ' hours<br />';
	}
}
echo '<a href="datamod.php?dtm=' . $dtm . '">Self link</a></p>';

if (!is_readable($datPath) || !is_readable($dattPath) || !is_readable($datmPath)) {
	echo Page::getStatusDiv('One or more data files are not available.', true);
	echo '</div>';
	Page::End();
	exit;
}

$modData = file($datPath);
$modDataT = file($dattPath);
$modDataM = file($datmPath);
$lineIndex = count($modData) - $dtm;
$lineIndexT = count($modDataT) - $dtm;
$lineIndexM = count($modDataM) - $dtm;
if ($lineIndex < 0 || $lineIndexT < 0 || $lineIndexM < 0
	|| !isset($modData[$lineIndex]) || !isset($modDataT[$lineIndexT]) || !isset($modDataM[$lineIndexM])) {
	echo Page::getStatusDiv('The selected day is outside the available data.', true);
	echo '</div>';
	Page::End();
	exit;
}

$modLine = explode(',', $modData[$lineIndex]);
$modLineT = explode(',', $modDataT[$lineIndexT]);
$modLineM = str_ireplace('?', ',', explode(',', $modDataM[$lineIndexM]));

$missing = array();
for ($i = count($modDataM) - 1; $i >= 0; $i--) {
	if (Util::strContains($modDataM[$i], ',blr,')) {
		$missing[] = count($modDataM) - $i;
	}
}
echo '<p>Missing days (dtm): ';
foreach ($missing as $missingDay) {
	echo '<a href="datamod.php?dtm=' . intval($missingDay) . '">' . intval($missingDay) . '</a>, ';
}
echo '</p>';

if (isset($_POST['pwd'])) {
	if ($_POST['pwd'] == 'datachanges') {
		$dailyLimit = min(count($modLine) - 2, count($types_original));
		for ($i = 0; $i < $dailyLimit; $i++) {
			$name = $types_original[$i];
			if (isset($_POST[$name]) && $_POST[$name] != '') {
				$modLine[$i] = $_POST[$name];
			}
			if (isset($_POST[$name . 't']) && $_POST[$name . 't'] != '') {
				$modLineT[$i] = $_POST[$name . 't'];
			}
		}
		$modData[$lineIndex] = implode(',', $modLine);
		$modDataT[$lineIndexT] = implode(',', $modLineT);

		for ($i = 0; $i < count($types_m_original) && $i < count($modLineM); $i++) {
			$name = $types_m_original[$i];
			if (isset($_POST[$name]) && $_POST[$name] != '') {
				$modLineM[$i] = str_ireplace(',', '?', $_POST[$name]);
			}
		}
		$modDataM[$lineIndexM] = implode(',', $modLineM);

		$saved = dataModWriteCsv($datPath, $modData, false)
			&& dataModWriteCsv($dattPath, $modDataT, false)
			&& dataModWriteCsv($datmPath, $modDataM, true);
		if ($saved) {
			clearstatcache(true, $datPath);
			echo '<p>Saved!</p><p>New file size: ' . dataModEscape(number_format(filesize($datPath))) . ' bytes</p>';
		} else {
			echo Page::getStatusDiv('Server error while saving data.', true);
		}
	} else {
		echo '<p>Password fail.</p>';
	}
}

$flags = array();
if (isset($types['tmin'], $types['nightmin'])
	&& $modLine[$types['tmin']] !== $modLine[$types['nightmin']]) {
	$flags[$types['nightmin']] = true;
}
if (isset($types['tmax'], $types['daymax'])
	&& $modLine[$types['tmax']] !== $modLine[$types['daymax']]) {
	$flags[$types['daymax']] = true;
}
$targetStart = date('Y', $modTimestamp) . '/stitchedmaingraph_';
$targetEnd = date('Ymd', $modTimestamp) . '.png';
?>

	<form method="get" action="">
		<label for="data-day">Days ago:</label>
		<input id="data-day" type="text" name="dtm" value="<?php echo $dtm; ?>" />
		<input type="submit" value="Choose day" />
	</form>
	<p>Viewing <?php echo dataModEscape(date('jS F Y', $modTimestamp)); ?></p>

	<form method="post" action="">
		<div class="table-main">
			<table class="table1" cellpadding="4">
				<tbody>
				<?php
				$dailyRows = min(count($modLine), count($types_original));
				for ($i = 0; $i < $dailyRows; $i++) {
					$rowClass = ($i % 2 === 0) ? 'rowlight' : 'rowdark';
					$cssNum = $nums_all[$i] + 10;
					$flagStyle = isset($flags[$i]) ? ' style="font-weight:bold;font-size:110%;text-decoration:underline;"' : '';
					echo '<tr class="' . $rowClass . '"><td class="td' . $cssNum . 'C">'
						. dataModEscape($types_original[$i]) . '</td><td class="td' . $cssNum . 'C"' . $flagStyle . '>'
						. dataModEscape(isset($modLine[$i]) ? $modLine[$i] : '') . '</td><td><input type="text" name="'
						. dataModEscape($types_original[$i]) . '" /></td><td class="td' . $cssNum . 'C">'
						. dataModEscape(isset($modLineT[$i]) ? $modLineT[$i] : '') . '</td><td><input type="text" name="'
						. dataModEscape($types_original[$i]) . 't" /></td>';
					if ($i === 0) {
						$largeGraph = $targetStart . $targetEnd;
						echo '<td class="td-center" rowspan="' . $dailyRows . '">';
						if (file_exists(ROOT . $largeGraph)) {
							echo '<img src="/' . dataModEscape($largeGraph) . '" alt="day graph" '
								. Site::GRAPH_DIMS_LARGE . ' />';
						} else {
							echo '<h3>FAIL GRAPH BIG!</h3>';
						}
						echo '</td>';
					}
					echo '</tr>';
				}
				?>
				</tbody>
			</table>
		</div>

		<br />
		<div class="table-main">
			<table class="table1" cellpadding="5">
				<tbody>
				<?php
				$rainIndex = $types['rain'];
				$commentDefault = isset($modLine[$rainIndex]) && $modLine[$rainIndex] > 0 ? 'rn' : '-';
				$manualRows = min(count($modLineM), count($types_m_original));
				for ($i = 0; $i < $manualRows; $i++) {
					$rowClass = ($i % 2 === 0) ? 'rowlight' : 'rowdark';
					$cssNum = $data_m_num[$i] + 10;
					$valueAttribute = $types_m_original[$i] === 'comms'
						? ' value="' . dataModEscape($commentDefault) . '"' : '';
					echo '<tr class="' . $rowClass . '"><td class="td' . $cssNum . 'C">'
						. dataModEscape($types_m_original[$i]) . '</td><td class="td' . $cssNum . 'C">'
						. dataModEscape($modLineM[$i]) . '</td><td><input type="text"' . $valueAttribute
						. ' name="' . dataModEscape($types_m_original[$i]) . '" /></td>';
					if ($i === 0) {
						echo '<td class="td-center" rowspan="' . $manualRows . '"><video id="tvid" width="864" height="650" controls="controls">'
							. '<source src="/cam/timelapse/skycam_' . dataModEscape(date('Ymd', $modTimestamp))
							. '.mp4" type="video/mp4" /></video></td>';
					}
					echo '</tr>';
				}
				?>
				</tbody>
			</table>
		</div>
		<p>
			<label for="data-user">User:</label> <input id="data-user" type="text" name="user" />
			<label for="data-password">Password:</label> <input id="data-password" type="password" name="pwd" />
			<input type="hidden" name="dtm" value="<?php echo $dtm; ?>" />
			<input type="submit" value="Submit Changes" />
		</p>
	</form>

<?php
$smallGraph = $targetStart . 'small_' . $targetEnd;
if (!isset($_POST['pwd'])) {
	$sunnyRanges = dataModSunnyRanges(ROOT . 'sun/' . date('Ymd', $modTimestamp) . '.json', true);
	echo '<p>Sunny ranges: ' . ($sunnyRanges === '' ? 'N/A' : dataModEscape($sunnyRanges)) . '</p>';
}
?>

	<img src="/<?php echo dataModEscape(date('Y/Ymd', $modTimestamp)); ?>dailywebcam.jpg" alt="day camera summary" />
	<h2>Rules for manual observations</h2>
	<dl>
		<dt>Snow</dt><dd>y: all snow; 0.1: trace; float: estimated snow amount when there was rain too.</dd>
		<dt>Lying snow</dt><dd>0.1: trace; float: non-trace specified quantity.</dd>
		<dt>Hail</dt><dd>1–3 scale: 1 small, 2 medium, 3 large.</dd>
		<dt>Thunder</dt><dd>1–4 scale: 1 thunder; 2 light, 3 medium, 4 severe thunderstorm.</dd>
		<dt>Fog</dt><dd>Blank or 1.</dd>
	</dl>

<?php
if (file_exists(ROOT . $smallGraph)) {
	echo '<img src="/' . dataModEscape($smallGraph) . '" alt="day graph" ' . Site::GRAPH_DIMS_SMALL . ' />';
} else {
	echo '<h3>FAIL GRAPH SMALL!</h3>';
}
?>
</div>

<?php Page::End(); ?>
