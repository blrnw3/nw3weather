<?php
require('unit-select.php');
	 ?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>NW3 Weather - Pond Admin</title>

	<meta name="description" content="Pond data input/mod" />
	<?php require('chead.php'); ?>
	<?php include('ggltrack.php'); ?>
</head>

<body>
	<?php require('header.php'); ?>
	<?php require('leftsidebar.php'); ?>

<div id="main">

	<h1>Pond temperature data modification</h1>

	<p style="margin:1em 3em;">
		<b>Instructions</b>: Enter the password, update the values as needed, then click 'submit changes'.
	</p>

<?php

if(isset($_GET['yr'])) { $yr = (int)$_GET['yr']; } else { $yr = $yr_yest; }

echo '<form method="get" action="">';
echo 'Year: <select name="yr" onchange="this.form.submit()">';
for($i = 2019; $i <= $yr_yest; $i++) {
	echo '<option value="' . $i . '"';
	if($i == $yr) { echo ' selected="selected"'; }
	echo '>', $i, '</option>';
}
echo '</select></form>';

$filepath = $fullpath."datm$yr.csv";
$datm_lines = file($filepath);
$day_count = count($datm_lines);

if(isset($_POST['pwd'])) {
	if($_POST['pwd'] === 'cold') {
		$fildatm = fopen($filepath, "w");
		if($fildatm === false) {
			echo("server error. contact Ben");
			die("pond_err");
		}
		for($i = 0; $i < $day_count; $i++) {
			$old_line = split(',', $datm_lines[$i]);
			$new_val = $_POST["day-$i"];
			if(is_numeric($new_val)) {
				$old_line[12] = $new_val;
			} elseif($i > 0 && $new_val !== '') {
					$dt = date('d M', mkdate(1, $i, $yr));
					echo "<p>Ignored non-numeric value $new_val for $dt</p>";
			}
			$old_line[count($old_line) - 1] = 0; // Last val in row 'spare' cannot be newline char
			fputcsv($fildatm, $old_line);
		}
		fclose($fildatm);
		echo '<p>Saved! <a href="http://nw3weather.co.uk/wxdataday.php?vartype=pond">View updated temps</a> (takes up to 1 minute to update)</p>';
	}
	else {
		echo 'password fail. try again';
	}
	echo '<p><a href="/pondAdmin.php">Start over</a></p>';
} else {
	//Form
	echo '<form style="margin:1em 2em;" method="post" action="">
		Password: <input type="password" name="pwd" />
		<input type="submit" value="Submit Changes" />
		<table style="margin:1em 3em;" border="1" cellpadding="2">';
	for($i = $day_count-1; $i > 0; $i--) {
		$line = split(',', $datm_lines[$i]);
		if($i % 2 == 0) { $style = 'light'; } else { $style = 'dark'; }
		echo '<tr class="row', $style ,'"><td class="td10C">', date('d M', mkdate(1, $i, $yr)), '</td>
			<td><input type="text" name="day-', $i, '" value="'. $line[12] .'" /> </td>';
		echo '</tr>';
	}
	echo '</table></form>';
}
?>
</div>

<!-- ##### Footer ##### -->
<?php require('footer.php'); ?>
</body>
</html>
