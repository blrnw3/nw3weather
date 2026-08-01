<?php
use nw3\app\helper\Main;

?>

<h1>Possible API calls</h1>

<p>
	All output is in JSON
</p>

<ul>
<?php foreach ($this->calls as $call): ?>
	<li>
		<a href="<?php echo HTML_ROOT; ?>api/<?php echo $this->base_api . $call ?>"><?php echo $call ?></a>
	</li>
<?php endforeach; ?>
</ul>
