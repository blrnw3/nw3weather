<?php
require 'Page.php';

Page::init(array(
	'fileNum' => 0,
	'title' => 'Pond Admin',
	'description' => 'Pond data input and modification.'
));
Page::Start();

function pondAdminEscape($value) {
	return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

$lastYear = (int)Date::$yr_yest;
$year = isset($_GET['yr']) ? (int)$_GET['yr'] : $lastYear;
if ($year < 2019 || $year > $lastYear) {
	$year = $lastYear;
}
$filePath = ROOT . 'datm' . $year . '.csv';
?>

<div class="admin-page">
	<h1>Pond temperature data modification</h1>
	<p><b>Instructions:</b> Enter the password, update the values as needed, then click “Submit Changes”.</p>

	<form method="get" action="">
		<label for="pond-year">Year:</label>
		<select id="pond-year" name="yr" onchange="this.form.submit()">
			<?php for ($i = 2019; $i <= $lastYear; $i++): ?>
				<option value="<?php echo $i; ?>"<?php echo $i === $year ? ' selected="selected"' : ''; ?>><?php echo $i; ?></option>
			<?php endfor; ?>
		</select>
		<noscript><input type="submit" value="Choose year" /></noscript>
	</form>

<?php
if (!is_readable($filePath)) {
	echo Page::getStatusDiv('Data file is not available.', true);
} else {
	$datmLines = file($filePath);
	$dayCount = count($datmLines);

	if (isset($_POST['pwd'])) {
		if ($_POST['pwd'] === 'cold') {
			$file = fopen($filePath, 'w');
			if ($file === false) {
				echo Page::getStatusDiv('Server error. Contact Ben.', true);
			} else {
				for ($i = 0; $i < $dayCount; $i++) {
					$oldLine = explode(',', $datmLines[$i]);
					$newValue = isset($_POST['day-' . $i]) ? $_POST['day-' . $i] : '';
					if (is_numeric($newValue)) {
						$oldLine[12] = $newValue;
					} elseif ($i > 0 && $newValue !== '') {
						$dateText = date('d M', Date::mkdate(1, $i, $year));
						echo '<p>Ignored non-numeric value ' . pondAdminEscape($newValue)
							. ' for ' . pondAdminEscape($dateText) . '</p>';
					}
					$lastColumn = count($oldLine) - 1;
					$oldLine[$lastColumn] = 0;
					fputcsv($file, $oldLine);
				}
				fclose($file);
				echo '<p>Saved! <a href="/wxdataday.php?vartype=pond">View updated temperatures</a>'
					. ' (takes up to 1 minute to update).</p>';
			}
		} else {
			echo '<p>Password fail. Try again.</p>';
		}
		echo '<p><a href="pondAdmin.php">Start over</a></p>';
	} else {
?>
	<form method="post" action="">
		<p>
			<label for="pond-password">Password:</label>
			<input id="pond-password" type="password" name="pwd" />
			<input type="submit" value="Submit Changes" />
		</p>
		<div class="table-main">
			<table class="table1" cellpadding="2">
				<tbody>
				<?php
				for ($i = $dayCount - 1; $i > 0; $i--) {
					$line = explode(',', $datmLines[$i]);
					$value = isset($line[12]) ? $line[12] : '';
					$rowClass = ($i % 2 === 0) ? 'rowlight' : 'rowdark';
					echo '<tr class="' . $rowClass . '"><td class="td10C">'
						. pondAdminEscape(date('d M', Date::mkdate(1, $i, $year)))
						. '</td><td><input type="text" name="day-' . $i
						. '" value="' . pondAdminEscape($value) . '" /></td></tr>';
				}
				?>
				</tbody>
			</table>
		</div>
	</form>
<?php
	}
}
?>
</div>

<?php Page::End(); ?>
