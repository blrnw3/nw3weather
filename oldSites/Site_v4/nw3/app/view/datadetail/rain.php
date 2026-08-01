<?php
$var = new nw3\app\api\Rain();

$this->viewette('datadetail_main', [
	'var' => $var,
	'title' => 'Rain'
]);
//TODO
//$show_now_graph = ($recent['rn24hr'] > 0);
?>


<img src="../graph/daily/rain" alt="Daily rain totals last 31 days" />
<img src="../graph/monthly/rain" alt="Monthly rain totals last 12 months" />

<p>
	<a href="../datareport?vartype=rain" title="<?php echo D_year; ?>daily rain totals">
		<b>View daily totals for the past year</b>
	</a>
</p>

<?php if($show_now_graph): ?>
	<img id="now_graph" src="../graph/liveauto/rain" alt="Last 24hrs Rainfall" />
<?php endif; ?>

<p>
	<b>Note 1:</b> Rain records began in February 2009<br />
	<b>Note 2:</b> The minimum recordable rain (the rain gauge resolution) is 0.2 mm<br />
	<b>Note 3:</b> Figures in brackets refer to departure from <a href="wxaverages.php" title="Long-term NW3 climate averages">average conditions</a><br />
	<b>Note 4:</b> Rain rate records are manually checked, and changed if necessary,
	due to occasional issues with the software. Initial high readings may well be corrected at a later date.<br />
</p>

