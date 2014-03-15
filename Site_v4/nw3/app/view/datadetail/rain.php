<?php
use nw3\app\util\Html;

?>

<h1>Detailed Rainfall Data</h1>

<table>
	<caption>Current / Latest</caption>
	<thead>
		<tr>
			<td>Measure</td>
			<td>Value</td>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td>Daily Rain (00-00)</td>
			<td><?php echo $this->live->rain ?></td>
		</tr>
		<tr>
			<td>Rain Last 10 mins</td>
			<td><?php echo $this->live->rain ?></td>
		</tr>
	</tbody>
</table>