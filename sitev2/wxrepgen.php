<?php
ini_set('display_errors', 0); 
error_reporting(E_ALL & ~E_NOTICE);

function yr_togg ($value, $year) {
	global $hide, $m, $r;
	if($hide == 1) { echo '<b>', $value, '</b><br />', $year, '</a>'; }
	else { echo '<acronym style="border-bottom-width: 0" title="',$year,'">
	',	$value, '</a>'; }
}
?>

<table align="center" cellpadding="3"><tr><td align="center" class="test">Report Type</td></tr>
	<tr><td align="center">
		<form name="wxhist" action="wxhist<?php echo $file-40; ?>.php">
			<input type="submit" value="Detail" <?php if($summary != 1 && $record != 1 && $average != 1 && $ranking != 1) { echo 'disabled="disabled"'; } ?> />
		</form>
		<form name="wxsumhist" action="wxsumhist<?php echo $file-40; ?>.php">
			<input type="submit" value="Summary" <?php if($summary == 1) { echo 'disabled="disabled"'; } ?> />
		</form>
		<?if($me != 1) { echo '<!--'; } ?>
		<form name="rechist" action="rechist<?php echo $file-40; ?>.php">
			<input type="submit" value="Records" <?php if($record == 1) { echo 'disabled="disabled"'; } ?> />
		</form>
		<form name="avhist" action="avhist<?php echo $file-40; ?>.php">
			<input type="submit" value="Averages" <?php if($average == 1) { echo 'disabled="disabled"'; } ?> />
		</form>
		<form name="rankhist" action="rankhist<?php echo $file-40; ?>.php">
			<input type="submit" value="Rankings" <?php if($ranking == 1) { echo 'disabled="disabled"'; } ?> />
		</form>
		<?if($me != 1) { echo '-->'; } ?>
		</td>
	</tr>
	
	<tr><td align="center">
		<? $data_type = array('Rain', 'Temperature', 'Wind', 'Dew Point', 'Humidity', 'Pressure');
			$dt_file_no = array(52,54,53,50,50.5,40);
		for ($i = 0; $i < count($data_type); $i++) {
			echo '<form name="hist',$dt_file_no[$i]-40,'" action="';
			if($summary == 1) { echo 'wxsum'; } elseif($record == 1) { echo 'rec'; }
			elseif($average == 1) { echo 'av'; } elseif($ranking == 1) { echo 'rank'; } else { echo 'wx'; } 
			echo 'hist', $dt_file_no[$i]-40, '.php">
			<input type="submit" value="', $data_type[$i], '"';
			if($file == $dt_file_no[$i]) { echo ' disabled="disabled"'; }
			echo '/></form>';
		} ?>
	</td></tr>
</table>

<?php if($summary == 1 || $record == 1 || $average == 1 || $ranking == 1) { echo '<!--'; $skip_repyr = 1; } ?>
<br /><table align="center" cellpadding="3"><tr><td align="center" class="rep">Report Year</td></tr>
<tr><td align="center">
<form method="get" action="<?php echo $SITE['self']; ?>" >
	<?php 
		for($i = 2009; $i <= $date_year; $i++) {
			echo '<input name="year" type="submit" value="' . $i . '"';
			if($i == $year) { echo ' disabled="disabled"'; }
			echo ' />';
		} 
	?>
</form></td></tr></table>
<?php if($skip_repyr == 1) { echo '-->'; } ?>
<br />

<a name="start"> </a>