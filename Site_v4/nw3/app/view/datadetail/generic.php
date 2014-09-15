<?php
$var = new nw3\app\api\Generic($this->generic_var);

$this->viewette('datadetail_main', [
	'var' => $var,
	'title' => $this->generic_var
]);
?>

<img src="../../graph/daily/<?php echo $this->generic_var ?>" alt="Daily mean humi last 31 days" />
<img src="../../graph/monthly/<?php echo $this->generic_var ?>" alt="Monthly mean humi last 12 months" />

<p>
	<a href="../datareport?type=<?php echo $this->generic_var ?>" title="<?php echo D_year; ?>daily pressure">
		<b>View daily min/max/mean [xxx] for the past year</b>
	</a>
</p>

<!--<img id="now_graph" src="../graph/liveauto/dewp" alt="Last 24hrs Dew Point" />-->

<h2>Notes</h2>
<ul>
	<li>Generic records began on 1st Feb 2009</li>
	<li>Figures in brackets refer to departure from
		<a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a>
	</li>
	<li>All figures, unless specified, relate to the period midnight-midnight, this being when daily extremes are reset.</li>
</ul>
